<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Bonjour, <?= esc(session('username')) ?></h2>
    <p>Bienvenue sur votre espace Mobile Money.</p>
</div>

<!-- ── Solde + Actions rapides ────────────────────────────────────────── -->
<div class="stats-grid" style="margin-bottom:1.5rem;">

    <!-- Solde -->
    <div class="stat-card" style="background:linear-gradient(135deg,var(--first-color),var(--first-color-dark));color:#fff;border:none;">
        <div class="stat-icon" style="background:rgba(255,255,255,.15);color:#fff;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label" style="color:rgba(255,255,255,.75);">Mon solde</div>
            <div class="stat-body__value" style="color:#fff;font-size:1.6rem; display:flex; align-items:center; gap:0.5rem;">
                <span id="balance-display" data-balance="<?= esc($balance ?? 0) ?>">••••</span>
                <span style="font-size:1rem;opacity:.8;"><?= esc($currency ?? 'Ar') ?></span>
                <button type="button" id="toggle-balance" aria-label="Afficher le solde" style="background:none;border:none;color:#fff;cursor:pointer;padding:0;display:flex;align-items:center;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </button>
            </div>
            <?php if ($balance_updated_at ?? null): ?>
                <div style="font-size:.75rem;opacity:.6;margin-top:.25rem;">Màj : <?= esc($balance_updated_at) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Numéro de téléphone -->
    <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3M10.5 19.5h3"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label">Mon numéro</div>
            <div class="stat-body__value" style="font-size:1.1rem;letter-spacing:.05rem; display:flex; align-items:center; gap:0.5rem;">
                <span id="phone-display"><?= esc(session('phone') ?: '—') ?></span>
                <?php if (session('phone')): ?>
                <button type="button" id="copy-phone" aria-label="Copier le numéro" title="Copier le numéro" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;display:flex;align-items:center;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/></svg>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Actions Mobile Money ─────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card__header">
        <span class="card__title">Opérations</span>
    </div>
    <div class="card__body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;padding:1.25rem;">

        <a href="<?= base_url('user/operations/formulaire/depot') ?>" class="quick-action quick-action--green">
            <div class="quick-action__icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </div>
            <span class="quick-action__label">Dépôt</span>
            <span class="quick-action__hint">Sans frais</span>
        </a>

        <a href="<?= base_url('user/operations/formulaire/retrait') ?>" class="quick-action quick-action--amber">
            <div class="quick-action__icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
            </div>
            <span class="quick-action__label">Retrait</span>
            <span class="quick-action__hint">Frais applicables</span>
        </a>

        <a href="<?= base_url('user/operations/formulaire/transfert') ?>" class="quick-action quick-action--blue">
            <div class="quick-action__icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            </div>
            <span class="quick-action__label">Transfert</span>
            <span class="quick-action__hint">Frais applicables</span>
        </a>

        <a href="<?= base_url('user/operations/formulaire/transfert_multiple') ?>" class="quick-action quick-action--purple">
            <div class="quick-action__icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            </div>
            <span class="quick-action__label">Groupé</span>
            <span class="quick-action__hint">Envoi multiple</span>
        </a>

        <a href="<?= base_url('user/operations/history') ?>" class="quick-action quick-action--violet">
            <div class="quick-action__icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="quick-action__label">Historique</span>
            <span class="quick-action__hint">Toutes les ops.</span>
        </a>

    </div>
</div>

<!-- ── Dernières transactions ──────────────────────────────────────────── -->
<?php if (!empty($recent_transactions)): ?>
<div class="card">
    <div class="card__header">
        <span class="card__title">Dernières opérations</span>
        <a href="<?= base_url('user/operations/history') ?>" class="btn btn--outline btn--sm">Voir tout</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Montant (Ar)</th>
                <th>Frais (Ar)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_transactions as $tx): ?>
            <tr>
                <td style="font-size:.85rem;"><?= $tx['created_at'] ? date('d/m/Y H:i', strtotime($tx['created_at'])) : '—' ?></td>
                <td><span class="badge badge--blue"><?= esc($tx['op_name']) ?></span></td>
                <td style="font-weight:600;"><?= number_format($tx['amount'], 2, ',', ' ') ?></td>
                <td style="color:var(--text-muted);"><?= $tx['fee_amount'] > 0 ? number_format($tx['fee_amount'], 2, ',', ' ') : '—' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="card">
    <div style="padding:2.5rem;text-align:center;color:var(--text-muted);">
        <div style="font-size:2.5rem;margin-bottom:.75rem;"></div>
        <p>Aucune opération pour l'instant.<br>
        <a href="<?= base_url('user/operations/formulaire/depot') ?>">Faites votre premier dépôt →</a></p>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-balance');
    const displayEl = document.getElementById('balance-display');
    
    if (toggleBtn && displayEl) {
        toggleBtn.addEventListener('click', function () {
            const isHidden = displayEl.textContent.includes('••••');
            if (isHidden) {
                const formatter = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2 });
                displayEl.textContent = formatter.format(displayEl.dataset.balance);
            } else {
                displayEl.textContent = '••••';
            }
        });
    }

    const copyBtn = document.getElementById('copy-phone');
    const phoneEl = document.getElementById('phone-display');
    if (copyBtn && phoneEl) {
        copyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(phoneEl.textContent.trim()).then(function () {
                const original = copyBtn.innerHTML;
                copyBtn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;color:var(--success-color);"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
                setTimeout(function () { copyBtn.innerHTML = original; }, 1500);
            });
        });
    }
});
</script>

<?= $this->endSection() ?>
