<?php

namespace App\Controller;

use App\Model\UserModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    #[Route('/settings', name: 'app_user_settings', methods: ['GET', 'POST'])]
    public function settings(): Response
    {

        return $this->render('user/settings.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/settings/save', name: 'app_user_settings_save', methods: ['POST'])]
    public function save(Request $request, UserModel $userModel, UserPasswordHasherInterface $passwordHasher): Response{
        $userModel->saveUser($request->request, $passwordHasher);
        return $this->redirectToRoute('app_home');

    }
}
