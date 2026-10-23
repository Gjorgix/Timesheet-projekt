<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 180)]
    public ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    public array $roles = [] {
        get {
            $roles = $this->roles;
            $roles[] = 'ROLE_USER';
            return array_unique($roles);
        }
    }

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    public ?string $password = null;

    #[ORM\Column(length: 100)]
    public ?string $firstName = null;

    #[ORM\Column(length: 100)]
    public ?string $lastName = null;

    #[ORM\Column]
    public ?bool $isActive = null;

    /**
     * @var Collection<int, TimeEntry>
     */
    #[ORM\OneToMany(targetEntity: TimeEntry::class, mappedBy: 'employee')]
    public Collection $timeEntries {
        get => $this->timeEntries;
        set => $value;
    }

    public function __construct()
    {
        $this->timeEntries = new ArrayCollection();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function addTimeEntry(TimeEntry $timeEntry): static
    {
        if (!$this->timeEntries->contains($timeEntry)) {
            $this->timeEntries->add($timeEntry);
            $timeEntry->employee = $this;
        }

        return $this;
    }

    public function removeTimeEntry(TimeEntry $timeEntry): static
    {
        if ($this->timeEntries->removeElement($timeEntry)) {

            if ($timeEntry->employee === $this) {
                $timeEntry->employee = null;
            }
        }

        return $this;
    }
}
