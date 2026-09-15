<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Administratorzy
        $anna = $this->createUser(
            $manager,
            'anna.nowak@example.com',
            'Anna',
            'Nowak',
            'ROLE_ADMIN',
            'AnnaNowak'
        );

        $piotr = $this->createUser(
            $manager,
            'piotr.kowalski@example.com',
            'Piotr',
            'Kowalski',
            'ROLE_ADMIN',
            'PiotrKowalski'
        );

        // Pracownicy
        $jan = $this->createUser(
            $manager,
            'jan.kowalski@example.com',
            'Jan',
            'Kowalski',
            'ROLE_USER',
            'JanKowalski'
        );

        $maria = $this->createUser(
            $manager,
            'maria.nowak@example.com',
            'Maria',
            'Nowak',
            'ROLE_USER',
            'MariaNowak'
        );

        $tomasz = $this->createUser(
            $manager,
            'tomasz.wisniewski@example.com',
            'Tomasz',
            'Wiśniewski',
            'ROLE_USER',
            'TomaszWiśniewski'
        );

        // Projekty
        $website = $this->createProject(
            $manager,
            'Strona internetowa',
            'Rozwój i utrzymanie strony internetowej.'
        );

        $crm = $this->createProject(
            $manager,
            'System CRM',
            'Rozwój systemu do zarządzania klientami.'
        );

        $mobile = $this->createProject(
            $manager,
            'Aplikacja mobilna',
            'Prace nad aplikacją mobilną.'
        );

        $testing = $this->createProject(
            $manager,
            'Testy i jakość',
            'Testowanie aplikacji i poprawa jakości.'
        );

        $documentation = $this->createProject(
            $manager,
            'Dokumentacja',
            'Tworzenie i aktualizacja dokumentacji projektu.'
        );

        $manager->flush();

        // Wpisy Jana
        $this->createTimeEntry(
            $manager,
            $jan,
            $website,
            '2026-09-01 08:00',
            '2026-09-01 12:00',
            'Prace nad stroną główną.'
        );

        $this->createTimeEntry(
            $manager,
            $jan,
            $crm,
            '2026-09-02 09:00',
            '2026-09-02 13:30',
            'Implementacja modułu klientów.'
        );

        $this->createTimeEntry(
            $manager,
            $jan,
            $website,
            '2026-09-03 08:30',
            '2026-09-03 15:00',
            'Poprawki formularza kontaktowego.'
        );

        $this->createTimeEntry(
            $manager,
            $jan,
            $testing,
            '2026-09-04 10:00',
            '2026-09-04 12:00',
            'Testy formularzy.'
        );

        // Wpisy Marii
        $this->createTimeEntry(
            $manager,
            $maria,
            $mobile,
            '2026-09-01 08:30',
            '2026-09-01 14:30',
            'Praca nad ekranem logowania.'
        );

        $this->createTimeEntry(
            $manager,
            $maria,
            $mobile,
            '2026-09-02 09:00',
            '2026-09-02 12:00',
            'Implementacja profilu użytkownika.'
        );

        $this->createTimeEntry(
            $manager,
            $maria,
            $testing,
            '2026-09-03 08:00',
            '2026-09-03 11:30',
            'Testy aplikacji mobilnej.'
        );

        $this->createTimeEntry(
            $manager,
            $maria,
            $documentation,
            '2026-09-04 10:00',
            '2026-09-04 13:00',
            'Aktualizacja dokumentacji.'
        );

        // Wpisy Tomasza
        $this->createTimeEntry(
            $manager,
            $tomasz,
            $crm,
            '2026-09-01 09:00',
            '2026-09-01 16:00',
            'Rozwój modułu raportów.'
        );

        $this->createTimeEntry(
            $manager,
            $tomasz,
            $testing,
            '2026-09-02 08:00',
            '2026-09-02 12:30',
            'Testy systemu CRM.'
        );

        $this->createTimeEntry(
            $manager,
            $tomasz,
            $documentation,
            '2026-09-03 10:00',
            '2026-09-03 14:00',
            'Przygotowanie dokumentacji technicznej.'
        );

        $this->createTimeEntry(
            $manager,
            $tomasz,
            $crm,
            '2026-09-04 08:30',
            '2026-09-04 15:30',
            'Poprawki i optymalizacja systemu.'
        );

        $manager->flush();
    }

    private function createUser(
        ObjectManager $manager,
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        string $plainPassword
    ): User {
        $user = new User();

        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles([$role]);
        $user->setIsActive(true);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );

        $manager->persist($user);

        return $user;
    }

    private function createProject(
        ObjectManager $manager,
        string $name,
        ?string $description = null
    ): Project {
        $project = new Project();

        $project->setName($name);
        $project->setDescription($description);
        $project->setIsActive(true);

        $manager->persist($project);

        return $project;
    }

    private function createTimeEntry(
        ObjectManager $manager,
        User $employee,
        Project $project,
        string $startAt,
        string $endAt,
        ?string $comment = null
    ): TimeEntry {
        $timeEntry = new TimeEntry();

        $timeEntry->setEmployee($employee);
        $timeEntry->setProject($project);
        $timeEntry->setStartAt(new \DateTime($startAt));
        $timeEntry->setEndAt(new \DateTime($endAt));
        $timeEntry->setComment($comment);

        $manager->persist($timeEntry);

        return $timeEntry;
    }
}
