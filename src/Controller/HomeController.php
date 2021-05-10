<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{

    private CategorieRepository $categorieRepo;

    // Constructeur
    public function __construct( CategorieRepository $categorieRepo)
    {
        $this->categorieRepo = $categorieRepo;
    }
    /**
     * @Route("/", name="home")
     *
     */
    public function index(): Response
    {
        $categories = $this->categorieRepo->findAll();
        dd($categories);

        return $this->render('home/index.html.twig', []);
    }
}



