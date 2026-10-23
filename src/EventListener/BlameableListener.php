<?php
namespace App\EventListener;

use App\Entity\BaseEntity;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::prePersist, priority: 500)]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500)]
class BlameableListener
{
    public function __construct(
        private Security $security
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();

        if ($user instanceof User && $entity instanceof BaseEntity && !$entity->createdBy) {
            $entity->createdBy = $user;
            $entity->createdAt = new DateTimeImmutable('now');
            $entity->updatedBy = $user;
            $entity->updatedAt = new DateTimeImmutable('now');
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();

        if ($user instanceof User && $entity instanceof BaseEntity) {
            $entity->updatedBy = $user;
            $entity->updatedAt = new DateTimeImmutable('now');
        }
    }
}
