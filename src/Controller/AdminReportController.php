<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\TimeEntry;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\TimeEntryFilterType;
#[IsGranted('ROLE_ADMIN')]
class AdminReportController extends AbstractController
{
    #[Route('/admin/report', name: 'app_admin_report')]
    public function index(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $employees = $userRepository->findBy(
            ['isActive' => true],
            ['lastName' => 'ASC', 'firstName' => 'ASC']
        );

        $filterForm = $this->createForm(TimeEntryFilterType::class);
        $filterForm->handleRequest($request);

        $employeeId = $request->query->get('employee');

        $from = $filterForm->get('from')->getData();
        $to = $filterForm->get('to')->getData();
        $limit = $filterForm->get('limit')->getData();

        $selectedEmployee = null;

        if ($employeeId !== null) {
            $selectedEmployee = $userRepository->find($employeeId);
        }

        $timeEntries = [];

        if ($selectedEmployee !== null) {
            $queryBuilder = $entityManager
                ->getRepository(TimeEntry::class)
                ->createQueryBuilder('t')
                ->where('t.employee = :employee')
                ->setParameter('employee', $selectedEmployee)
                ->orderBy('t.startAt', 'ASC');

            if ($from !== null) {
                $from->setTime(0, 0, 0);

                $queryBuilder
                    ->andWhere('t.startAt >= :from')
                    ->setParameter('from', $from);
            }

            if ($to !== null) {
                $to->setTime(23, 59, 59);

                $queryBuilder
                    ->andWhere('t.startAt <= :to')
                    ->setParameter('to', $to);
            }

            if ($limit !== null) {
                $queryBuilder
                    ->setMaxResults($limit);
            }

            $timeEntries = $queryBuilder
                ->getQuery()
                ->getResult();
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

        return $this->render('admin_report/index.html.twig', [
            'employees' => $employees,
            'employee_id' => $employeeId,
            'selected_employee' => $selectedEmployee,
            'time_entries' => $timeEntries,
            'filter_form' => $filterForm,
            'project_totals' => $projectTotals,
        ]);
    }
}
