<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\PanneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Extra\String\StringExtension;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PanneController extends AbstractController
{

    private CategorieRepository $categorieRepo;
    private PanneRepository $panneRepo;



    // Constructeur
    public function __construct(CategorieRepository $categorieRepo, PanneRepository $panneRepo)
    {
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;


    }

    /**
     * @Route("/panne/categorie/{id}", name="panne")
     * @param $id
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function panne($id, PaginatorInterface $paginator, Request $request): Response
    {
        $categories = $this->categorieRepo->findAll();
        $catPannes = $this->categorieRepo->find($id);
        $pannes = $this->panneRepo->findBy(['categorie' => $id]);
        $pagepannes = $paginator->paginate(
            $pannes,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('panne/panne.html.twig', [
            'controller_name' => 'PanneController',
            'catPannes' => $catPannes,
            'categories' => $categories,
            'pannes' => $pannes,
            'pagepannes' => $pagepannes
        ]);
    }
}
