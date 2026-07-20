<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Bonjour, <?= esc(session('username')) ?></h2>
    <p>Bienvenue sur votre espace Mobile Money.</p>
</div>

<!-- ── Solde + Actions rapides ────────────────────────────────────────── -->
<div class="stats-grid" style="margin-bottom:1.5rem;">

    <!-- Solde -->
    <div class="stat-card" style="background:linear-gradient(135deg,#1a56db,#0f3ba8);color:#fff;border:none;">
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
            <div class="stat-body__value" style="font-size:1.1rem;letter-spacing:.05rem;">
                <?= esc(session('phone') ?: '—') ?>
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

        <a href="<?= base_url('user/operations/formulaire/depot') ?>" style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;border-radius:12px;background:#f0fdf4;border:2px solid #bbf7d0;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
            <div style="width:48px;height:48px;border-radius:50%;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </div>
            <span style="font-weight:600;color:#166534;font-size:.9rem;">Dépôt</span>
            <span style="font-size:.75rem;color:#4ade80;">Sans frais</span>
        </a>

        <a href="<?= base_url('user/operations/formulaire/retrait') ?>" style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;border-radius:12px;background:#fff7ed;border:2px solid #fed7aa;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#ffedd5'" onmouseout="this.style.background='#fff7ed'">
            <div style="width:48px;height:48px;border-radius:50%;background:#f97316;color:#fff;display:flex;align-items:center;justify-content:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
            </div>
            <span style="font-weight:600;color:#9a3412;font-size:.9rem;">Retrait</span>
            <span style="font-size:.75rem;color:#fb923c;">Frais applicables</span>
        </a>

        <a href="<?= base_url('user/operations/formulaire/transfert') ?>" style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;border-radius:12px;background:#eff6ff;border:2px solid #bfdbfe;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
            <div style="width:48px;height:48px;border-radius:50%;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            </div>
            <span style="font-weight:600;color:#1d4ed8;font-size:.9rem;">Transfert</span>
            <span style="font-size:.75rem;color:#60a5fa;">Frais applicables</span>
        </a>

        <a href="<?= base_url('user/operations/formulaire/transfert_multiple') ?>" style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;border-radius:12px;background:#fdf4ff;border:2px solid #f5d0fe;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#fae8ff'" onmouseout="this.style.background='#fdf4ff'">
            <div style="width:48px;height:48px;border-radius:50%;background:#c026d3;color:#fff;display:flex;align-items:center;justify-content:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            </div>
            <span style="font-weight:600;color:#86198f;font-size:.9rem;">Groupé</span>
            <span style="font-size:.75rem;color:#d946ef;">Envoi multiple</span>
        </a>

        <a href="<?= base_url('user/operations/history') ?>" style="display:flex;flex-direction:column;align-items:center;gap:.6rem;padding:1.25rem 1rem;border-radius:12px;background:#faf5ff;border:2px solid #e9d5ff;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='#f3e8ff'" onmouseout="this.style.background='#faf5ff'">
            <div style="width:48px;height:48px;border-radius:50%;background:#a855f7;color:#fff;display:flex;align-items:center;justify-content:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span style="font-weight:600;color:#6b21a8;font-size:.9rem;">Historique</span>
            <span style="font-size:.75rem;color:#c084fc;">Toutes les ops.</span>
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
});
</script>

<?= $this->endSection() ?>
