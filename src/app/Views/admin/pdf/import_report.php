<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'import</title>
    <style>
        /*
            PEDAGOGIE — CSS pour Dompdf :
            Dompdf ne comprend qu'un sous-ensemble de CSS (proche de CSS 2.1).
            On évite Flexbox/Grid ici et on utilise des balises simples
            (table, div, styles inline) pour un rendu fiable en PDF.
            @page définit le format/marges de la page PDF elle-même.
        */
        @page { margin: 25px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .muted { color: #777; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Rapport d'import CSV — Utilisateurs</h1>
    <p class="muted">Généré le <?= esc($generatedAt) ?></p>

    <table>
        <tr>
            <th>Lignes importées</th>
            <td><?= (int) $report['inserted'] ?></td>
        </tr>
        <tr>
            <th>Lignes ignorées</th>
            <td><?= (int) $report['skipped'] ?></td>
        </tr>
    </table>

    <?php if (! empty($report['errors'])): ?>
        <h2 style="font-size:14px; margin-top:20px;">Détail des erreurs</h2>
        <table>
            <thead>
                <tr><th style="width:40px;">#</th><th>Message</th></tr>
            </thead>
            <tbody>
                <?php foreach ($report['errors'] as $i => $err): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($err) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
