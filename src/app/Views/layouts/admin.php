<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Administration') ?> — <?= esc(getenv('app.name') ?: 'Blue Monay') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="app" id="app">

    <!-- ===== OVERLAY MOBILE ===== -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar__brand">
            <div class="sidebar__brand-icon">A</div>
            <span><?= esc(getenv('app.name') ?: 'Blue Monay') ?></span>
            <button class="sidebar__collapse-btn" id="sidebarCollapseBtn" aria-label="Réduire le menu" title="Réduire le menu">
                <?= svg_icon('chevron-left') ?>
            </button>
            <button class="sidebar__close-btn" onclick="closeSidebar()" aria-label="Fermer le menu">
                <?= svg_icon('x') ?>
            </button>
        </div>

        <nav class="sidebar__nav">
            <span class="sidebar__group-label">Général</span>
            <a href="<?= base_url('admin/dashboard') ?>"
               class="sidebar__link <?= (current_url(true)->getPath() === '/admin/dashboard') ? 'active' : '' ?>"
               title="Dashboard">
                <?= svg_icon('grid') ?>
                <span class="sidebar__link-label">Dashboard</span>
            </a>

            <?php if (has_any_permission(['users.manage', 'users.delete', 'users.create'])): ?>
            <span class="sidebar__group-label">Gestion</span>
            <a href="<?= base_url('admin/users') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/admin/users') ? 'active' : '' ?>"
               title="Utilisateurs">
                <?= svg_icon('users') ?>
                <span class="sidebar__link-label">Utilisateurs</span>
            </a>
            <?php endif; ?>

            <?php if (has_permission('types.manage')): ?>
            <a href="<?= base_url('admin/types') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/admin/types') ? 'active' : '' ?>"
               title="Types &amp; Rôles">
                <?= svg_icon('shield') ?>
                <span class="sidebar__link-label">Types &amp; Rôles</span>
            </a>
            <?php endif; ?>

            <?php if (has_permission('import.csv')): ?>
            <span class="sidebar__group-label">Outils</span>
            <a href="<?= base_url('admin/import') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/admin/import') ? 'active' : '' ?>"
               title="Import CSV">
                <?= svg_icon('upload') ?>
                <span class="sidebar__link-label">Import CSV</span>
            </a>
            <?php endif; ?>

            <?php if (has_permission('types.manage')): ?>
            <span class="sidebar__group-label">Mobile Money</span>
            <a href="<?= base_url('admin/settings/prefixes') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/admin/settings/prefixes') ? 'active' : '' ?>"
               title="Préfixes opérateur">
                <?= svg_icon('shield') ?>
                <span class="sidebar__link-label">Préfixes opérateur</span>
            </a>
            <a href="<?= base_url('admin/settings/fees') ?>"
               class="sidebar__link <?= str_starts_with(current_url(true)->getPath(), '/admin/settings/fees') ? 'active' : '' ?>"
               title="Barèmes de frais">
                <?= svg_icon('wallet') ?>
                <span class="sidebar__link-label">Barèmes de frais</span>
            </a>
            <?php endif; ?>

            <span class="sidebar__group-label">Compte</span>
            <a href="<?= base_url('admin/profile') ?>"
               class="sidebar__link <?= (current_url(true)->getPath() === '/admin/profile') ? 'active' : '' ?>"
               title="Mon profil">
                <?= svg_icon('user') ?>
                <span class="sidebar__link-label">Mon profil</span>
            </a>
        </nav>

        <div class="sidebar__footer">
            <div class="sidebar__user">
                <div class="sidebar__avatar">
                    <?= strtoupper(substr(session('username') ?? 'A', 0, 1)) ?>
                </div>
                <div class="sidebar__user-info">
                    <div class="sidebar__user-name"><?= esc(session('username') ?? '') ?></div>
                    <div class="sidebar__user-role"><?= esc(session('user_type_name') ?? 'Administrateur') ?></div>
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
                <!-- Point de déconnexion unique (menu déroulant du compte),
                     comme côté user : plus de doublon avec la sidebar. -->
                <div class="account-menu" id="accountMenu">
                    <button type="button" class="account-menu__trigger" id="accountMenuTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="account-menu__avatar"><?= strtoupper(substr(session('username') ?? 'A', 0, 1)) ?></span>
                        <span class="account-menu__name"><?= esc(session('username') ?? '') ?></span>
                        <?= svg_icon('chevron-down', 'account-menu__chevron') ?>
                    </button>
                    <div class="account-menu__dropdown" id="accountMenuDropdown">
                        <div class="account-menu__header">
                            <div class="account-menu__fullname"><?= esc(session('username') ?? '') ?></div>
                            <span class="badge badge--blue"><?= esc(session('user_type_name') ?? 'Admin') ?></span>
                        </div>
                        <a href="<?= base_url('admin/profile') ?>" class="account-menu__item">
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
// Helper SVG icons inline
function svg_icon(string $name, ?string $extraClass = null): string {
    $icons = [
        'grid'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
        'users'        => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
        'shield'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
        'user'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
        'logout'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>',
        'wallet'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>',
        'lock'         => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>',
        'upload'       => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>',
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
