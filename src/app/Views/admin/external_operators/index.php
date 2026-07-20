<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h2>Opérateurs Externes</h2>
    <p>Gérez ici les opérateurs concurrents et leurs commissions.</p>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert--success"><?= session()->getFlashdata('success') ?></div>
<?php endif ?>
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

<div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap:wrap;">
    <!-- Formulaire d'ajout -->
    <div class="card card--form" style="flex:1; min-width:300px;">
        <div class="card__header">
            <span class="card__title">Ajouter un Opérateur Externe</span>
        </div>
        <div class="card__body">
            <form action="<?= site_url('admin/external-operators') ?>" method="post" class="form" style="box-shadow:none; padding:0; border:none; background:transparent;">
                <?= csrf_field() ?>
                <div class="form__div">
                    <input type="text" class="form__input" id="nom" name="nom" value="<?= old('nom') ?>" placeholder=" " required>
                    <label for="nom" class="form__label">Nom de l'opérateur</label>
                </div>
                <div class="form__div">
                    <input type="number" step="0.01" class="form__input" id="commission_pourcentage" name="commission_pourcentage" value="<?= old('commission_pourcentage') ?>" placeholder=" " required>
                    <label for="commission_pourcentage" class="form__label">Commission (%)</label>
                </div>
                <div class="form__div">
                    <input type="text" class="form__input" id="prefixes" name="prefixes" value="<?= old('prefixes') ?>" placeholder=" " required>
                    <label for="prefixes" class="form__label">Préfixes (ex: 032,031)</label>
                </div>
                <button type="submit" class="btn btn--primary" style="width:100%;">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Liste -->
    <div class="card" style="flex:2; min-width:300px;">
        <div class="card__header">
            <span class="card__title">Liste des Opérateurs</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Commission</th>
                        <th>Préfixes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($operators)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Aucun opérateur externe trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($operators as $op): ?>
                            <tr>
                                <td><?= esc($op['id']) ?></td>
                                <td><?= esc($op['nom']) ?></td>
                                <td><?= esc($op['commission_pourcentage']) ?> %</td>
                                <td>
                                    <?php if ($op['prefixes']): ?>
                                        <?php foreach (explode(',', $op['prefixes']) as $p): ?>
                                            <span class="badge badge--gray"><?= esc($p) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('admin/external-operators/' . $op['id'] . '/edit') ?>" class="btn btn--secondary" style="padding:0.2rem 0.5rem; font-size:0.75rem;">Modifier</a>
                                    <a href="<?= site_url('admin/external-operators/' . $op['id'] . '/delete') ?>" class="btn btn--danger" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="return confirm('Supprimer cet opérateur ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
