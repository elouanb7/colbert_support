<?php

namespace App\DataFixtures;

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

    // Constructeur
    public function __construct(UserPasswordEncoderInterface $encoder, CategorieRepository $categorieRepo)
    {
        $this->encoder = $encoder;
        $this->categorieRepo = $categorieRepo;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');


        // 2 Je crée 50 utilisateurs et j'ajoute 10 pannes par utilsateurs


        for ($j = 0; $j < 10; $i++) {
            $user = new User();
            $user->setFirstName($faker->name())
                 ->setLastName($faker->surname())
                 ->setEmail($faker->email())
                 ->setRoles(['ROLE_USER'])
                 ->setPassword($this->encoder->encodePassword('password', $user))

            ;

            for ($k = 0; $k < 10; $k++){

                $panne = new Panne();
                $panne->setCategorie($this->categorieRepo->findOneBy(mt_rand(0, 3)))
                ;
            }

        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
