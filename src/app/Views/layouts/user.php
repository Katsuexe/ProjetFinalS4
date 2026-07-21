<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Mon espace') ?> — <?= esc(getenv('app.name') ?: 'Blue Monay') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="app" id="app">

    <!-- ===== OVERLAY MOBILE ===== -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar__brand">
            <div class="sidebar__brand-icon">M</div>
            <span><?= esc(getenv('app.name') ?: 'Blue Monay') ?></span>
            <button class="sidebar__collapse-btn" id="sidebarCollapseBtn" aria-label="Réduire le menu" title="Réduire le menu">
                <?= svg_icon('chevron-left') ?>
            </button>
            <button class="sidebar__close-btn" onclick="closeSidebar()" aria-label="Fermer le menu">
                <?= svg_icon('x') ?>
            </button>
        </div>

        <nav class="sidebar__nav">
            <span class="sidebar__group-label">Navigation</span>
            <a href="<?= base_url('user/dashboard') ?>"
               class="sidebar__link <?= (current_url(true)->getPath() === '/user/dashboard') ? 'active' : '' ?>"
               title="Tableau de bord">
                <?= svg_icon('grid') ?>
                <span class="sidebar__link-label">Tableau de bord</span>
            </a>

            <span class="sidebar__group-label">Mobile Money</span>
            <a href="<?= base_url('user/operations/history') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/user/operations/history') ? 'active' : '' ?>"
               title="Historique &amp; Solde">
                <?= svg_icon('grid') ?>
                <span class="sidebar__link-label">Historique &amp; Solde</span>
            </a>
            <a href="<?= base_url('user/operations') ?>"
               class="sidebar__link <?= current_url(true)->getPath() === '/user/operations' || str_starts_with(current_url(true)->getPath(), '/user/operations/formulaire') ? 'active' : '' ?>"
               title="Effectuer une opération">
                <?= svg_icon('wallet') ?>
                <span class="sidebar__link-label">Effectuer une opération</span>
            </a>

            <span class="sidebar__group-label">Compte</span>
            <a href="<?= base_url('user/profile') ?>"
               class="sidebar__link <?= (current_url(true)->getPath() === '/user/profile') ? 'active' : '' ?>"
               title="Mon profil">
                <?= svg_icon('user') ?>
                <span class="sidebar__link-label">Mon profil</span>
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
                <!-- Un seul point de déconnexion dans toute l'app : le menu
                     déroulant du compte (avant : icône dupliquée ici ET dans
                     le pied de la sidebar). -->
                <div class="account-menu" id="accountMenu">
                    <button type="button" class="account-menu__trigger" id="accountMenuTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="account-menu__avatar"><?= strtoupper(substr(session('username') ?? 'U', 0, 1)) ?></span>
                        <span class="account-menu__name"><?= esc(session('username') ?? '') ?></span>
                        <?= svg_icon('chevron-down', 'account-menu__chevron') ?>
                    </button>
                    <div class="account-menu__dropdown" id="accountMenuDropdown">
                        <div class="account-menu__header">
                            <div class="account-menu__fullname"><?= esc(session('username') ?? '') ?></div>
                            <span class="badge badge--gray"><?= esc(session('user_type_name') ?? 'Utilisateur') ?></span>
                        </div>
                        <a href="<?= base_url('user/profile') ?>" class="account-menu__item">
                            <?= svg_icon('user') ?> Mon profil
                        </a>
                        <a href="<?= base_url('logout') ?>" class="account-menu__item account-menu__item--danger">
                            <?= svg_icon('logout') ?> Se déconnecter
                        </a>
                    </div>
                </div>
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

document.addEventListener('DOMContentLoaded', function () {
    // ── Collapse de la sidebar (desktop uniquement, état mémorisé) ──────
    const appEl = document.getElementById('app');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');

    if (appEl && localStorage.getItem('sidebar-collapsed') === '1' && window.innerWidth >= 768) {
        appEl.classList.add('sidebar-collapsed');
    }

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            const collapsed = appEl.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
        });
    }

    // ── Menu déroulant du compte (unique point de déconnexion) ──────────
    const trigger  = document.getElementById('accountMenuTrigger');
    const dropdown = document.getElementById('accountMenuDropdown');
    const menu     = document.getElementById('accountMenu');

    if (trigger && dropdown && menu) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = dropdown.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target)) {
                dropdown.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
</script>

<?php
function svg_icon(string $name, ?string $extraClass = null): string {
    $icons = [
        'grid'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
        'wallet'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>',
        'user'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
        'lock'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>',
        'logout'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>',
        'menu'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',
        'x'            => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        'chevron-left' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>',
        'chevron-down' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>',
    ];
    $svg = $icons[$name] ?? '';
    if ($svg && $extraClass) {
        $svg = str_replace('<svg ', '<svg class="' . esc($extraClass, 'attr') . '" ', $svg);
    }
    return $svg;
}
?>

</body>
</html>
