<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Demander une modification : <?= esc($user['username']) ?></h2>
    <p><a href="<?= base_url('admin/users/' . $user['id']) ?>">&larr; Retour à la fiche</a></p>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (! empty($pending)): ?>
    <div class="alert alert--info">Une demande est déjà en attente de validation pour cet utilisateur — inutile d'en soumettre une deuxième.</div>
<?php endif; ?>

<div class="card card--form" style="max-width:480px;">
    <div class="card__header">
        <div class="form-card__icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
        </div>
        <div class="form-card__header-text">
            <strong><?= esc($user['username']) ?></strong>
            <span>Envoyée à un administrateur pour validation</span>
        </div>
    </div>
    <!--
        PEDAGOGIE : ce formulaire ressemble à admin/users/edit.php, mais avec
        deux différences volontaires :
          1) PAS de champ id_type ni password — un modérateur ne peut jamais
             proposer de changer le rôle ou le mot de passe d'un autre user
             (principe de moindre privilège, voir DESIGN.md).
          2) Le POST ne modifie RIEN directement : il va vers
             Admin\Dashboard::storeRequestEditUser(), qui crée une
             pending_action de type 'moderator.user_update'. Le changement ne
             sera visible qu'après validation d'un admin sur /admin/validations.
    -->
    <form action="<?= base_url('admin/users/' . $user['id'] . '/request-edit') ?>" method="post">
        <?= csrf_field() ?>

        <div class="card__body">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= esc(old('username') ?? $user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= esc(old('email') ?? $user['email']) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Envoyer la demande</button>
            <a href="<?= base_url('admin/users') ?>" class="btn btn--outline">Annuler</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
