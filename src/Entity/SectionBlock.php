<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\SectionLayout;
use App\Repository\SectionBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionBlockRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SectionBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Page $page = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $label = null;

    /**
     * HTML fragment id for in-page anchors (#projets) and header navigation.
     */
    #[ORM\Column(length: 80, nullable: true)]
    #[Assert\Length(max: 80)]
    #[Assert\Regex(
        pattern: '/^[a-z][a-z0-9_-]*$/',
        message: 'L’ancre doit commencer par une lettre et ne contenir que minuscules, chiffres, tirets ou underscores.'
    )]
    private ?string $anchorId = null;

    #[ORM\Column(length: 30, enumType: SectionLayout::class)]
    #[Assert\NotNull]
    private ?SectionLayout $layout = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $payload = [];

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $isEnabled = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
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
        return $this->label ?? ($this->layout?->label() ?? 'Bloc');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getAnchorId(): ?string
    {
        return $this->anchorId;
    }

    public function setAnchorId(?string $anchorId): self
    {
        $this->anchorId = $anchorId;

        return $this;
    }

    public function getLayout(): ?SectionLayout
    {
        return $this->layout;
    }

    public function setLayout(SectionLayout $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** @param array<string, mixed> $payload */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

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

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    /**
     * Virtual fields for EasyAdmin: they read/write the JSON payload so clients never edit raw JSON.
     * Reassigns the whole array so Doctrine reliably detects changes on the JSON column.
     */
    private function setPayloadScalar(string $key, ?string $value): void
    {
        $p = $this->payload;
        if (null === $value || '' === $value || (\is_string($value) && '' === trim($value))) {
            unset($p[$key]);
        } else {
            $p[$key] = $value;
        }
        $this->payload = $p;
    }

    public function getPayloadHeadline(): ?string
    {
        $v = $this->payload['headline'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadHeadline(?string $headline): self
    {
        $this->setPayloadScalar('headline', $headline);

        return $this;
    }

    public function getPayloadSubheadline(): ?string
    {
        $v = $this->payload['subheadline'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadSubheadline(?string $subheadline): self
    {
        $this->setPayloadScalar('subheadline', $subheadline);

        return $this;
    }

    public function getPayloadBodyHtml(): ?string
    {
        $v = $this->payload['bodyHtml'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadBodyHtml(?string $bodyHtml): self
    {
        $this->setPayloadScalar('bodyHtml', $bodyHtml);

        return $this;
    }

    public function getPayloadImage(): ?string
    {
        $v = $this->payload['image'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadImage(?string $image): self
    {
        $this->setPayloadScalar('image', $image);

        return $this;
    }

    public function getPayloadCtaLabel(): ?string
    {
        $v = $this->payload['ctaLabel'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadCtaLabel(?string $ctaLabel): self
    {
        $this->setPayloadScalar('ctaLabel', $ctaLabel);

        return $this;
    }

    public function getPayloadCtaUrl(): ?string
    {
        $v = $this->payload['ctaUrl'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadCtaUrl(?string $ctaUrl): self
    {
        $this->setPayloadScalar('ctaUrl', $ctaUrl);

        return $this;
    }

    public function getPayloadSecondaryCtaLabel(): ?string
    {
        $v = $this->payload['secondaryCtaLabel'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadSecondaryCtaLabel(?string $secondaryCtaLabel): self
    {
        $this->setPayloadScalar('secondaryCtaLabel', $secondaryCtaLabel);

        return $this;
    }

    public function getPayloadSecondaryCtaUrl(): ?string
    {
        $v = $this->payload['secondaryCtaUrl'] ?? null;

        return \is_string($v) ? $v : null;
    }

    public function setPayloadSecondaryCtaUrl(?string $secondaryCtaUrl): self
    {
        $this->setPayloadScalar('secondaryCtaUrl', $secondaryCtaUrl);

        return $this;
    }
}
