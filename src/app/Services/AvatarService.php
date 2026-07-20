<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * ============================================================================
 *  AvatarService — validation + stockage des photos de profil
 * ============================================================================
 *
 * PEDAGOGIE — pourquoi un Service et pas du code directement dans les
 * contrôleurs (Admin\Dashboard, User\Dashboard) ? Exactement la même
 * raison que CsvImportService/ExcelService : LA MÊME logique de stockage
 * est utilisée à quatre endroits (photo d'un admin, d'un modérateur, d'un
 * user, et l'upload lui-même dans le workflow de validation) — la centraliser
 * ici évite de dupliquer les règles de sécurité (extensions, taille) et
 * garantit qu'elles sont identiques partout.
 *
 * STOCKAGE : les fichiers sont écrits directement dans
 * public/assets/uploads/avatars/ (donc servables tels quels via base_url(),
 * pas besoin d'un contrôleur dédié pour les servir) — que la photo soit déjà
 * approuvée ou encore en attente de validation. Ce n'est PAS un problème de
 * confidentialité ici (une photo de profil n'est pas un document sensible),
 * et ça permet à l'admin/modérateur de voir un APERÇU de la photo en attente
 * directement dans la file de validation (voir admin/validations/*.php).
 * ============================================================================
 */
class AvatarService
{
    /** Sous-dossier de public/ où sont stockées les photos. */
    private const UPLOAD_SUBDIR = 'assets/uploads/avatars';

    /** Extensions autorisées (vérifiées via getExtension() ET getMimeType()). */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Taille maximale acceptée, en octets (2 Mo). */
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024;

    public function uploadDir(): string
    {
        return FCPATH . self::UPLOAD_SUBDIR;
    }

    public function urlFor(string $filename): string
    {
        // PEDAGOGIE : on charge le helper ICI plutôt que de compter sur le
        // contrôleur appelant (qui le fait déjà dans son initController(),
        // mais un Service ne devrait pas dépendre silencieusement de l'ordre
        // d'exécution d'un autre composant — voir le même principe dans
        // CsvImportService, qui n'a besoin d'aucun helper et n'en a donc pas
        // besoin, contrairement à ici).
        helper('url');

        return base_url(self::UPLOAD_SUBDIR . '/' . $filename);
    }

    /**
     * Valide et enregistre un fichier uploadé. Renvoie le nom de fichier
     * généré (à stocker en base — jamais le nom original, voir plus bas).
     *
     * @throws \RuntimeException si le fichier est invalide (message déjà
     *                           adapté à un affichage direct à l'utilisateur).
     */
    public function store(?UploadedFile $file): string
    {
        if ($file === null || ! $file->isValid()) {
            throw new \RuntimeException('Aucune image valide envoyée.');
        }

        if ($file->hasMoved()) {
            throw new \RuntimeException('Ce fichier a déjà été traité.');
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \RuntimeException('Format non autorisé (jpg, jpeg, png ou webp uniquement).');
        }

        // PEDAGOGIE — SÉCURITÉ : ne JAMAIS se fier uniquement à l'extension
        // du fichier (un .jpg peut contenir n'importe quoi). getMimeType()
        // inspecte le CONTENU réel du fichier (magic bytes), pas juste son
        // nom — beaucoup plus fiable pour bloquer un fichier renommé.
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \RuntimeException("Le contenu du fichier ne correspond pas à une image valide.");
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \RuntimeException('Image trop volumineuse (2 Mo maximum).');
        }

        $targetDir = $this->uploadDir();
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // getRandomName() génère un nom unique (évite collisions ET évite
        // d'exposer le nom de fichier original de l'utilisateur).
        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return $newName;
    }

    /**
     * Supprime un fichier avatar du disque (silencieusement si absent —
     * on ne veut jamais qu'une suppression de photo fasse planter une
     * approbation/rejet à cause d'un fichier déjà manquant).
     */
    public function delete(?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $path = $this->uploadDir() . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
