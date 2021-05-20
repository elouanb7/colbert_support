<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;
use App\Repository\PanneRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    private CategorieRepository $categorieRepo;
    private PanneRepository $panneRepo;
    private UserRepository $userRepo;
    private ValidationService $validationService;

    public function __construct(CategorieRepository $categorieRepo, PanneRepository $panneRepo, UserRepository $userRepo, ValidationService $validationService)
    {
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;
        $this->userRepo = $userRepo;
        $this->validationService = $validationService;
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
     * @Route("/dashboard/users", name="users")
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
     * @Route("/dashboard/users/ajout", name="add_user")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function addUser(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $categories = $this->categorieRepo->findAll();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        //Je vérifie le formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            //Je récupère mes données du formulaire
            $user = $form->getData();
            //Abcdef3!
            $plainPassword = $form->get('plainPassword')->getData();
            $violations = $this->validationService->setPasswordViolation($plainPassword);
            if (0 !== count($violations)) {
                return $this->render('user/user_add.html.twig', [
                    'form' => $form->createView(),
                    'categories' => $categories,
                    'violations' => $violations,
                ]);
            }
            $user->setPassword($passwordEncoder->encodePassword($user, $plainPassword));
            $user->setRoles($form->get('roles')->getData());
            //Je met à jour la date
            $user->setCreatedAt(new \DateTime('now'));
            //Je persiste mes données
            $manager->persist($user);
            //J'enregistre mes données
            $manager->flush();

            //Message de succès
            $this->addflash(
                'success',
                "Le nouvel Utilisateur à bien été enregistré !"
            );

            return $this->redirectToRoute('profil', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/user_add.html.twig', ['form' => $form->createView(),
            'categories' => $categories,]);
    }

    /**
     * @Route("/users/{id}/delete", name="del_user")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */

    public function delUser(User $user, EntityManagerInterface $manager)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            // TODO : Revoir cette méthode
            if($this->getUser()==$user){
                $manager->remove($user);
                //
                $manager->flush();
                //
                $this->addFlash(
                    'success',
                    "L'utilisateur à bien été supprimé !"
                );
                //J'affiche la vue
                return $this->redirectToRoute('app_login', []);
            }
            $manager->remove($user);
            //
            $manager->flush();
            //
            $this->addFlash(
                'success',
                "L'utilisateur à bien été supprimé !"
            );
            //J'affiche la vue
            return $this->redirectToRoute('users', []);
        }
        $this->addFlash(
            'danger',
            "Vous n'avez pas les permissions nécéssaires !"
        );
        return $this->redirectToRoute('users', []);
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
        return $this->render('user/dashboard.html.twig', [
            'users' => $users,
            'lastUser' => $this->userRepo->findBy([], ['createdAt' => "DESC"], 1),
            'lastAdmin' => $this->userRepo->findLastByRole('ROLE_ADMIN'),
            'lastContributor' => $this->userRepo->findLastByRole('ROLE_CONTRIBUTOR'),
            'categories' => $categories,
            'nbUsers' => $nbUsers,
            'tickets' => $this->panneRepo->findBy(['isTicket' => true])
        ]);
    }
}
