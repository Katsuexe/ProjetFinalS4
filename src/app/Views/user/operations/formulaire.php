<?= $this->extend('layouts/user') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php
// Classes de thème par type d'opération (voir .op-title--* / .op-submit--*
// dans app.css) : plus de couleur hexadécimale codée en dur dans la vue.
$themeClass = in_array($type, ['depot', 'retrait', 'transfert', 'transfert_multiple'], true)
    ? $type
    : null;

$subtitles = [
    'depot' => '(Simulation : le dépôt est automatique et sans frais)',
    'retrait' => 'Veuillez saisir le montant à retirer de votre compte.',
    'transfert' => 'Envoyez de l\'argent vers un autre compte Mobile Money.',
    'transfert_multiple' => 'Précisez un numéro et un montant pour chaque destinataire.'
];
?>

<div class="card card--form" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 class="card__title <?= $themeClass ? 'op-title--' . $themeClass : '' ?>" style="margin: 0;"><?= esc($title) ?></h2>
        <a href="<?= base_url('user/operations/formulaire') ?>" class="btn btn--outline btn--sm" style="border-radius: var(--radius-pill); display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; text-decoration:none;">
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
            <p class="text-muted" style="margin-bottom:1rem; font-size:0.85rem;">La case « Frais retrait » se choisit individuellement pour chaque destinataire.</p>

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
                    <div class="form-group" style="margin-bottom: 0; display:flex; flex-direction:column; align-items:center; gap:0.25rem;">
                        <label style="font-size:0.7rem; white-space:nowrap; margin:0;">Frais retrait</label>
                        <input type="checkbox" class="withdraw-fee-input" checked style="width:auto; height:20px;">
                        <input type="hidden" name="recipient_include_withdraw_fees[]" class="withdraw-fee-hidden" value="1">
                    </div>
                    <button type="button" class="btn btn--outline remove-recipient" style="padding:0.5rem; height:42px;" disabled>
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                    </button>
                </div>
            </div>
            
            <button type="button" id="add-recipient" class="btn btn--outline" style="width:100%; margin-bottom:1.5rem; border-style:dashed;">+ Ajouter un destinataire</button>

            <div id="preview-multiple-transfer" style="display:none; margin-bottom: 1.5rem;"></div>
        <?php endif; ?>

        <button type="submit" class="btn btn--primary <?= $themeClass ? 'op-submit--' . $themeClass : '' ?>" id="submit-btn" style="width: 100%; margin-top: 0.5rem;">
            Valider l'opération
        </button>
    </form>
</div>

<script>
// La protection CSRF est en mode "cookie" (voir Config\Security) et le
// jeton est régénéré à chaque requête POST. Le <form> principal envoie
// bien son jeton via csrf_field(), mais les 3 appels fetch() ci-dessous
// n'en envoyaient aucun : le filtre `csrf`, actif globalement, rejetait
// alors la requête AVANT le contrôleur, et le JSON attendu par formatAr()
// était vide -> NaN affiché partout dans l'aperçu.
// Le cookie CSRF est en HttpOnly (Config\Cookie::$httponly = true), donc
// impossible à relire depuis document.cookie : le jeton doit être fourni
// par PHP au chargement de la page, puis rafraîchi après CHAQUE appel
// AJAX à partir du hash renvoyé par le contrôleur (côté serveur, le
// filtre `csrf` régénère déjà ce hash avant même que le contrôleur ne
// s'exécute, puisque regenerate = true).
let csrfToken = '<?= esc(csrf_hash(), 'js') ?>';
const CSRF_HEADER_NAME = '<?= esc(csrf_header(), 'js') ?>';

function refreshCsrfToken(data) {
    if (data && typeof data.csrf_hash === 'string') {
        csrfToken = data.csrf_hash;
    }
}

// Générateur partagé de la carte d'aperçu (utilisé par les 3 scripts
// ci-dessous) : une seule fonction plutôt que 3 gabarits HTML dupliqués.
function formatAr(n) {

    return new Intl.NumberFormat('fr-FR').format(n) + ' Ar';
}
function buildBreakdownCard(title, rows, badgeHtml) {
    badgeHtml = badgeHtml || '';
    const rowsHtml = rows.map(function (r) {
        if (r.total) {
            return '<tr class="receipt-breakdown__row--total">' +
                '<td class="receipt-breakdown__label--total">' + r.label + '</td>' +
                '<td class="receipt-breakdown__value--total">' + formatAr(r.value) + '</td></tr>';
        }
        if (r.highlight) {
            return '<tr class="receipt-breakdown__row--highlight">' +
                '<td class="receipt-breakdown__label">' + r.label + '</td>' +
                '<td class="receipt-breakdown__value">' + formatAr(r.value) + '</td></tr>';
        }
        const cls = r.cls ? ' receipt-breakdown__value--' + r.cls : '';
        return '<tr><td class="receipt-breakdown__label">' + r.label + '</td>' +
            '<td class="receipt-breakdown__value' + cls + '">' + formatAr(r.value) + '</td></tr>';
    }).join('');
    return '<div class="receipt-breakdown">' +
        '<h4 class="receipt-breakdown__title">' + title + badgeHtml + '</h4>' +
        '<table class="receipt-breakdown__table">' + rowsHtml + '</table></div>';
}
</script>

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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [CSRF_HEADER_NAME]: csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    refreshCsrfToken(data);
                    previewContainer.innerHTML = buildBreakdownCard('Aperçu du retrait', [
                        { label: 'Montant demandé', value: data.amount },
                        { label: 'Frais de retrait', value: data.fee, cls: 'warning' },
                        { label: 'Total débité de votre solde', value: data.total_debit, total: true },
                    ]);
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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [CSRF_HEADER_NAME]: csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    refreshCsrfToken(data);
                    if (data.error) {
                        previewContainer.innerHTML = `<div class="alert alert--error">${data.error}</div>`;
                        previewContainer.style.display = 'block';
                        submitBtn.disabled = true;
                    } else {
                        const rows = [
                            { label: 'Montant envoyé', value: data.amount },
                            { label: 'Frais de transfert', value: data.transfer_fee },
                        ];
                        if (data.is_external && data.commission > 0) {
                            rows.push({ label: 'Commission inter-opérateur', value: data.commission, cls: 'warning' });
                        }
                        if (data.withdraw_fee_eq > 0) {
                            rows.push({ label: 'Frais de retrait inclus', value: data.withdraw_fee_eq, cls: 'success' });
                        }
                        rows.push({ label: 'Total débité', value: data.total_debit, total: true });
                        rows.push({ label: 'Montant reçu par le destinataire', value: data.amount_received, highlight: true });

                        const badge = data.is_external
                            ? '<span class="badge badge--orange" style="float:right;">Vers opérateur externe</span>'
                            : '';
                        previewContainer.innerHTML = buildBreakdownCard('Aperçu de la transaction', rows, badge);
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

    // La case visible ne fait que refléter/piloter l'input caché envoyé au
    // serveur : un checkbox non coché n'est jamais soumis en POST, ce qui
    // désalignerait le tableau avec recipient_phones[]/recipient_amounts[].
    // L'input caché, lui, est toujours présent -> l'index reste fiable même
    // si le destinataire n° 2 décoche sa case pendant qu'un autre reste coché.
    function bindFeeCheckbox(row) {
        const visible = row.querySelector('.withdraw-fee-input');
        const hidden = row.querySelector('.withdraw-fee-hidden');
        visible.addEventListener('change', function () {
            hidden.value = visible.checked ? '1' : '0';
            updatePreview();
        });
    }

    function updatePreview() {
        const phones = Array.from(document.querySelectorAll('.phone-input')).map(el => el.value);
        const amounts = Array.from(document.querySelectorAll('.amount-input')).map(el => el.value);
        const fees = Array.from(document.querySelectorAll('.withdraw-fee-hidden')).map(el => el.value);
        
        let hasValidInput = false;
        const formData = new FormData();
        
        for (let i = 0; i < phones.length; i++) {
            if (phones[i].length >= 10 && amounts[i] >= 100) {
                hasValidInput = true;
            }
            formData.append('phones[]', phones[i]);
            formData.append('amounts[]', amounts[i]);
            formData.append('include_withdraw_fees[]', fees[i]);
        }

        if (hasValidInput) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                fetch('<?= base_url('user/operations/preview-multiple-transfer') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [CSRF_HEADER_NAME]: csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    refreshCsrfToken(data);
                    if (data.error) {
                        previewContainer.innerHTML = `<div class="alert alert--error">${data.error}</div>`;
                        previewContainer.style.display = 'block';
                        submitBtn.disabled = true;
                    } else {
                        const rows = [
                            { label: 'Montant total envoyé', value: data.total_amount },
                            { label: 'Frais de transfert cumulés', value: data.total_transfer_fee },
                        ];
                        if (data.total_commission > 0) {
                            rows.push({ label: 'Commissions inter-opérateurs', value: data.total_commission, cls: 'warning' });
                        }
                        if (data.total_withdraw_fee > 0) {
                            rows.push({ label: 'Frais de retrait cumulés', value: data.total_withdraw_fee, cls: 'success' });
                        }
                        rows.push({ label: 'Total débité de votre solde', value: data.total_debit, total: true });

                        previewContainer.innerHTML = buildBreakdownCard("Aperçu global de l'envoi groupé", rows);
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
                <div class="form-group" style="margin-bottom: 0; display:flex; flex-direction:column; align-items:center; gap:0.25rem;">
                    <label style="font-size:0.7rem; white-space:nowrap; margin:0;">Frais retrait</label>
                    <input type="checkbox" class="withdraw-fee-input" checked style="width:auto; height:20px;">
                    <input type="hidden" name="recipient_include_withdraw_fees[]" class="withdraw-fee-hidden" value="1">
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
            bindFeeCheckbox(row);
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
        document.querySelectorAll('.recipient-row').forEach(bindFeeCheckbox);
    }
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>