<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .info-block {
            margin-bottom: 20px;
        }
        .info-block p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .color-green { color: #059669; }
        .color-red { color: #dc2626; }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>BlueMoney</h1>
        <p><strong><?= esc($title) ?></strong></p>
    </div>

    <div class="info-block">
        <p><strong>Client :</strong> <?= esc($user['first_name'] . ' ' . $user['last_name']) ?></p>
        <p><strong>Téléphone :</strong> <?= esc($user['phone']) ?></p>
        <p><strong>Édité le :</strong> <?= date('d/m/Y à H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Détails</th>
                <th class="text-right">Montant (Ar)</th>
                <th class="text-right">Frais (Ar)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="5" class="text-center">Aucune transaction trouvée.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                    <?php
                        $isSender    = ((int)$tx['user_id'] === (int)$userId);
                        $isDeposit   = ($tx['op_name'] === 'Dépôt');
                        $isTransfer  = ($tx['op_name'] === 'Transfert');

                        if ($isDeposit) {
                            $sign  = '+';
                            $colorClass = 'color-green';
                            $label = 'Dépôt';
                        } elseif ($isSender) {
                            $sign  = '-';
                            $colorClass = 'color-red';
                            $label = $isTransfer ? 'Transfert envoyé' : 'Retrait';
                        } else {
                            $sign  = '+';
                            $colorClass = 'color-green';
                            $label = 'Transfert reçu';
                        }
                        
                        $external_badge = '';
                        if ($isTransfer && $isSender && !empty($tx['external_operator_id'])) {
                            $external_badge = ' (Vers ' . esc($tx['external_operator_name'] ?? 'Externe') . ')';
                        }
                        
                        $details = esc($label) . $external_badge;
                        if ($isTransfer) {
                            if ($isSender) {
                                $details .= ' → ' . esc($tx['external_phone'] ?? $tx['recipient_phone'] ?? '?');
                            } else {
                                $details .= ' ← ' . esc($tx['sender_phone'] ?? '?');
                            }
                        }
                    ?>
                    <tr>
                        <td><?= $tx['created_at'] ? date('d/m/Y H:i', strtotime($tx['created_at'])) : '—' ?></td>
                        <td><?= esc($tx['op_name']) ?></td>
                        <td><?= $details ?></td>
                        <td class="text-right <?= $colorClass ?>">
                            <strong><?= $sign ?><?= number_format($tx['amount'], 0, ',', ' ') ?></strong>
                        </td>
                        <td class="text-right">
                            <?= $tx['fee_amount'] > 0 ? number_format($tx['fee_amount'], 0, ',', ' ') : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement par BlueMoney.
    </div>

</body>
</html>
