<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', TextType::class, [
                'label' => "Prenom",
                'empty_data' => '',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => "Nom",
                'empty_data' => '',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => "Adresse e-mail",
                'empty_data' => '',
                'required' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => "Nouveau mot de passe"],
                'second_options' => ['label' => "Confimez le nouveau mot de passe"],
                'invalid_message' => 'Les deux champs doivent être identiques.',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => $options['rolechoices'],
                'multiple' => false,
                'expanded' => false,
                'label' => "Roles",
            ]);
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));

    }

    // Data transformer

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'rolechoices' => [],
        ]);

    }
}