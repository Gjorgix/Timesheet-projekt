<?php

namespace App\Model;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserModel extends BaseModel
{
    public function getUserList(): array {
        $users = $this->getEm()->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);

        return [
            'users' => $users
        ];
    }

    public function newUser(ParameterBag $data, UserPasswordHasherInterface $passwordHasher): void
    {
        $user = new User();
        $user->setEmail($data->get('user_email'));
        $user->setFirstName($data->get('user_firstName'));
        $user->setLastName($data->get('user_lastName'));
        $user->setIsActive($data->has('user_isActive'));

        $plainPassword = $data->get('user_plainPassword');
        $user->setPassword(
            $passwordHasher->hashPassword($user, $plainPassword)
        );

        $this->getEm()->persist($user);
        $this->getEm()->flush();
    }

    public function editUser(User $user, ParameterBag $data, UserPasswordHasherInterface $passwordHasher): void
    {
        $user->setEmail($data->get('user_email'));
        $user->setFirstName($data->get('user_firstName'));
        $user->setLastName($data->get('user_lastName'));
        $user->setIsActive($data->has('user_isActive'));

        $plainPassword = $data->get('user_plainPassword');
        $user->setPassword(
            $passwordHasher->hashPassword($user, $plainPassword)
        );

        $this->getEm()->flush();
    }

    public function activateUser(User $user): void
    {
        $user->setIsActive(true);
        $this->getEm()->flush();
    }
    public function deactivateUser(User $user): void
    {
        $user->setIsActive(false);
        $this->getEm()->flush();
    }

}
