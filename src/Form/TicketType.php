<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Panne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('intitule', TextType::class, [
                'label' => "Intitulé du ticket",
                'empty_data' => '',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description du ticket",
                'empty_data' => '',
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'label' => "Catégorie",
                'class' => Categorie::class,
                'choice_label' => function ($categorie) {
                    return $categorie->getName();
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Panne::class,
        ]);
    }
}