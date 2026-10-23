<?php

namespace App\Controller;

use App\Entity\Project;
use App\Model\ProjectModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'app_project_list')]
    public function list(ProjectModel $projectModel): Response
    {
        $data = $projectModel->getProjectList();
        return $this->render('project/index.html.twig',$data);
    }

    #[Route('/projects/new', name: 'app_project_new')]
    public function new(): Response{
        return $this->render('project/new.html.twig');
    }

    #[Route('/projects/{id}/edit', name: 'app_project_edit')]
    public function edit(Project $project): Response{
        return $this->render('project/edit.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/project/save/', name: 'app_project_save')]
    public function save(Request $request, ProjectModel $projectModel): Response
    {
        $projectModel->save($request->request);
        return $this->redirectToRoute('app_project_list');
    }

    #[Route('/projects/{id}/deactivate', name: 'app_project_deactivate', methods: ['POST'])]
    public function deactivate(Project $project, ProjectModel $projectModel) : Response{
        $projectModel->changeActive($project, false);

        return $this->redirectToRoute('app_project_list');

    }

    #[Route('/projects/{id}/activate', name: 'app_project_activate', methods: ['POST'])]
    public function activate(Project $project, ProjectModel $projectModel): Response
    {
        $projectModel->changeActive($project, true);
        return $this->redirectToRoute('app_project_list');
    }
}
