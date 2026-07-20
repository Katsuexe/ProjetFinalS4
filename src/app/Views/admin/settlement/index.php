<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h2>Montants à envoyer</h2>
    <p>Transferts en attente de règlement vers les opérateurs externes.</p>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert--success"><?= session()->getFlashdata('success') ?></div>
<?php endif ?>

<div class="card">
    <div class="card__header">
        <span class="card__title">Règlements inter-opérateurs</span>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Opérateur Externe</th>
                    <th>Transactions</th>
                    <th>Montant dû (Ar)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($due)): ?>
                    <tr><td colspan="4" style="text-align:center;">Aucun montant à envoyer.</td></tr>
                <?php else: ?>
                    <?php foreach ($due as $row): ?>
                    <tr>
                        <td><?= esc($row['nom']) ?></td>
                        <td><span class="badge badge--blue"><?= esc($row['nb_transactions']) ?></span></td>
                        <td style="font-weight:600; color:var(--warning-color);"><?= number_format($row['montant_du'], 2, ',', ' ') ?> Ar</td>
                        <td>
                            <form action="<?= site_url('admin/montants-a-envoyer/marquer/' . $row['id']) ?>" method="post" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn--primary" style="padding:0.3rem 0.6rem; font-size:0.8rem;" onclick="return confirm('Confirmer l\'envoi global pour cet opérateur ?')">Marquer comme envoyé</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
