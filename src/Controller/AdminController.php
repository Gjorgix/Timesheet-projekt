<?php

namespace App\Controller;

use App\Model\AdminModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_ADMIN')]
#[Route("/admin")]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/report', name: 'app_admin_report')]
    public function report(AdminModel $adminReportModel): Response {
        $data = $adminReportModel->getDataToReport();
        return $this->render('admin/report.html.twig', $data);
    }


    #[Route('/report/ajax', name: 'app_admin_report_ajax', methods: ["POST"])]
    public function reportAjax(Request $request, AdminModel $adminReportModel): Response {
        $data = $adminReportModel->generateReport($request->request);
        return new JsonResponse($data, Response::HTTP_OK);
    }

}
