<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h2>Situation des Gains</h2>
    <p>Consultez ici les gains générés par les frais et les commissions.</p>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap:2rem;">
    <!-- Gains Internes -->
    <div class="card">
        <div class="card__header">
            <span class="card__title">Nos opérations (Dépôt / Retrait / Interne)</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Opération</th>
                        <th>Total Frais</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['internal'])): ?>
                        <tr><td colspan="2" style="text-align:center;">Aucun gain interne enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($stats['internal'] as $row): ?>
                        <tr>
                            <td><?= esc($row['libelle']) ?></td>
                            <td style="font-weight:600; color:var(--success-color);"><?= number_format($row['total_frais'], 2, ',', ' ') ?> Ar</td>
                        </tr>
                        <?php endforeach ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gains Externes -->
    <div class="card">
        <div class="card__header">
            <span class="card__title">Transferts vers autres opérateurs</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Opérateur</th>
                        <th>Frais</th>
                        <th>Commission</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['external'])): ?>
                        <tr><td colspan="4" style="text-align:center;">Aucun gain externe enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($stats['external'] as $row): ?>
                        <tr>
                            <td><?= esc($row['nom']) ?></td>
                            <td><?= number_format($row['total_frais'], 2, ',', ' ') ?> Ar</td>
                            <td><?= number_format($row['total_commission'], 2, ',', ' ') ?> Ar</td>
                            <td style="font-weight:600; color:var(--first-color);"><?= number_format($row['total_frais'] + $row['total_commission'], 2, ',', ' ') ?> Ar</td>
                        </tr>
                        <?php endforeach ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
