<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use App\Enum\SectionBlockKind;

/**
 * Prepares payloads for embedded payload forms + cleans payload before persistence.
 */
final class SectionBlockPayloadMapper
{
    /** @param array<string, mixed> $stored */
    public function datasetForForm(SectionBlockKind $kind, array $stored): array
    {
        return match ($kind) {
            SectionBlockKind::Hero => $this->heroDataset($stored),
            SectionBlockKind::TextImage => $this->textImageDataset($stored),
            SectionBlockKind::CardsGrid => $this->cardsDataset($stored),
            SectionBlockKind::ImageGallery => $this->galleryDataset($stored),
            SectionBlockKind::Contact => $this->contactDataset($stored),
        };
    }

    /** @param array<string, mixed> $input */
    public function sanitizePersistedPayload(SectionBlockKind $kind, array $input): array
    {
        return match ($kind) {
            SectionBlockKind::Hero => $this->sanitizeHero($input),
            SectionBlockKind::TextImage => $this->sanitizeTextImage($input),
            SectionBlockKind::CardsGrid => $this->sanitizeCards($input),
            SectionBlockKind::ImageGallery => $this->sanitizeGallery($input),
            SectionBlockKind::Contact => $this->sanitizeContact($input),
        };
    }

    /** @param array<string, mixed> $stored */
    private function heroDataset(array $stored): array
    {
        return [
            'titre' => (string) ($stored['titre'] ?? $stored['headline'] ?? ''),
            'sousTitre' => (string) ($stored['sousTitre'] ?? $stored['subheadline'] ?? ''),
            'imageFond' => (string) ($stored['imageFond'] ?? $stored['image'] ?? ''),
            'ctaTexte' => (string) ($stored['ctaTexte'] ?? $stored['ctaLabel'] ?? ''),
            'ctaLien' => (string) ($stored['ctaLien'] ?? $stored['ctaUrl'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $stored */
    private function textImageDataset(array $stored): array
    {
        return [
            'titre' => (string) ($stored['titre'] ?? $stored['headline'] ?? ''),
            'contenu' => (string) ($stored['contenu'] ?? $stored['bodyHtml'] ?? ''),
            'image' => (string) ($stored['image'] ?? ''),
            'inverserOrdre' => (bool) ($stored['inverserOrdre'] ?? false),
        ];
    }

    /** @param array<string, mixed> $stored */
    private function cardsDataset(array $stored): array
    {
        if (\is_array($stored['cartes'] ?? null)) {
            /** @var list<array<string, mixed>> $rows */
            $rows = array_values(array_filter($stored['cartes'], static fn ($r): bool => \is_array($r)));

            return [
                'titreSection' => (string) ($stored['titreSection'] ?? $stored['headline'] ?? ''),
                'descriptionSection' => (string) ($stored['descriptionSection'] ?? ''),
                'cartes' => array_map(fn (array $r) => $this->normalizeCarteRow($r), $rows),
            ];
        }

        $items = $stored['items'] ?? [];
        if (!\is_array($items)) {
            $items = [];
        }

        $cartes = [];
        foreach ($items as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $cartes[] = $this->normalizeCarteRow([
                'titre' => $row['titre'] ?? $row['title'] ?? '',
                'description' => $row['description'] ?? $row['subtitle'] ?? '',
                'imageOuIcone' => $row['imageOuIcone'] ?? $row['image'] ?? '',
                'lien' => $row['lien'] ?? $row['url'] ?? '',
            ]);
        }

        return [
            'titreSection' => (string) ($stored['titreSection'] ?? $stored['headline'] ?? ''),
            'descriptionSection' => (string) ($stored['descriptionSection'] ?? ''),
            'cartes' => $cartes,
        ];
    }

    /** @param array<string, mixed> $stored */
    private function galleryDataset(array $stored): array
    {
        $imagesRaw = $stored['images'] ?? [];
        $images = \is_array($imagesRaw)
            ? $imagesRaw
            : [];

        $paths = [];
        foreach ($images as $maybe) {
            if (\is_string($maybe) && '' !== trim($maybe)) {
                $paths[] = trim($maybe);
            }
        }

        return [
            'titreSection' => (string) ($stored['titreSection'] ?? $stored['headline'] ?? ''),
            'images' => array_values(array_unique($paths)),
        ];
    }

    /** @param array<string, mixed> $stored */
    private function contactDataset(array $stored): array
    {
        $contenu = (string) ($stored['contenu'] ?? $stored['bodyHtml'] ?? '');

        return [
            'titre' => (string) ($stored['titre'] ?? $stored['headline'] ?? ''),
            'introduction' => (string) ($stored['introduction'] ?? $stored['subheadline'] ?? ''),
            'contenu' => $contenu,
            'titreEncart' => (string) ($stored['titreEncart'] ?? $stored['infoBoxTitle'] ?? ''),
            'emailAffiche' => (string) ($stored['emailAffiche'] ?? $stored['contactEmail'] ?? ''),
            'telephoneAffiche' => (string) ($stored['telephoneAffiche'] ?? $stored['contactPhone'] ?? ''),
            'lienTelephone' => (string) ($stored['lienTelephone'] ?? $stored['contactPhoneHref'] ?? ''),
            'mentionSoumission' => (string) ($stored['mentionSoumission'] ?? $stored['formDisclaimer'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $row */
    private function normalizeCarteRow(array $row): array
    {
        return [
            'titre' => (string) ($row['titre'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'imageOuIcone' => (string) ($row['imageOuIcone'] ?? ''),
            'lien' => (string) ($row['lien'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $input */
    private function sanitizeHero(array $input): array
    {
        $clean = [];

        foreach (['titre', 'sousTitre', 'imageFond', 'ctaTexte', 'ctaLien'] as $k) {
            $v = $input[$k] ?? null;
            if (\is_string($v)) {
                $t = trim($v);
                if ('' !== $t) {
                    $clean[$k] = $t;
                }
            }
        }

        return $this->copyOptionalNavLabel($input, $clean);
    }

    /** @param array<string, mixed> $input */
    private function sanitizeTextImage(array $input): array
    {
        $clean = [];

        $titre = isset($input['titre']) && \is_string($input['titre']) ? trim($input['titre']) : '';
        if ('' !== $titre) {
            $clean['titre'] = $titre;
        }

        if (isset($input['contenu'])) {
            if (\is_string($input['contenu']) && '' !== trim($input['contenu'])) {
                $clean['contenu'] = trim($input['contenu']);
            }
        }

        $image = isset($input['image']) && \is_string($input['image']) ? trim($input['image']) : '';
        if ('' !== $image) {
            $clean['image'] = $image;
        }

        $clean['inverserOrdre'] = !empty($input['inverserOrdre']);

        return $this->copyOptionalNavLabel($input, $clean);
    }

    /** @param array<string, mixed> $input */
    private function sanitizeCards(array $input): array
    {
        $clean = [];

        foreach (['titreSection', 'descriptionSection'] as $key) {
            $v = $input[$key] ?? null;
            if (\is_string($v)) {
                $t = trim($v);
                if ('' !== $t) {
                    $clean[$key] = $t;
                }
            }
        }

        $cartes = [];
        $rows = $input['cartes'] ?? [];
        if (\is_array($rows)) {
            foreach ($rows as $row) {
                if (!\is_array($row)) {
                    continue;
                }

                $c = [];
                foreach (['titre', 'description', 'imageOuIcone', 'lien'] as $fk) {
                    $fv = $row[$fk] ?? null;
                    if (\is_string($fv)) {
                        $fv = trim($fv);
                        if ('' !== $fv) {
                            $c[$fk] = $fv;
                        }
                    }
                }

                if ([] !== $c) {
                    $cartes[] = $c;
                }
            }
        }

        $clean['cartes'] = $cartes;

        return $this->copyOptionalNavLabel($input, $clean);
    }

    /** @param array<string, mixed> $input */
    private function sanitizeGallery(array $input): array
    {
        $paths = [];

        foreach ($input['images'] ?? [] as $row) {
            if (\is_string($row)) {
                $t = trim($row);
                if ('' !== $t) {
                    $paths[] = $t;
                }
            }
        }

        $clean = ['images' => array_values(array_unique($paths))];

        if (isset($input['titreSection']) && \is_string($input['titreSection'])) {
            $t = trim($input['titreSection']);
            if ('' !== $t) {
                $clean['titreSection'] = $t;
            }
        }

        return $this->copyOptionalNavLabel($input, $clean);
    }

    /** @param array<string, mixed> $input */
    private function sanitizeContact(array $input): array
    {
        $clean = [];

        foreach (['titre', 'introduction'] as $k) {
            if (isset($input[$k]) && \is_string($input[$k])) {
                $t = trim($input[$k]);
                if ('' !== $t) {
                    $clean[$k] = $t;
                }
            }
        }

        if (isset($input['contenu']) && \is_string($input['contenu'])) {
            $html = trim($input['contenu']);
            if ('' !== $html) {
                $clean['contenu'] = $html;
            }
        }

        foreach (['titreEncart', 'emailAffiche', 'telephoneAffiche', 'lienTelephone', 'mentionSoumission'] as $k) {
            if (isset($input[$k]) && \is_string($input[$k])) {
                $t = trim($input[$k]);
                if ('' !== $t) {
                    $clean[$k] = $t;
                }
            }
        }

        return $this->copyOptionalNavLabel($input, $clean);
    }

    /**
     * Copies optional menu label into persisted JSON when present in the sanitizer input (e.g. merged from previous save).
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $clean
     *
     * @return array<string, mixed>
     */
    private function copyOptionalNavLabel(array $input, array $clean): array
    {
        if (!isset($input['navLabel']) || !\is_string($input['navLabel'])) {
            return $clean;
        }

        $t = trim($input['navLabel']);
        if ('' !== $t) {
            $clean['navLabel'] = $t;
        }

        return $clean;
    }
}
