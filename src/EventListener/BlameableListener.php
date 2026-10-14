<?php
namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

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

        if ($user instanceof User) {
            if (method_exists($entity, 'setCreatedBy') && !$entity->getCreatedBy()) {
                $entity->setCreatedBy($user);
                $entity->setCreatedAt(new \DateTimeImmutable('now'));
                $entity->setUpdatedBy($user);
                $entity->setUpdatedAt(new \DateTimeImmutable('now'));
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();

        if ($user instanceof User) {
            if (method_exists($entity, 'setUpdatedBy')) {
                $entity->setUpdatedBy($user);
                $entity->setUpdatedAt(new \DateTimeImmutable('now'));
            }
        }
    }
}
