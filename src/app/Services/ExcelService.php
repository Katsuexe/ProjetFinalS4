<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ============================================================================
 *  ExcelService — lire et écrire des fichiers .xlsx avec PhpSpreadsheet
 * ============================================================================
 *
 * PEDAGOGIE — pourquoi PhpSpreadsheet et pas fgetcsv() comme pour le CSV ?
 * ----------------------------------------------------------------------------
 * Un fichier .xlsx n'est PAS un fichier texte : c'est en réalité une archive
 * ZIP contenant plusieurs fichiers XML (feuilles, styles, formules...).
 * On ne peut donc pas le lire ligne par ligne avec fopen()/fgetcsv() comme
 * un CSV. La bibliotheque PhpSpreadsheet sait decoder ce format (et aussi
 * .xls, .ods, .csv en bonus) et l'expose comme un tableau de cellules
 * facile a manipuler.
 *
 * INSTALLATION (une seule fois, avec Composer) :
 *     composer require phpoffice/phpspreadsheet
 *
 * Comme pour Dompdf, le vendor/ n'est pas fourni dans ce zip pedagogique
 * (pas d'acces reseau a packagist.org dans cet environnement de generation) :
 * executez la commande ci-dessus sur votre poste avant de tester ce service.
 * ============================================================================
 */
class ExcelService
{
    /**
     * Lit un fichier Excel et renvoie son contenu sous forme de tableau
     * de tableaux (une entree par ligne), la premiere ligne etant l'en-tete.
     *
     * @param string $filePath Chemin absolu vers le fichier .xlsx uploadé
     * @return array<int, array<string, mixed>> Ex: [ ['username'=>'jdupont','email'=>'...'], ... ]
     */
    public function readAsAssoc(string $filePath): array
    {
        // IOFactory::load() détecte automatiquement le format du fichier
        // (xlsx, xls, csv, ods...) grâce à sa signature binaire, et renvoie
        // un objet Spreadsheet qui représente le classeur complet.
        $spreadsheet = IOFactory::load($filePath);

        // getActiveSheet() : on ne travaille que sur la 1re feuille de calcul.
        // Pour un classeur multi-feuilles : $spreadsheet->getSheet(1), etc.
        $sheet = $spreadsheet->getActiveSheet();

        // toArray() convertit toute la feuille en tableau PHP indexé
        // [ligne][colonne] => valeur. Chaque cellule vide devient null.
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        // La 1re ligne du tableau contient les en-têtes de colonnes.
        $header = array_map(static fn ($v) => trim((string) $v), array_shift($rows));

        $result = [];
        foreach ($rows as $row) {
            // On saute les lignes totalement vides (souvent en fin de fichier).
            if (implode('', $row) === '') {
                continue;
            }
            // array_combine associe chaque en-tête à sa valeur de colonne,
            // comme dans CsvImportService::validateRow().
            $result[] = array_combine($header, $row);
        }

        return $result;
    }

    /**
     * Génère un fichier .xlsx à partir d'un tableau de données et le
     * renvoie sous forme de chaîne binaire (à envoyer ensuite au navigateur
     * par le contrôleur, voir ImportController::exportUsersExcel()).
     *
     * @param string[]                     $headers Libellés des colonnes (ligne 1)
     * @param array<int, array<int, mixed>> $rows    Données, une ligne = un tableau de valeurs
     * @param string                        $sheetTitle Nom de l'onglet Excel
     */
    public function buildXlsx(array $headers, array $rows, string $sheetTitle = 'Export'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        // --- Ligne d'en-tête (ligne 1, colonnes A, B, C...) ---------------------
        // fromArray() écrit un tableau PHP d'un coup à partir d'une cellule
        // donnée (ici A1), sans boucle manuelle cellule par cellule.
        $sheet->fromArray($headers, null, 'A1');

        // Mise en forme simple : en-tête en gras.
        $lastColumn = $sheet->getHighestColumn(); // ex: 'D'
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        // --- Lignes de données (à partir de la ligne 2) -------------------------
        $sheet->fromArray($rows, null, 'A2');

        // Ajuste automatiquement la largeur des colonnes au contenu.
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Sérialisation en mémoire (sans écrire de fichier sur le disque) ----
        // IOFactory::createWriter() choisit le format d'écriture (ici Xlsx).
        // On écrit dans un flux mémoire (php://memory) plutôt que sur le
        // disque, puis on récupère son contenu comme une simple string —
        // pratique pour un téléchargement direct sans fichier temporaire.
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $stream = fopen('php://memory', 'r+');
        $writer->save($stream);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }
}
