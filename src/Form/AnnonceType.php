<?php

namespace App\Form;

use App\Entity\Annonce;
// use App\Entity\User;
// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('price_type', ChoiceType::class, [
                'choices' => [
                    "Prix TTC" => 'ttc',
                    "Prix HT" => 'ht'
                ]
            ])
            ->add('picture', FileType::class, [
                'label' => 'Photo du produit',
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Merci d\'envoyer une image au format JPG ou PNG',
                    ])
                ]
            ])
            ->add('pegi')
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => function ($user) {
            //         return $user->getFirstname(). ' '. $user->getLastname();
            //     }
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
