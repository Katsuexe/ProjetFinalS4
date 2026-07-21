* [ ] 

# Comment créer un tag (Git)

Ce document explique rapidement comment créer, lister, pousser et supprimer des tags Git.

## Types de tags

- Tag léger (lightweight) : simple pointeur vers un commit.
- Tag annoté (annotated) : stocke un message, l'auteur et la date; recommandé pour les releases.
- Tag signé : annoté et signé avec une clé GPG.

## Créer un tag

- Tag léger :

```bash
git tag v1.0
```

- Tag annoté :

```bash
git tag -a v1.0 -m "Release v1.0"
```

- Tag signé (si vous avez configuré GPG) :

```bash
git tag -s v1.0 -m "Signed release v1.0"
```

## Vérifier un tag

- Lister tous les tags :

```bash
git tag -l
```

- Afficher les détails d'un tag :

```bash
git show v1.0
```

- Vérifier la signature d'un tag signé :

```bash
git tag -v v1.0
```

## Pousser des tags vers le dépôt distant

- Pousser un tag spécifique :

```bash
git push origin v1.0
```

- Pousser tous les tags :

```bash
git push --tags
```

## Supprimer un tag

- Supprimer localement :

```bash
git tag -d v1.0
```

- Supprimer sur le distant (après suppression locale) :

```bash
git push --delete origin v1.0
```

ou (ancienne syntaxe) :

```bash
git push origin :refs/tags/v1.0
```

## Basculer / utiliser un tag

Les tags ne sont pas des branches. Pour travailler depuis un tag, créez une nouvelle branche basée sur le tag :

```bash
git checkout -b feature-from-v1.0 v1.0
```

## Bonnes pratiques

- Utilisez des tags annotés pour les releases publiques.
- Utilisez des noms sémantiques (par exemple `v1.2.3`).
- Poussez les tags immédiatement après la création si d'autres ont besoin d'y accéder.
- Signez les tags si vous publiez des releases officielles.

## Références rapides

- Afficher le commit pointé par un tag : `git show v1.0`
- Lister les tags triés par version (exige tri sémantique externe) : utiliser des outils comme `git tag --sort=v:refname` ou `git for-each-ref`.

---

Guide concis pour créer et gérer des tags Git.
