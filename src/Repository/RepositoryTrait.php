<?php

namespace Mygento\Payment\Repository;

trait RepositoryTrait
{
    /**
     * @throws \DomainException in case of entity class was not match for declared
     */
    public function save(object $entity, bool $flush = false): object
    {
        if (!($entity instanceof ($this->getEntityName()))) {
            throw new \DomainException(self::class . '::save() works only with "' . $this->getEntityName() . '" objects!');
        }

        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $entity;
    }

    /**
     * @throws \DomainException in case of entity class was not match for declared
     */
    public function remove(object $entity, bool $flush = false): void
    {
        if (!($entity instanceof ($this->getEntityName()))) {
            throw new \DomainException(self::class . '::remove() works only with "' . $this->getEntityName() . '" objects!');
        }

        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function clearEntityManager(): void
    {
        $this->getEntityManager()->clear();
    }

    public function detach(object $entity): void
    {
        $this->getEntityManager()->detach($entity);
    }

    public function beginTransaction(): void
    {
        $this->getEntityManager()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getEntityManager()->commit();
    }

    public function rollback(): void
    {
        $this->getEntityManager()->rollback();
    }
}
