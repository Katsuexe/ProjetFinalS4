<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!--
    Vue : admin/user_detail.php — fiche détail d'un utilisateur.
    Reçoit $user (tableau associatif enrichi par UserModel::withType(),
    voir Admin\Dashboard::userDetail()).
-->

<div class="page-header">
    <h2>Utilisateur : <?= esc($user['username']) ?></h2>
    <p><a href="<?= base_url('admin/users') ?>">&larr; Retour à la liste</a></p>
</div>

<div class="card">
    <div style="display:flex; align-items:center; gap:1rem; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border-color);">
        <?php if (! empty($user['photo'])): ?>
            <img src="<?= base_url('assets/uploads/avatars/' . $user['photo']) ?>" alt=""
                 class="avatar-circle" style="width:64px; height:64px;">
        <?php else: ?>
            <div class="avatar-placeholder" style="width:64px; height:64px; font-size:1.4rem;">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div>
            <strong style="font-size:1.05rem;"><?= esc($user['username']) ?></strong>
            <div style="color:var(--text-muted); font-size:.85rem;"><?= esc($user['type_name'] ?? '—') ?></div>
        </div>
    </div>

    <table>
        <tbody>
            <tr><th style="width:200px;">ID</th><td><?= esc($user['id']) ?></td></tr>
            <tr><th>Nom d'utilisateur</th><td><?= esc($user['username']) ?></td></tr>
            <tr><th>Email</th><td><?= esc($user['email']) ?></td></tr>
            <tr><th>Type</th><td><span class="badge badge--blue"><?= esc($user['type_name'] ?? '—') ?></span></td></tr>
            <tr>
                <th>Statut</th>
                <td>
                    <?php if ($user['is_active']): ?>
                        <span class="badge badge--green">Actif</span>
                    <?php else: ?>
                        <span class="badge badge--red">Inactif</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr><th>Inscrit le</th><td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td></tr>
            <tr><th>Dernière connexion</th><td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '—' ?></td></tr>
        </tbody>
    </table>

    <div style="margin-top:1.5rem; display:flex; gap:.5rem; flex-wrap:wrap;">
        <?php if (has_permission('users.manage')): ?>
            <a href="<?= base_url('admin/users/' . $user['id'] . '/edit') ?>" class="btn btn--outline btn--sm">
                Modifier le profil
            </a>
            <a href="<?= base_url('admin/users/' . $user['id'] . '/toggle') ?>" class="btn btn--outline btn--sm">
                <?= $user['is_active'] ? 'Désactiver' : 'Activer' ?>
            </a>
        <?php elseif (has_permission('users.create')): ?>
            <!-- PEDAGOGIE : un modérateur (users.create sans users.manage) ne
                 peut pas éditer directement — mais avant ce correctif, il
                 n'avait AUCUN moyen d'atteindre /admin/users/{id}/request-edit
                 depuis l'interface (route déjà fonctionnelle, juste jamais
                 liée). Voir Admin\Dashboard::requestEditUser(). -->
            <a href="<?= base_url('admin/users/' . $user['id'] . '/request-edit') ?>" class="btn btn--outline btn--sm">
                Modifier le profil
            </a>
        <?php endif; ?>
        <?php if (has_permission('users.delete')): ?>
            <a href="<?= base_url('admin/users/' . $user['id'] . '/delete') ?>"
               class="btn btn--outline btn--sm"
               style="color:var(--danger-color);border-color:var(--danger-color);"
               onclick="return confirm('Supprimer définitivement cet utilisateur ?');">
                Supprimer
            </a>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
