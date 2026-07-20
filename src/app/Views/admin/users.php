<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!--
    ============================================================================
    Vue : admin/users.php — liste complète des utilisateurs
    ============================================================================
    PEDAGOGIE — AUTORISATION CÔTÉ VUE :
    Le filtre de route 'admin' protège déjà l'ACCÈS à cette page (il faut la
    permission 'admin.panel'). Mais à L'INTÉRIEUR de la page, on veut aussi
    masquer les boutons "Activer/Désactiver" et "Supprimer" si l'utilisateur
    connecté n'a pas les permissions précises 'users.manage' / 'users.delete'
    -- même si le contrôleur (Admin\Dashboard::toggleUser/deleteUser) vérifie
    DÉJÀ ces permissions avant d'agir (voir le contrôleur).

    RÈGLE D'OR : ne JAMAIS se reposer uniquement sur le fait de "cacher un
    bouton" pour sécuriser une action. Cacher le bouton améliore juste
    l'expérience utilisateur (on ne montre pas une action qui échouerait).
    La VRAIE sécurité est toujours vérifiée côté contrôleur (voir
    has_permission() dans Admin\Dashboard::toggleUser()).
    ============================================================================
-->

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h2>Gestion des utilisateurs</h2>
        <p><?= count($users) ?> utilisateur(s) au total.</p>
    </div>
    <?php if (has_any_permission(['users.manage', 'users.create'])): ?>
        <a href="<?= base_url('admin/users/create') ?>" class="btn btn--primary btn--sm">+ Nouvel utilisateur</a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
            <tr>
                <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">
                    Aucun utilisateur pour l'instant.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= esc($u['id']) ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <?php if (! empty($u['photo'])): ?>
                            <img src="<?= base_url('assets/uploads/avatars/' . $u['photo']) ?>" alt=""
                                 class="avatar-circle avatar-circle--sm">
                        <?php else: ?>
                            <div class="avatar-placeholder avatar-placeholder--sm">
                                <?= strtoupper(substr($u['username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <?= esc($u['username']) ?>
                    </div>
                </td>
                <td><?= esc($u['email']) ?></td>
                <td><span class="badge badge--blue"><?= esc($u['type_name'] ?? '—') ?></span></td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <span class="badge badge--green">Actif</span>
                    <?php else: ?>
                        <span class="badge badge--red">Inactif</span>
                    <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td style="white-space:nowrap;">
                    <a href="<?= base_url('admin/users/' . $u['id']) ?>" class="btn btn--outline btn--sm">Voir</a>

                    <?php // On n'affiche le bouton que si la permission est présente. ?>
                    <?php if (has_permission('users.manage')): ?>
                        <a href="<?= base_url('admin/users/' . $u['id'] . '/edit') ?>" class="btn btn--outline btn--sm">Modifier</a>
                        <a href="<?= base_url('admin/users/' . $u['id'] . '/toggle') ?>" class="btn btn--outline btn--sm">
                            <?= $u['is_active'] ? 'Désactiver' : 'Activer' ?>
                        </a>
                    <?php endif; ?>

                    <?php if (has_permission('users.delete')): ?>
                        <a href="<?= base_url('admin/users/' . $u['id'] . '/delete') ?>"
                           class="btn btn--danger btn--sm"
                           onclick="return confirm('Supprimer définitivement cet utilisateur ?');">
                            Supprimer
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
