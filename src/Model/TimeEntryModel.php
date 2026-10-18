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
    public function editTimeEntry(ParameterBag $data, TimeEntry $timeEntry){

        $timeEntry.setStartAt();

        $form = $this->createForm(TimeEntryType::class, $timeEntry);

        $form->get('startDate')->setData($timeEntry->getStartAt());
        $form->get('startTime')->setData(
            $timeEntry->getStartAt()->format('H:i')
        );

        $form->get('endDate')->setData($timeEntry->getEndAt());
        $form->get('endTime')->setData(
            $timeEntry->getEndAt()->format('H:i')
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData();
            $startTime = $form->get('startTime')->getData();

            $endDate = $form->get('endDate')->getData();
            $endTime = $form->get('endTime')->getData();

            $startAt = \DateTime::createFromFormat(
                'Y-m-d H:i',
                $startDate->format('Y-m-d') . ' ' . $startTime
            );

            $endAt = \DateTime::createFromFormat(
                'Y-m-d H:i',
                $endDate->format('Y-m-d') . ' ' . $endTime
            );

            if ($startAt === false) {
                $form->get('startTime')->addError(
                    new FormError('Godzina musi być podana w formacie HH:MM.')
                );
            }elseif($endAt === false){
                $form->get('endTime')->addError(
                    new FormError('Godzina musi być podana w formacie HH:MM.')
                );
            }elseif ($endAt <= $startAt) {
                $form->get('endTime')->addError(
                    new FormError('Data i godzina końca musi być późniejsza niż rozpoczęcia.')
                );
            } else {
                $timeEntry->setStartAt($startAt);
                $timeEntry->setEndAt($endAt);

                $entityManager->flush();

                return $this->redirectToRoute('app_time_entry_index');
            }
        }
    }
}
