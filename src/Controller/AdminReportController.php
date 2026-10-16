<?php

namespace App\Controller;

use App\Model\AdminReportModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_ADMIN')]
class AdminReportController extends AbstractController
{
    #[Route('/admin/report', name: 'app_admin_report')]
    public function report(AdminReportModel $adminReportModel): Response {
        $data = $adminReportModel->getDataToReport();
        return $this->render('admin_report/report.html.twig', $data);
    }


    #[Route('/admin/report/ajax', name: 'app_admin_report_ajax', methods: ["POST"])]
    public function reportAjax(Request $request, AdminReportModel $adminReportModel): Response {
        $data = $adminReportModel->generateReport($request->request);
        return new JsonResponse($data, Response::HTTP_OK);
    }
}
