<?php

namespace Mygento\Payment\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Mygento\Payment\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Registration>
 *
 * @method Registration|null find($id)
 * @method Registration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registration[] findAll()
 * @method Registration[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationRepository extends ServiceEntityRepository
{
    use RepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }
}
