<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des Barèmes de Frais</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; text-align: center; color: #0056b3; }
        .date { text-align: right; font-style: italic; font-size: 10px; margin-bottom: 20px; }
        .section-title { font-size: 14px; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: right; }
        th { background-color: #f4f4f4; text-align: center; }
        td:first-child, th:first-child { text-align: left; }
    </style>
</head>
<body>

    <h1>Rapport Officiel : Barèmes de Frais</h1>
    <div class="date">Généré le : <?= esc($generatedAt) ?></div>

    <?php foreach ($feesByType as $typeData): ?>
        <h2 class="section-title">Type d'opération : <?= esc($typeData['name']) ?></h2>
        
        <?php if (empty($typeData['scales'])): ?>
            <p>Aucun barème configuré pour ce type d'opération.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Montant Minimum (Ar)</th>
                        <th>Montant Maximum (Ar)</th>
                        <th>Frais (Ar)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($typeData['scales'] as $s): ?>
                    <tr>
                        <td><?= number_format($s['min_amount'], 2, ',', ' ') ?></td>
                        <td><?= number_format($s['max_amount'], 2, ',', ' ') ?></td>
                        <td style="font-weight:bold; color:#d9534f;"><?= number_format($s['fee_amount'], 2, ',', ' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>

</body>
</html>
