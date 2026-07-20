<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Mon espace') ?> — <?= esc(getenv('app.name') ?: 'MonApp') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="app">

    <!-- ===== OVERLAY MOBILE ===== -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar__brand">
            <div class="sidebar__brand-icon">M</div>
            <span><?= esc(getenv('app.name') ?: 'MonApp') ?></span>
            <button class="sidebar__close-btn" onclick="closeSidebar()" aria-label="Fermer le menu">
                <?= svg_icon('x') ?>
            </button>
        </div>

        <nav class="sidebar__nav">
            <span class="sidebar__group-label">Navigation</span>
            <a href="<?= base_url('user/dashboard') ?>"
               class="sidebar__link <?= (current_url(true)->getPath() === '/user/dashboard') ? 'active' : '' ?>">
                <?= svg_icon('grid') ?>
                Tableau de bord
            </a>

            <?php if (has_permission('wallet.view')): ?>
            <a href="<?= base_url('user/wallet') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/user/wallet') ? 'active' : '' ?>">
                <?= svg_icon('wallet') ?>
                Mon solde
            </a>
            <?php endif; ?>

            <span class="sidebar__group-label">Compte</span>
            <a href="<?= base_url('user/profile') ?>"
               class="sidebar__link <?= (current_url(true)->getPath() === '/user/profile') ? 'active' : '' ?>">
                <?= svg_icon('user') ?>
                Mon profil
            </a>
        </nav>

        <div class="sidebar__footer">
            <div class="sidebar__user">
                <div class="sidebar__avatar">
                    <?= strtoupper(substr(session('username') ?? 'U', 0, 1)) ?>
                </div>
                <div class="sidebar__user-info">
                    <div class="sidebar__user-name"><?= esc(session('username') ?? '') ?></div>
                    <div class="sidebar__user-role"><?= esc(session('user_type_name') ?? 'Utilisateur') ?></div>
                </div>
                <a href="<?= base_url('logout') ?>" class="sidebar__logout" title="Se déconnecter">
                    <?= svg_icon('logout') ?>
                </a>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <div class="main">
        <header class="topbar">
            <button class="topbar__burger" onclick="openSidebar()" aria-label="Ouvrir le menu">
                <?= svg_icon('menu') ?>
            </button>
            <span class="topbar__title"><?= esc($pageTitle ?? $title ?? '') ?></span>
            <div class="topbar__right">
                <span class="badge badge--gray"><?= esc(session('user_type_name') ?? 'Utilisateur') ?></span>
                <a href="<?= base_url('logout') ?>" class="topbar__logout-link" title="Se déconnecter">
                    <?= svg_icon('logout') ?>
                </a>
            </div>
        </header>

        <main class="page-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert--info"><?= esc(session()->getFlashdata('info')) ?></div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

</div>

<script>
function openSidebar()  { document.getElementById('sidebar').classList.add('is-open'); document.getElementById('sidebarOverlay').classList.add('is-open'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('is-open'); document.getElementById('sidebarOverlay').classList.remove('is-open'); }
</script>

<?php
function svg_icon(string $name): string {
    $icons = [
        'grid'    => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
        'wallet'  => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>',
        'user'    => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
        'lock'    => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>',
        'logout'  => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>',
        'menu'    => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',
        'x'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>

</body>
</html>
