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
}
