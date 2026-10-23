<?php

namespace App\Model;

use App\Entity\Project;
use App\Entity\User;
use DateTime;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\ParameterBag;

class AdminModel extends BaseModel
{
    public function getReportData(): array
    {
        return [
            'projects'=>$this->getEm()->getRepository(Project::class)->findAll(),
            'employeesList' => $this->getEm()->getRepository(User::class)->findBy(
                ['isActive' => true],
                ['lastName' => 'ASC', 'firstName' => 'ASC'],
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function generateReport(array $filters): array
    {
        $employee = $this->getEm()->getRepository(User::class)->find($filters['employee_id']);

        if (!$employee instanceof User) {
            return [
                'selected_employee' => null,
                'time_entries' => [],
                'project_totals' => [],
            ];
        }

        return [
            'selected_employee' => [
                'employee_id' => $employee->id,
                'firstName' => $employee->firstName,
                'lastName' => $employee->lastName,
            ],
            'time_entries' => $this->getEntriesList($filters, $employee),
            'project_totals' => $this->getProjectTotals($filters, $employee),
        ];
    }

    /**
     * @throws Exception
     */
    public function getEntriesList(Array $filters, User $employee): array
    {

        $parameters = ['employee' => $employee->id];
        $parameters['from'] = $filters['from'];
        $parameters['to'] = $filters['to'];
        $parameters['project'] = $filters['project'];


        $sql = 'SELECT
                to_char(t.start_at, \'YYYY-MM-DD HH24:MI\') AS start_at,
                to_char(t.end_at, \'YYYY-MM-DD HH24:MI\') AS end_at,
                p.name AS project,
                FLOOR(EXTRACT(EPOCH FROM (t.end_at - t.start_at)) / 60)::int AS duration,
                t.comment AS comment
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

    /**
     * @throws Exception
     */
    public function getProjectTotals(Array $filters, User $employee): array
    {
        $parameters = ['employee' => $employee->id];
        $parameters['from'] = $filters['from'];
        $parameters['to'] = $filters['to'];

        $sql =
            'WITH filtered_entries AS (
                SELECT t.id, t.project_id, t.start_at, t.end_at
                FROM time_entry t
                WHERE t.employee_id = :employee AND
                t.start_at BETWEEN :from AND :to
            )
            SELECT
                p.name AS project,
                SUM(FLOOR(EXTRACT(EPOCH FROM (t.end_at - t.start_at)) / 60)) AS minutes
            FROM filtered_entries t
            INNER JOIN project p ON p.id = t.project_id
            GROUP BY p.id, p.name
            ORDER BY p.name ASC';

        return $this->getEm()->getConnection()->executeQuery($sql, $parameters)->fetchAllAssociative();
    }

    public function getFilters(ParameterBag $data): array
    {

        return [
            'employee_id' => $data->getInt('employee_id'),
            'from' => $data->getString('from', new DateTime('-1 days')->format('Y-m-d')),
            'to' => $data->getString('to', new DateTime()->format('Y-m-d')),
            'project' => $data->getInt('project')
        ];
    }
}
