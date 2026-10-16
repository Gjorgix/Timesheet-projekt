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
}
