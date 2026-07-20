<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php
// Définir la couleur du thème selon le type
$colorMap = [
    'depot' => '#059669',
    'retrait' => '#d97706',
    'transfert' => '#2563eb',
    'transfert_multiple' => '#9333ea'
];
$themeColor = $colorMap[$type] ?? '#1e293b';

$subtitles = [
    'depot' => '(Simulation : le dépôt est automatique et sans frais)',
    'retrait' => 'Veuillez saisir le montant à retirer de votre compte.',
    'transfert' => 'Envoyez de l\'argent vers un autre compte Mobile Money.',
    'transfert_multiple' => 'Précisez un numéro et un montant pour chaque destinataire.'
];
?>

<div class="card card--form" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 class="card__title" style="color: <?= $themeColor ?>; margin: 0;"><?= esc($title) ?></h2>
        <a href="<?= base_url('user/operations/formulaire') ?>" class="btn btn--outline btn--sm" style="border-radius: 50px; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; text-decoration:none;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Changer d'opération
        </a>
    </div>
    
    <p class="text-muted" style="margin-bottom:1.5rem;"><?= esc($subtitles[$type] ?? '') ?></p>
    
    <form action="<?= base_url('user/operations/traiter/' . $type) ?>" method="post" class="form">
        <?= csrf_field() ?>
        
        <input type="hidden" name="type_operation" value="<?= esc($type) ?>">

        <?php if ($type !== 'transfert_multiple'): ?>
        <div class="form-group">
            <label for="amount">Montant (Ar)</label>
            <input type="number" name="amount" id="amount" min="100" step="100" required>
        </div>
        <?php endif; ?>

        <?php if ($type === 'retrait'): ?>
            <div id="preview-withdraw" style="display:none; margin-bottom: 1.5rem;"></div>
        <?php endif; ?>

        <?php if ($type === 'transfert'): ?>
            <div class="form-group">
                <label for="recipient_phone">Numéro du destinataire</label>
                <input type="tel" name="recipient_phone" id="recipient_phone" required inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10">
            </div>
            
            <?php if (!empty($external_operators)): ?>
            <div class="form-group">
                <label for="force_operator_id">Opérateur externe (Optionnel)</label>
                <select name="force_operator_id" id="force_operator_id">
                    <option value="">Détection automatique (recommandé)</option>
                    <?php foreach ($external_operators as $extOp): ?>
                        <option value="<?= esc($extOp['id']) ?>"><?= esc($extOp['nom']) ?> (Frais: <?= esc($extOp['commission_pourcentage']) ?>%)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom: 1.5rem;">
                <input type="checkbox" name="include_withdraw_fee" id="include_withdraw_fee" value="1" checked style="width:auto;">
                <label for="include_withdraw_fee" style="margin:0; color:#475569; font-size:0.9rem; font-weight:500;">Inclure les frais de retrait</label>
            </div>
            
            <div id="preview-transfer" style="display:none; margin-bottom: 1.5rem;"></div>
        <?php endif; ?>

        <?php if ($type === 'transfert_multiple'): ?>
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom: 1.5rem;">
                <input type="checkbox" name="include_withdraw_fee_multiple" id="include_withdraw_fee_multiple" value="1" checked style="width:auto;">
                <label for="include_withdraw_fee_multiple" style="margin:0; color:#475569; font-size:0.9rem; font-weight:500;">Inclure les frais de retrait</label>
            </div>
            
            <div id="recipients-container">
                <div class="recipient-row" style="display:flex; gap:0.5rem; margin-bottom: 1rem; align-items: flex-end;">
                    <div class="form-group" style="flex:2; margin-bottom: 0;">
                        <label>Numéro 1</label>
                        <input type="tel" name="recipient_phones[]" class="phone-input" required inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10">
                    </div>
                    <div class="form-group" style="flex:1; margin-bottom: 0;">
                        <label>Montant 1</label>
                        <input type="number" name="recipient_amounts[]" class="amount-input" required min="100" step="100">
                    </div>
                    <button type="button" class="btn btn--outline remove-recipient" style="padding:0.5rem; height:42px;" disabled>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                    </button>
                </div>
            </div>
            
            <button type="button" id="add-recipient" class="btn btn--outline" style="width:100%; margin-bottom:1.5rem; border-style:dashed;">+ Ajouter un destinataire</button>

            <div id="preview-multiple-transfer" style="display:none; margin-bottom: 1.5rem;"></div>
        <?php endif; ?>

        <button type="submit" class="btn btn--primary" id="submit-btn" style="width: 100%; background: <?= $themeColor ?>; border-color: <?= $themeColor ?>; margin-top: 0.5rem;">
            Valider l'opération
        </button>
    </form>
</div>

<?php if ($type === 'retrait'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const previewContainer = document.getElementById('preview-withdraw');
    let timeoutId;

    function updatePreview() {
        const amount = amountInput.value;
        if (amount >= 100) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                const formData = new FormData();
                formData.append('amount', amount);
                
                fetch('<?= base_url('user/operations/preview-withdraw') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    let html = `
                    <div class="receipt-breakdown" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem;">
                        <h4 style="font-size:1rem; margin-bottom:1rem; color:#1e293b; border-bottom:1px solid #e2e8f0; padding-bottom:0.5rem;">Aperçu du retrait</h4>
                        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                            <tr>
                                <td style="padding:0.4rem 0; color:#475569;">Montant demandé</td>
                                <td style="padding:0.4rem 0; text-align:right; font-weight:500;">${new Intl.NumberFormat('fr-FR').format(data.amount)} Ar</td>
                            </tr>
                            <tr>
                                <td style="padding:0.4rem 0; color:#475569;">Frais de retrait</td>
                                <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#d97706;">${new Intl.NumberFormat('fr-FR').format(data.fee)} Ar</td>
                            </tr>
                            <tr style="border-top:1px dashed #cbd5e1;">
                                <td style="padding:0.6rem 0; color:#0f172a; font-weight:600;">Total débité de votre solde</td>
                                <td style="padding:0.6rem 0; text-align:right; font-weight:700; color:#b91c1c; font-size:1.05rem;">${new Intl.NumberFormat('fr-FR').format(data.total_debit)} Ar</td>
                            </tr>
                        </table>
                    </div>`;
                    previewContainer.innerHTML = html;
                    previewContainer.style.display = 'block';
                });
            }, 300);
        } else {
            previewContainer.style.display = 'none';
        }
    }
    if (amountInput) amountInput.addEventListener('input', updatePreview);
});
</script>
<?php endif; ?>

<?php if ($type === 'transfert'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('recipient_phone');
    const amountInput = document.getElementById('amount');
    const includeFeeCheck = document.getElementById('include_withdraw_fee');
    const forceOperatorSelect = document.getElementById('force_operator_id');
    const previewContainer = document.getElementById('preview-transfer');
    const submitBtn = document.getElementById('submit-btn');
    let timeoutId;

    function updatePreview() {
        const phone = phoneInput.value;
        const amount = amountInput.value;
        const forceOp = forceOperatorSelect ? forceOperatorSelect.value : '';
        
        if (phone.length >= 10 && amount >= 100) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                const formData = new FormData();
                formData.append('recipient_phone', phone);
                formData.append('amount', amount);
                formData.append('include_withdraw_fee', includeFeeCheck.checked ? '1' : '0');
                if (forceOp) formData.append('force_operator_id', forceOp);
                
                fetch('<?= base_url('user/operations/preview-transfer') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        previewContainer.innerHTML = `<div class="alert alert--error">${data.error}</div>`;
                        previewContainer.style.display = 'block';
                        submitBtn.disabled = true;
                    } else {
                        let html = `
                        <div class="receipt-breakdown" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem;">
                            <h4 style="font-size:1rem; margin-bottom:1rem; color:#1e293b; border-bottom:1px solid #e2e8f0; padding-bottom:0.5rem;">
                                Aperçu de la transaction
                                ${data.is_external ? '<span class="badge badge--orange" style="float:right; font-size:0.7rem; background:#d97706;">Vers opérateur externe</span>' : ''}
                            </h4>
                            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Montant envoyé</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500;">${new Intl.NumberFormat('fr-FR').format(data.amount)} Ar</td>
                                </tr>
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Frais de transfert</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500;">${new Intl.NumberFormat('fr-FR').format(data.transfer_fee)} Ar</td>
                                </tr>`;
                                
                        if (data.is_external && data.commission > 0) {
                            html += `
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Commission inter-opérateur</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#d97706;">${new Intl.NumberFormat('fr-FR').format(data.commission)} Ar</td>
                                </tr>`;
                        }
                        
                        if (data.withdraw_fee_eq > 0) {
                            html += `
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Frais de retrait inclus</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#059669;">${new Intl.NumberFormat('fr-FR').format(data.withdraw_fee_eq)} Ar</td>
                                </tr>`;
                        }
                        
                        html += `
                                <tr style="border-top:1px dashed #cbd5e1;">
                                    <td style="padding:0.6rem 0; color:#0f172a; font-weight:600;">Total débité</td>
                                    <td style="padding:0.6rem 0; text-align:right; font-weight:700; color:#b91c1c; font-size:1.05rem;">${new Intl.NumberFormat('fr-FR').format(data.total_debit)} Ar</td>
                                </tr>
                                <tr style="background:#e0e7ff; border-radius:4px;">
                                    <td style="padding:0.6rem 0.5rem; color:#4338ca; font-weight:600; border-radius:4px 0 0 4px;">Montant reçu par le destinataire</td>
                                    <td style="padding:0.6rem 0.5rem; text-align:right; font-weight:700; color:#4338ca; border-radius:0 4px 4px 0;">${new Intl.NumberFormat('fr-FR').format(data.amount_received)} Ar</td>
                                </tr>
                            </table>
                        </div>`;
                        
                        previewContainer.innerHTML = html;
                        previewContainer.style.display = 'block';
                        submitBtn.disabled = false;
                    }
                });
            }, 500);
        } else {
            previewContainer.style.display = 'none';
        }
    }

    if (phoneInput && amountInput) {
        phoneInput.addEventListener('input', updatePreview);
        amountInput.addEventListener('input', updatePreview);
        includeFeeCheck.addEventListener('change', updatePreview);
        if (forceOperatorSelect) forceOperatorSelect.addEventListener('change', updatePreview);
    }
});
</script>
<?php endif; ?>

<?php if ($type === 'transfert_multiple'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('recipients-container');
    const btnAdd = document.getElementById('add-recipient');
    const includeFeeCheck = document.getElementById('include_withdraw_fee_multiple');
    const previewContainer = document.getElementById('preview-multiple-transfer');
    const submitBtn = document.getElementById('submit-btn');
    let recipientCount = 1;
    let timeoutId;

    function updateRemoveButtons() {
        const removeBtns = document.querySelectorAll('.remove-recipient');
        removeBtns.forEach(btn => {
            btn.disabled = removeBtns.length <= 1;
        });
    }

    function updatePreview() {
        const phones = Array.from(document.querySelectorAll('.phone-input')).map(el => el.value);
        const amounts = Array.from(document.querySelectorAll('.amount-input')).map(el => el.value);
        
        let hasValidInput = false;
        const formData = new FormData();
        
        for (let i = 0; i < phones.length; i++) {
            if (phones[i].length >= 10 && amounts[i] >= 100) {
                hasValidInput = true;
            }
            formData.append('phones[]', phones[i]);
            formData.append('amounts[]', amounts[i]);
        }
        
        formData.append('include_withdraw_fee', includeFeeCheck.checked ? '1' : '0');

        if (hasValidInput) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                fetch('<?= base_url('user/operations/preview-multiple-transfer') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        previewContainer.innerHTML = `<div class="alert alert--error">${data.error}</div>`;
                        previewContainer.style.display = 'block';
                        submitBtn.disabled = true;
                    } else {
                        let html = `
                        <div class="receipt-breakdown" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem;">
                            <h4 style="font-size:1rem; margin-bottom:1rem; color:#1e293b; border-bottom:1px solid #e2e8f0; padding-bottom:0.5rem;">Aperçu global de l'envoi groupé</h4>
                            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Montant total envoyé</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500;">${new Intl.NumberFormat('fr-FR').format(data.total_amount)} Ar</td>
                                </tr>
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Frais de transfert cumulés</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500;">${new Intl.NumberFormat('fr-FR').format(data.total_transfer_fee)} Ar</td>
                                </tr>`;
                                
                        if (data.total_commission > 0) {
                            html += `
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Commissions inter-opérateurs</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#d97706;">${new Intl.NumberFormat('fr-FR').format(data.total_commission)} Ar</td>
                                </tr>`;
                        }
                        
                        if (data.total_withdraw_fee > 0) {
                            html += `
                                <tr>
                                    <td style="padding:0.4rem 0; color:#475569;">Frais de retrait cumulés</td>
                                    <td style="padding:0.4rem 0; text-align:right; font-weight:500; color:#059669;">${new Intl.NumberFormat('fr-FR').format(data.total_withdraw_fee)} Ar</td>
                                </tr>`;
                        }
                        
                        html += `
                                <tr style="border-top:1px dashed #cbd5e1;">
                                    <td style="padding:0.6rem 0; color:#0f172a; font-weight:600;">Total débité de votre solde</td>
                                    <td style="padding:0.6rem 0; text-align:right; font-weight:700; color:#b91c1c; font-size:1.05rem;">${new Intl.NumberFormat('fr-FR').format(data.total_debit)} Ar</td>
                                </tr>
                            </table>
                        </div>`;
                        
                        previewContainer.innerHTML = html;
                        previewContainer.style.display = 'block';
                        submitBtn.disabled = false;
                    }
                });
            }, 500);
        } else {
            previewContainer.style.display = 'none';
            submitBtn.disabled = true; // Wait for valid input
        }
    }

    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            recipientCount++;
            const row = document.createElement('div');
            row.className = 'recipient-row';
            row.style.cssText = 'display:flex; gap:0.5rem; margin-bottom: 1rem; align-items: flex-end;';
            row.innerHTML = `
                <div class="form-group" style="flex:2; margin-bottom: 0;">
                    <label>Numéro ${recipientCount}</label>
                    <input type="tel" name="recipient_phones[]" class="phone-input" required inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10">
                </div>
                <div class="form-group" style="flex:1; margin-bottom: 0;">
                    <label>Montant ${recipientCount}</label>
                    <input type="number" name="recipient_amounts[]" class="amount-input" required min="100" step="100">
                </div>
                <button type="button" class="btn btn--outline remove-recipient" style="padding:0.5rem; height:42px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                </button>
            `;
            container.appendChild(row);
            updateRemoveButtons();
            
            // Add listeners to new inputs
            row.querySelector('.phone-input').addEventListener('input', updatePreview);
            row.querySelector('.amount-input').addEventListener('input', updatePreview);
        });
    }

    if (container) {
        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-recipient');
            if (btn && !btn.disabled) {
                btn.closest('.recipient-row').remove();
                updateRemoveButtons();
                
                const rows = container.querySelectorAll('.recipient-row');
                rows.forEach((r, index) => {
                    r.querySelector('.phone-input').previousElementSibling.textContent = 'Numéro ' + (index + 1);
                    r.querySelector('.amount-input').previousElementSibling.textContent = 'Montant ' + (index + 1);
                });
                recipientCount = rows.length;
                updatePreview();
            }
        });
        
        // Initial listeners
        document.querySelectorAll('.phone-input').forEach(el => el.addEventListener('input', updatePreview));
        document.querySelectorAll('.amount-input').forEach(el => el.addEventListener('input', updatePreview));
    }
    
    if (includeFeeCheck) includeFeeCheck.addEventListener('change', updatePreview);
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>
