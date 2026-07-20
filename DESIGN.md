# DESIGN.md — Charte de design du projet

> Ce document est **normatif**. Toute nouvelle vue, tout nouveau composant CSS
> doit s'y conformer. Si un besoin n'est pas couvert ici, on complète ce
> fichier avant d'improviser dans le HTML (`style="..."` inline interdit sauf
> exception justifiée en commentaire).
>
> Référence externe utilisée en cas de doute sur une disposition :
> [gestaltprinciples.com](https://www.gestaltprinciples.com/) — les 9 lois de
> la perception visuelle. Ce document les traduit en règles concrètes pour ce
> projet (section 2).

---

## 0. Principe directeur : KISS

**Keep It Simple & Smart.** Deux conséquences concrètes pour ce projet :

1. **Un fichier CSS unique** (`public/assets/css/app.css`), organisé par
   sections commentées (`/* ===== NOM ===== */`), pas de framework CSS
   externe, pas de préprocesseur. Le but est qu'un étudiant puisse lire tout
   le CSS du projet en 15 minutes.
2. **Composer, ne pas réinventer.** Une nouvelle vue doit *toujours* essayer
   de composer les classes existantes (section 3) avant d'en créer une
   nouvelle. Si un nouveau composant est nécessaire, il doit être **assez
   petit pour être compris en une lecture** — s'il devient compliqué,
   diviser en sous-parties simples plutôt que d'empiler des cas particuliers
   dans une seule classe (voir section 5, "diviser plutôt que complexifier").

---

## 1. Fondations (déjà en place — ne pas dupliquer)

Toutes les couleurs, tailles et polices passent par les variables CSS
définies dans `:root` (`app.css`, lignes 1-16). **Ne jamais écrire une
couleur en dur** (`#1A73E8`) dans une nouvelle règle : toujours la variable.

| Variable | Valeur | Usage |
|---|---|---|
| `--first-color` | `#1A73E8` | Couleur de marque, liens, boutons primaires, focus |
| `--danger-color` | `#D93025` | Erreurs, suppression, actions destructives |
| `--success-color` | `#1E8E3E` | Confirmations, statuts positifs |
| `--input-color` | `#80868B` | Texte placeholder, icônes secondaires |
| `--border-color` | `#DADCE0` | Toutes les bordures |
| `--bg-color` | `#F8F9FA` | Fond de page (jamais le fond des cartes) |
| `--text-dark` | `#202124` | Texte principal |
| `--text-muted` | `#5F6368` | Texte secondaire, légendes |
| `--card-shadow` | ombre douce | Cartes flottantes (formulaires d'auth) |
| `--body-font` | Roboto | Police unique du projet |
| `--normal-font-size` / `--small-font-size` | `1rem` / `.75rem` | Les deux seules tailles de base ; toute variation se fait par `font-weight`, pas par une nouvelle taille |

**Couleur d'accent manquante à ajouter** (utilisée par les badges "amber"
mais jamais déclarée en variable — à corriger en même temps que ce guide) :

```css
--warning-color: #F29900;
```

---

## 2. Les 9 lois de Gestalt appliquées à ce projet

En cas de doute sur une disposition, se poser la question via le principe
correspondant plutôt que de deviner :

| Loi | Règle concrète dans ce projet |
|---|---|
| **Proximité** | Les champs d'un même formulaire sont regroupés dans `.form-group` avec un espacement constant (`1.25rem`) ; l'espace *entre* deux sections d'une carte (`.card__header` / `.card__body`) est toujours plus grand que l'espace *entre* deux champs d'un même formulaire. |
| **Similarité** | Toutes les actions destructives sont en `--danger-color`, quel que soit le contexte (bouton, badge, texte). Un utilisateur ne doit jamais deviner qu'une action est dangereuse — la couleur le lui dit partout pareil. |
| **Continuité** | Les tableaux et listes s'alignent sur une grille verticale unique (`padding` identique sur `th`/`td`) ; les colonnes d'actions sont toujours à droite, jamais mélangées avec les données. |
| **Clôture (Closure)** | Les cartes (`.card`) ont toujours un bord/ombre visible même quand leur contenu est incomplet (état vide, chargement) — l'œil doit percevoir un contour fermé, pas un bloc de texte flottant. |
| **Figure-fond** | Le fond de page est `--bg-color` (gris très clair), les cartes/formulaires sont blancs. Ce contraste est la seule façon de signaler "ceci est un bloc interactif" — ne jamais mettre une carte blanche sur fond blanc. |
| **Destin commun** | Les éléments qui changent ensemble (ex : ligne de tableau + son badge de statut + ses boutons d'action) doivent être dans le même conteneur `<tr>`/`<div>`, jamais séparés visuellement par une autre info non liée. |
| **Région commune** | Toute zone qui regroupe plusieurs informations liées (une carte stat, une ligne de formulaire, un item de calendrier) doit avoir une bordure ou un fond distinct — c'est ce qui fait qu'on "voit" un groupe sans avoir à lire le texte. |
| **Symétrie & ordre** | Les formulaires à deux boutons suivent toujours l'ordre *action principale (gauche) → action secondaire/annuler (droite)*, jamais l'inverse, pour que l'œil retrouve le même repère partout dans l'app. |
| **Prägnanz** | Face à deux façons de présenter une info, choisir toujours la plus simple perceptuellement : un badge de couleur plutôt qu'une phrase, une icône reconnue plutôt qu'un texte, un statut binaire plutôt qu'une énumération. |

---

## 3. Bibliothèque de composants existants (à réutiliser)

Ne pas recréer ces classes. Elles vivent déjà dans `app.css` :

- **Mise en page** : `.app`, `.sidebar`, `.main`, `.topbar`, `.page-content`, `.page-header`
- **Cartes** : `.card`, `.card__header`, `.card__title`, `.card__body`
- **Cartes stats** : `.stats-grid`, `.stat-card`, `.stat-icon` (+ variantes `--blue/--green/--amber/--red`), `.stat-body__label`, `.stat-body__value`
- **Formulaires (page interne, sur fond de carte)** : `.form-group` (label au-dessus + input pleine largeur) — *voir section 4, corrige un manque*
- **Formulaires (page d'auth, floating label)** : `.form`, `.form__div`, `.form__input`, `.form__label`, `.form__button`, `.form__error` — **ne pas mélanger avec `.form-group`**, ce sont deux systèmes différents pour deux contextes différents (auth plein écran vs. formulaire dans une carte admin)
- **Boutons** : `.btn` + `--primary` / `--outline` / `--danger` (+ `--sm` pour les actions de ligne de tableau)
- **Badges** : `.badge` + `--blue` / `--green` / `--red` / `--gray` (ajouter `--amber` — section 4)
- **Alertes** : `.alert` + `--error` / `--success` / `--info`
- **Tableaux** : styles natifs `table/thead/tbody` déjà stylés globalement, pas de classe à ajouter sauf besoin réel

---

## 4. Manques constatés (à corriger avant tout nouveau développement)

Ces classes sont **utilisées dans les vues** (`admin/types/create.php`,
`admin/profile.php`, `user/profile.php`, `user/password.php`,
`admin/import/index.php`) mais **absentes de `app.css`** — c'est la cause
directe du problème de mise en forme remonté dans les captures d'écran :

1. `.form-group` (+ `label`, `input`, `textarea`, `select` non stylés dans ce contexte)
2. `.badge--amber` (utilisé implicitement par cohérence avec `.stat-icon--amber`, mais pas encore nécessaire — à ajouter dès le premier badge de statut "en attente")

➡️ Corrigé dans le même lot que ce document (voir `app.css`, section
`FORM GROUP (cartes admin/user)`).

---

## 5. Règles pour tout nouveau composant

1. **Chercher d'abord** dans la section 3 si une classe existante convient.
2. **Nommer en BEM simplifié**, comme l'existant : `.bloc`, `.bloc__partie`,
   `.bloc--variante`. Pas de nom générique type `.box1`.
3. **Diviser plutôt que complexifier** : si un composant a besoin de plus de
   3 variantes ou de règles conditionnelles imbriquées, c'est le signal
   qu'il faut le découper en sous-composants simples (ex : plutôt qu'un
   `.card--big-with-icon-and-footer-and-badge`, composer `.card` +
   `.card__header` + `.badge` + un slot footer optionnel).
4. **Toujours passer par les variables CSS** de la section 1.
5. **Un nouveau fichier de vue = zéro `<style>` inline.** Si un ajustement
   ponctuel est nécessaire, il vit dans `app.css`, documenté par un
   commentaire expliquant *pourquoi* (comme le fait déjà le commentaire
   pédagogique sur `.form__error` dans le fichier actuel).

---

## 6. Portée de ce document

Ce document couvre le **design visuel** (CSS, disposition, composants). Il
ne couvre pas l'architecture applicative (contrôleurs, services, migrations)
— voir `GUIDE_PEDAGOGIQUE.md` pour ça. Les deux documents sont
complémentaires et doivent rester cohérents : toute nouvelle fonctionnalité
documentée dans `GUIDE_PEDAGOGIQUE.md` doit respecter ce `DESIGN.md` pour sa
partie vue.
