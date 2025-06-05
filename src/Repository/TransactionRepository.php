<?php

namespace Mygento\Payment\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Mygento\Payment\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[] findAll()
 * @method Transaction[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    use RepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return array<int, array<string>>
     */
    public function getTransactionSummary(string $code, string $paymentIdentifier): array
    {
        $qb = $this->createQueryBuilder('tr');
        $qb->select('tr.transactionType, sum(tr.amount) as sum');
        $qb->andWhere($qb->expr()->eq('tr.code', $qb->expr()->literal($code)));
        $qb->andWhere($qb->expr()->eq('tr.paymentIdentifier', $qb->expr()->literal($paymentIdentifier)));
        $qb->addGroupBy('tr.transactionType');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array<string>>
     */
    public function getTransactionSummaryByOrder(string $code, string $order): array
    {
        $qb = $this->createQueryBuilder('tr');
        $qb->select('tr.transactionType, sum(tr.amount) as sum');
        $qb->andWhere($qb->expr()->eq('tr.code', $qb->expr()->literal($code)));
        $qb->andWhere($qb->expr()->eq('tr.order', $qb->expr()->literal($order)));
        $qb->addGroupBy('tr.transactionType');

        return $qb->getQuery()->getResult();
    }
}
