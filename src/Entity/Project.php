<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project extends BaseEntity
{

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 1024, nullable: true)]
    public ?string $description = null;

    #[ORM\Column]
    public bool $isActive = true;

    /**
     * @var Collection<int, TimeEntry>
     */
    #[ORM\OneToMany(targetEntity: TimeEntry::class, mappedBy: 'project')]
    private Collection $timeEntries;

    public function __construct()
    {
        $this->timeEntries = new ArrayCollection();
    }


    /**
     * @return Collection<int, TimeEntry>
     */
    public function getTimeEntries(): Collection
    {
        return $this->timeEntries;
    }

    public function addTimeEntry(TimeEntry $timeEntry): static
    {
        if (!$this->timeEntries->contains($timeEntry)) {
            $this->timeEntries->add($timeEntry);
            $timeEntry->project = $this;
        }

        return $this;
    }

    public function removeTimeEntry(TimeEntry $timeEntry): static
    {
        if ($this->timeEntries->removeElement($timeEntry)) {

            if ($timeEntry->project === $this) {
                $timeEntry->project = null;
            }
        }

        return $this;
    }
}
