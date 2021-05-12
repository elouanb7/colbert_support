<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\PanneRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
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
    private UserRepository $userRepo;


    // Constructeur
    public function __construct(CategorieRepository $categorieRepo, PanneRepository $panneRepo, UserRepository $userRepo)
    {
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;
        $this->userRepo = $userRepo;
    }


    /**
     * Permet d'afficher la liste des pannes
     *
     * @Route("/panne/categorie/{id}", name="panne")
     *
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
        $pagination = $paginator->paginate(
            $pannes,
            $request->query->getInt('page', 1),
            6
        );
        $pagination->setTemplate('ressources/twitter_bootstrap_v4_pagination.html.twig');
        $pagination->setCustomParameters([
            'align' => 'center', # center|right (for template: twitter_bootstrap_v4_pagination and foundation_v6_pagination)
            'style' => 'bottom',
            'span_class' => 'whatever',
        ]);
        return $this->render('panne/panne.html.twig', [
            'controller_name' => 'PanneController',
            'catPannes' => $catPannes,
            'categories' => $categories,
            'pannes' => $pannes,
            'pagination' => $pagination
        ]);
    }


    /**
     * @Route("/panne/detail/{id}", name="detail")
     * @param $id
     * @return Response
     */
    public function detail($id): Response
    {
        $categories = $this->categorieRepo->findAll();
        $panne = $this->panneRepo->findOneBy(['id' => $id]);
     return $this->render('panne/detail.html.twig', [
         'panne' => $panne,
         'categories' => $categories,
    ]);
    }

}
