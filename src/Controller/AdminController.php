<?php

namespace App\Controller;

use App\Model\AdminModel;
use Doctrine\DBAL\Exception;
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
        return $this->render('admin/report.html.twig', $adminReportModel->getReportData());
    }


    /**
     * @throws Exception
     */
    #[Route('/report/ajax', name: 'app_admin_report_ajax', methods: ["POST"])]
    public function reportAjax(Request $request, AdminModel $adminReportModel): Response {
        $filters = $adminReportModel->getFilters($request->request);
        $data = $adminReportModel->generateReport($filters);
        return new JsonResponse($data, Response::HTTP_OK);
    }

}
