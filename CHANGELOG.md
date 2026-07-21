# Refonte UX — Blue Monay (Soft Sky)

## Palette
- Primary  #3B82F6 (bleu)
- Accent   #A5B4FC (lavande)
- Fond     #F0F9FF (sky-50)
- Texte    #1E293B

## Typo
- Titres : Sora (500-700)
- Corps  : Manrope (400-600)

## Fichiers modifiés
- `src/public/assets/css/app.css` — nouveau `:root` clair + overrides
  de surfaces adaptés (light theme).
- `src/app/Views/layouts/{admin,auth,user}.php` — nom d'app
  `MonApp` → `Blue Monay` (fallback si `app.name` non défini).
- `src/app/Views/auth/{login,register}.php` — même remplacement.

Aucun nom de classe ni structure PHP modifié : drop-in.

## v3 — Responsive mobile complet

`public/assets/css/app.css` — ajout d'une section responsive dédiée en fin de fichier :
- Breakpoints : ≤1024px (tablette), ≤767px (mobile), ≤480px (petit mobile), plus paysage bas.
- Sidebar off-canvas plein-hauteur (100dvh) avec `is-open`, overlay et bouton close.
- Topbar compacte 56px + burger, safe-area iOS, menu compte réduit à l'avatar (dropdown fixé).
- Grilles `stats-grid` / `perm-grid` en 1 colonne, cards / stat-card compactées.
- Inputs `font-size:16px` (anti-zoom iOS), min-height 44px sur tous les tap targets.
- `form-actions` en `column-reverse` (action principale sous le pouce), boutons pleine largeur.
- Auth : carte pleine largeur, `100dvh`, safe-area bas.
- Modal en bottom-sheet mobile, tables scrollables horizontalement.
- `prefers-reduced-motion` respecté, tap highlight iOS supprimé sur tactile.
- Anti-débordement : `overflow-x:hidden` sur html/body, medias `max-width:100%`.
