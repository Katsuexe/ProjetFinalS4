<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card card--form" style="max-width: 800px; margin: 0 auto; overflow: hidden; position: relative;">
    
    <!-- Zone de sélection (Les 4 gros boutons) -->
    <div id="selection-screen" style="padding: 3rem 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            
            <a href="<?= base_url('user/operations/formulaire/depot') ?>" class="action-btn action-btn--depot">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Dépôt</span>
            </a>

            <a href="<?= base_url('user/operations/formulaire/retrait') ?>" class="action-btn action-btn--retrait">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                <span>Retrait</span>
            </a>

            <a href="<?= base_url('user/operations/formulaire/transfert') ?>" class="action-btn action-btn--transfert">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Transfert</span>
            </a>

            <a href="<?= base_url('user/operations/formulaire/transfert_multiple') ?>" class="action-btn action-btn--transfert_multiple">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Envoi Groupé</span>
            </a>

        </div>
    </div>
</div>

<script>
// :active en CSS est peu fiable sur les <a> dans Safari iOS sans geste
// explicite. On bascule donc une classe au toucher pour garantir
// l'animation même sur mobile, en plus du :hover desktop.
document.querySelectorAll('.action-btn').forEach(function (btn) {
    ['touchstart', 'touchend', 'touchcancel'].forEach(function (evt) {
        btn.addEventListener(evt, function () {
            btn.classList.toggle('is-active', evt === 'touchstart');
        }, { passive: true });
    });
});
</script>

<?= $this->endSection() ?>