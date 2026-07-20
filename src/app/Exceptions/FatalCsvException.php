<?php

namespace App\Exceptions;

/**
 * ============================================================================
 *  FatalCsvException
 * ============================================================================
 * PEDAGOGIE : contrairement a NonFatalCsvException (une ligne en erreur),
 * cette exception "fatale" arrete TOUT l'import immediatement :
 *
 *   - fichier illisible / introuvable
 *   - en-tetes (colonnes) manquantes ou dans le mauvais ordre
 *   - mode ErrorTolerance::BLOCK actif et une ligne est invalide
 *     (une seule ligne fausse => on annule TOUT, y compris les lignes deja
 *     valides, pour garder une base de donnees coherente)
 *
 * Quand cette exception est levee, le controleur (ImportController) doit
 * l'attraper avec un try/catch, annuler la transaction SQL en cours
 * (voir $this->db->transRollback() dans CsvImportService) et afficher un
 * message d'erreur clair a l'utilisateur SANS rien inserer en base.
 * ============================================================================
 */
class FatalCsvException extends \Exception
{
    // Aucune propriete supplementaire necessaire ici : le message hérité
    // de \Exception suffit a decrire le probleme (ex: "Colonne 'email'
    // manquante dans l'en-tete du fichier CSV").
}
