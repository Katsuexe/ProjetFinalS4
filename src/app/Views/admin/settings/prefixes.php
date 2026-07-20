<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Préfixes opérateur</h2>
    <p>Les numéros de téléphone dont le préfixe n'est pas listé ci-dessous seront refusés lors du login client.</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

    <!-- Liste des préfixes -->
    <div class="card">
        <div class="card__header">
            <span class="card__title">Préfixes actifs</span>
        </div>
        <?php if (empty($prefixes)): ?>
            <div style="padding:2rem;text-align:center;color:var(--text-muted);">Aucun préfixe configuré.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Préfixe</th>
                    <th>Ajouté le</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prefixes as $p): ?>
                <tr>
                    <td><strong><?= esc($p['prefix']) ?></strong></td>
                    <td><?= $p['created_at'] ? date('d/m/Y', strtotime($p['created_at'])) : '—' ?></td>
                    <td>
                        <a href="<?= base_url('admin/settings/prefixes/' . $p['id'] . '/delete') ?>"
                           class="btn btn--danger btn--sm"
                           onclick="return confirm('Supprimer le préfixe <?= esc($p['prefix']) ?> ?')">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Formulaire ajout -->
    <div class="card card--form">
        <div class="card__header">
            <span class="card__title">Ajouter un préfixe</span>
        </div>
        <div class="card__body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('admin/settings/prefixes') ?>" method="post" class="form">
                <?= csrf_field() ?>
                <div class="form__div">
                    <input type="text" name="prefix" id="prefix" class="form__input" placeholder=" "
                           pattern="\d{3,4}" maxlength="4" required>
                    <label for="prefix" class="form__label">Préfixe (ex : 033)</label>
                </div>
                <button type="submit" class="button button--primary" style="width:100%;">Ajouter</button>
            </form>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
