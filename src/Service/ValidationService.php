<?php

namespace App\Service;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Validation;

class ValidationService
{
    /*public function hello($prenom)
    {
        dd($this->calcul(1, 2));
        return $result = "Salut " . $prenom;
    }

    private function calcul($a, $b)
    {
        return $a + $b;
    }*/

    /**
     * Définis la règle de validation du mot de passe
     * @param $new_pwd
     * @return ConstraintViolationListInterface
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    public function setPasswordViolation($new_pwd): ConstraintViolationListInterface
    {
        // Vérification des données transmises
        $validator = Validation::createValidator();
        // Je retourne la validation
        return $validator->validate($new_pwd, [
            new Regex(
                '/^(?=.*\d)(?=.*[A-Z])(?=.*[@#$%!])(?!.*(.)\1{2}).*[a-z]/m',
                'Votre mot de passe doit comporter au moins 1 lettre Majuscule, 1 lettre minuscule, 1 chiffre et 1 caratère spécial (@#$%!)'),
            new Length(['min' => 6, 'minMessage' => 'Votre mot de passe doit contenir au moins 6 caractères'])
        ]);
    }

}