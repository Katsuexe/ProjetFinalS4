<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Changer le mot de passe</h2>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card card--form" style="max-width:480px;">
    <div class="card__header">
        <div class="form-card__icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
        </div>
        <div class="form-card__header-text">
            <strong>Changer le mot de passe</strong>
            <span>Choisissez un mot de passe d'au moins 8 caractères</span>
        </div>
    </div>
    <!-- Cible User\Dashboard::updatePassword() (déjà existant). -->
    <form action="<?= base_url('user/password') ?>" method="post">
        <?= csrf_field() ?>

        <div class="card__body">
            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" minlength="8" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Mettre à jour</button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
