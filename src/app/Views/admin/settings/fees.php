<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Barèmes de frais</h2>
    <p>Les frais s'appliquent uniquement aux opérations de <strong>retrait</strong> et de <strong>transfert</strong>. Le dépôt est gratuit.</p>
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

<?= $this->endSection() ?>
