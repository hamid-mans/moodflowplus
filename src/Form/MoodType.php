<?php

namespace App\Form;

use App\Entity\Mood;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mood', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    "😎 Super Heureux" => 1,
                    "😁 Heureux" => 2,
                    "🤔 Neutre" => 3,
                    "😞 Triste" => 4,
                    "😫 Très Triste" => 5
                ],
                'label_html' => true,
                'attr' => [
                    'class' => 'ui fluid dropdown',
                ]
            ])
            ->add('note', TextareaType::class, [
                'label' => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'ui purple button'
                ],
                'label' => 'ENREGISTRER'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mood::class,
        ]);
    }
}
