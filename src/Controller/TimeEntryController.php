<?php

namespace App\Controller;

use App\Entity\TimeEntry;
use App\Model\TimeEntryModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class TimeEntryController extends AbstractController
{
    #[Route('/time-entries/new', name: 'app_time_entry_new')]
    public function new(TimeEntryModel $timeEntryModel): Response
    {
        return $this->render('time_entry/new.html.twig', $timeEntryModel->getNewData());
    }

    #[Route('/time-entries/new/create', name: 'app_time_entry_new_create', methods: ['POST'])]
    public function newCreate(Request $request, TimeEntryModel $timeEntryModel): Response
    {
        if (!$timeEntryModel->newTimeEntry($request->request, $this->getUser())) {
            $this->addFlash('error', 'Data i godzina rozpoczęcia muszą być wcześniejsze niż zakończenia.');

            return $this->redirectToRoute('app_time_entry_new');
        }

        return $this->redirectToRoute('app_time_entry_index');
    }

    #[Route('/time-entries', name: 'app_time_entry_index')]
    public function index(): Response{
        return $this->render('time_entry/index.html.twig');
    }

    #[Route('/time-entries/ajax', name: 'app_time_entry_index_ajax', methods: ['POST'])]
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
        if (!$timeEntriesModel->editTimeEntry($request->request, $timeEntry)) {
            $this->addFlash('error', 'Data i godzina rozpoczęcia muszą być wcześniejsze niż zakończenia.');

            return $this->redirectToRoute('app_time_entry_edit', ['id' => $timeEntry->getId()]);
        }

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
