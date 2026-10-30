<?php

namespace App\Model;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\ParameterBag;
use Throwable;


class ProjectModel extends BaseModel
{
    public function getProjectList(): array {
        $projectList = $this->getEm()->getRepository(Project::class)->findBy([], ['createdAt' => 'DESC']);

        return [
            'projectList' => $projectList
        ];
    }

    public function save(ParameterBag $data): void {
        $id = $data->getInt('id');
        try {
            $this->beginTransaction();
            $project = $this->getEm()->getRepository(Project::class)->find($id);

            if (!$project) {
                $project = new Project(); //Unity of work //Poczytać o transakcjach
                $this->getEm()->persist($project);
            }

            $project->name = $data->getString("project_name");
            $project->description = $data->getString("project_description");
            $project->isActive = $data->getBoolean("project_isActive");

            $this->getEm()->flush();
            $this->commit();
        } catch (Throwable $exception) {
            $this->rollback();
            throw $exception;
        }

    }

    public function changeActive(Project $project, bool $active) :void
    {
        $project->isActive = $active;
        $this->getEm()->flush();
    }

}
