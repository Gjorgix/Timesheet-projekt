<?php

namespace App\Entity;

use App\Repository\TimeEntryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
//doczytac DTO
#[ORM\Entity(repositoryClass: TimeEntryRepository::class)]
class TimeEntry extends BaseEntity
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public ?DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public ?DateTimeImmutable $endAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $employee = null;

    #[ORM\ManyToOne(inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(nullable: false)]
    public ?Project $project = null;
}
