<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * ============================================================================
 *  PdfService — génération de PDF avec Dompdf
 * ============================================================================
 *
 * PEDAGOGIE — comment fonctionne Dompdf ?
 * ----------------------------------------------------------------------------
 * Dompdf n'est PAS un moteur "code -> PDF" : c'est un moteur "HTML+CSS -> PDF".
 * On lui donne une chaine HTML (generalement produite par une VUE CodeIgniter,
 * comme n'importe quelle page web), et Dompdf la "dessine" dans un document
 * PDF, un peu comme un navigateur qui imprimerait la page.
 *
 * ==> Consequence pratique : pour creer un PDF, on cree une vue CodeIgniter
 *     NORMALE (du HTML), on la rend en string avec view(), puis on la passe
 *     a ce service. On NE fait PAS de $this->response->setBody() habituel.
 *
 * INSTALLATION (a faire une fois, en local, avec Composer) :
 *     composer require dompdf/dompdf
 *
 * Le reseau de cet environnement de demonstration ne permet pas d'aller sur
 * packagist.org, donc le vendor/ n'est pas fourni dans ce zip pedagogique :
 * il faudra lancer la commande ci-dessus sur votre machine avant de tester.
 *
 * LIMITES DE DOMPDF (a connaitre) :
 *   - CSS supporte : proche de CSS 2.1 + une partie de CSS3 (pas de Flexbox
 *     complet, pas de Grid). Prefere des mises en page en <table> pour les
 *     PDF complexes (factures, releves...).
 *   - Les polices "web-safe" (DejaVu Sans, etc.) sont embarquees par defaut ;
 *     pour une police personnalisee, voir Options::setFontDir().
 *   - Pas de JavaScript execute (les scripts sont ignores).
 * ============================================================================
 */
class PdfService
{
    protected Dompdf $dompdf;

    public function __construct()
    {
        // Options de configuration de Dompdf.
        $options = new Options();

        // isRemoteEnabled : autorise le chargement d'images/CSS via une URL
        // distante (http://...) dans le HTML fourni. A activer seulement si
        // necessaire (risque de securite / lenteur si active a tort).
        $options->set('isRemoteEnabled', true);

        // isHtml5ParserEnabled : utilise un parseur HTML plus tolerant
        // (recommande, gere mieux les balises mal fermees).
        $options->set('isHtml5ParserEnabled', true);

        // defaultFont : police utilisee si aucune n'est precisee en CSS.
        $options->set('defaultFont', 'DejaVu Sans');

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Génère un PDF à partir d'une vue CodeIgniter et le renvoie en téléchargement
     * (ou affichage direct dans le navigateur si $attachment = false).
     *
     * @param string $viewName   Nom de la vue CI4 (ex: 'admin/pdf/user_report')
     * @param array  $data       Données passées à la vue, comme avec view()
     * @param string $filename   Nom du fichier PDF proposé au téléchargement
     * @param string $paper      Format papier: 'a4', 'letter', ...
     * @param string $orientation 'portrait' ou 'landscape'
     */
    public function renderView(
        string $viewName,
        array $data = [],
        string $filename = 'document.pdf',
        string $paper = 'a4',
        string $orientation = 'portrait'
    ): string {
        // On genere le HTML exactement comme pour une page web normale :
        // la fonction view() de CodeIgniter charge app/Views/{$viewName}.php
        // et y injecte les variables de $data.
        $html = view($viewName, $data);

        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper, $orientation);

        // render() effectue le calcul de mise en page (equivalent du rendu
        // d'une page par un navigateur avant impression).
        $this->dompdf->render();

        // output() renvoie le contenu binaire du fichier PDF (une string).
        // On le renvoie tel quel : c'est le CONTROLEUR qui decidera de
        // l'envoyer au navigateur (voir ImportController::exportReportPdf()).
        return $this->dompdf->output();
    }
}
