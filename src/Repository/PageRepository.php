<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function findOnePublishedHomepage(): ?Page
    {
        /** @var Page|null $page */
        $page = $this->createQueryBuilder('p')
            ->andWhere('p.isHomepage = :h')
            ->andWhere('p.isPublished = true')
            ->setParameter('h', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $page;
    }

    public function findOnePublishedBySlug(string $slug): ?Page
    {
        /** @var Page|null $page */
        $page = $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isPublished = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        return $page;
    }
}
