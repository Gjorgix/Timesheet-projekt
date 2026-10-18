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

    #[Route('/projects/new/create', name: 'app_project_new_create', methods: ['POST'])]
    public function newCreate(Request $request, ProjectModel $projectModel): Response{
        $projectModel->newProject($request->request);

        return $this->redirectToRoute('app_project_list');
    }

    #[Route('/projects/{id}/edit', name: 'app_project_edit')]
    public function edit(Project $project): Response{

        return $this->render('project/edit.html.twig', [
            'project' => $project,
        ]);
    }
    #[Route('/projects/{id}/edit/update', name: 'app_project_edit_update', methods: ['POST'])]
    public function editUpdate(Request $request, ProjectModel $projectModel,Project $project): Response{
        $projectModel->editProject($project,$request->request);
        return $this->redirectToRoute('app_project_list');
    }

    #[Route('/projects/{id}/deactivate', name: 'app_project_deactivate', methods: ['POST'])]
    public function deactivate(Project $project, ProjectModel $projectModel) : Response{
        $projectModel->deactivateProject($project);

        return $this->redirectToRoute('app_project_list');

    }

    #[Route('/projects/{id}/activate', name: 'app_project_activate', methods: ['POST'])]
    public function activate(Project $project, ProjectModel $projectModel): Response
    {
        $projectModel->activateProject($project);
        return $this->redirectToRoute('app_project_list');
    }
}
