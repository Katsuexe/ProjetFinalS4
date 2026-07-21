<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Authentification') ?> — <?= esc(getenv('app.name') ?: 'Blue Monay') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="l-form">
    <?= $this->renderSection('content') ?>
</div>

</body>
</html>
