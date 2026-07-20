<?php

/**
 * Vérifie si l'utilisateur connecté possède une permission donnée.
 * Usage dans les vues et controllers : has_permission('wallet.view')
 */
function has_permission(string $slug): bool
{
    $permissions = session()->get('permissions') ?? [];
    return in_array($slug, $permissions, true);
}

/**
 * Vérifie plusieurs permissions (toutes requises).
 */
function has_permissions(array $slugs): bool
{
    foreach ($slugs as $slug) {
        if (! has_permission($slug)) {
            return false;
        }
    }
    return true;
}

/**
 * Vérifie si l'utilisateur a au moins une des permissions listées.
 */
function has_any_permission(array $slugs): bool
{
    foreach ($slugs as $slug) {
        if (has_permission($slug)) {
            return true;
        }
    }
    return false;
}

/**
 * Retourne le slug du type de l'utilisateur connecté.
 */
function user_type(): string
{
    return session()->get('type_slug') ?? '';
}
