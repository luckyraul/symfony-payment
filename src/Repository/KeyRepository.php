<?php

namespace Mygento\Payment\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Mygento\Payment\Entity\Key;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Key>
 *
 * @method Key|null find($id)
 * @method Key|null findOneBy(array $criteria, array $orderBy = null)
 * @method Key[] findAll()
 * @method Key[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KeyRepository extends ServiceEntityRepository
{
    use RepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Key::class);
    }
}
