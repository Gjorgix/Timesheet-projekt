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

#[IsGranted("ROLE_USER")]
final class TimeEntryController extends AbstractController
{
    #[Route('/time-entries/new', name: 'app_time_entry_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $timeEntry = new TimeEntry();

        $now = new \DateTime();
        $timeEntry->setStartAt($now);
        $timeEntry->setEndAt($now);

        $form = $this->createForm(TimeEntryType::class, $timeEntry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timeEntry->setEmployee($this->getUser());

            $startAt = $form->get('startAt')->getData();
            $endAt = $form->get('endAt')->getData();
            if ($endAt <= $startAt) {
                $form->get('endAt')->addError(
                    new FormError('Data końca musi być późniejsza niż data rozpoczęcia.')
                );
            } else {
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
    public function index(EntityManagerInterface $entityManager): Response{
        $user = $this->getUser();

        $timeEntries = $entityManager->getRepository(TimeEntry::class)->findBy(
            ['employee' => $user],
            ['startAt' => 'DESC']
        );

        return $this->render('time_entry/index.html.twig', [
            'time_entries' => $timeEntries,
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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startAt = $form->get('startAt')->getData();
            $endAt = $form->get('endAt')->getData();
            if ($endAt <= $startAt) {
                $form->get('endAt')->addError(new FormError('Data końca musi być po dacie zaczęcia'));
            }else {
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
