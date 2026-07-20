# TODO — Revue complete du projet BlueMoney

> **Branche** : `dev`
> Ce document listait les taches restantes. Toutes ont ete completees.

---

## 1. Fonctionnalites metier — TOUTES IMPLEMENTEES

### Cote operateur
- [x] Configuration des prefixes operateur (032, 033, 034, 037, 038) — table `operator_prefixes`, CRUD Admin, vue dediee
- [x] Types d'operations (depot, retrait, transfert) avec bareme de frais par tranche de montant, modifiable — tables `operation_types` / `fee_scales`, CRUD Admin
- [x] Vue "situation des gains" (total des frais collectes par type d'operation sur le dashboard Admin)
- [x] Vue "situation des comptes clients" (liste des clients avec solde, telephone et statut)

### Cote client
- [x] Login automatique avec le numero de telephone (pas d'inscription prealable) — creation auto du compte et du solde a 0 Ar
- [x] Voir le solde — affiche sur le tableau de bord et la page historique
- [x] Faire un depot (automatique, credit instantane)
- [x] Faire un retrait (automatique, frais calcules selon bareme)
- [x] Faire un transfert (frais calcules selon bareme, verification du destinataire)
- [x] Voir les historiques d'operations (tableau complet : type, montant, frais, date)

### Base de donnees
- [x] `base.sql` a la racine du projet — schema complet + donnees de test
- [x] Table `operator_prefixes` (prefixes operateur)
- [x] Table `operation_types` (depot/retrait/transfert)
- [x] Table `fee_scales` (baremes par tranche de montant)
- [x] Table `transactions` (historique des operations)
- [x] Donnees de test (comptes Admin, Modo, Alice, Bob, prefixes, baremes)

### Livraison
- [ ] Tag Git `v1` sur le depot public (a faire au moment de la livraison finale)
- [x] Depot public sur Github
- [ ] Renseigner les informations dans le formulaire fourni

---

## 2. Bugs precedemment identifies — CORRIGES

### Devise EUR remplacee par Ar (Ariary)
- [x] Corrige dans le seeder et dans les vues/controleurs.

### Cle de session import CSV
- [x] Cle harmonisee entre la vue et le controleur.

### Permission import.csv
- [x] Ajoutee dans le seeder et attribuee au type Admin.

---

## 3. Services additionnels

| Fichier | Statut |
|---|---|
| `Services/MobileMoneyService.php` | Fonctionnel (depot, retrait, transfert avec frais) |
| `Services/ExcelService.php` | Pret (necessite `composer require phpoffice/phpspreadsheet`) |
| `Libraries/PdfService.php` | Pret (necessite `composer require dompdf/dompdf`) |

---

## 4. UX et Securite

- [x] Design glassmorphism avec principes de Gestalt
- [x] Responsivite mobile complete
- [x] Actions destructives en rouge (`btn--danger`)
- [x] Aucun emoji — texte sobre et professionnel
- [x] Messages d'erreur generiques (pas de fuite d'information sur les comptes existants)
- [x] Login admin secret sans indication visuelle

---

## Ce qui est stable et valide

- **Authentification** : Login telephone (client) + login email/mot de passe (admin via numero secret)
- **Base de donnees** : SQLite embarque, schema complet dans `base.sql`
- **Permissions** : Systeme granulaire (admin.panel, users.manage, users.delete, etc.)
- **Layouts et CSS** : Responsive mobile-first, glassmorphism, sidebar burger
- **Config** : `app/Config/` (Database, Routes, Filters, Security)