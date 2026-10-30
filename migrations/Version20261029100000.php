<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261029100000 extends AbstractMigration
{
    private const CREATED_AT = '2026-10-29 00:00:00';

    public function getDescription(): string
    {
        return 'Import demo users, projects, and time entries';
    }

    public function up(Schema $schema): void
    {
        $users = [];
        foreach ([
                     ['anna.nowak@example.com', 'Anna', 'Nowak', 'ROLE_ADMIN', 'AnnaNowak'],
                     ['piotr.kowalski@example.com', 'Piotr', 'Kowalski', 'ROLE_ADMIN', 'PiotrKowalski'],
                     ['jan.kowalski@example.com', 'Jan', 'Kowalski', 'ROLE_USER', 'JanKowalski'],
                     ['maria.nowak@example.com', 'Maria', 'Nowak', 'ROLE_USER', 'MariaNowak'],
                     ['tomasz.wisniewski@example.com', 'Tomasz', 'Wiśniewski', 'ROLE_USER', 'TomaszWiśniewski'],
                 ] as [$email, $firstName, $lastName, $role, $plainPassword]) {
            $users[$email] = (int) $this->connection->fetchOne(
                'INSERT INTO "user"
                    (email, roles, password, first_name, last_name, is_active, created_at, updated_at)
                 VALUES
                    (:email, CAST(:roles AS JSON), :password, :first_name, :last_name, :is_active, :created_at, :updated_at)
                 RETURNING id',
                [
                    'email' => $email,
                    'roles' => json_encode([$role], JSON_THROW_ON_ERROR),
                    'password' => password_hash($plainPassword, PASSWORD_DEFAULT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'is_active' => true,
                    'created_at' => self::CREATED_AT,
                    'updated_at' => self::CREATED_AT,
                ],
            );
        }

        $projects = [];
        foreach ([
                     ['website', 'Strona internetowa', 'Rozwój i utrzymanie strony internetowej.'],
                     ['crm', 'System CRM', 'Rozwój systemu do zarządzania klientami.'],
                     ['mobile', 'Aplikacja mobilna', 'Prace nad aplikacją mobilną.'],
                     ['testing', 'Testy i jakość', 'Testowanie aplikacji i poprawa jakości.'],
                     ['documentation', 'Dokumentacja', 'Tworzenie i aktualizacja dokumentacji projektu.'],
                 ] as [$key, $name, $description]) {
            $projects[$key] = (int) $this->connection->fetchOne(
                'INSERT INTO project (name, description, is_active, created_at, updated_at)
                 VALUES (:name, :description, :is_active, :created_at, :updated_at)
                 RETURNING id',
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                    'created_at' => self::CREATED_AT,
                    'updated_at' => self::CREATED_AT,
                ],
            );
        }

        foreach ([
                     ['jan.kowalski@example.com', 'website', '2026-09-01 08:00', '2026-09-01 12:00', 'Prace nad stroną główną.'],
                     ['jan.kowalski@example.com', 'crm', '2026-09-02 09:00', '2026-09-02 13:30', 'Implementacja modułu klientów.'],
                     ['jan.kowalski@example.com', 'website', '2026-09-03 08:30', '2026-09-03 15:00', 'Poprawki formularza kontaktowego.'],
                     ['jan.kowalski@example.com', 'testing', '2026-09-04 10:00', '2026-09-04 12:00', 'Testy formularzy.'],

                     ['maria.nowak@example.com', 'mobile', '2026-09-01 08:30', '2026-09-01 14:30', 'Praca nad ekranem logowania.'],
                     ['maria.nowak@example.com', 'mobile', '2026-09-02 09:00', '2026-09-02 12:00', 'Implementacja profilu użytkownika.'],
                     ['maria.nowak@example.com', 'testing', '2026-09-03 08:00', '2026-09-03 11:30', 'Testy aplikacji mobilnej.'],
                     ['maria.nowak@example.com', 'documentation', '2026-09-04 10:00', '2026-09-04 13:00', 'Aktualizacja dokumentacji.'],

                     ['tomasz.wisniewski@example.com', 'crm', '2026-09-01 09:00', '2026-09-01 16:00', 'Rozwój modułu raportów.'],
                     ['tomasz.wisniewski@example.com', 'testing', '2026-09-02 08:00', '2026-09-02 12:30', 'Testy systemu CRM.'],
                     ['tomasz.wisniewski@example.com', 'documentation', '2026-09-03 10:00', '2026-09-03 14:00', 'Przygotowanie dokumentacji technicznej.'],
                     ['tomasz.wisniewski@example.com', 'crm', '2026-09-04 08:30', '2026-09-04 15:30', 'Poprawki i optymalizacja systemu.'],
                 ] as [$email, $project, $startAt, $endAt, $comment]) {
            $this->connection->insert('time_entry', [
                'employee_id' => $users[$email],
                'project_id' => $projects[$project],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'comment' => $comment,
                'created_at' => self::CREATED_AT,
                'updated_at' => self::CREATED_AT,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $emails = [
            'anna.nowak@example.com',
            'piotr.kowalski@example.com',
            'jan.kowalski@example.com',
            'maria.nowak@example.com',
            'tomasz.wisniewski@example.com',
        ];

        $projectNames = [
            'Strona internetowa',
            'System CRM',
            'Aplikacja mobilna',
            'Testy i jakość',
            'Dokumentacja',
        ];

        $this->connection->executeStatement(
            'DELETE FROM time_entry
             WHERE employee_id IN (SELECT id FROM "user" WHERE email IN (?))',
            [$emails],
            [\Doctrine\DBAL\ArrayParameterType::STRING],
        );

        $this->connection->executeStatement(
            'DELETE FROM project
             WHERE name IN (?)
             AND NOT EXISTS (SELECT 1 FROM time_entry WHERE time_entry.project_id = project.id)',
            [$projectNames],
            [\Doctrine\DBAL\ArrayParameterType::STRING],
        );

        $this->connection->executeStatement(
            'DELETE FROM "user" WHERE email IN (?)',
            [$emails],
            [\Doctrine\DBAL\ArrayParameterType::STRING],
        );
    }
}
