<?php

namespace App\Service;


use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @param $users
     * @return array
     */
    private function extractUsers($users)
    {
        $roles = null;
        $usersTab = [];
        foreach ($users as $user) {
            if ($user) {
                foreach ($user as $item) {
                    array_push($usersTab, $item);
                }
            }
        }
        return $usersTab;
    }



    /**
     * @param $id
     * @param $users
     * @return mixed
     */
    public function sortUsers($id, $users)
    {


        $users = $this->extractUsers($users);
        return $users;

    }

    /**
     * @param $request
     * @return mixed
     */
    public function whichRoles($request, $id)
    {
        if ($request->request->get('usersCheck')) {
            $Users = $this->userRepo->findByRoles('ROLE_USER', $id);
        } else {
            $Users = [];
        }
        if ($request->request->get('contributorsCheck')) {
            $Contributors = $this->userRepo->findByRoles('ROLE_CONTRIBUTOR', $id);
        } else {
            $Contributors = [];
        }
        if ($request->request->get('adminsCheck')) {
            $Admins = $this->userRepo->findByRoles('ROLE_ADMIN', $id);
        } else {
            $Admins = [];
        }
        if ($request->request->get('superAdminsCheck')) {
            $SupAdmins = $this->userRepo->findByRoles('ROLE_SUPER_ADMIN', $id);
        } else {
            $SupAdmins = [];
        }

        $users = [$Users, $Contributors, $Admins, $SupAdmins];

        return $users;

    }


}