<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h2>Tableau de bord</h2>
    <p>Bienvenue, <?= esc(session('username')) ?> — vue d'ensemble de l'application.</p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label">Utilisateurs</div>
            <div class="stat-body__value"><?= esc($stats['total_users'] ?? 0) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--green">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label">Actifs</div>
            <div class="stat-body__value"><?= esc($stats['active_users'] ?? 0) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--amber">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label">Types de rôles</div>
            <div class="stat-body__value"><?= esc($stats['total_types'] ?? 0) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--red">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-body__label">Inactifs</div>
            <div class="stat-body__value"><?= esc($stats['inactive_users'] ?? 0) ?></div>
        </div>
    </div>
</div>

<!-- Derniers utilisateurs -->
<div class="card">
    <div class="card__header">
        <span class="card__title">Derniers utilisateurs inscrits</span>
        <a href="<?= base_url('admin/users') ?>" class="btn btn--outline btn--sm">Voir tous</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_users)): ?>
            <tr>
                <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">
                    Aucun utilisateur pour l'instant.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($recent_users as $u): ?>
            <tr>
                <td><?= esc($u['id']) ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--info-bg);color:var(--info-fg);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:500;flex-shrink:0;">
                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                        </div>
                        <?= esc($u['username']) ?>
                    </div>
                </td>
                <td><?= esc($u['email']) ?></td>
                <td>
                    <span class="badge badge--blue"><?= esc($u['type_name'] ?? '—') ?></span>
                </td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <span class="badge badge--green">Actif</span>
                    <?php else: ?>
                        <span class="badge badge--red">Inactif</span>
                    <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <a href="<?= base_url('admin/users/' . $u['id']) ?>" class="btn btn--outline btn--sm">Voir</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Gains de l'opérateur ────────────────────────────────────────── -->
<div class="card" style="margin-top:1.5rem;">
    <div class="card__header">
        <span class="card__title">Gains de l'opérateur</span>
        <span class="badge badge--green">Total frais : <?= number_format($total_fees ?? 0, 2, ',', ' ') ?> Ar</span>
    </div>
    <?php if (empty($gains)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">Aucune transaction enregistrée.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Type d'opération</th>
                <th>Nb transactions</th>
                <th>Volume total (Ar)</th>
                <th>Frais collectés (Ar)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gains as $g): ?>
            <tr>
                <td><span class="badge badge--blue"><?= esc($g['op_name']) ?></span></td>
                <td><?= esc($g['nb_tx']) ?></td>
                <td><?= number_format($g['total_amount'], 2, ',', ' ') ?></td>
                <td style="font-weight:700;color:var(--success-color);"><?= number_format($g['total_fees'], 2, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- ── Situation des comptes clients ────────────────────────────────── -->
<div class="card" style="margin-top:1.5rem;">
    <div class="card__header">
        <span class="card__title">Comptes clients Mobile Money</span>
        <a href="<?= base_url('admin/users') ?>" class="btn btn--outline btn--sm">Gérer</a>
    </div>
    <?php if (empty($client_accounts)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">Aucun client enregistré.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Solde (Ar)</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($client_accounts as $c): ?>
                <tr>
                    <td><?= esc($c['id']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div style="width:28px;height:28px;border-radius:50%;background:var(--success-bg);color:var(--success-fg);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600;flex-shrink:0;">
                                <?= strtoupper(substr($c['username'], 0, 1)) ?>
                            </div>
                            <?= esc($c['username']) ?>
                        </div>
                    </td>
                    <td><code><?= esc($c['phone'] ?? '—') ?></code></td>
                    <td style="font-weight:700;"><?= number_format($c['balance'], 2, ',', ' ') ?> <?= esc($c['currency'] ?? 'Ar') ?></td>
                    <td>
                        <?php if ($c['is_active']): ?>
                            <span class="badge badge--green">Actif</span>
                        <?php else: ?>
                            <span class="badge badge--red">Inactif</span>
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
