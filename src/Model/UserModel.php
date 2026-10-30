<?php

namespace App\Model;

use App\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use InvalidArgumentException;
use Throwable;


class UserModel extends BaseModel
{
    public function getUserList(): array {
        $userList = $this->getEm()->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);

        return [
            'userList' => $userList
        ];
    }


    /**
     * @throws Throwable
     */
    public function saveUser(ParameterBag $data, UserPasswordHasherInterface $passwordHasher): void
    {
        $id = $data->getInt('id');
        try {
            $this->beginTransaction();
            $user = $this->getEm()->getRepository(User::class)->find($id);

            if (!$user) {
                if ($data->getString('user_plainPassword') === '') {
                    throw new InvalidArgumentException('A password is required when creating a user.');
                }
                $user = new User();
                $this->getEm()->persist($user);
            }

            $user->email = $data->getString('user_email');
            $user->firstName = $data->getString('user_firstName');
            $user->lastName = $data->getString('user_lastName');
            $user->isActive = $data->getBoolean('user_isActive');
            $user->roles = [$data->getString('user_role')];

            $plainPassword = $data->getString('user_plainPassword');
            if ($plainPassword !== '') {
                $user->password = $passwordHasher->hashPassword($user, $plainPassword);
            }

            $this->getEm()->flush();
            $this->commit();
        } catch (Throwable $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function changeActive(User $user, Bool $status): void
    {
        $user->isActive = $status;
        $this->getEm()->flush();
    }

}
