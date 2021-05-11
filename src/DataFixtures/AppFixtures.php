<?php

namespace App\DataFixtures;

use App\Repository\PanneRepository;
use Faker;
use App\Entity\Categorie;
use App\Entity\User;
use App\Entity\Panne;
use App\Repository\CategorieRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{
    // Propriétés
    private UserPasswordEncoderInterface $encoder;
    private CategorieRepository $categorieRepo;
    private PanneRepository $panneRepo;

    // Constructeur
    public function __construct(UserPasswordEncoderInterface $encoder, CategorieRepository $categorieRepo, PanneRepository $panneRepo)
    {
        $this->encoder = $encoder;
        $this->categorieRepo = $categorieRepo;
        $this->panneRepo = $panneRepo;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        // 1 - Récupérer toutes les pannes


        // 3 - Je boucle (for)

        // 2 Je crée 50 utilisateurs et j'ajoute 10 pannes par utilsateurs

        for ($i = 0; $i < 4; $i++) {
            $categorie = new Categorie();
            $categorie->setName('Categorie n°' . $i)
                      ->setDescription($faker->sentence())
            ;

            $manager->persist($categorie);
        }
        $manager->flush();
        // Je crée les users
        for ($j = 0; $j < 10; $j++) {

            // Je crée un nouvel objet User
            $user = new User();
            // Je paramètre mon objet
            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->encoder->encodePassword($user, 'password'));
            // Je stock en mémoire
            $manager->persist($user);

            for ($k = 0; $k < 10; $k++) {


                $panne = new Panne();
                $panne->setCreatedAt(new \DateTime('now'))
                    ->setDescription($faker->sentence())
                    ->setSolution($faker->paragraph(10))
                    ->setUser($user);
                $manager->persist($panne);
            }
            $manager->flush();

            $pannes = $this->panneRepo->findAll();
            // 2 - Boucle sur les pannes (foreach)
            foreach ($pannes as $panne) {
                $categorie = $this->categorieRepo->findOneBy(['id' => mt_rand(1, 4)]);
                $panne->setCategorie($categorie);
                $manager->persist($panne);
            }

        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
