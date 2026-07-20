# Suivi des Taches - Operateur Mobile Money (BlueMoney)

Ce fichier trace les travaux effectues a chaque livraison (tag), conformement aux consignes du projet.

## Informations Etudiant
- **Nom(s) et Prenom(s)** : [A REMPLIR]
- **Lien formulaire** : Rempli via https://forms.gle/nCv6xJYHVvVj2FKA

---

## Livraison : Version 1 (Tag `v1`)

### Travaux Preparatoires (Socle de base)
Avant d'attaquer les fonctionnalites specifiques du sujet, le socle technique a ete mis en place :
- **Architecture Technique** : CodeIgniter 4 avec SQLite embarque, HTML/CSS natif (mobile-first), pas de framework JS lourd.
- **Base de donnees** : Creation du script `base.sql` contenant les schemas complets et les donnees de test.
- **Authentification et Permissions** : Systeme de roles (Admin/Moderateur/Utilisateur) avec filtres de securite et permissions granulaires.
- **Interface (UI)** : Layouts responsives (Admin et User), design glassmorphism, principes de Gestalt, devise Ariary (`Ar`).

### Fonctionnalites "Cote Operateur"
- [x] Configuration des prefixes valables de l'operateur (032, 033, 034, 037, 038) via l'interface Admin.
- [x] Creation des types d'operations (depot, retrait, transfert) avec gestion CRUD complete.
- [x] Gestion des baremes de frais par tranche de montant (modifiables via l'interface Admin).
- [x] Affichage de la situation des gains (total des frais collectes par type d'operation).
- [x] Affichage de la situation des comptes clients (liste des clients avec solde et statut).

### Fonctionnalites "Cote Client"
- [x] Login automatique avec le numero de telephone (sans inscription prealable, creation auto du compte).
- [x] Consulter le solde de son compte depuis le tableau de bord.
- [x] Faire un depot (simulation automatique, credit instantane).
- [x] Faire un retrait (simulation automatique, frais calcules selon bareme).
- [x] Faire un transfert vers un autre numero (frais calcules selon bareme).
- [x] Consulter l'historique des operations (tableau complet avec type, montant, frais, date).

---
*(Toutes les fonctionnalites ont ete implementees, testees et livrees.)*

---

## Livraison : Version 2 (Tag `v2`)

### Cote operateur
- [x] Configuration des operateurs externes (nom, commission en %) — table `external_operators`, CRUD Admin complet.
- [x] Gestion des prefixes par operateur externe (un operateur peut avoir plusieurs prefixes) — table `external_operator_prefixes`.
- [x] Vue "Situation des gains" separee en deux blocs : gains internes (depot/retrait/transfert interne) et gains externes (commissions inter-operateurs) — `admin/gains/index.php`.
- [x] Vue "Montants a envoyer a chaque operateur" avec statut envoye/non envoye et bouton de marquage — `admin/settlement/index.php`, `SettlementController.php`.

### Cote client
- [x] Option "Inclure les frais de retrait" lors d'un transfert (case a cocher, cochee par defaut).
- [x] Apercu dynamique (AJAX) des frais en temps reel : retrait, transfert simple, envoi groupe.
- [x] Detection automatique de l'operateur du destinataire (interne/externe) avec affichage des commissions.
- [x] Envoi groupe dynamique : chaque destinataire a son propre numero et son propre montant.
- [x] Formulaire unique dynamique (depot/retrait/transfert/groupe) avec affichage conditionnel selon l'operation choisie.
- [x] Export PDF de l'historique des operations.
- [x] Formulaire creation utilisateur dynamique selon le type de compte (User = telephone, Admin/Modo = email+mdp).

### Base de donnees
- [x] `base.sql` mis a jour avec : `external_operators`, `external_operator_prefixes`, `fee_credits`, colonnes additionnelles sur `transactions` (external_operator_id, external_phone, commission_amount, envoye, date_envoi).

---
*(Version 2 livree et taguee v2.)*
