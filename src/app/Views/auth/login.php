<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<form action="<?= base_url('login') ?>" method="post" class="form" novalidate>
    <?= csrf_field() ?>

    <div class="form__logo">
        <div class="form__logo-icon">M</div>
        <span class="form__logo-name"><?= esc(getenv('app.name') ?: 'MonApp') ?></span>
    </div>

    <h1 class="form__title">Connexion</h1>
    <p class="form__subtitle">Bienvenue ! Entrez vos identifiants.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert--error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert--success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <!-- Email -->
    <div class="form__div">
        <input
            type="email"
            id="email"
            name="email"
            class="form__input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
            placeholder=" "
            value="<?= old('email') ?>"
            autocomplete="email"
            required
        >
        <label for="email" class="form__label">Adresse email</label>
        <?php if (isset($errors['email'])): ?>
            <span class="form__error"><?= esc($errors['email']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Mot de passe -->
    <div class="form__div">
        <input
            type="password"
            id="password"
            name="password"
            class="form__input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
            placeholder=" "
            autocomplete="current-password"
            required
        >
        <label for="password" class="form__label">Mot de passe</label>
        <?php if (isset($errors['password'])): ?>
            <span class="form__error"><?= esc($errors['password']) ?></span>
        <?php endif; ?>
    </div>

    <input type="submit" class="form__button" value="Se connecter">

    <div class="form__footer">
        Pas encore de compte ?
        <a href="<?= base_url('register') ?>">Créer un compte</a>
    </div>
</form>

<?= $this->endSection() ?>
