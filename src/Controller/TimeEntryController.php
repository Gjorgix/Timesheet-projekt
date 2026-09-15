<?php

namespace App\Controller;

use App\Entity\TimeEntry;
use App\Form\TimeEntryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\TimeEntryFilterType;
#[IsGranted("ROLE_USER")]
final class TimeEntryController extends AbstractController
{
    #[Route('/time-entries/new', name: 'app_time_entry_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $timeEntry = new TimeEntry();

        $now = new \DateTime();

        $form = $this->createForm(TimeEntryType::class, $timeEntry);

        $form->get('startDate')->setData($now);
        $form->get('startTime')->setData($now->format('H:i'));
        $form->get('endDate')->setData($now);
        $form->get('endTime')->setData($now->format('H:i'));

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
                $timeEntry->setEmployee($this->getUser());

                $entityManager->persist($timeEntry);
                $entityManager->flush();

                return $this->redirectToRoute('app_time_entry_index');
            }
        }

        return $this->render('time_entry/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/time-entries', name: 'app_time_entry_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response{
        $user = $this->getUser();

        $form = $this->createForm(TimeEntryFilterType::class);
        $form->handleRequest($request);

        $from = $form->get('from')->getData();
        $to = $form->get('to')->getData();
        $limit = $form->get('limit')->getData();

        $queryBuilder = $entityManager
            ->getRepository(TimeEntry::class)
            ->createQueryBuilder('t')
            ->where('t.employee = :employee'
            )->setParameter('employee', $user)
            ->orderBy('t.startAt', 'ASC');
        if ($from != null){
            $from->setTime(0,0,0);

            $queryBuilder->andWhere('t.startAt >= :from')
                ->setParameter('from', $from);
        }
        if ($to != null){
            $to->setTime(23,59,59);

            $queryBuilder->andWhere('t.endAt <= :to')
                ->setParameter('to', $to);
        }
        if ($limit != null){
            $queryBuilder->setMaxResults($limit);
        }
        $timeEntries = $queryBuilder->getQuery()->getResult();


        $projectTotals = [];
        foreach ($timeEntries as $timeEntry) {
            $projectName = $timeEntry->getProject()->getName();
            $duration = $timeEntry->getDurationInMinutes();

            if (!isset($projectTotals[$projectName])) {
                $projectTotals[$projectName] = 0;
            }
            $projectTotals[$projectName] += $duration;
        }


        return $this->render('time_entry/index.html.twig', [
            'time_entries' => $timeEntries,
            'filter_form' => $form,
            'project_totals' => $projectTotals,
        ]);
    }

    #[Route('/time-entries/{id}/edit', name: 'app_time_entry_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        TimeEntry $timeEntry
    ): Response {
        if ($timeEntry->getEmployee() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

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

        return $this->render('time_entry/edit.html.twig', [
            'time_entry' => $timeEntry,
            'form' => $form,
        ]);
    }
    #[Route('/time-entries/{id}/delete', name: 'app_time_entry_delete', methods: ['POST'])]
    public function delete(
        TimeEntry $timeEntry,
        EntityManagerInterface $entityManager
    ): Response {
        if ($timeEntry->getEmployee() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($timeEntry);
        $entityManager->flush();

        return $this->redirectToRoute('app_time_entry_index');
    }


}
