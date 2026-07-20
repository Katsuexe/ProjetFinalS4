<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

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
            
            <?php if (has_permission('users.manage')): ?>
                <div class="form-group">
                    <label for="id_type">Type de compte</label>
                    <select id="id_type" name="id_type" data-simple-id="<?= esc($defaultUserId ?? '') ?>" required>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= esc($t['id']) ?>" <?= ((int) ($defaultUserId ?? $t['id']) === (int) $t['id']) ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="id_type" id="id_type" data-simple-id="<?= esc($defaultUserId ?? '') ?>" value="<?= esc($defaultUserId ?? '') ?>">
                <p class="form-hint" style="margin-bottom: 1.25rem;">Type : <strong>Utilisateur</strong> (par défaut)</p>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" required>
            </div>

            <div class="form-group" id="email_group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>">
                <small class="form-hint" id="email_hint">Obligatoire pour les admins/modérateurs.</small>
            </div>

            <div class="form-group" id="phone_group">
                <label for="phone">Numéro de téléphone</label>
                <input type="tel" id="phone" name="phone" value="<?= esc(old('phone')) ?>" placeholder="ex: 0321234567">
                <small class="form-hint" id="phone_hint">Obligatoire pour un compte Mobile Money.</small>
            </div>

            <div class="form-group" id="password_group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password">
                <small class="form-hint">8 caractères minimum.</small>
            </div>

            <div class="form-group" id="password_confirm_group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm">
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Créer</button>
            <a href="<?= base_url('admin/users') ?>" class="btn btn--outline">Annuler</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('id_type');
    if (!typeSelect) return;

    const defaultUserId = typeSelect.getAttribute('data-simple-id');
    
    const emailGroup = document.getElementById('email_group');
    const emailInput = document.getElementById('email');
    
    const phoneGroup = document.getElementById('phone_group');
    const phoneInput = document.getElementById('phone');
    
    const passwordGroup = document.getElementById('password_group');
    const passwordInput = document.getElementById('password');
    
    const passwordConfirmGroup = document.getElementById('password_confirm_group');
    const passwordConfirmInput = document.getElementById('password_confirm');

    function updateFormFields() {
        const selectedType = typeSelect.value;
        const isSimpleUser = (selectedType === defaultUserId);

        if (isSimpleUser) {
            // Utilisateur simple : tel obligatoire, mdp caché, email optionnel
            phoneGroup.style.display = 'block';
            phoneInput.required = true;
            
            passwordGroup.style.display = 'none';
            passwordInput.required = false;
            
            passwordConfirmGroup.style.display = 'none';
            passwordConfirmInput.required = false;
            
            emailInput.required = false;
            
        } else {
            // Admin / Modo : mdp obligatoire, email obligatoire, tel caché
            phoneGroup.style.display = 'none';
            phoneInput.required = false;
            
            passwordGroup.style.display = 'block';
            passwordInput.required = true;
            
            passwordConfirmGroup.style.display = 'block';
            passwordConfirmInput.required = true;
            
            emailInput.required = true;
        }
    }

    typeSelect.addEventListener('change', updateFormFields);
    
    // Initial run
    updateFormFields();
});
</script>

<?= $this->endSection() ?>
