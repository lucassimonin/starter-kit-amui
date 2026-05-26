<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\SectionBlockKind;
use App\Repository\SectionBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionBlockRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'section_block')]
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

    /**
     * Primary site header (#anchors): show a link only when anchor + labels exist and this is true.
     */
    #[ORM\Column(options: ['default' => true])]
    private bool $showInNav = true;

    #[ORM\Column(length: 32, enumType: SectionBlockKind::class)]
    private SectionBlockKind $kind;

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
        $this->kind = SectionBlockKind::Hero;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        $kindLabel = $this->kind->label();

        return sprintf('%s (%s)', $this->label ?? 'Sans nom', $kindLabel);
    }

    /** Short label shown in public header anchors when explicit payload.navLabel is absent. */
    public function resolvedNavMenuLabel(): string
    {
        $nav = $this->payload['navLabel'] ?? '';
        $navStr = \is_string($nav) ? trim($nav) : '';
        if ('' !== $navStr) {
            return $navStr;
        }

        return trim((string) ($this->label ?? ''));
    }

    public function appearsInPrimaryNav(): bool
    {
        if (!$this->showInNav) {
            return false;
        }

        $anchor = trim((string) ($this->anchorId ?? ''));

        return '' !== $anchor && '' !== $this->resolvedNavMenuLabel();
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

    public function isShowInNav(): bool
    {
        return $this->showInNav;
    }

    public function setShowInNav(bool $showInNav): self
    {
        $this->showInNav = $showInNav;

        return $this;
    }

    public function getKind(): SectionBlockKind
    {
        return $this->kind;
    }

    public function setKind(SectionBlockKind $kind): self
    {
        $this->kind = $kind;

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
}
