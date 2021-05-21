<?php

namespace App\Service;


use App\Repository\UserRepository;

class UserService
{
    // Propriétés
    private UserRepository $userRepo;

    // Constructeur
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function sortUsers($id)
    {
        $users = $this->userRepo->findBy([], ['lastName' => 'ASC']);
        switch ($id) {
            case 1:
                $users = $this->userRepo->findBy([], ['lastName' => 'DESC']);
                break;
            case 2:
                $users = $this->userRepo->findBy([], ['id' => 'ASC']);
                break;
            case 3:
                $users = $this->userRepo->findBy([], ['createdAt' => 'DESC']);
                break;
            case 4:
                $users = $this->userRepo->findBy([], ['createdAt' => 'ASC']);
                break;
        }

        return $users;

    }


}