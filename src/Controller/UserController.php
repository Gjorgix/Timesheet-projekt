<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Model\UserModel;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user_list')]
    public function list(UserModel $userModel): Response
    {
        $data = $userModel->getUserList();
        return $this->render('user/index.html.twig',$data);
    }

    #[Route('/users/new', name: 'app_user_new')]
    public function new(): Response{
        return $this->render('user/new.html.twig');
    }

    #[Route('/users/save', name: 'app_user_save', methods: ['POST'])]
    public function save(Request $request, UserModel $userModel, UserPasswordHasherInterface $passwordHasher): Response{
        $userModel->saveUser($request->request, $passwordHasher);
        return $this->redirectToRoute('app_user_list');

    }

    #[Route('/users/{id}/edit', name: 'app_user_edit')]
    public function edit(User $user): Response {
        return $this->render('user/edit.html.twig', ['user' => $user,]);
    }

    #[Route('/users/{id}/deactivate', name: 'app_user_deactivate')]
    public function deactivate(User $user, UserModel $userModel): Response {
        $userModel->changeActive($user, false);
        return $this->redirectToRoute('app_user_list');
    }

    #[Route('/users/{id}/activate', name: 'app_user_activate')]
    public function activate(User $user, UserModel $userModel): Response {
        $userModel->changeActive($user, true);
        return $this->redirectToRoute('app_user_list');
    }

}
