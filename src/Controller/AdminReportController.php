<?php

namespace App\Controller;

use App\Model\AdminReportModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
#[IsGranted('ROLE_ADMIN')]
class AdminReportController extends AbstractController
{
    #[Route('/admin/report', name: 'app_admin_report')]
    public function index(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        AdminReportModel $adminReportModel
    ): Response {


        $dataArray = $adminReportModel->generateReport($request, $userRepository, $entityManager);

        return $this->render('admin_report/index.html.twig', $dataArray);
    }
}
