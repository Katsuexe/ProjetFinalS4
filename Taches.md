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

### 🔐 Identifiants de Test

Pour evaluer le projet, voici les donnees issues du fichier `Workspace/users.csv` :

**Comment se connecter en tant qu'Admin/Operateur ?**
1. Sur la page de connexion, entrez le **numero secret : `0330000000`**
2. Le formulaire client se transforme alors en formulaire Operateur (Email + Mot de passe).
3. Utilisez les identifiants suivants :
   - **Administrateur** : `admin@example.com` / `Admin@1234`
   - **Moderateur** : `mod@example.com` / `Modo@1234`

**Comment se connecter en tant que Client (Mobile Money) ?**
1. Sur la page de connexion, entrez simplement l'un des numeros de test.
2. Le login est automatique et immediat (sans mot de passe).
   - **Client Alice** : `0331234567`
   - **Client Bob** : `0341234567`
*(Note : Entrer n'importe quel autre numero valide avec un prefixe autorise creera un nouveau compte client a la volee).*

---
*(Toutes les fonctionnalites ont ete implementees, testees et livrees.)*
