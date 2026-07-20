<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Modifier : <?= esc($user['username']) ?></h2>
    <p><a href="<?= base_url('admin/users/' . $user['id']) ?>">&larr; Retour à la fiche</a></p>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card" style="max-width:480px;">
    <form action="<?= base_url('admin/users/' . $user['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="<?= esc(old('username') ?? $user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email') ?? $user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="id_type">Type</label>
            <select id="id_type" name="id_type" required>
                <?php foreach ($types as $t): ?>
                    <option value="<?= esc($t['id']) ?>" <?= ((int) $user['id_type'] === (int) $t['id']) ? 'selected' : '' ?>>
                        <?= esc($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password">
            <small>Laisser vide pour conserver le mot de passe actuel.</small>
        </div>

        <button type="submit" class="btn btn--primary">Enregistrer</button>
        <a href="<?= base_url('admin/users/' . $user['id']) ?>" class="btn btn--outline">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>
