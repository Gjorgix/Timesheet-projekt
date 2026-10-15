<?php

namespace App\Model;

use App\Entity\TimeEntry;
use App\Form\TimeEntryFilterType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminReportModel
{
    public function generateReport(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager)
    {
        $employees = $userRepository->findBy(
            ['isActive' => true],
            ['lastName' => 'ASC', 'firstName' => 'ASC']
        );

        $employeeId = '';
        $from = '';
        $to = '';
        $limit = '';

        if ($request->isMethod('POST')) {
            $employeeId = $request->request->get('employee', '');

            $from = $request->request->get('from', '');
            $to = $request->request->get('to', '');
            $limit = $request->request->get('limit', '');
        }

        $selectedEmployee = null;

        if ($employeeId !== "") {
            $selectedEmployee = $userRepository->find($employeeId);
        }

        $timeEntries = [];

        if ($request->isMethod('POST') && $selectedEmployee !== null) {



            $queryBuilder = $entityManager
                ->getRepository(TimeEntry::class)
                ->createQueryBuilder('t')
                ->where('t.employee = :employee')
                ->setParameter('employee', $selectedEmployee)
                ->orderBy('t.startAt', 'ASC');

            if ($from !== "") {
                $from = DateTime::createFromFormat('Y-m-d',$from)->setTime(0, 0, 0);

                $queryBuilder
                    ->andWhere('t.startAt >= :from')
                    ->setParameter('from', $from);
            }

            if ($to !== "") {
                $to = DateTime::createFromFormat('Y-m-d',$to)->setTime(23, 59, 59);

                $queryBuilder
                    ->andWhere('t.startAt <= :to')
                    ->setParameter('to', $to);
            }

            if ($limit !== "") {
                $queryBuilder
                    ->setMaxResults($limit);
            }

            $timeEntries = $queryBuilder
                ->getQuery()
                ->getResult();
            dump(count($timeEntries));
            dump($timeEntries);
        }

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
            'employees' => $employees,
            'employee_id' => $employeeId,
            'selected_employee' => $selectedEmployee,
            'debug_employee' => $selectedEmployee,
            'time_entries' => $timeEntries,
            'project_totals' => $projectTotals,
        ];
    }
}
