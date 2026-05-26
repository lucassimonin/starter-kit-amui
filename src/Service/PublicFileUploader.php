<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Stores uploaded images under public/uploads/page-builder for JSON-friendly paths.
 */
final class PublicFileUploader
{
    public const PUBLIC_SUBDIR = 'uploads/page-builder';

    public function __construct(
        private readonly string $projectDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function storeImage(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Fichier de téléchargement invalide.');
        }

        $mime = (string) $file->getMimeType();
        if (!str_starts_with($mime, 'image/')) {
            throw new \InvalidArgumentException('Seules les images sont acceptées.');
        }

        $targetDir = $this->projectDir.'/public/'.self::PUBLIC_SUBDIR;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \RuntimeException(sprintf('Impossible de créer le répertoire %s.', $targetDir));
        }

        $original = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = $this->slugger->slug($original ?: 'asset')->lower()->toString();
        $ext = $file->guessExtension();
        if (null === $ext || '' === $ext) {
            $ext = strtolower((string) $file->getClientOriginalExtension());
        }
        if ('' === $ext) {
            $ext = 'bin';
        }
        $name = sprintf('%s-%s.%s', $safe, bin2hex(random_bytes(4)), strtolower($ext));

        $file->move($targetDir, $name);

        return '/'.self::PUBLIC_SUBDIR.'/'.$name;
    }
}
