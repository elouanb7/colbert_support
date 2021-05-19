<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;
use App\Repository\PanneRepository;
use App\Repository\UserRepository;

class UserController extends AbstractController
{

    private CategorieRepository $categorieRepo;
    private PanneRepository $panneRepo;
    private UserRepository $userRepo;

    public function __construct(CategorieRepository $categorieRepo, PanneRepository $panneRepo, UserRepository $userRepo)
    {
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/profil/user/{id}", name="profil")
     * @param $id
     * @return Response
     */
    public function profile($id): Response
    {
        $categories = $this->categorieRepo->findAll();
        $user = $this->userRepo->findOneBy(['id' => $id]);
        return $this->render('user/profil.html.twig', [
            'controller_name' => 'UserController',
            'categories' => $categories,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profil/dashboard/users", name="users")
     * @return Response
     */
    public function users(): Response
    {
        $categories = $this->categorieRepo->findAll();
        $users = $this->userRepo->findAll();
        return $this->render('user/users.html.twig', [
            'controller_name' => 'UserController',
            'categories' => $categories,
            'users' => $users,
        ]);
    }


    /**
     * @Route("/dashboard", name="dashboard")
     * @return Response
     */
    public function dashboard(): Response
    {
        $users = $this->userRepo->findAll();
        $nbUsers = count($users);
        $categories = $this->categorieRepo->findAll();
        return $this->render('user/dashboard.html.twig',[
            'users' => $users,
            'lastUser' => $this->userRepo->findBy([],['createdAt' => "DESC"], 1),
            'lastAdmin' => $this->userRepo->findLastByRole('ROLE_ADMIN'),
            'lastContributor' => $this->userRepo->findLastByRole('ROLE_CONTRIBUTOR'),
            'categories' => $categories,
            'nbUsers' => $nbUsers,
            'tickets' => $this->panneRepo->findBy(['isTicket' => true])
        ]);
    }
}
