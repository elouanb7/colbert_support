<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Panne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class PanneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class,[
                'label'=> "Description de la panne"
            ])
            ->add('solution', TextareaType::class,[
                'label'=> "Solution de la panne",
            ])
            /*->add('idCategory', EntityType::class,[
                'label'=> "Catégorie",
                'class'=> Categorie::class,
                'choice_label'=> function ($category){
                    return $category->();
                }
            ])*/
                    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Panne::class,
        ]);
    }
}