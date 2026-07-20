<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2>Modifier : <?= esc($operator['nom']) ?></h2>
    </div>
    <div>
        <a href="<?= site_url('admin/external-operators') ?>" class="btn btn--secondary">Retour</a>
    </div>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert--error"><?= session()->getFlashdata('error') ?></div>
<?php endif ?>
<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert alert--error">
        <ul style="margin:0; padding-left:1.5rem;">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<div class="card card--form" style="max-width: 600px;">
    <div class="card__header">
        <span class="card__title">Informations de l'opérateur</span>
    </div>
    <div class="card__body">
        <form action="<?= site_url('admin/external-operators/' . $operator['id']) ?>" method="post" class="form" style="box-shadow:none; padding:0; border:none; background:transparent;">
            <?= csrf_field() ?>
            <div class="form__div">
                <input type="text" class="form__input" id="nom" name="nom" value="<?= esc(old('nom', $operator['nom'])) ?>" placeholder=" " required>
                <label for="nom" class="form__label">Nom de l'opérateur</label>
            </div>
            <div class="form__div">
                <input type="number" step="0.01" class="form__input" id="commission_pourcentage" name="commission_pourcentage" value="<?= esc(old('commission_pourcentage', $operator['commission_pourcentage'])) ?>" placeholder=" " required>
                <label for="commission_pourcentage" class="form__label">Commission (%)</label>
            </div>
            <div class="form__div" style="margin-bottom:0.5rem;">
                <input type="text" class="form__input" id="prefixes" name="prefixes" value="<?= esc(old('prefixes', $operator['prefixes'])) ?>" placeholder=" " required>
                <label for="prefixes" class="form__label">Préfixes associés</label>
            </div>
            <small style="display:block; color:var(--text-muted); margin-bottom:1.5rem;">Séparés par des virgules (ex: 032, 031)</small>
            
            <button type="submit" class="btn btn--primary" style="width:100%;">Enregistrer les modifications</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
