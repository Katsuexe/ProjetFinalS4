<?php

namespace App\Services;

use App\Enums\ErrorTolerance;
use App\Exceptions\FatalCsvException;
use App\Exceptions\NonFatalCsvException;
use App\Models\UserModel;

/**
 * ============================================================================
 *  CsvImportService — importer un fichier .csv d'utilisateurs en base
 * ============================================================================
 *
 * PEDAGOGIE — pourquoi un "Service" et pas juste du code dans le contrôleur ?
 * ----------------------------------------------------------------------------
 * Dans CodeIgniter (et en MVC en general), le CONTROLEUR ne doit faire que
 * 3 choses : (1) lire la requete HTTP, (2) appeler la logique metier,
 * (3) renvoyer une reponse (vue ou redirection). Toute la "logique metier"
 * complexe (parsing CSV, validation, écriture en base...) est deleguee a
 * une classe de SERVICE independante. Avantages :
 *   - le controleur reste court et lisible
 *   - le service est testable seul (PHPUnit) sans simuler une requete HTTP
 *   - le service est reutilisable (ex: appelable aussi depuis une commande
 *     CLI `php spark import:csv fichier.csv`)
 *
 * FORMAT ATTENDU DU CSV (avec en-tete sur la 1re ligne) :
 *   username,email,password,id_type
 *   jdupont,jean.dupont@mail.com,Passw0rd!,2
 *   marie,marie@mail.com,Azerty123,2
 *
 * ============================================================================
 */
class CsvImportService
{
    protected UserModel $userModel;

    public function __construct()
    {
        // On instancie le modele directement ici (pas d'injection de
        // dependances "magique" dans ce starter pedagogique -- en
        // production on pourrait passer par le conteneur de services CI4,
        // voir app/Config/Services.php).
        $this->userModel = new UserModel();
    }

    /**
     * Importe un fichier CSV d'utilisateurs.
     *
     * @param string         $filePath  Chemin absolu vers le fichier CSV
     *                                  (ex: le fichier temporaire uploade,
     *                                  voir $file->getTempName() dans le
     *                                  controleur).
     * @param ErrorTolerance $tolerance Comportement a adopter face aux
     *                                  lignes en erreur (voir l'enum).
     *
     * @return array{
     *     inserted: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     *
     * @throws FatalCsvException si le fichier est illisible, l'en-tete est
     *                           invalide, ou (en mode BLOCK) une ligne est
     *                           en erreur.
     */
    public function import(string $filePath, ErrorTolerance $tolerance): array
    {
        // 1) Ouverture du fichier ------------------------------------------------
        // fopen() renvoie un "handle" (pointeur) sur le fichier, ou false si echec.
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new FatalCsvException("Impossible d'ouvrir le fichier CSV.");
        }

        // 2) Lecture de l'en-tete -------------------------------------------------
        // fgetcsv() lit UNE ligne du fichier et la decoupe automatiquement selon
        // le separateur (',' par defaut). Elle renvoie un tableau de colonnes.
        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            fclose($handle);
            throw new FatalCsvException('Le fichier CSV est vide.');
        }

        // On nettoie les espaces eventuels autour de chaque nom de colonne.
        $header = array_map('trim', $header);

        $expected = ['username', 'email', 'password', 'id_type'];
        $missing  = array_diff($expected, $header);
        if (! empty($missing)) {
            fclose($handle);
            throw new FatalCsvException(
                "Colonnes manquantes dans l'en-tete du CSV : " . implode(', ', $missing)
            );
        }

        // 3) Lecture ligne par ligne ----------------------------------------------
        $inserted     = 0;
        $skipped      = 0;
        $errors       = [];   // rapport rempli uniquement en mode VERBOSE
        $rowsToInsert = [];   // utilise pour l'insertion groupee en mode BLOCK
        $csvLineNo    = 1;    // ligne 1 = en-tete, donc les donnees commencent a 2

        // On demarre une TRANSACTION SQL : soit toutes les lignes sont
        // inserees, soit aucune (rollback) en cas d'erreur bloquante.
        // C'est le mecanisme standard pour garantir la coherence des donnees.
        $this->userModel->db->transStart();

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $csvLineNo++;

            // On ignore les lignes vides (ex: derniere ligne du fichier)
            if ($row === [null] || $row === false) {
                continue;
            }

            try {
                $data = $this->validateRow($header, $row, $csvLineNo);

                if ($tolerance === ErrorTolerance::BLOCK) {
                    // En mode BLOCK, on accumule d'abord toutes les lignes
                    // valides ; on ne les insere qu'a la toute fin, une fois
                    // certain qu'AUCUNE ligne n'a echoue.
                    $rowsToInsert[] = $data;
                } else {
                    // Modes NONE / VERBOSE : insertion immediate, ligne par ligne.
                    $this->userModel->insert($data);
                    $inserted++;
                }
            } catch (NonFatalCsvException $e) {
                if ($tolerance === ErrorTolerance::BLOCK) {
                    // En mode BLOCK, une erreur "non fatale" devient fatale :
                    // on annule tout et on remonte l'erreur au controleur.
                    $this->userModel->db->transRollback();
                    fclose($handle);
                    throw new FatalCsvException(
                        "Import annule (mode strict) : ligne {$e->getCsvLine()} — {$e->getMessage()}"
                    );
                }

                // Modes NONE / VERBOSE : on saute la ligne et on continue.
                $skipped++;
                if ($tolerance === ErrorTolerance::VERBOSE) {
                    $errors[] = "Ligne {$e->getCsvLine()} : {$e->getMessage()}";
                }
            }
        }

        fclose($handle);

        // 4) Insertion groupee pour le mode BLOCK ---------------------------------
        if ($tolerance === ErrorTolerance::BLOCK && ! empty($rowsToInsert)) {
            foreach ($rowsToInsert as $data) {
                $this->userModel->insert($data);
                $inserted++;
            }
        }

        // On valide (COMMIT) la transaction : les donnees sont ecrites pour de bon.
        $this->userModel->db->transComplete();

        return [
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    /**
     * Valide une ligne du CSV et la transforme en tableau pret pour
     * UserModel::insert(). Leve une NonFatalCsvException si la ligne
     * est invalide (email mal forme, colonne vide, etc.)
     *
     * @param string[] $header    Noms de colonnes (ligne 1 du CSV)
     * @param string[] $row       Valeurs de la ligne courante
     * @param int      $csvLineNo Numero de ligne (pour le message d'erreur)
     */
    protected function validateRow(array $header, array $row, int $csvLineNo): array
    {
        if (count($row) !== count($header)) {
            throw new NonFatalCsvException(
                'Nombre de colonnes incorrect (attendu ' . count($header) . ', recu ' . count($row) . ')',
                $csvLineNo
            );
        }

        // array_combine() associe chaque nom de colonne a sa valeur :
        // ['username','email',...] + ['jdupont','jean@mail.com',...]
        //   => ['username' => 'jdupont', 'email' => 'jean@mail.com', ...]
        $data = array_combine($header, array_map('trim', $row));

        if ($data['username'] === '') {
            throw new NonFatalCsvException("Le nom d'utilisateur est vide.", $csvLineNo);
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new NonFatalCsvException("Email invalide : '{$data['email']}'.", $csvLineNo);
        }

        if (strlen($data['password']) < 6) {
            throw new NonFatalCsvException('Mot de passe trop court (6 caracteres minimum).', $csvLineNo);
        }

        if (! ctype_digit((string) $data['id_type'])) {
            throw new NonFatalCsvException("id_type doit etre un nombre entier, recu '{$data['id_type']}'.", $csvLineNo);
        }

        // On ne stocke jamais un mot de passe en clair : on le hashe
        // avec la meme methode que le formulaire d'inscription (Auth::registerProcess).
        return [
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $this->userModel->hashPassword($data['password']),
            'id_type'  => (int) $data['id_type'],
            'is_active'=> 1,
        ];
    }
}
