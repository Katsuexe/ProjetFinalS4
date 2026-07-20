# Suivi des Tâches - Opérateur Mobile Money

Ce fichier trace les travaux effectués à chaque livraison (tag), conformément aux consignes du projet.

## Informations Étudiant
- **Nom(s) et Prénom(s)** : [À REMPLIR]
- **Lien formulaire** : Rempli via https://forms.gle/nCv6xJYHVvVj2FKA

---

## Livraison : Version 1 (Tag `v1`)

### 🛠️ Travaux Préparatoires (Socle de base)
Avant d'attaquer les fonctionnalités spécifiques du sujet, le socle technique a été mis en place :
- **Architecture Technique** : CodeIgniter 4 avec SQLite embarqué, HTML/CSS natif (mobile-first), pas de framework JS lourd.
- **Base de données** : Création du script `base.sql` (ou migrations CI4) contenant les schémas initiaux.
- **Authentification & Permissions** : Mise en place d'un système de rôles (Admin/Opérateur, Utilisateur/Client) avec filtres de sécurité.
- **Interface (UI)** : Création des layouts responsives (Admin et User) et intégration de la devise Ariary (`Ar`) comme unité par défaut.

### 📱 Fonctionnalités "Côté Opérateur" (En cours / À faire)
- [ ] Configuration des préfixes valables de l'opérateur (ex: 033 et 037).
- [ ] Création des types d'opérations (dépôt, retrait, transfert).
- [ ] Gestion des barèmes de frais par tranche de montant (modifiables).
- [ ] Affichage de la situation des gains (via les frais de retrait et transfert).
- [ ] Affichage de la situation des comptes clients.

### 👤 Fonctionnalités "Côté Client" (En cours / À faire)
- [ ] Login automatique avec le numéro de téléphone (sans inscription préalable).
- [ ] Consulter le solde de son compte.
- [ ] Faire un dépôt (simulation automatique).
- [ ] Faire un retrait (simulation automatique).
- [ ] Faire un transfert vers un autre numéro.
- [ ] Consulter l'historique des opérations.

---
*(Note : Ce fichier sera mis à jour au fur et à mesure des développements pour détailler l'implémentation de chaque point avant la livraison finale de la v1 à 13h).*
