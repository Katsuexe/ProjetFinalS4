<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!--
    ============================================================================
    Vue : admin/import/index.php
    ============================================================================
    PEDAGOGIE : cette vue étend "layouts/admin" grâce à $this->extend(), un
    mécanisme d'HÉRITAGE DE VUES propre à CodeIgniter. Le layout définit la
    structure commune (sidebar, header...) et délègue le contenu central à
    la section "content" définie ici avec $this->section('content') / endSection().
    C'est l'équivalent Blade (Laravel) de @extends / @section.
    ============================================================================
-->

<div class="page-header">
    <h2>Import d'utilisateurs</h2>
    <p>Importez des utilisateurs en masse depuis un fichier CSV ou Excel (.xlsx).</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- ============================ BLOC 1 : IMPORT CSV ============================ -->
<div class="card" style="margin-bottom:1.5rem;">
    <h3>1. Import CSV</h3>
    <p>
        Fichier attendu : colonnes <code>username,email,password,id_type</code>.
        <a href="<?= base_url('admin/import/template') ?>">Télécharger un modèle</a>.
    </p>

    <!--
        PEDAGOGIE — formulaire d'upload de fichier :
        1) method="post" obligatoire (un fichier ne peut pas être envoyé en GET)
        2) enctype="multipart/form-data" OBLIGATOIRE dès qu'un <input type="file">
           est présent, sinon PHP ne reçoit JAMAIS le fichier (piège classique !)
        3) csrf_field() insère un jeton anti-CSRF caché ; CodeIgniter le vérifie
           automatiquement grâce au filtre 'csrf' (voir app/Config/Filters.php)
    -->
    <form action="<?= base_url('admin/import') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="csv">Fichier CSV</label>
            <input type="file" id="csv" name="csv" accept=".csv" required>
        </div>

        <div class="form-group">
            <label for="tolerance">Comportement en cas d'erreur</label>
            <select id="tolerance" name="tolerance">
                <?php foreach ($tolerances as $t): ?>
                    <option value="<?= esc($t->value) ?>"><?= esc($t->label()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn--primary">Importer</button>
    </form>

    <?php if ($report = session('import_report')): ?>
        <div style="margin-top:1rem;">
            <p><strong><?= (int) $report['inserted'] ?></strong> importé(s),
               <strong><?= (int) $report['skipped'] ?></strong> ignoré(s).</p>

            <?php if (! empty($report['errors'])): ?>
                <ul>
                    <?php foreach ($report['errors'] as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Export du rapport en PDF (Dompdf) -->
            <a href="<?= base_url('admin/import/export-pdf') ?>" class="btn btn--secondary">
                Télécharger le rapport en PDF
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- ============================ BLOC 2 : IMPORT EXCEL ============================ -->
<div class="card" style="margin-bottom:1.5rem;">
    <h3>2. Import Excel (.xlsx)</h3>
    <p>Mêmes colonnes que le CSV, mais dans un classeur Excel.</p>

    <form action="<?= base_url('admin/import/import-excel') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="excel">Fichier Excel</label>
            <input type="file" id="excel" name="excel" accept=".xlsx,.xls" required>
        </div>
        <button type="submit" class="btn btn--primary">Importer</button>
    </form>
</div>

<!-- ============================ BLOC 3 : EXPORT EXCEL ============================ -->
<div class="card">
    <h3>3. Export Excel</h3>
    <p>Télécharge la liste actuelle des utilisateurs au format .xlsx.</p>
    <a href="<?= base_url('admin/import/export-excel') ?>" class="btn btn--secondary">
        Télécharger utilisateurs.xlsx
    </a>
</div>

<?= $this->endSection() ?>
