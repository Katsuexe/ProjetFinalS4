<?php

namespace App\Exceptions;

/**
 * ============================================================================
 *  NonFatalCsvException
 * ============================================================================
 * PEDAGOGIE : une exception "non fatale" represente une erreur qui concerne
 * UNE seule ligne du CSV (ex : email invalide a la ligne 12) mais qui ne doit
 * PAS empecher le traitement du reste du fichier.
 *
 * Le CsvImportService "catch" (attrape) cette exception ligne par ligne,
 * l'ajoute a un tableau de rapport, puis continue la boucle sur la ligne
 * suivante -- sauf si le mode ErrorTolerance::BLOCK est actif, auquel cas
 * on la relance comme une erreur bloquante (voir CsvImportService).
 *
 * On etend la classe native \Exception de PHP : on herite gratuitement de
 * getMessage(), getCode(), getLine(), getFile(), etc. On ajoute juste une
 * propriete maison : le numero de ligne CSV concernee (different de
 * getLine() qui donne la ligne du fichier PHP, pas du CSV !).
 * ============================================================================
 */
class NonFatalCsvException extends \Exception
{
    /** Numero de la ligne du fichier CSV qui a provoque l'erreur (1-indexe, hors en-tete). */
    protected int $csvLine;

    public function __construct(string $message, int $csvLine, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->csvLine = $csvLine;
    }

    public function getCsvLine(): int
    {
        return $this->csvLine;
    }
}
