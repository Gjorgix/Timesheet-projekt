<?php

namespace App\Model;

use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class TimeEntryModel extends BaseModel
{
    public function getTimeEntries(ParameterBag $data, UserInterface $user): array
    {
        return [
            'time_entries' => $this->getEntriesList($data, $user),
            'project_totals' => $this->getProjectTotals($data, $user),
        ];
    }

    /**
     * @throws Exception
     */
    public function getProjectTotals(ParameterBag $data, UserInterface $user): array
    {
        $connection = $this->getEm()->getConnection();

        $parameters = ['employee' => $user->id];
        $parameters['from'] = $data->getString('from', new DateTime('-1 days')->format('Y-m-d'));
        $parameters['to'] = $data->getString('to', new DateTime()->format('Y-m-d'));
        $parameters['project'] = $data->getInt('project');


        $sql = 'WITH filtered_entries AS (
            SELECT t.id, t.project_id, t.start_at, t.end_at
            FROM time_entry t
            WHERE t.employee_id = :employee AND t.start_at BETWEEN :from AND :to
            AND (:project = 0 OR t.project_id = :project)
            )
            SELECT
            p.name AS project,
            SUM(FLOOR(EXTRACT(EPOCH FROM (t.end_at - t.start_at)) / 60)) AS minutes
            FROM filtered_entries t
            INNER JOIN project p ON p.id = t.project_id
            GROUP BY p.id, p.name
            ORDER BY p.name ASC';

        return $connection->executeQuery($sql, $parameters)->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function getEntriesList(ParameterBag $data, UserInterface $user): array
    {
        $parameters = ['employee' => $user->id];
        $parameters['from'] = $data->getString('from', new DateTime('-1 days')->format('Y-m-d'));
        $parameters['to'] = $data->getString('to', new DateTime()->format('Y-m-d'));
        $parameters['project'] = $data->getInt('project');



        $sql = 'SELECT
                to_char(t.start_at, \'YYYY-MM-DD HH24:MI\') AS start_at,
                to_char(t.end_at, \'YYYY-MM-DD HH24:MI\') AS end_at,
                p.name AS project,
                FLOOR(EXTRACT(EPOCH FROM (t.end_at - t.start_at)) / 60)::int AS duration,
                t.comment AS comment,
                t.id AS id
                FROM time_entry t
                INNER JOIN project p ON p.id = t.project_id
                WHERE t.employee_id = :employee AND
                t.start_at BETWEEN :from AND :to AND
                (:project = 0 OR t.project_id = :project)
                ORDER BY t.start_at ASC';

        return $this->getEm()
            ->getConnection()
            ->executeQuery($sql, $parameters)
            ->fetchAllAssociative();
    }

    public function getEditData(TimeEntry $timeEntry): array
    {
        $projects = $this->getEm()->getRepository(Project::class)->findAll();
        return ['projects' => $projects,
            'time_entry' => $timeEntry];
    }

    public function getIndexData(): array
    {
        return [
            'projects' => $this->getEm()->getRepository(Project::class)->findBy([], ['name' => 'ASC']),
        ];
    }

    public function getNewData(): array
    {
        return [
            'projects' => $this->getEm()->getRepository(Project::class)->findBy(
                ['isActive' => true],
                ['name' => 'ASC'],
            ),
        ];
    }

    /**
     * @throws Throwable
     */
    public function saveTimeEntry(ParameterBag $data, UserInterface $user): bool
    {
        $id = $data->getInt('id');
        $timeEntry = $this->getEm()->getRepository(TimeEntry::class)->find($id);

        $startAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $data->get('time_entryStartDate'). ' ' . $data->get('time_entryStartTime'));
        $endAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $data->get('time_entryEndDate'). ' ' . $data->get('time_entryEndTime'));
        if ($endAt <= $startAt) {
            return false;
        }

        $comment = $data->getString('time_entryComment');
        $project = $this->getEm()
            ->getRepository(Project::class)
            ->find($data->get('time_entryProject'));
        if (!$project instanceof Project) {
            throw new InvalidArgumentException('Projekt nie istnieje');
        }


        if (!$timeEntry) {
            $timeEntry = new TimeEntry();
            $isNew = true;
        }else{
            $isNew = false;
        }

        $this->beginTransaction();

        try {
            if ($isNew) {
                $timeEntry->employee = $user;
                $this->getEm()->persist($timeEntry);
            }

            $timeEntry->project = $project;
            $timeEntry->startAt = $startAt;
            $timeEntry->endAt = $endAt;
            $timeEntry->comment = $comment;

            $this->getEm()->flush();
            $this->commit();
        } catch (Throwable $exception) {
            $this->rollback();
            throw $exception;
        }

        return true;
    }

}
