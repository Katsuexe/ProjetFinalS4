# TODO V2 détaillé — BlueMoney

Décisions actées : **1A · 2A · 3B · 4A · 5A · 6A** (voir `decisions-v2.md`). Ce document traduit ces choix en tâches concrètes, avec exemples de code CodeIgniter 4.

---

## 0. 🔴 COMMUN — à faire ensemble AVANT de vous séparer (1h-1h30)

Ne vous séparez pas tant que ces 3 points ne sont pas figés en code, sinon vos deux parties seront incompatibles.

### 0.1 Migration du schéma (une seule migration, additive)

```php
<?php
// app/Database/Migrations/2026-07-20-000001_AddExternalOperatorsV2.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExternalOperatorsV2 extends Migration
{
    public function up()
    {
        // Opérateurs externes (décision 6A : commission stockée directement ici)
        $this->forge->addField([
            'id'                     => ['type' => 'INTEGER', 'auto_increment' => true],
            'nom'                    => ['type' => 'VARCHAR', 'constraint' => 100],
            'commission_pourcentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('external_operators');

        // Préfixes liés à un opérateur externe (un opérateur peut avoir plusieurs préfixes)
        $this->forge->addField([
            'id'                  => ['type' => 'INTEGER', 'auto_increment' => true],
            'external_operator_id'=> ['type' => 'INTEGER', 'constraint' => 11],
            'prefix'              => ['type' => 'VARCHAR', 'constraint' => 5],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('external_operator_id', 'external_operators', 'id', '', 'CASCADE');
        $this->forge->createTable('external_operator_prefixes');

        // Extension de transactions (décision 1A : pas de compte, juste une trace)
        $fields = [
            'external_operator_id' => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true, 'after' => 'recipient_id'],
            'external_phone'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'external_operator_id'],
            'commission_amount'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0, 'after' => 'fee'],
            // décision 3B : workflow de règlement avec statut
            'envoye'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'date_envoi'           => ['type' => 'DATETIME', 'null' => true],
        ];
        $this->forge->addColumn('transactions', $fields);

        // décision 4A : crédits de frais de retrait (transfert "frais inclus")
        $this->forge->addField([
            'id'                    => ['type' => 'INTEGER', 'auto_increment' => true],
            'user_id'               => ['type' => 'INTEGER', 'constraint' => 11],
            'amount_remaining'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'source_transaction_id' => ['type' => 'INTEGER', 'constraint' => 11],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fee_credits');
    }

    public function down()
    {
        $this->forge->dropTable('fee_credits');
        $this->forge->dropColumn('transactions', ['external_operator_id', 'external_phone', 'commission_amount', 'envoye', 'date_envoi']);
        $this->forge->dropTable('external_operator_prefixes');
        $this->forge->dropTable('external_operators');
    }
}
```

Lancer : `php spark migrate`, puis régénérer `base.sql` (export complet, pas un diff) :
```bash
sqlite3 writable/database.db .dump > base.sql
```

### 0.2 `FeeCalculatorService` — source de vérité unique des frais

Utilisé par les DEUX côtés (aperçu client + calcul serveur + rapports opérateur). Personne ne recalcule les frais ailleurs.

```php
<?php
// app/Services/FeeCalculatorService.php
namespace App\Services;

use App\Models\FeeScaleModel;
use App\Models\ExternalOperatorModel;
use App\Models\ExternalOperatorPrefixModel;
use App\Models\OperatorPrefixModel;

class FeeCalculatorService
{
    protected FeeScaleModel $feeScales;
    protected ExternalOperatorModel $externalOperators;
    protected ExternalOperatorPrefixModel $externalPrefixes;
    protected OperatorPrefixModel $ownPrefixes;

    public function __construct()
    {
        $this->feeScales         = new FeeScaleModel();
        $this->externalOperators = new ExternalOperatorModel();
        $this->externalPrefixes  = new ExternalOperatorPrefixModel();
        $this->ownPrefixes       = new OperatorPrefixModel();
    }

    /**
     * Décision 1A : résout un numéro en "interne" (nous), "externe" (opérateur concurrent) ou "inconnu".
     * @return array{type:string, external_operator_id:?int}
     */
    public function resolveOperator(string $phone): array
    {
        $prefix = substr(preg_replace('/\D/', '', $phone), 0, 3);

        if ($this->ownPrefixes->where('prefix', $prefix)->first()) {
            return ['type' => 'internal', 'external_operator_id' => null];
        }

        $externalPrefix = $this->externalPrefixes->where('prefix', $prefix)->first();
        if ($externalPrefix) {
            return ['type' => 'external', 'external_operator_id' => $externalPrefix['external_operator_id']];
        }

        return ['type' => 'unknown', 'external_operator_id' => null];
    }

    /** Frais barème par tranche (dépôt/retrait/transfert interne — inchangé depuis v1) */
    public function scaleFee(int $operationTypeId, float $amount): float
    {
        $scale = $this->feeScales
            ->where('operation_type_id', $operationTypeId)
            ->where('montant_min <=', $amount)
            ->groupStart()
                ->where('montant_max >=', $amount)
                ->orWhere('montant_max', null)
            ->groupEnd()
            ->first();

        if (!$scale) return 0.0;

        return $scale['frais_fixe'] + ($amount * $scale['frais_pourcentage'] / 100);
    }

    /**
     * Décision 2A : commission externe additive, décision 4A : frais de retrait "inclus" optionnel.
     * Retourne le détail complet pour affichage ET pour débit réel — même structure des 2 côtés.
     */
    public function computeTransfer(float $amount, ?int $externalOperatorId, bool $includeWithdrawFee): array
    {
        $transferTypeId = 3; // id du type "transfert" dans operation_types
        $withdrawTypeId = 2; // id du type "retrait"

        $transferFee = $this->scaleFee($transferTypeId, $amount);

        $commission = 0.0;
        if ($externalOperatorId !== null) {
            $operator   = $this->externalOperators->find($externalOperatorId);
            $commission = $amount * ($operator['commission_pourcentage'] / 100);
        }

        $withdrawFeeEq = $includeWithdrawFee ? $this->scaleFee($withdrawTypeId, $amount) : 0.0;

        return [
            'amount'          => $amount,
            'transfer_fee'    => $transferFee,
            'commission'      => $commission,
            'withdraw_fee_eq' => $withdrawFeeEq,
            'total_debit'     => $amount + $transferFee + $commission + $withdrawFeeEq,
            'amount_received' => $amount, // toujours le montant plein (décision 1A/4A)
        ];
    }
}
```

### 0.3 Contrat de `MobileMoneyService::transfer()` v2

À figer ensemble (signature + valeur de retour), chacun code contre cette interface :

```php
/**
 * @param string $senderPhone
 * @param string $recipientPhone   numéro composé (interne ou externe)
 * @param float  $amount
 * @param bool   $includeWithdrawFee  décision 4A
 * @return array  ['transaction_id' => int, 'breakdown' => array (issu de FeeCalculatorService)]
 * @throws \RuntimeException  message générique, ne jamais révéler si le numéro existe ou non
 */
public function transfer(string $senderPhone, string $recipientPhone, float $amount, bool $includeWithdrawFee = true): array;

/**
 * Décision 5A : une transaction par destinataire, split égal, frais calculé par ligne.
 * @param string   $senderPhone
 * @param string[] $recipientPhones
 * @param float    $totalAmount
 * @return array   liste de résultats individuels (même forme que transfer())
 */
public function transferMultiple(string $senderPhone, array $recipientPhones, float $totalAmount, bool $includeWithdrawFee = true): array;
```

Une fois ces 3 points commit-és et poussés sur une branche partagée, **séparez-vous**.

---

## 1. 🟢 Côté opérateur (Dev A)

### 1.1 CRUD "Opérateurs externes" (préfixes + nom + commission)

```php
<?php
// app/Controllers/Admin/ExternalOperatorController.php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExternalOperatorModel;
use App\Models\ExternalOperatorPrefixModel;

class ExternalOperatorController extends BaseController
{
    public function index()
    {
        $model = new ExternalOperatorModel();
        // jointure pour afficher les préfixes de chaque opérateur dans la liste
        $operators = $model->select('external_operators.*, GROUP_CONCAT(external_operator_prefixes.prefix) as prefixes')
            ->join('external_operator_prefixes', 'external_operator_prefixes.external_operator_id = external_operators.id', 'left')
            ->groupBy('external_operators.id')
            ->findAll();

        return view('admin/external_operators/index', ['operators' => $operators]);
    }

    public function create()
    {
        $rules = [
            'nom'                    => 'required|min_length[2]',
            'commission_pourcentage' => 'required|decimal|greater_than_equal_to[0]',
            'prefixes'               => 'required', // ex: "032,031"
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ExternalOperatorModel();
        $id = $model->insert([
            'nom'                    => $this->request->getPost('nom'),
            'commission_pourcentage' => $this->request->getPost('commission_pourcentage'),
            'created_at'             => date('Y-m-d H:i:s'),
        ]);

        $prefixModel = new ExternalOperatorPrefixModel();
        foreach (explode(',', $this->request->getPost('prefixes')) as $prefix) {
            $prefix = trim($prefix);
            if ($prefix === '') continue;

            // garde-fou : un préfixe externe ne doit jamais chevaucher un préfixe interne
            $ownConflict = (new \App\Models\OperatorPrefixModel())->where('prefix', $prefix)->first();
            if ($ownConflict) {
                return redirect()->back()->withInput()->with('error', "Le préfixe $prefix appartient déjà à votre opérateur.");
            }

            $prefixModel->insert(['external_operator_id' => $id, 'prefix' => $prefix]);
        }

        return redirect()->to('/admin/external-operators')->with('success', 'Opérateur externe créé.');
    }

    // edit(), update(), delete() sur le même modèle que votre CRUD operator_prefixes existant
}
```

Routes à ajouter (`app/Config/Routes.php`) :
```php
$routes.group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes.resource('external-operators', ['controller' => 'Admin\ExternalOperatorController']);
    $routes.get('gains', 'Admin\GainsController::index');
    $routes.get('montants-a-envoyer', 'Admin\SettlementController::index');
    $routes.post('montants-a-envoyer/marquer/(:num)', 'Admin\SettlementController::marquerEnvoye/$1');
});
```

### 1.2 Page "Situation gains" — séparer opérateur / autres opérateurs

```php
<?php
// app/Models/TransactionModel.php — nouvelle méthode à ajouter à côté de getGainsStats()

public function getGainsStatsV2(): array
{
    // Gains internes (dépôt, retrait, transfert interne) : frais barème uniquement
    $internal = $this->select('operation_types.libelle, SUM(transactions.fee) as total_frais')
        ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
        ->where('transactions.external_operator_id', null)
        ->groupBy('operation_types.id')
        ->findAll();

    // Gains externes : frais barème + commission, groupés PAR OPÉRATEUR EXTERNE
    $external = $this->select('external_operators.nom,
            SUM(transactions.fee) as total_frais,
            SUM(transactions.commission_amount) as total_commission')
        ->join('external_operators', 'external_operators.id = transactions.external_operator_id')
        ->where('transactions.external_operator_id !=', null)
        ->groupBy('external_operators.id')
        ->findAll();

    return ['internal' => $internal, 'external' => $external];
}
```

Vue (2 blocs distincts, cf. livrable "séparer opérateur et autres opérateurs") :
```php
<!-- app/Views/admin/gains/index.php -->
<h3>Gains — nos opérations (dépôt / retrait / transfert interne)</h3>
<table class="table">
  <?php foreach ($stats['internal'] as $row): ?>
    <tr><td><?= esc($row['libelle']) ?></td><td><?= number_format($row['total_frais'], 0, ',', ' ') ?> Ar</td></tr>
  <?php endforeach ?>
</table>

<h3>Gains — transferts vers autres opérateurs</h3>
<table class="table">
  <thead><tr><th>Opérateur</th><th>Frais</th><th>Commission</th><th>Total</th></tr></thead>
  <?php foreach ($stats['external'] as $row): ?>
    <tr>
      <td><?= esc($row['nom']) ?></td>
      <td><?= number_format($row['total_frais'], 0, ',', ' ') ?> Ar</td>
      <td><?= number_format($row['total_commission'], 0, ',', ' ') ?> Ar</td>
      <td><?= number_format($row['total_frais'] + $row['total_commission'], 0, ',', ' ') ?> Ar</td>
    </tr>
  <?php endforeach ?>
</table>
```

### 1.3 Page "Montants à envoyer à chaque opérateur" (décision 3B — workflow avec statut)

```php
<?php
// app/Controllers/Admin/SettlementController.php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

class SettlementController extends BaseController
{
    public function index()
    {
        $model = new TransactionModel();

        $due = $model->select('external_operators.id, external_operators.nom, SUM(transactions.amount) as montant_du, COUNT(*) as nb_transactions')
            ->join('external_operators', 'external_operators.id = transactions.external_operator_id')
            ->where('transactions.envoye', 0)
            ->groupBy('external_operators.id')
            ->findAll();

        return view('admin/settlement/index', ['due' => $due]);
    }

    // Marque TOUTES les transactions non envoyées d'un opérateur comme réglées, en une fois
    public function marquerEnvoye(int $externalOperatorId)
    {
        $model = new TransactionModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $model->where('external_operator_id', $externalOperatorId)
            ->where('envoye', 0)
            ->set(['envoye' => 1, 'date_envoi' => date('Y-m-d H:i:s')])
            ->update();

        $db->transComplete();

        return redirect()->to('/admin/montants-a-envoyer')->with('success', 'Montant marqué comme envoyé.');
    }
}
```

- Attention : `montant_du` = `SUM(amount)`, **pas** `amount + fee + commission` — c'est le montant que le destinataire doit recevoir chez le concurrent, la commission/frais restent chez vous.
- Ajoutez un lien "voir le détail" par opérateur → liste des transactions individuelles concernées (filtrable aussi sur l'historique déjà réglé, `envoye = 1`).

### 1.4 Checklist opérateur restante
- [ ] Vue CRUD opérateurs externes (form + liste), sur le modèle exact de votre vue `operator_prefixes` existante
- [ ] Filtre de période (date début/fin) sur gains et montants à envoyer
- [ ] Mettre à jour `base.sql` avec seed de 2-3 opérateurs externes (noms génériques, **jamais** de vrais noms de marque)
- [ ] Vérifier que le filtre `admin` protège bien toutes ces routes (checklist sécurité §10 du brouillon)

---

## 2. 🔵 Côté client (Dev B)

### 2.1 Détection interne/externe + aperçu avant confirmation

```php
<?php
// app/Controllers/User/OperationController.php — méthode transfer() adaptée

public function previewTransfer()
{
    $recipientPhone = $this->request->getPost('recipient_phone');
    $amount = (float) $this->request->getPost('amount');
    $includeWithdrawFee = (bool) $this->request->getPost('include_withdraw_fee', true); // coché par défaut

    $calculator = new \App\Services\FeeCalculatorService();
    $resolution = $calculator->resolveOperator($recipientPhone);

    if ($resolution['type'] === 'unknown') {
        // message générique : ne jamais dire "ce numéro n'existe pas chez nous ni ailleurs"
        return $this->response->setJSON(['error' => 'Numéro invalide.']);
    }

    $breakdown = $calculator->computeTransfer(
        $amount,
        $resolution['external_operator_id'],
        $includeWithdrawFee
    );

    $breakdown['is_external'] = $resolution['type'] === 'external';

    return $this->response->setJSON($breakdown); // jamais le taux brut, juste les montants calculés
}
```

### 2.2 Composant réutilisable "reçu" (§6 du brouillon)

```php
<!-- app/Views/user/partials/_receipt_breakdown.php -->
<!-- Utilisé 3 fois : aperçu avant confirmation, message de succès, détail historique -->
<table class="table receipt-breakdown">
  <tr><td>Montant envoyé</td><td><?= number_format($breakdown['amount'], 0, ',', ' ') ?> Ar</td></tr>
  <tr><td>Frais de transfert</td><td><?= number_format($breakdown['transfer_fee'], 0, ',', ' ') ?> Ar</td></tr>
  <?php if ($breakdown['is_external']): ?>
    <tr><td>Commission inter-opérateur</td><td><?= number_format($breakdown['commission'], 0, ',', ' ') ?> Ar</td></tr>
  <?php endif ?>
  <?php if ($breakdown['withdraw_fee_eq'] > 0): ?>
    <tr><td>Frais de retrait inclus</td><td><?= number_format($breakdown['withdraw_fee_eq'], 0, ',', ' ') ?> Ar</td></tr>
  <?php endif ?>
  <tr class="fw-bold"><td>Total débité</td><td><?= number_format($breakdown['total_debit'], 0, ',', ' ') ?> Ar</td></tr>
  <tr><td>Montant reçu par le destinataire</td><td><?= number_format($breakdown['amount_received'], 0, ',', ' ') ?> Ar</td></tr>
</table>
```

### 2.3 `MobileMoneyService::transfer()` — recalcul serveur + crédit de frais (décision 4A)

```php
<?php
// app/Services/MobileMoneyService.php

public function transfer(string $senderPhone, string $recipientPhone, float $amount, bool $includeWithdrawFee = true): array
{
    $calculator = new FeeCalculatorService();
    $resolution = $calculator->resolveOperator($recipientPhone);

    if ($resolution['type'] === 'unknown') {
        throw new \RuntimeException('Opération impossible.'); // message générique
    }

    // Ne JAMAIS faire confiance à un frais envoyé depuis le formulaire — tout est recalculé ici
    $breakdown = $calculator->computeTransfer($amount, $resolution['external_operator_id'], $includeWithdrawFee);

    $db = \Config\Database::connect();
    $db->transStart();

    $sender = $this->userModel->where('phone', $senderPhone)->first();
    if (!$sender || $sender['balance'] < $breakdown['total_debit']) {
        throw new \RuntimeException('Opération impossible.');
    }

    // Débit expéditeur (recalculer en PHP puis update() — pas de concaténation brute, cf. §10 sécurité)
    $this->userModel->update($sender['id'], ['balance' => $sender['balance'] - $breakdown['total_debit']]);

    $transactionId = null;

    if ($resolution['type'] === 'internal') {
        $recipient = $this->userModel->where('phone', $recipientPhone)->first();
        $this->userModel->update($recipient['id'], ['balance' => $recipient['balance'] + $breakdown['amount_received']]);

        $transactionId = $this->transactionModel->insert([
            'sender_id'    => $sender['id'],
            'recipient_id' => $recipient['id'],
            'amount'       => $amount,
            'fee'          => $breakdown['transfer_fee'],
            'operation_type_id' => 3,
        ]);

        // décision 4A : crédit de frais de retrait pour le DESTINATAIRE INTERNE uniquement
        if ($includeWithdrawFee && $breakdown['withdraw_fee_eq'] > 0) {
            $this->feeCreditModel->insert([
                'user_id'               => $recipient['id'],
                'amount_remaining'      => $breakdown['withdraw_fee_eq'],
                'source_transaction_id' => $transactionId,
                'created_at'            => date('Y-m-d H:i:s'),
            ]);
        }
    } else {
        // externe (décision 1A) : pas de compte destinataire, juste une trace
        $transactionId = $this->transactionModel->insert([
            'sender_id'             => $sender['id'],
            'recipient_id'          => null,
            'external_operator_id'  => $resolution['external_operator_id'],
            'external_phone'        => $recipientPhone,
            'amount'                => $amount,
            'fee'                   => $breakdown['transfer_fee'],
            'commission_amount'     => $breakdown['commission'],
            'operation_type_id'     => 3,
            'envoye'                => 0, // décision 3B
        ]);
        // pas de crédit de frais pour un destinataire externe : on ne gère pas son retrait
    }

    $db->transComplete();

    return ['transaction_id' => $transactionId, 'breakdown' => $breakdown];
}
```

### 2.4 Consommation du crédit de frais au retrait

```php
<?php
// app/Services/MobileMoneyService.php

public function withdraw(string $phone, float $amount): array
{
    $calculator = new FeeCalculatorService();
    $fee = $calculator->scaleFee(2, $amount); // type "retrait"

    $creditModel = new \App\Models\FeeCreditModel();
    $user = $this->userModel->where('phone', $phone)->first();

    // Consommer les crédits les plus anciens en premier
    $credits = $creditModel->where('user_id', $user['id'])->where('amount_remaining >', 0)->orderBy('created_at', 'ASC')->findAll();

    $consumed = 0.0;
    foreach ($credits as $credit) {
        if ($consumed >= $fee) break;
        $take = min($credit['amount_remaining'], $fee - $consumed);
        $creditModel->update($credit['id'], ['amount_remaining' => $credit['amount_remaining'] - $take]);
        $consumed += $take;
    }

    $realFee = $fee - $consumed; // ce qui reste réellement à payer

    // ... suite du retrait avec $realFee au lieu de $fee, débit du solde, insertion transaction
    // afficher sur le reçu : "frais de retrait offert : X Ar" si $consumed > 0
}
```

### 2.5 Envoi multiple (décision 5A)

```php
public function transferMultiple(string $senderPhone, array $recipientPhones, float $totalAmount, bool $includeWithdrawFee = true): array
{
    $n = count($recipientPhones);
    $amountPerRecipient = round($totalAmount / $n, 2); // split égal

    $db = \Config\Database::connect();
    $db->transStart(); // tout ou rien pour l'ensemble de l'envoi

    $results = [];
    foreach ($recipientPhones as $phone) {
        // réutilise transfer() tel quel : chaque destinataire = 1 transaction, son propre frais (décision 5A)
        $results[] = $this->transfer($senderPhone, $phone, $amountPerRecipient, $includeWithdrawFee);
    }

    $db->transComplete();
    if ($db->transStatus() === false) {
        throw new \RuntimeException('Opération impossible.');
    }

    return $results;
}
```

### 2.6 Solde masqué (§7) — JS pur, pas de re-fetch

```html
<!-- app/Views/user/dashboard.php -->
<span id="balance-display" data-balance="<?= esc($user['balance']) ?>">•••• Ar</span>
<button type="button" id="toggle-balance" aria-label="Afficher le solde">👁</button>

<script>
document.getElementById('toggle-balance').addEventListener('click', function () {
  const el = document.getElementById('balance-display');
  const hidden = el.textContent.includes('••••');
  el.textContent = hidden
    ? new Intl.NumberFormat('fr-FR').format(el.dataset.balance) + ' Ar'
    : '•••• Ar';
});
</script>
```
Pas de `localStorage` : au rechargement, toujours masqué par défaut (conforme à la décision du brouillon).

### 2.7 Checklist client restante
- [ ] Formulaire transfert : case "inclure les frais de retrait" cochée par défaut + aperçu JS qui appelle `previewTransfer()` en live
- [ ] Formulaire envoi multiple : liste dynamique de numéros (ajout/suppression de champs en JS), aperçu par destinataire avant validation
- [ ] Historique : badge/label distinct pour un transfert externe ("vers [nom opérateur]"), et affichage du crédit "frais offert" si consommé
- [ ] Réutiliser `_receipt_breakdown.php` aux 3 endroits (aperçu, succès, détail historique) — ne pas dupliquer le HTML

---

## 3. 🔴 À faire ensemble avant le tag `v2` (30 min)

- [ ] **CSRF** : ajouter `'csrf'` dans `globals['before']` de `Config/Filters.php` — avant de tester les nouveaux formulaires, sinon vous allez déboguer des faux positifs de CSRF en pensant que c'est un bug métier
- [ ] Test croisé : un transfert externe créé côté client doit apparaître correctement dans "Situation gains" ET "Montants à envoyer" côté opérateur, avec les bons montants
- [ ] Test croisé : un retrait qui consomme un crédit de frais affiche bien "frais offert" et ne débite pas deux fois
- [ ] Vérifier qu'aucune route admin (`external-operators`, `gains`, `montants-a-envoyer`) n'est accessible sans le filtre `admin`
- [ ] Mettre à jour `Taches.md` (chacun sa partie, une entrée par personne) et `base.sql` (export complet)
- [ ] `git tag v2` + push (tag + `main` à jour)

