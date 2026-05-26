<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TwitterCardKind;
use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug est déjà utilisé.')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[ORM\Column(length: 160, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Le slug doit être en minuscules, sans accents, avec des tirets (ex: "mentions-legales").'
    )]
    private ?string $slug = null;

    #[ORM\Column(length: 70, nullable: true)]
    #[Assert\Length(max: 70)]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Length(max: 180)]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ogImage = null;

    /**
     * Crawl policy for &lt;meta name="robots"&gt;. Empty → index,follow when published.
     */
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $metaRobots = null;

    /** Absolute https URL, URL relative to origin (/path), or slug-like path without leading slash — optional override. */
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $canonicalOverride = null;

    #[ORM\Column(length: 32)]
    private string $ogType = 'website';

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $ogSiteName = null;

    #[ORM\Column(length: 32, enumType: TwitterCardKind::class)]
    private TwitterCardKind $twitterCard = TwitterCardKind::SummaryLargeImage;

    #[ORM\Column]
    private bool $isHomepage = false;

    #[ORM\Column]
    private bool $isPublished = true;

    /**
     * Optional site chrome for the public layout (footer links, brand line, etc.).
     *
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $footerPayload = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, SectionBlock> */
    #[ORM\OneToMany(
        targetEntity: SectionBlock::class,
        mappedBy: 'page',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title ?? 'Nouvelle page';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): self
    {
        $this->ogImage = $ogImage;

        return $this;
    }

    public function getMetaRobots(): ?string
    {
        return $this->metaRobots;
    }

    public function setMetaRobots(?string $metaRobots): self
    {
        $t = null === $metaRobots ? '' : trim($metaRobots);
        $this->metaRobots = '' === $t ? null : $t;

        return $this;
    }

    public function getCanonicalOverride(): ?string
    {
        return $this->canonicalOverride;
    }

    public function setCanonicalOverride(?string $canonicalOverride): self
    {
        $t = null === $canonicalOverride ? '' : trim($canonicalOverride);
        $this->canonicalOverride = '' === $t ? null : $t;

        return $this;
    }

    public function getOgType(): string
    {
        return $this->ogType;
    }

    public function setOgType(string $ogType): self
    {
        $t = trim($ogType);

        $this->ogType = '' !== $t ? $t : 'website';

        return $this;
    }

    public function getOgSiteName(): ?string
    {
        return $this->ogSiteName;
    }

    public function setOgSiteName(?string $ogSiteName): self
    {
        $t = null === $ogSiteName ? '' : trim($ogSiteName);
        $this->ogSiteName = '' === $t ? null : $t;

        return $this;
    }

    public function getTwitterCard(): TwitterCardKind
    {
        return $this->twitterCard;
    }

    public function setTwitterCard(TwitterCardKind $twitterCard): self
    {
        $this->twitterCard = $twitterCard;

        return $this;
    }

    public function isHomepage(): bool
    {
        return $this->isHomepage;
    }

    public function setIsHomepage(bool $isHomepage): self
    {
        $this->isHomepage = $isHomepage;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getFooterPayload(): ?array
    {
        return $this->footerPayload;
    }

    /** @param array<string, mixed>|null $footerPayload */
    public function setFooterPayload(?array $footerPayload): self
    {
        $this->footerPayload = $footerPayload;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, SectionBlock> */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(SectionBlock $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setPage($this);
        }

        return $this;
    }

    public function removeSection(SectionBlock $section): self
    {
        if ($this->sections->removeElement($section) && $section->getPage() === $this) {
            $section->setPage(null);
        }

        return $this;
    }

    /** @return Collection<int, SectionBlock> */
    public function getEnabledSections(): Collection
    {
        $blocks = array_values(array_filter(
            $this->sections->toArray(),
            static fn (SectionBlock $s): bool => $s->isEnabled(),
        ));

        usort(
            $blocks,
            static fn (SectionBlock $a, SectionBlock $b): int => $a->getPosition() <=> $b->getPosition(),
        );

        return new ArrayCollection($blocks);
    }

    /** Title for &lt;title&gt;, Open Graph and Twitter (meta title or internal title). */
    public function sharingDocumentTitle(): string
    {
        $t = trim((string) ($this->metaTitle ?? ''));
        if ('' !== $t) {
            return $t;
        }

        return trim((string) ($this->title ?? ''));
    }

    public function effectiveOgSiteName(): ?string
    {
        $n = trim((string) ($this->ogSiteName ?? ''));
        if ('' !== $n) {
            return $n;
        }

        $fp = $this->footerPayload;
        if (!\is_array($fp)) {
            return null;
        }

        $brand = $fp['brand'] ?? null;
        if (!\is_array($brand)) {
            return null;
        }

        $line = isset($brand['line']) && \is_string($brand['line']) ? trim($brand['line']) : '';

        return '' !== $line ? $line : null;
    }

    public function resolvedMetaRobots(): string
    {
        $r = trim((string) ($this->metaRobots ?? ''));

        return '' !== $r ? $r : 'index,follow';
    }

    /** Normalized OG type tag (fallback website). */
    public function resolvedOgType(): string
    {
        $t = strtolower(trim($this->ogType));

        return '' !== $t ? $t : 'website';
    }

    public function canonicalAbsoluteUrl(Request $request): string
    {
        $origin = self::normalizeRequestOrigin($request);
        $slug = trim((string) ($this->slug ?? ''));
        $defaultPath = '/' === $slug || '' === $slug || $this->isHomepage()
            ? '/'
            : '/'.$slug;

        $override = trim((string) ($this->canonicalOverride ?? ''));
        if ('' === $override) {
            return $origin.$defaultPath;
        }

        if (preg_match('#^https?://#i', $override)) {
            return $override;
        }

        if ('/' === ($override[0] ?? '')) {
            return $origin.$override;
        }

        return $origin.'/'.ltrim($override, '/');
    }

    public function resolvedOgImageAbsoluteUrl(Request $request): ?string
    {
        return self::resolvePublicAssetAbsoluteUrl($request, $this->ogImage);
    }

    /** JSON-LD WebSite snippet (safe for |json_encode in Twig). */
    public function structuredDataWebsite(Request $request): array
    {
        $name = $this->effectiveOgSiteName() ?? $this->sharingDocumentTitle();
        if ('' === trim($name)) {
            $name = 'Site';
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $this->canonicalAbsoluteUrl($request),
        ];
    }

    private static function normalizeRequestOrigin(Request $request): string
    {
        return $request->getScheme().'://'.$request->getHost()
            .($request->getPort() && !\in_array($request->getPort(), [80, 443], true)
                ? ':'.$request->getPort()
                : '');
    }

    private static function resolvePublicAssetAbsoluteUrl(Request $request, ?string $value): ?string
    {
        $v = trim((string) ($value ?? ''));
        if ('' === $v) {
            return null;
        }

        if (preg_match('#^https?://#i', $v)) {
            return $v;
        }

        $origin = self::normalizeRequestOrigin($request);

        return '/' === ($v[0] ?? '') ? $origin.$v : $origin.'/'.$v;
    }
}
