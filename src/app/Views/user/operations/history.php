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
            <div class="stat-body__value"><?= number_format($balance, 2, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="stat-card" style="gap:.5rem; flex-direction:column; align-items:flex-start; justify-content:center;">
        <div class="stat-body__label" style="font-size:.8rem;color:var(--text-muted)">Actions rapides</div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= base_url('user/operations/deposit') ?>" class="btn btn--primary btn--sm">+ Dépôt</a>
            <a href="<?= base_url('user/operations/withdraw') ?>" class="btn btn--outline btn--sm">- Retrait</a>
            <a href="<?= base_url('user/operations/transfer') ?>" class="btn btn--outline btn--sm">↗ Transfert</a>
        </div>
    </div>
</div>

<!-- Historique des transactions -->
<div class="card">
    <div class="card__header">
        <span class="card__title">Historique des opérations</span>
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
                        <?= esc($label) ?>
                        <?php if ($isTransfer): ?>
                            <?php if ($isSender): ?>
                                → <?= esc($tx['recipient_phone'] ?? '?') ?>
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

<?= $this->endSection() ?>
