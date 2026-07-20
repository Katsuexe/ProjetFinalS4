<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<h1 class="page-title"><?= esc($title) ?></h1>

<!-- Solde actuel -->
<div class="stats-grid" style="margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon stat-icon--green">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label">Solde actuel</div>
            <div class="stat-body__value" style="display:flex; align-items:center; gap:0.5rem;">
                <span id="balance-display" data-balance="<?= esc($balance ?? 0) ?>">••••</span>
                <span style="font-size:1rem;opacity:.8;">Ar</span>
                <button type="button" id="toggle-balance" aria-label="Afficher le solde" style="background:none;border:none;color:inherit;cursor:pointer;padding:0;display:flex;align-items:center;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </button>
            </div>
        </div>
    </div>
    <div class="stat-card" style="gap:.5rem; flex-direction:column; align-items:flex-start; justify-content:center;">
        <div class="stat-body__label" style="font-size:.8rem;color:var(--text-muted)">Actions rapides</div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= base_url('user/operations/formulaire/depot') ?>" class="btn btn--primary btn--sm">+ Dépôt</a>
            <a href="<?= base_url('user/operations/formulaire/retrait') ?>" class="btn btn--outline btn--sm">- Retrait</a>
            <a href="<?= base_url('user/operations/formulaire/transfert') ?>" class="btn btn--outline btn--sm">↗ Transfert</a>
        </div>
    </div>
</div>

<!-- Historique des transactions -->
<div class="card">
    <div class="card__header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="card__title">Vos 10 dernières opérations</h2>
        <a href="<?= base_url('user/operations/history/pdf') ?>" class="btn btn--outline btn--sm" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Exporter en PDF
        </a>
    </div>

    <?php if (empty($transactions)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">
            Aucune transaction pour l'instant.
        </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Montant (Ar)</th>
                    <th>Frais (Ar)</th>
                    <th>Détail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <?php
                    $isSender    = ((int)$tx['user_id'] === (int)$userId);
                    $isDeposit   = ($tx['op_name'] === 'Dépôt');
                    $isTransfer  = ($tx['op_name'] === 'Transfert');

                    if ($isDeposit) {
                        $sign  = '+';
                        $color = 'green';
                        $label = 'Dépôt';
                    } elseif ($isSender) {
                        $sign  = '-';
                        $color = 'red';
                        $label = $isTransfer ? 'Transfert envoyé' : 'Retrait';
                    } else {
                        $sign  = '+';
                        $color = 'green';
                        $label = 'Transfert reçu';
                    }
                    
                    $external_badge = '';
                    if ($isTransfer && $isSender && !empty($tx['external_operator_id'])) {
                        $external_badge = ' <span class="badge badge--orange" style="background:#d97706; font-size:0.7rem;">' . esc($tx['external_operator_name'] ?? 'Externe') . '</span>';
                    }
                ?>
                <tr>
                    <td><?= $tx['created_at'] ? date('d/m/Y H:i', strtotime($tx['created_at'])) : '—' ?></td>
                    <td><span class="badge badge--blue"><?= esc($tx['op_name']) ?></span></td>
                    <td style="font-weight:600;color:var(--<?= $color === 'green' ? 'success' : 'danger' ?>);">
                        <?= $sign ?><?= number_format($tx['amount'], 2, ',', ' ') ?>
                    </td>
                    <td style="color:var(--text-muted);">
                        <?= $tx['fee_amount'] > 0 ? number_format($tx['fee_amount'], 2, ',', ' ') : '—' ?>
                    </td>
                    <td style="font-size:.85rem;color:var(--text-muted);">
                        <?= esc($label) ?><?= $external_badge ?>
                        <?php if ($isTransfer): ?>
                            <?php if ($isSender): ?>
                                → <?= esc($tx['external_phone'] ?? $tx['recipient_phone'] ?? '?') ?>
                            <?php else: ?>
                                ← <?= esc($tx['sender_phone'] ?? '?') ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

</div>

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
