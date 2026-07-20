<!--
    ============================================================================
    Partial : partials/_avatar_card.php — ADD / MODIFIER / SUPPRIMER la photo
    ============================================================================
    PEDAGOGIE : ce fragment est inclus (via view(), pas $this->extend()) par
    admin/profile.php ET user/profile.php — même carte, mêmes 3 actions,
    seules les URLs de destination changent (passées en variables), exactement
    comme admin/types/_permissions_fieldset.php est partagé entre create.php
    et edit.php.

    Variables ATTENDUES par ce partial :
      $user         array  — l'utilisateur courant (au moins 'username','photo')
      $pendingPhoto array|null — pendingPhotoForUser() : demande en attente
      $uploadUrl    string — route POST d'upload (multipart)
      $deleteUrl    string — route POST de suppression
      $exportPdfUrl string — route GET d'export PDF
    ============================================================================
-->
<div class="card" style="max-width:480px; margin-bottom:1.5rem;">
    <div class="avatar-card__body">
        <?php if (! empty($user['photo'])): ?>
            <img src="<?= esc(base_url('assets/uploads/avatars/' . $user['photo'])) ?>"
                 alt="Photo de profil"
                 class="avatar-circle avatar-circle--lg"
                 style="margin:0 auto 1rem;">
        <?php else: ?>
            <div class="avatar-placeholder avatar-placeholder--lg" style="margin:0 auto 1rem;">
                <?= esc(strtoupper(substr($user['username'] ?? '?', 0, 1))) ?>
            </div>
        <?php endif; ?>

        <?php if ($pendingPhoto): ?>
            <!--
                PEDAGOGIE — "statut en attente" : payload_decoded['photo'] vaut
                soit un nom de fichier (changement), soit null (suppression) —
                voir Admin\Dashboard::uploadPhoto()/deletePhoto(). On adapte le
                libellé du badge en conséquence, sans jamais rien appliquer
                tant qu'un admin/modérateur n'a pas validé (voir
                Admin\Validations::resolvePhoto()).
            -->
            <div class="badge badge--amber" style="margin-bottom:1rem;">
                <?= $pendingPhoto['payload_decoded']['photo'] === null
                    ? 'Suppression en attente de validation'
                    : 'Nouvelle photo en attente de validation' ?>
            </div>
        <?php endif; ?>

        <div class="avatar-card__actions">
            <!-- ADD / MODIFIER — un seul input file : son libellé change selon qu'une photo existe déjà -->
            <form action="<?= $uploadUrl ?>" method="post" enctype="multipart/form-data" style="display:inline-block;">
                <?= csrf_field() ?>
                <input type="file"
                       name="photo"
                       id="avatar-file-input"
                       class="avatar-card__file-input"
                       accept="image/png,image/jpeg,image/webp"
                       onchange="this.form.submit()">
                <label for="avatar-file-input" class="btn btn--outline btn--sm" style="cursor:pointer;">
                    <?= ! empty($user['photo']) ? 'Changer la photo' : 'Ajouter une photo' ?>
                </label>
            </form>

            <!-- SUPPRIMER — affiché uniquement s'il y a une photo active à supprimer -->
            <?php if (! empty($user['photo'])): ?>
                <form action="<?= $deleteUrl ?>" method="post" style="display:inline-block;" onsubmit="return confirm('Supprimer votre photo de profil ?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
                </form>
            <?php endif; ?>
        </div>

        <div style="margin-top:.9rem;">
            <a href="<?= $exportPdfUrl ?>" class="btn btn--secondary btn--sm">Exporter ma fiche en PDF</a>
        </div>
    </div>
</div>
