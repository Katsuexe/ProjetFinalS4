<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<form action="<?= base_url('register') ?>" method="post" class="form" novalidate>
    <?= csrf_field() ?>

    <div class="form__logo">
        <div class="form__logo-icon">M</div>
        <span class="form__logo-name"><?= esc(getenv('app.name') ?: 'Blue Monay') ?></span>
    </div>

    <h1 class="form__title">Créer un compte</h1>
    <p class="form__subtitle">Rejoignez-nous en quelques secondes.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert--error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Nom d'utilisateur -->
    <div class="form__div">
        <input
            type="text"
            id="username"
            name="username"
            class="form__input <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
            placeholder=" "
            value="<?= old('username') ?>"
            autocomplete="username"
            minlength="3"
            maxlength="100"
            required
        >
        <label for="username" class="form__label">Nom d'utilisateur</label>
        <?php if (isset($errors['username'])): ?>
            <span class="form__error"><?= esc($errors['username']) ?></span>
        <?php endif; ?>
    </div>

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
            autocomplete="new-password"
            minlength="8"
            required
        >
        <label for="password" class="form__label">Mot de passe</label>
        <?php if (isset($errors['password'])): ?>
            <span class="form__error"><?= esc($errors['password']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Confirmation -->
    <div class="form__div">
        <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            class="form__input <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
            placeholder=" "
            autocomplete="new-password"
            minlength="8"
            required
        >
        <label for="password_confirm" class="form__label">Confirmer le mot de passe</label>
        <?php if (isset($errors['password_confirm'])): ?>
            <span class="form__error"><?= esc($errors['password_confirm']) ?></span>
        <?php endif; ?>
    </div>

    <input type="submit" class="form__button" value="Créer mon compte">

    <div class="form__footer">
        Déjà un compte ?
        <a href="<?= base_url('login') ?>">Se connecter</a>
    </div>
</form>

<?= $this->endSection() ?>
