<?php

namespace App\Form;

use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class StudentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageprofile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'constraints'=> new NotBlank([
                    'message' => 'Le nom ne peut pas être vide.',
                ]),
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => '',
                ],


            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom',
                'required' => false,
                'constraints'=> new NotBlank([
                    'message' => 'Le Prenom ne peut pas être vide.',
                ]),


            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}
