<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Enums\ErrorTolerance;
use App\Exceptions\FatalCsvException;
use App\Libraries\PdfService;
use App\Models\UserModel;
use App\Services\CsvImportService;
use App\Services\ExcelService;

/**
 * ============================================================================
 *  ImportController — page d'admin regroupant TROIS démonstrations :
 *
 *    1) import()            : upload + import d'un fichier CSV d'utilisateurs
 *    2) exportUsersExcel()   : export de la liste des utilisateurs en .xlsx
 *    3) exportReportPdf()    : export du dernier rapport d'import en .pdf
 *
 *  C'est le fichier le plus "complet" du projet pédagogique : chaque
 *  méthode illustre un cas d'usage classique en entreprise (import de
 *  données en masse, export tableur, export document imprimable).
 * ============================================================================
 */
class ImportController extends BaseController
{
    protected UserModel $userModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    // ─── GET /admin/import ──────────────────────────────────────────────────
    // Affiche simplement le formulaire d'upload (voir app/Views/admin/import/index.php)
    public function index(): string
    {
        return view('admin/import/index', [
            'title'      => 'Import CSV',
            'pageTitle'  => "Import d'utilisateurs (CSV)",
            'tolerances' => ErrorTolerance::cases(), // liste des 3 modes pour le <select>
        ]);
    }

    // ─── POST /admin/import ─────────────────────────────────────────────────
    public function import()
    {
        // 1) Récupération du fichier envoyé -------------------------------------
        // getFile() encapsule $_FILES['csv'] dans un objet CodeIgniter\HTTP\Files\UploadedFile,
        // beaucoup plus pratique/sûr que manipuler $_FILES directement.
        $file = $this->request->getFile('csv');

        if ($file === null || ! $file->isValid()) {
            return redirect()->to('/admin/import')->with('error', 'Aucun fichier valide envoyé.');
        }

        // isValid() vérifie que l'upload PHP s'est bien déroulé (pas d'erreur
        // réseau/serveur). hasMoved() vérifie qu'on ne traite pas 2x le même fichier.
        if ($file->hasMoved()) {
            return redirect()->to('/admin/import')->with('error', 'Ce fichier a déjà été traité.');
        }

        // Vérification de l'extension (sécurité minimale : on n'accepte que .csv).
        // NE JAMAIS se fier uniquement à l'extension en production : on
        // pourrait aussi vérifier $file->getMimeType() ici.
        if (strtolower($file->getClientExtension()) !== 'csv') {
            return redirect()->to('/admin/import')->with('error', 'Le fichier doit être au format .csv');
        }

        // 2) Lecture du niveau de tolérance choisi dans le formulaire -----------
        $toleranceValue = $this->request->getPost('tolerance') ?? ErrorTolerance::NONE->value;
        $tolerance      = ErrorTolerance::tryFrom($toleranceValue) ?? ErrorTolerance::NONE;

        // 3) Appel du service métier ---------------------------------------------
        // getTempName() donne le chemin du fichier temporaire créé par PHP lors
        // de l'upload (ex: /tmp/phpXXXXXX) — c'est CE chemin qu'on lit, pas le
        // nom original du fichier choisi par l'utilisateur.
        $service = new CsvImportService();

        try {
            $report = $service->import($file->getTempName(), $tolerance);
        } catch (FatalCsvException $e) {
            // Import annulé entièrement : on affiche le message d'erreur.
            return redirect()->to('/admin/import')->with('error', $e->getMessage());
        }

        // 4) On stocke le rapport en session pour pouvoir l'exporter en PDF
        //    juste après (voir exportReportPdf() ci-dessous), sans le recalculer.
        session()->set('last_import_report', $report);

        $message = "Import terminé : {$report['inserted']} utilisateur(s) importé(s), "
                 . "{$report['skipped']} ligne(s) ignorée(s).";

        return redirect()->to('/admin/import')->with('success', $message)->with('import_report', $report);
    }

    // ─── GET /admin/import/template ─────────────────────────────────────────
    // Permet de télécharger un exemple de fichier CSV bien formaté, pour que
    // l'utilisateur sache exactement quelles colonnes remplir.
    public function downloadTemplate()
    {
        $csv  = "username,email,password,id_type\n";
        $csv .= "jdupont,jean.dupont@mail.com,Passw0rd!,2\n";
        $csv .= "marie,marie@mail.com,Azerty123,2\n";

        // download() est un helper CodeIgniter qui force le téléchargement
        // (Content-Disposition: attachment) plutôt que d'afficher le texte
        // brut dans le navigateur.
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="modele_import_utilisateurs.csv"')
            ->setBody($csv);
    }

    // ─── GET /admin/import/export-excel ─────────────────────────────────────
    // DÉMONSTRATION EXCEL : exporte la liste complète des utilisateurs en .xlsx
    public function exportUsersExcel()
    {
        $users = $this->userModel->withType()->findAll();

        // On transforme le tableau associatif de la base en tableau de
        // lignes "brutes" (juste les valeurs, dans l'ordre des en-têtes).
        $headers = ['ID', 'Nom utilisateur', 'Email', 'Type', 'Actif', 'Créé le'];
        $rows    = array_map(static function (array $u) {
            return [
                $u['id'],
                $u['username'],
                $u['email'],
                $u['type_name'] ?? '—',
                $u['is_active'] ? 'Oui' : 'Non',
                $u['created_at'],
            ];
        }, $users);

        $excel   = new ExcelService();
        $content = $excel->buildXlsx($headers, $rows, 'Utilisateurs');

        // Le type MIME officiel d'un fichier .xlsx est celui ci-dessous
        // (OOXML = Office Open XML) : sans lui, certains navigateurs
        // afficheraient le fichier comme du texte brut au lieu de proposer
        // de l'ouvrir dans Excel/LibreOffice.
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="utilisateurs.xlsx"')
            ->setBody($content);
    }

    // ─── POST /admin/import/import-excel ────────────────────────────────────
    // DÉMONSTRATION EXCEL : réutilise CsvImportService en convertissant
    // d'abord le .xlsx uploadé en tableau associatif (même contrat de
    // données que le CSV), pour montrer qu'on peut brancher une AUTRE
    // source de fichier sur la même logique métier de validation.
    public function importExcel()
    {
        $file = $this->request->getFile('excel');

        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/admin/import')->with('error', 'Aucun fichier Excel valide envoyé.');
        }

        $excel = new ExcelService();
        $rows  = $excel->readAsAssoc($file->getTempName());

        $inserted = 0;
        $skipped  = 0;
        foreach ($rows as $row) {
            // Validation minimaliste pour la démo — en production, on
            // réutiliserait la même méthode de validation que pour le CSV
            // (idéalement en extrayant validateRow() dans une classe commune).
            if (empty($row['username']) || ! filter_var($row['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $this->userModel->insert([
                'username'  => $row['username'],
                'email'     => $row['email'],
                'password'  => $this->userModel->hashPassword((string) ($row['password'] ?? 'ChangeMe123')),
                'id_type'   => (int) ($row['id_type'] ?? 2),
                'is_active' => 1,
            ]);
            $inserted++;
        }

        return redirect()->to('/admin/import')
            ->with('success', "Import Excel terminé : {$inserted} importé(s), {$skipped} ignoré(s).");
    }

    // ─── GET /admin/import/export-pdf ───────────────────────────────────────
    // DÉMONSTRATION DOMPDF : génère un PDF récapitulatif du dernier import CSV.
    public function exportReportPdf()
    {
        $report = session('last_import_report') ?? ['inserted' => 0, 'skipped' => 0, 'errors' => []];

        $pdf = new PdfService();

        // renderView() charge app/Views/admin/pdf/import_report.php (du HTML
        // normal) et le convertit en PDF — voir PdfService pour le détail.
        $pdfContent = $pdf->renderView(
            'admin/pdf/import_report',
            [
                'report'      => $report,
                'generatedAt' => date('d/m/Y H:i'),
            ],
            'rapport_import.pdf'
        );

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            // 'attachment' force le téléchargement ; utiliser 'inline' pour
            // ouvrir le PDF directement dans un nouvel onglet du navigateur.
            ->setHeader('Content-Disposition', 'attachment; filename="rapport_import.pdf"')
            ->setBody($pdfContent);
    }
}
