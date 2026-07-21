<?php
// Rendu du même détail de transaction que buildBreakdownCard() en JS
// (voir formulaire.php) — réutilise les classes .receipt-breakdown__*
// du design system au lieu de dupliquer le visuel avec des couleurs
// codées en dur, pour rester visuellement identique à l'aperçu live.
?>
<div class="receipt-breakdown">
    <h4 class="receipt-breakdown__title">Détail de la transaction</h4>
    <table class="receipt-breakdown__table">
        <tr>
            <td class="receipt-breakdown__label">Montant envoyé</td>
            <td class="receipt-breakdown__value"><span class="amount-val"><?= number_format($breakdown['amount'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <tr>
            <td class="receipt-breakdown__label">Frais de transfert</td>
            <td class="receipt-breakdown__value"><span class="fee-val"><?= number_format($breakdown['transfer_fee'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php if (!empty($breakdown['is_external'])): ?>
        <tr class="external-row">
            <td class="receipt-breakdown__label">Commission inter-opérateur</td>
            <td class="receipt-breakdown__value receipt-breakdown__value--warning"><span class="commission-val"><?= number_format($breakdown['commission'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($breakdown['withdraw_fee_eq']) && $breakdown['withdraw_fee_eq'] > 0): ?>
        <tr class="withdraw-fee-row">
            <td class="receipt-breakdown__label">Frais de retrait inclus</td>
            <td class="receipt-breakdown__value receipt-breakdown__value--success"><span class="withdraw-fee-val"><?= number_format($breakdown['withdraw_fee_eq'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php endif; ?>
        <tr class="receipt-breakdown__row--total">
            <td class="receipt-breakdown__label--total">Total débité</td>
            <td class="receipt-breakdown__value--total"><span class="total-debit-val"><?= number_format($breakdown['total_debit'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php if (isset($breakdown['amount_received'])): ?>
        <tr class="receipt-breakdown__row--highlight">
            <td class="receipt-breakdown__label">Montant reçu par le destinataire</td>
            <td class="receipt-breakdown__value"><span class="amount-received-val"><?= number_format($breakdown['amount_received'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php endif; ?>
    </table>
</div>
