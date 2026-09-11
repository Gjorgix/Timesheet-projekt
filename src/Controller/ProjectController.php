<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class ProjectController extends AbstractController
{
    #[Route('/admin/projects', name: 'app_project_index')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/admin/projects/new', name: 'app_project_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response{
        $project = new Project();

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('project/new.html.twig', ['form' => $form]);
    }

    #[Route('/admin/projects/{id}/edit', name: 'app_project_edit')]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager):
        Response{
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $entityManager->flush();
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/admin/projects/{id}/deactivate', name: 'app_project_deactivate', methods: ['POST'])]
    public function deactivate(Project $project, EntityManagerInterface $entityManager) : Response{
        $project->setIsActive(false);
        $entityManager->flush();
        return $this->redirectToRoute('app_project_index');

    }

    #[Route('/admin/projects/{id}/activate', name: 'app_project_activate', methods: ['POST'])]
    public function activate(Project $project, EntityManagerInterface $entityManager): Response
    {
        $project->setIsActive(true);
        $entityManager->flush();

        return $this->redirectToRoute('app_project_index');
    }
}
