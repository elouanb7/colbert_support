<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Panne;
use App\Entity\User;
use App\Form\PanneType;
use App\Form\TicketType;
use App\Repository\CategorieRepository;
use App\Repository\PanneRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Extra\String\StringExtension;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use function Composer\Autoload\includeFile;

class PanneController extends AbstractController
{

    private CategorieRepository $categorieRepo;
    private PanneRepository $panneRepo;
    private UserRepository $userRepo;
    private EntityManagerInterface $manager;


    // Constructeur
    public function __construct(CategorieRepository $categorieRepo, PanneRepository $panneRepo, UserRepository $userRepo, EntityManagerInterface $manager)
    {
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;
        $this->userRepo = $userRepo;
        $this->manager = $manager;
    }


    /**
     * Permet d'afficher la liste des pannes
     *
     * @Route("/pannes/categorie/{id}", name="pannes")
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
        $pannes = $this->panneRepo->findBy(['categorie' => $id, 'isTicket' => false]);
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
        return $this->render('panne/pannes.html.twig', [
            'controller_name' => 'PanneController',
            'catPannes' => $catPannes,
            'categories' => $categories,
            'pannes' => $pannes,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/pannes/ajout", name="add_panne")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */

    public function addPanne(Request $request)
    {
        $categories = $this->categorieRepo->findAll();
        $panne = new Panne();

        $form = $this->createForm(PanneType::class, $panne);

        $form->handleRequest($request);
        //Je vérifie le formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            //Je récupère mes données du formulaire
            $panne = $form->getData();

            //Je récupère la date d'édition
            $editDate = new \DateTime('now');
            //Je met à jour la date
            $panne->setCreatedAt($editDate);
            $panne->setUser($this->getUser());
            //Je persiste mes données
            $this->manager->persist($panne);
            //J'enregistre mes données
            $this->manager->flush();

            //Message de succès
            $this->addflash(
                'success',
                "La panne à bien été créée !"
            );

            return $this->redirectToRoute('detail', [
                'id' => $panne->getId()
            ]);
        }
        return $this->render('panne/panne_add.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/pannes/detail/{id}/edit", name="edit_panne")
     * @param Panne $panne
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function editPanne(Panne $panne, Request $request): Response
    {
        $categories = $this->categorieRepo->findAll();
        //Je crée le formulaire
        $form = $this->createForm(PanneType::class, $panne);
        //Je lance la requête
        $form->handleRequest($request);

        //Je vérifie le formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            //Je récupère mes données du formulaire
            $panne = $form->getData();

            //Je met à jour la date
            $panne->setCreatedAt(new \DateTime('now'));
            if (!$form->get('solution')->getData()) {
                $panne->setIsTicket(true);
            }
            //Je persiste mes données
            $this->manager->persist($panne);
            //J'enregistre mes données
            $this->manager->flush();

            //Message de succès
            $this->addflash(
                'success',
                "La modification est enregistrée !"
            );

            return $this->redirectToRoute('detail', [
                'id' => $panne->getId()
            ]);
        }
        return $this->render('panne/panne_edit.html.twig', [
            'form' => $form->createView(),
            'panne' => $panne,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/pannes/detail/{id}/delete", name="del_panne")
     *
     * @param Panne $panne
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */

    public function delPanne(Panne $panne)
    {
        $categories = $this->categorieRepo->findAll();
        $catPannes = $panne->getCategorie()->getId();
        $id = $catPannes;
        if ($this->isGranted('ROLE_ADMIN')) {

            //
            $this->manager->remove($panne);
            //
            $this->manager->flush();
            //
            $this->addFlash(
                'success',
                "La panne à bien été supprimée !"
            );
            //J'affiche la vue
            return $this->redirectToRoute('pannes', [
                'id' => $id
            ]);
        }
        $this->addFlash(
            'danger',
            "Vous n'avez pas les permissions nécéssaires !"
        );
        return $this->redirectToRoute('pannes', [
            'panne' => $panne,
            'id' => $id,
        ]);
    }

    /**
     * @Route("/pannes/detail/{id}", name="detail")
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

    /**
     * Permet d'afficher la liste des pannes
     *
     * @Route("/tickets", name="tickets")
     *
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function ticket(PaginatorInterface $paginator, Request $request): Response
    {

        $categories = $this->categorieRepo->findAll();
        $id = $request->request->getInt('categories');
        if ($id) {
            $tickets = $this->panneRepo->findBy(['isTicket' => true, 'categorie' => $id],['createdAt' => 'ASC']);
        } else {
            $tickets = $this->panneRepo->findBy(['isTicket' => true],['createdAt' => 'ASC']);
        }
        $pagination = $paginator->paginate(
            $tickets,
            $request->query->getInt('page', 1),
            6
        );

        $pagination->setTemplate('ressources/twitter_bootstrap_v4_pagination.html.twig');
        $pagination->setCustomParameters([
            'align' => 'center', # center|right (for template: twitter_bootstrap_v4_pagination and foundation_v6_pagination)
            'style' => 'bottom',
            'span_class' => 'whatever',
        ]);
        return $this->render('panne/tickets.html.twig', [
            'controller_name' => 'PanneController',
            'categories' => $categories,
            'tickets' => $tickets,
            'pagination' => $pagination,
            'id' => $id,
        ]);
    }

    /**
     * @Route("/tickets/ajout", name="add_ticket")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function addTicket(Request $request)
    {
        $categories = $this->categorieRepo->findAll();
        $ticket = new Panne();

        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);
        //Je vérifie le formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            //Je récupère mes données du formulaire
            $ticket = $form->getData();
            //Je met à jour la date
            $ticket->setCreatedAt(new \DateTime('now'));
            $ticket->setUser($this->getUser());
            $ticket->setIsTicket(true);
            //Je persiste mes données
            $this->manager->persist($ticket);
            //J'enregistre mes données
            $this->manager->flush();

            //Message de succès
            $this->addflash(
                'success',
                "Le ticket à bien été envoyé ! Il sera traité dans peu de temps !"
            );

            return $this->redirectToRoute('detail', [
                'id' => $ticket->getId()
            ]);
        }
        return $this->render('panne/ticket_add.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/tickets/detail/{id}/edit", name="edit_ticket")
     * @param Panne $ticket
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function editTicket(Panne $ticket, Request $request): Response
    {
        $categories = $this->categorieRepo->findAll();
        //Je crée le formulaire
        $form = $this->createForm(PanneType::class, $ticket);
        //Je lance la requête
        $form->handleRequest($request);

        //Je vérifie le formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            //Je récupère mes données du formulaire
            $ticket = $form->getData();
            //Je met à jour la date
            $ticket->setCreatedAt(new \DateTime('now'));
            //Je transforme mon ticket en panne résolue
            $ticket->setIsTicket(false);
            //Je persiste mes données
            $this->manager->persist($ticket);
            //J'enregistre mes données
            $this->manager->flush();

            //Message de succès
            $this->addflash(
                'success',
                "La modification est enregistrée !"
            );

            return $this->redirectToRoute('detail', [
                'id' => $ticket->getId()
            ]);
        }
        return $this->render('panne/ticket_edit.html.twig', [
            'form' => $form->createView(),
            'ticket' => $ticket,
            'categories' => $categories,
        ]);
    }


}
