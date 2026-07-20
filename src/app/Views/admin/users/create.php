<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!--
    Vue : admin/users/create.php
    PEDAGOGIE : le sélecteur de type n'est affiché QUE si l'utilisateur
    connecté a 'users.manage' (voir Admin\Dashboard::createUser() côté
    contrôleur). Un modérateur (qui n'a que 'users.create') ne voit pas ce
    champ : il crée toujours un compte de type 'user' par défaut. C'est la
    même règle appliquée deux fois (vue = confort d'affichage, contrôleur =
    vraie sécurité), voir la règle d'or dans admin/users.php.
-->

<div class="page-header">
    <h2>Nouvel utilisateur</h2>
    <p><a href="<?= base_url('admin/users') ?>">&larr; Retour à la liste</a></p>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card card--form" style="max-width:480px;">
    <div class="card__header">
        <div class="form-card__icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v6m3-3h-6m-1.5-6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
        </div>
        <div class="form-card__header-text">
            <strong>Nouvel utilisateur</strong>
            <span>Créer un compte et lui attribuer un type</span>
        </div>
    </div>
    <form action="<?= base_url('admin/users') ?>" method="post">
        <?= csrf_field() ?>

        <div class="card__body">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                <small>8 caractères minimum.</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <?php if (has_permission('users.manage')): ?>
            <div class="form-group">
                <label for="id_type">Type</label>
                <select id="id_type" name="id_type" required>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= esc($t['id']) ?>"><?= esc($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="id_type" value="">
                <p class="form-hint">Le compte sera créé avec le type "Utilisateur" par défaut.</p>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Créer</button>
            <a href="<?= base_url('admin/users') ?>" class="btn btn--outline">Annuler</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
