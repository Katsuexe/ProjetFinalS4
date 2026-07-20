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
        <h2 class="card__title">Retirer de l'argent</h2>
    </div>
    <div class="card__body">
        <p class="text-muted" style="margin-bottom:1rem;">Attention: des frais s'appliquent selon le montant retiré.</p>
        
        <form action="<?= base_url('user/operations/withdraw') ?>" method="post" class="form">
            <?= csrf_field() ?>
            <div class="form__div">
                <input type="number" name="amount" id="amount" class="form__input" placeholder=" " min="100" step="100" required>
                <label for="amount" class="form__label">Montant (Ar)</label>
            </div>
            
            <button type="submit" class="button button--danger" style="width: 100%;">Valider le retrait</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
