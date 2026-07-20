<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<form action="<?= base_url('login') ?>" method="post" class="form" novalidate>
    <?= csrf_field() ?>

    <div class="form__logo">
        <div class="form__logo-icon">M</div>
        <span class="form__logo-name"><?= esc(getenv('app.name') ?: 'MonApp') ?></span>
    </div>

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

    <?php
    // Mode : 'phone' (défaut public) ou 'admin' (activé par le numéro secret)
    $mode = $mode ?? 'phone';
    ?>

    <?php if ($mode === 'admin'): ?>

        <!-- ── Formulaire Admin/Modo (email + mot de passe) ─────────────────── -->
        <h1 class="form__title">Accès opérateur</h1>
        <p class="form__subtitle">Connexion réservée à l'équipe de gestion.</p>

        <?php if (isset($errors['login_id'])): ?>
            <div class="alert alert--error"><?= esc($errors['login_id']) ?></div>
        <?php endif; ?>

        <div class="form__div">
            <input
                type="email"
                id="login_id"
                name="login_id"
                class="form__input <?= isset($errors['login_id']) ? 'is-invalid' : '' ?>"
                placeholder=" "
                value="<?= old('login_id') ?>"
                autocomplete="email"
                required
            >
            <label for="login_id" class="form__label">Adresse e-mail</label>
        </div>

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

        <!-- Champ caché pour indiquer que c'est une soumission admin -->
        <input type="hidden" name="login_mode" value="admin">

        <input type="submit" class="form__button" value="Se connecter">

        <div class="form__footer" style="margin-top:.75rem;">
            <a href="<?= base_url('login') ?>" style="font-size:.85rem;color:var(--text-muted);">← Retour</a>
        </div>

    <?php else: ?>

        <!-- ── Formulaire Client (numéro de téléphone uniquement) ───────────── -->
        <h1 class="form__title">Connexion</h1>
        <p class="form__subtitle">Entrez votre numéro de téléphone pour accéder à votre compte.</p>

        <?php if (isset($errors['login_id'])): ?>
            <div class="alert alert--error"><?= esc($errors['login_id']) ?></div>
        <?php endif; ?>

        <div class="form__div">
            <input
                type="tel"
                id="login_id"
                name="login_id"
                class="form__input <?= isset($errors['login_id']) ? 'is-invalid' : '' ?>"
                placeholder=" "
                value="<?= old('login_id') ?>"
                autocomplete="tel"
                pattern="[0-9]{10}"
                maxlength="10"
                required
            >
            <label for="login_id" class="form__label">Numéro de téléphone</label>
        </div>

        <input type="submit" class="form__button" value="Se connecter">

    <?php endif; ?>

</form>

<?= $this->endSection() ?>
