<?php

namespace App\Enums;

/**
 * ============================================================================
 *  ErrorTolerance — Enum (PHP 8.1+)
 * ============================================================================
 *
 * PEDAGOGIE : un "enum" (enumeration) est un type qui n'accepte qu'un nombre
 * fini de valeurs nommees. C'est plus sur qu'une simple chaine de caracteres
 * ('none', 'verbose', 'block', ...) car PHP refuse toute valeur qui n'est
 * pas dans la liste : impossible de se tromper en ecrivant "NON" au lieu
 * de "NONE" sans provoquer une erreur immediate.
 *
 * Ici, on definit comment le CsvImportService doit reagir quand une ligne
 * du fichier CSV est invalide (colonne manquante, email mal forme, etc.) :
 *
 *   - NONE    : on ignore silencieusement les erreurs, on importe ce qui
 *               est valide et on saute les lignes en erreur sans rien dire.
 *   - VERBOSE : comme NONE, mais on collecte le detail de chaque erreur
 *               dans un rapport pour l'afficher a l'utilisateur a la fin.
 *   - BLOCK   : la moindre ligne invalide arrete tout l'import (rien n'est
 *               insere en base) -- utile pour des imports "tout ou rien".
 *
 * UTILISATION :
 *   $tolerance = ErrorTolerance::VERBOSE;
 *   if ($tolerance === ErrorTolerance::BLOCK) { ... }
 *
 *   // Recuperer un enum depuis une string (ex: valeur d'un <select> HTML) :
 *   $tolerance = ErrorTolerance::from($this->request->getPost('tolerance'));
 *   // ->from() leve une erreur si la valeur est inconnue.
 *   // ->tryFrom() renvoie null au lieu de lever une erreur (plus permissif).
 * ============================================================================
 */
enum ErrorTolerance: string
{
    // Le ": string" ci-dessus signifie que chaque cas a une valeur string
    // sous-jacente (utile pour la stocker en base, dans un <select>, etc.)

    case NONE    = 'none';
    case VERBOSE = 'verbose';
    case BLOCK   = 'block';

    /**
     * Methode "helper" : renvoie un libelle lisible pour l'humain (affiche
     * dans les <select> des vues). On peut ajouter des methodes a un enum
     * comme sur une classe normale.
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE    => 'Ignorer les erreurs silencieusement',
            self::VERBOSE => 'Ignorer les erreurs mais generer un rapport',
            self::BLOCK   => 'Bloquer tout import si une erreur est detectee',
        };
    }
}
