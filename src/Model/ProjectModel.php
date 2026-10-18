<?php

namespace App\Model;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\ParameterBag;


class ProjectModel extends BaseModel
{
    public function getProjectList(): array {
        $projects = $this->getEm()->getRepository(Project::class)->findBy([], ['createdAt' => 'DESC']);

        return [
            'projects' => $projects
        ];
    }

    public function newProject(ParameterBag $data): void
    {
        $project_new = new Project();
        $project_new->setName($data->get('project_name'));
        $project_new->setDescription($data->get('project_description'));
        $project_new->setIsActive($data->has('project_isActive'));

        $this->getEm()->persist($project_new);
        $this->getEm()->flush();
    }

    public function editProject(Project $project, ParameterBag $data): void
    {
        $project->setName($data->get('project_name'));
        $project->setDescription($data->get('project_description'));
        $project->setIsActive($data->get('project_isActive'));
        $this->getEm()->flush();
    }

    public function activateProject(Project $project): void
    {
        $project->setIsActive(true);
        $this->getEm()->flush();
    }
    public function deactivateProject(Project $project): void
    {
        $project->setIsActive(false);
        $this->getEm()->flush();
    }

}
