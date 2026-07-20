<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Mon tableau de bord</h2>
    <p>Bonjour, <?= esc(session('username')) ?> — voici votre espace.</p>
</div>

<!-- Info carte utilisateur -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card__header">
        <span class="card__title">Mon compte</span>
        <a href="<?= base_url('user/profile') ?>" class="btn btn--outline btn--sm">Modifier</a>
    </div>
    <div class="card__body">
        <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            <div style="width:56px;height:56px;border-radius:50%;background:#1A73E8;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:500;flex-shrink:0;">
                <?= strtoupper(substr(session('username') ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <div style="font-weight:500;font-size:1.05rem;"><?= esc(session('username')) ?></div>
                <div style="color:var(--text-muted);font-size:.875rem;"><?= esc(session('email')) ?></div>
                <div style="margin-top:.4rem;">
                    <span class="badge badge--blue"><?= esc(session('user_type_name') ?? 'Utilisateur') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permissions actives (exemple pédagogique) -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card__header">
        <span class="card__title">Mes permissions</span>
    </div>
    <div class="card__body">
        <?php if (empty(session('permissions'))): ?>
            <p style="color:var(--text-muted);font-size:.875rem;">Aucune permission assignée à votre type de compte.</p>
        <?php else: ?>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                <?php foreach (session('permissions') as $perm): ?>
                    <span class="badge badge--gray" style="font-family:monospace;"><?= esc($perm) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Solde (visible seulement si permission wallet.view) -->
<?php if (has_permission('wallet.view')): ?>
<div class="card">
    <div class="card__header">
        <span class="card__title">Mon solde</span>
    </div>
    <div class="card__body">
        <div style="font-size:2rem;font-weight:500;color:var(--first-color);">
            <?= number_format($balance ?? 0, 2) ?> <span style="font-size:1rem;color:var(--text-muted);">€</span>
        </div>
        <p style="color:var(--text-muted);font-size:.8rem;margin-top:.4rem;">
            Dernière mise à jour : <?= $balance_updated_at ?? '—' ?>
        </p>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
