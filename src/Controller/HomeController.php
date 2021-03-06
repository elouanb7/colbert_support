<?php

namespace App\Controller;

use App\Entity\Panne;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Service\ValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{

    private CategorieRepository $categorieRepo;
    private ValidationService $testService;

    // Constructeur
    public function __construct(CategorieRepository $categorieRepo, ValidationService $testService)
    {
        $this->categorieRepo = $categorieRepo;
        $this->testService = $testService;
    }

    /**
     * @Route("/", name="home")
     *
     */
    public function index(): Response
    {

        $categories = $this->categorieRepo->findAll();
        return $this->render('home/index.html.twig', [
            'categories' => $categories,

        ]);
    }
}



