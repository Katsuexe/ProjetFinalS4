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
            
            <a href="<?= base_url('user/operations/formulaire/depot') ?>" class="action-btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); text-decoration:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Dépôt</span>
            </a>

            <a href="<?= base_url('user/operations/formulaire/retrait') ?>" class="action-btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); text-decoration:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                <span>Retrait</span>
            </a>

            <a href="<?= base_url('user/operations/formulaire/transfert') ?>" class="action-btn" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); text-decoration:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Transfert</span>
            </a>

            <a href="<?= base_url('user/operations/formulaire/transfert_multiple') ?>" class="action-btn" style="background: linear-gradient(135deg, #c026d3 0%, #9333ea 100%); text-decoration:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Envoi Groupé</span>
            </a>

        </div>
    </div>
</div>

<style>
.action-btn {
    border: none;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s, box-shadow 0.2s;
}
.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
.action-btn svg {
    width: 32px;
    height: 32px;
}
</style>

<?= $this->endSection() ?>
