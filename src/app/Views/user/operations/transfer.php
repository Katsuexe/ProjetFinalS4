<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<h1 class="page-title"><?= esc($title) ?></h1>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card card--form">
    <div class="card__header">
        <h2 class="card__title">Transférer de l'argent</h2>
    </div>
    <div class="card__body">
        <p class="text-muted" style="margin-bottom:1rem;">Des frais de transfert s'appliquent selon le barème.</p>
        
        <form action="<?= base_url('user/operations/transfer') ?>" method="post" class="form">
            <?= csrf_field() ?>
            
            <div class="form__div">
                <input type="tel" name="recipient_phone" id="recipient_phone" class="form__input" placeholder=" " required inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10">
                <label for="recipient_phone" class="form__label">Numéro du destinataire (10 chiffres)</label>
            </div>

            <div class="form__div">
                <input type="number" name="amount" id="amount" class="form__input" placeholder=" " min="100" step="100" required>
                <label for="amount" class="form__label">Montant à transférer (Ar)</label>
            </div>
            
            <button type="submit" class="button button--primary" style="width: 100%;">Valider le transfert</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
