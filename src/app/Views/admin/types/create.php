<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Créer un type d'utilisateur</h2>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card card--form" style="max-width:640px;">
    <div class="card__header">
        <div class="form-card__icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div class="form-card__header-text">
            <strong>Nouveau type</strong>
            <span>Définissez son nom, son slug et ses permissions</span>
        </div>
    </div>
    <form action="<?= base_url('admin/types') ?>" method="post">
        <?= csrf_field() ?>

        <div class="card__body">
            <div class="form-group">
                <label for="name">Nom affiché</label>
                <input type="text" id="name" name="name" value="<?= esc(old('name')) ?>" placeholder="Ex: Support client" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug technique (unique, sans espace)</label>
                <input type="text" id="slug" name="slug" value="<?= esc(old('slug')) ?>" placeholder="Ex: support" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?= esc(old('description')) ?></textarea>
            </div>

            <?= view('admin/types/_permissions_fieldset', ['permissions' => $permissions, 'checkedIds' => $checkedIds]) ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Créer</button>
            <a href="<?= base_url('admin/types') ?>" class="btn btn--outline">Annuler</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
