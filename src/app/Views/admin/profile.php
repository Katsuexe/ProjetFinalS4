<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Mon profil</h2>
</div>

<div class="card card--form" style="max-width:480px;">
    <div class="card__header">
        <div class="form-card__icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        </div>
        <div class="form-card__header-text">
            <strong><?= esc(session('username') ?? 'Mon profil') ?></strong>
            <span><?= esc(session('user_type_name') ?? 'Administrateur') ?></span>
        </div>
    </div>
    <form action="<?= base_url('admin/profile') ?>" method="post">
        <?= csrf_field() ?>

        <div class="card__body">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= esc(old('username') ?: session('username')) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= esc(session('email')) ?>" disabled>
                <small>L'email ne peut pas être modifié depuis cette page.</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Enregistrer</button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
