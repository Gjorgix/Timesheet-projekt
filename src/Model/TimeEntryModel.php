<?php

namespace App\Model;

use App\Entity\Project;
use App\Entity\TimeEntry;
use DateTime;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\User\UserInterface;

class TimeEntryModel extends BaseModel
{


    public function getTimeEntries(ParameterBag $data, UserInterface $user): array
    {

        $from = $data->get("from");
        $to = $data->get("to");
        $limit = $data->get("limit");


        $queryBuilder = $this->getEm()
                ->getRepository(TimeEntry::class)
                ->createQueryBuilder('t')
                ->where('t.employee = :employee')
                ->setParameter('employee', $user)
                ->orderBy('t.startAt', 'ASC');

            if ($from) {
                $from = DateTime::createFromFormat('Y-m-d',$from)->setTime(0, 0, 0);

                $queryBuilder
                    ->andWhere('t.startAt >= :from')
                    ->setParameter('from', $from);
            }

            if ($to) {
                $to = DateTime::createFromFormat('Y-m-d',$to)->setTime(23, 59, 59);

                $queryBuilder
                    ->andWhere('t.startAt <= :to')
                    ->setParameter('to', $to);
            }

            if ($limit) {
                $queryBuilder
                    ->setMaxResults($limit);
            }

        $timeEntries = $queryBuilder
            ->getQuery()
            ->getResult();

        $projectTotals = [];

        foreach ($timeEntries as $timeEntry) {
            $projectName = $timeEntry->getProject()->getName();
            $duration = $timeEntry->getDurationInMinutes();

            if (!isset($projectTotals[$projectName])) {
                $projectTotals[$projectName] = 0;
            }

            $projectTotals[$projectName] += $duration;
        }

        return [

            'time_entries' => array_map(fn (TimeEntry $entry) => [
                'id' => $entry->getId(),
                'start_at' => $entry->getStartAt()?->format('Y-m-d H:i:s'),
                'end_at' => $entry->getEndAt()?->format('Y-m-d H:i:s'),
                'project' => $entry->getProject()->getName(),
                'duration' => $entry->getDurationInMinutes(),
                'comment' => $entry->getComment(),
            ], $timeEntries),
            'project_totals' => array_map(
                static fn (string $project, int $minutes) => [
                    'project' => $project,
                    'minutes' => $minutes,
                ],
                array_keys($projectTotals),
                array_values($projectTotals),
            ),
        ];
    }

    public function getEditData(TimeEntry $timeEntry): array
    {
        $projects = $this->getEm()->getRepository(Project::class)->findAll();
        return ['projects' => $projects,
            'time_entry' => $timeEntry];
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

    public function newTimeEntry(ParameterBag $data, UserInterface $user): bool
    {
        $startAt = $this->createDateTime(
            $data->get('time_entryStartDate'),
            $data->get('time_entryStartTime'),
        );
        $endAt = $this->createDateTime(
            $data->get('time_entryEndDate'),
            $data->get('time_entryEndTime'),
        );

        if ($endAt <= $startAt) {
            return false;
        }

        $project = $this->getEm()
            ->getRepository(Project::class)
            ->find($data->get('time_entryProject'));

        if (!$project instanceof Project || !$project->isActive()) {
            throw new \InvalidArgumentException('Projekt nie istnieje lub jest nieaktywny');
        }

        $timeEntry = new TimeEntry();
        $timeEntry->setProject($project);
        $timeEntry->setEmployee($user);
        $timeEntry->setStartAt($startAt);
        $timeEntry->setEndAt($endAt);
        $timeEntry->setComment($data->get('time_entryComment'));

        $this->getEm()->persist($timeEntry);
        $this->getEm()->flush();

        return true;
    }

    public function editTimeEntry(ParameterBag $data, TimeEntry $timeEntry): bool
    {
        $startAt = $this->createDateTime(
            $data->get('time_entryStartDate'),
            $data->get('time_entryStartTime'),
        );
        $endAt = $this->createDateTime(
            $data->get('time_entryEndDate'),
            $data->get('time_entryEndTime'),
        );

        if ($endAt <= $startAt) {
            return false;
        }

        $project = $this->getEm()
            ->getRepository(Project::class)
            ->find($data->get('time_entryProject'));

        if (!$project instanceof Project) {
            throw new \InvalidArgumentException('Projekt nie istnieje');
        }

        $timeEntry->setProject($project);
        $timeEntry->setStartAt($startAt);
        $timeEntry->setEndAt($endAt);
        $timeEntry->setComment($data->get('time_entryComment'));

        $this->getEm()->flush();

        return true;
    }

    private function createDateTime(mixed $date, mixed $time): DateTime
    {
        if (!is_string($date) || !is_string($time)) {
            throw new \InvalidArgumentException('Niepoprawny format daty');
        }

        $dateTime = DateTime::createFromFormat('!Y-m-d H:i', $date . ' ' . $time);
        $errors = DateTime::getLastErrors();

        if ($dateTime === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new \InvalidArgumentException('Niepoprawny format daty');
        }

        return $dateTime;
    }
}
