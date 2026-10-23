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
    public function new(TimeEntryModel $timeEntryModel): Response{
        return $this->render('time_entry/new.html.twig', $timeEntryModel->getNewData());
    }
    #[Route('/time-entries/save', name: 'app_time_entry_save', methods: ['POST'])]
    public function save(Request $request, TimeEntryModel $timeEntryModel): Response{

        if (!$timeEntryModel->saveTimeEntry($request->request, $this->getUser())) {
            $this->addFlash('error', 'Data i godzina rozpoczęcia muszą być wcześniejsze niż zakończenia.');
        }

        return $this->redirectToRoute('app_time_entry_index');

    }
    #[Route('/time-entries/{id}/edit', name: 'app_time_entry_edit')]
    public function edit(TimeEntry $timeEntry, TimeEntryModel $timeEntryModel): Response {
        return $this->render('time_entry/edit.html.twig',$timeEntryModel->getEditData($timeEntry));
    }

    #[Route('/time-entries', name: 'app_time_entry_index')]
    public function index(TimeEntryModel $timeEntryModel): Response{
        return $this->render('time_entry/index.html.twig', $timeEntryModel->getIndexData());
    }

    #[Route('/time-entries/ajax', name: 'app_time_entry_index_ajax', methods: ['POST'])]
    public function reportAjax(Request $request, TimeEntryModel $timeEntryModel): Response {
        $data = $timeEntryModel->getTimeEntries($request->request,  $this->getUser());
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/time-entries/{id}/delete', name: 'app_time_entry_delete', methods: ['POST'])]
    public function delete(TimeEntry $timeEntry, EntityManagerInterface $entityManager): Response {

        $entityManager->remove($timeEntry);
        $entityManager->flush();

        return $this->redirectToRoute('app_time_entry_index');
    }


}
