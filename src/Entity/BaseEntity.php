<?php

namespace App\Entity;


use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $createdBy = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $updatedBy = null;
}
