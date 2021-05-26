<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    private UserService $userService;

    public function __construct(CategorieRepository $categorieRepo, PanneRepository $panneRepo, UserRepository $userRepo, ValidationService $validationService, UserService $userService)
    {
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;
        $this->userRepo = $userRepo;
        $this->userService = $userService;
        $this->validationService = $validationService;
    }

    /**
     * @Route("/profil/user/{id}", name="profil")
     * @param $id
     * @return Response
     */
    public function profile($id): Response
    {
        $allTickets = $this->panneRepo->findBy(['isTicket' => true, 'user' => $this->getUser()], [],);
        $allPannes = $this->panneRepo->findBy(['isTicket' => false, 'user' => $this->getUser()], [],);
        $categories = $this->categorieRepo->findAll();
        $user = $this->userRepo->findOneBy(['id' => $id]);
        return $this->render('user/profil.html.twig', [
            'controller_name' => 'UserController',
            'categories' => $categories,
            'user' => $user,
            'tickets' => $this->panneRepo->findBy(['isTicket' => true, 'user' => $this->getUser()], [], 6),
            'pannes' => $this->panneRepo->findBy(['isTicket' => false, 'user' => $this->getUser()], [], 6),
            'allTickets' => count($allTickets),
            'allPannes' => count($allPannes),
        ]);
    }

    /**
     * @Route("/dashboard/users", name="users")
     * @param Request $request
     * @return Response
     */
    public function users(Request $request): Response
    {
        $id = $request->request->getInt('tri');
        /*$users = $this->userService->sortUsers($id);*/
        $users = $this->userService->whichRoles($request, $id);
        $users = $this->userService->sortUsers($id,$users);
        if ($users == []){
            $users = $this->userRepo->findBy([],['lastName' => 'ASC']);
        }



        $categories = $this->categorieRepo->findAll();

        return $this->render('user/users.html.twig', [
            'controller_name' => 'UserController',
            'categories' => $categories,
            'users' => $users,
            'id' => $id,
            'usersCheck' => $request->request->get('usersCheck'),
            'contributorsCheck' => $request->request->get('contributorsCheck'),
            'adminsCheck' => $request->request->get('adminsCheck'),
            'superAdminsCheck' => $request->request->get('superAdminsCheck'),
        ]);
    }

    /**
     * @Route("/dashboard/users/ajout", name="add_user")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $passwordEncoder
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
     * @Route("/dashboard/users/{id}/edit", name="edit_user")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $categories = $this->categorieRepo->findAll();
        $options = $this->getUser()->getRoles();
        $roles  = [];
        foreach ($options as $option){
            if($option == "ROLE_SUPER_ADMIN"){
                $roles = ['Utilisateur' => 'ROLE_USER',
                    'Contributeur' => 'ROLE_CONTRIBUTOR',
                    'Admin' => 'ROLE_ADMIN',
                    'Super Admin' => 'ROLE_SUPER_ADMIN'
                ];
            }
            if($option == "ROLE_ADMIN"){
                $roles = ['Utilisateur' => 'ROLE_USER',
                    'Contributeur' => 'ROLE_CONTRIBUTOR',
                    'Admin' => 'ROLE_ADMIN',
                ];
            }
        };
            $form = $this->createForm(UserType::class, $user, [
                'rolechoices' => $roles,
            ]);

        $form->handleRequest($request);
        //Je vérifie le formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            //Je récupère mes données du formulaire
            $user = $form->getData();
            //Abcdef3!
            $plainPassword = $form->get('plainPassword')->getData();
            $violations = $this->validationService->setPasswordViolation($plainPassword);
            if (0 !== count($violations)) {
                return $this->render('user/user_edit.html.twig', [
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
                "Les modifications ont bien été enregistrées !"
            );

            return $this->redirectToRoute('profil', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/user_edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/dashboard/users/{id}/delete", name="del_user")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */

    public function delUser(User $user, EntityManagerInterface $manager)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            // TODO : Revoir cette méthode
            if ($this->getUser() == $user) {
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
        $allTickets = $this->panneRepo->findBy(['isTicket' => true], [],);
        $categories = $this->categorieRepo->findAll();
        return $this->render('user/dashboard.html.twig', [
            'users' => $users,
            'lastUser' => $this->userRepo->findBy([], ['createdAt' => "DESC"], 1),
            'lastAdmin' => $this->userRepo->findLastByRole('ROLE_ADMIN'),
            'lastContributor' => $this->userRepo->findLastByRole('ROLE_CONTRIBUTOR'),
            'categories' => $categories,
            'nbUsers' => $nbUsers,
            'tickets' => $this->panneRepo->findBy(['isTicket' => true], [], 6),
            'allTickets' => count($allTickets),
        ]);
    }
}
