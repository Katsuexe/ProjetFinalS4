<?php
/**
 * Partial : admin/types/_permissions_fieldset.php
 *
 * PEDAGOGIE — pourquoi un partial plutôt que de dupliquer ce bloc dans
 * create.php ET edit.php : c'est exactement le même HTML dans les deux cas
 * (seules les cases pré-cochées changent). Le dupliquer serait contraire au
 * principe KISS (DESIGN.md §0/§5) : la moindre modification de ce bloc
 * (ajouter une colonne, changer le style) devrait alors être répétée à deux
 * endroits, avec le risque classique de les faire diverger avec le temps.
 *
 * Variables attendues :
 *   $permissions : array   liste complète des permissions (PermissionModel::findAll())
 *   $checkedIds  : int[]   ids des permissions déjà assignées au type (vide à la création)
 *
 * Loi de Gestalt appliquée ici : RÉGION COMMUNE (DESIGN.md §2) — chaque
 * case + son slug + son nom sont regroupés dans un même petit encart
 * (.perm-check) pour que l'œil perçoive immédiatement "ceci est une unité
 * cochable", plutôt qu'une simple liste de cases alignées sans repère visuel.
 */
?>
<div class="form-group">
    <?php if (empty($permissions)): ?>
        <label>Permissions assignées à ce type</label>
        <p class="form-hint">Aucune permission n'existe encore dans la table <code>permissions</code>.</p>
    <?php else: ?>
        <div class="perm-grid__header">
            <label style="margin-bottom:0;">Permissions assignées à ce type</label>
            <span class="perm-grid__count" data-perm-count>
                <strong><?= count($checkedIds) ?></strong> / <?= count($permissions) ?> sélectionnées
            </span>
        </div>
        <div class="perm-grid" data-perm-grid>
            <?php foreach ($permissions as $perm): ?>
                <label class="perm-check">
                    <input type="checkbox"
                           name="permissions[]"
                           value="<?= esc($perm['id']) ?>"
                           <?= in_array((int) $perm['id'], $checkedIds, true) ? 'checked' : '' ?>>
                    <span>
                        <span class="perm-check__name"><?= esc($perm['name']) ?></span>
                        <code class="perm-check__slug"><?= esc($perm['slug']) ?></code>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <script>
        (function () {
            var grid = document.currentScript.previousElementSibling;
            var header = grid.previousElementSibling;
            var counter = header.querySelector('[data-perm-count] strong');
            grid.addEventListener('change', function () {
                counter.textContent = grid.querySelectorAll('input[type="checkbox"]:checked').length;
            });
        })();
        </script>
    <?php endif; ?>
</div>
