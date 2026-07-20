<div class="receipt-breakdown" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem; margin-top:1rem;">
    <h4 style="font-size:1rem; margin-bottom:1rem; color:#1e293b; border-bottom:1px solid #e2e8f0; padding-bottom:0.5rem;">Détail de la transaction</h4>
    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
        <tr>
            <td style="padding:0.4rem 0; color:#475569;">Montant envoyé</td>
            <td style="padding:0.4rem 0; text-align:right; font-weight:500;"><span class="amount-val"><?= number_format($breakdown['amount'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <tr>
            <td style="padding:0.4rem 0; color:#475569;">Frais de transfert</td>
            <td style="padding:0.4rem 0; text-align:right; font-weight:500;"><span class="fee-val"><?= number_format($breakdown['transfer_fee'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php if (!empty($breakdown['is_external'])): ?>
        <tr class="external-row">
            <td style="padding:0.4rem 0; color:#475569;">Commission inter-opérateur</td>
            <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#d97706;"><span class="commission-val"><?= number_format($breakdown['commission'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($breakdown['withdraw_fee_eq']) && $breakdown['withdraw_fee_eq'] > 0): ?>
        <tr class="withdraw-fee-row">
            <td style="padding:0.4rem 0; color:#475569;">Frais de retrait inclus</td>
            <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#059669;"><span class="withdraw-fee-val"><?= number_format($breakdown['withdraw_fee_eq'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <?php endif; ?>
        <tr style="border-top:1px dashed #cbd5e1;">
            <td style="padding:0.6rem 0; color:#0f172a; font-weight:600;">Total débité</td>
            <td style="padding:0.6rem 0; text-align:right; font-weight:700; color:#b91c1c; font-size:1.05rem;"><span class="total-debit-val"><?= number_format($breakdown['total_debit'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
        <tr style="background:#e0e7ff; border-radius:4px;">
            <td style="padding:0.6rem 0.5rem; color:#4338ca; font-weight:600; border-radius:4px 0 0 4px;">Montant reçu par le destinataire</td>
            <td style="padding:0.6rem 0.5rem; text-align:right; font-weight:700; color:#4338ca; border-radius:0 4px 4px 0;"><span class="amount-received-val"><?= number_format($breakdown['amount_received'] ?? 0, 0, ',', ' ') ?></span> Ar</td>
        </tr>
    </table>
</div>
