<?php

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;

class BaseModel
{
        public function __construct(protected EntityManagerInterface $entityManager) {}

    public function getEm(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function beginTransaction(): void
    {
        $this->getEm()->beginTransaction();
    }

    public function commit(): void
    {
            $this->getEm()->commit();
    }

    public function rollback(): void
    {
            $this->getEm()->rollback();
    }
}
