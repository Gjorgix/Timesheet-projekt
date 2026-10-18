<?php

namespace App\Model;

use App\Entity\TimeEntry;
use App\Entity\User;
use App\Form\TimeEntryFilterType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class AdminModel extends BaseModel
{
    public function getDataToReport(): array {
        $employeesList = $this->getEm()->getRepository(User::class)->findBy(['isActive' => true], ['lastName' => 'ASC', 'firstName' => 'ASC']);

        return [
            'employeesList' => $employeesList,
        ];
    }

    public function generateReport(ParameterBag $data): array
    {
        $employeeId = $data->getInt('employee_id');
        $from = $data->get("from");
        $to = $data->get("to");
        $limit = $data->get("limit");

        $selectedEmployee = $this->getEm()->getRepository(User::class)->find($employeeId);

        $queryBuilder = $this->getEm()
                ->getRepository(TimeEntry::class)
                ->createQueryBuilder('t')
                ->where('t.employee = :employee')
                ->setParameter('employee', $selectedEmployee)
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

            'selected_employee' => [
                'employee_id' => $employeeId,
                'firstName' => $selectedEmployee->getFirstName(),
                'lastName' => $selectedEmployee->getLastName(),
            ],
            'time_entries' => array_map(fn (TimeEntry $entry) => [
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

}
