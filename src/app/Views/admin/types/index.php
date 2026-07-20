<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!--
    Vue : admin/types/index.php
    Liste les "types d'utilisateurs" (= rôles : admin, user, moderator...)
    et, pour chacun, les permissions qui lui sont associées via la table
    pivot user_type_permissions (voir Admin\Types::index()).
-->

<div class="page-header">
    <h2>Types & Rôles</h2>
    <p>Chaque utilisateur possède UN type ; chaque type possède PLUSIEURS permissions.</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card__header" style="margin-bottom:1rem;">
    <a href="<?= base_url('admin/types/create') ?>" class="btn btn--primary btn--sm">+ Nouveau type</a>
</div>

<?php foreach ($types as $type): ?>
<div class="card" style="margin-bottom:1rem;">
    <div class="card__header">
        <span class="card__title">
            <?= esc($type['name']) ?>
            <code style="font-size:.75rem;color:var(--text-muted);">(<?= esc($type['slug']) ?>)</code>
        </span>

        <a href="<?= base_url('admin/types/' . $type['id'] . '/edit') ?>" class="btn btn--outline btn--sm">
            Modifier
        </a>

        <?php // Les 3 types créés par le seeder sont protégés contre la
              // suppression -- voir Admin\Types::delete(). On masque donc
              // le bouton pour ces slugs, en plus de la protection serveur. ?>
        <?php if (! in_array($type['slug'], ['admin', 'user', 'moderator'], true)): ?>
            <a href="<?= base_url('admin/types/' . $type['id'] . '/delete') ?>"
               class="btn btn--outline btn--sm"
               style="color:var(--danger-color);border-color:var(--danger-color);"
               onclick="return confirm('Supprimer ce type ?');">
                Supprimer
            </a>
        <?php endif; ?>
    </div>
    <div class="card__body">
        <p style="color:var(--text-muted);font-size:.875rem;"><?= esc($type['description'] ?? '') ?></p>

        <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.5rem;">
            <?php if (empty($type['permissions'])): ?>
                <span style="color:var(--text-muted);font-size:.8rem;">Aucune permission assignée.</span>
            <?php else: ?>
                <?php foreach ($type['permissions'] as $perm): ?>
                    <span class="badge badge--gray" style="font-family:monospace;" title="<?= esc($perm['name']) ?>">
                        <?= esc($perm['slug']) ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?= $this->endSection() ?>
