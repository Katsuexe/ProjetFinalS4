<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>Barèmes de frais</h2>
        <p>Les frais s'appliquent uniquement aux opérations de <strong>retrait</strong> et de <strong>transfert</strong>. Le dépôt est gratuit.</p>
    </div>
    <div>
        <a href="<?= base_url('admin/settings/fees/export-pdf') ?>" class="btn btn--outline">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:1.2rem;height:1.2rem;vertical-align:middle;margin-right:0.25rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Exporter en PDF
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<!-- Tableaux des barèmes existants par type -->
<?php foreach ($feesByType as $typeId => $typeData): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card__header">
        <span class="card__title">Barème — <?= esc($typeData['name']) ?></span>
    </div>
    <?php if (empty($typeData['scales'])): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">Aucun barème configuré pour ce type.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Montant min (Ar)</th>
                    <th>Montant max (Ar)</th>
                    <th>Frais (Ar)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($typeData['scales'] as $s): ?>
                <tr>
                    <td><?= number_format($s['min_amount'], 2, ',', ' ') ?></td>
                    <td><?= number_format($s['max_amount'], 2, ',', ' ') ?></td>
                    <td style="font-weight:600;"><?= number_format($s['fee_amount'], 2, ',', ' ') ?></td>
                    <td>
                        <a href="<?= base_url('admin/settings/fees/' . $s['id'] . '/delete') ?>"
                           class="btn btn--danger btn--sm"
                           onclick="return confirm('Supprimer ce barème ?')">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Formulaire ajout barème -->
<div class="card card--form">
    <div class="card__header">
        <span class="card__title">Ajouter un barème</span>
    </div>
    <div class="card__body">
        <form action="<?= base_url('admin/settings/fees') ?>" method="post" class="form">
            <?= csrf_field() ?>
            <div class="form__div">
                <select name="operation_type_id" id="operation_type_id" class="form__input" required>
                    <option value="" disabled selected>Choisir un type…</option>
                    <?php foreach ($opTypes as $op): ?>
                        <option value="<?= $op['id'] ?>"><?= esc($op['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="operation_type_id" class="form__label" style="transform:translateY(-1.6rem) scale(.82);color:var(--primary);">Type d'opération</label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="form__div">
                    <input type="number" name="min_amount" id="min_amount" class="form__input" placeholder=" " min="0" step="1" required>
                    <label for="min_amount" class="form__label">Min (Ar)</label>
                </div>
                <div class="form__div">
                    <input type="number" name="max_amount" id="max_amount" class="form__input" placeholder=" " min="1" step="1" required>
                    <label for="max_amount" class="form__label">Max (Ar)</label>
                </div>
                <div class="form__div">
                    <input type="number" name="fee_amount" id="fee_amount" class="form__input" placeholder=" " min="0" step="1" required>
                    <label for="fee_amount" class="form__label">Frais (Ar)</label>
                </div>
            </div>
            <button type="submit" class="button button--primary" style="width:100%;">Ajouter le barème</button>
        </form>
    </div>
</div>

<!-- Formulaire import CSV -->
<div class="card card--form" style="margin-top:1.5rem;">
    <div class="card__header">
        <span class="card__title">Importer un fichier CSV</span>
    </div>
    <div class="card__body">
        <form action="<?= base_url('admin/settings/fees/import-csv') ?>" method="post" enctype="multipart/form-data" class="form">
            <?= csrf_field() ?>
            <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:1rem;">
                Format attendu (sans en-tête ou ignoré sur la ligne 1) : <code>operation_slug, min_amount, max_amount, fee_amount</code><br>
                Exemple : <code>transfer, 100, 1000, 50</code>
            </p>
            <div class="form-group mb-3">
                <input type="file" name="csv_file" accept=".csv" required style="width:100%;">
            </div>
            <button type="submit" class="button button--outline" style="width:100%;">Importer le CSV</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
