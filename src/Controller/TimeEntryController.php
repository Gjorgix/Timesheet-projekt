<?php

namespace App\Controller;

use App\Entity\TimeEntry;
use App\Form\TimeEntryType;
use App\Model\TimeEntryModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function index(): Response{
        return $this->render('time_entry/index.html.twig');
    }

    #[Route('/time-entries/ajax', name: 'app_time_entry_index_ajax')]
    public function reportAjax(Request $request, TimeEntryModel $timeEntriesModel): Response {
        $user = $this->getUser();
        $data = $timeEntriesModel->getTimeEntries($request->request, $user);
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/time-entries/{id}/edit', name: 'app_time_entry_edit')]
    public function edit(
        TimeEntry $timeEntry,
        TimeEntryModel $timeEntryModel
    ): Response {
        $data = $timeEntryModel->getEditData($timeEntry);
        return $this->render('time_entry/edit.html.twig',$data);
    }

    #[Route('/time-entries/{id}/edit/update', name: 'app_time_entry_edit_update', methods: ["POST"])]
    public function editUpdate(Request $request, TimeEntry $timeEntry, TimeEntryModel $timeEntriesModel): Response
    {
        $timeEntriesModel->editTimeEntry($request->request, $timeEntry);
        return $this->redirectToRoute("app_time_entry_index");
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
