<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<!--
    Vue : user/wallet.php
    N'est atteignable QUE si l'utilisateur a la permission 'wallet.view'
    (voir le filtre de route dans app/Config/Routes.php et
    User\Wallet::index()). Avec les données du MainSeeder, seul le type
    "user" (Alice) possède cette permission.
-->

<div class="page-header">
    <h2>Mon solde</h2>
</div>

<div class="card" style="max-width:480px;">
    <div class="card__body">
        <div style="font-size:2rem;font-weight:500;color:var(--first-color);">
            <?= number_format($balance, 2) ?> <span style="font-size:1rem;color:var(--text-muted);"><?= esc($currency) ?></span>
        </div>
        <p style="color:var(--text-muted);font-size:.8rem;margin-top:.4rem;">
            Dernière mise à jour : <?= $updated_at ?? '—' ?>
        </p>
    </div>
</div>

<?= $this->endSection() ?>
