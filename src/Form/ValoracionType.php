<?php

namespace App\Form;

use App\Entity\Valoracion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValoracionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('estrellas', IntegerType::class, [
                'attr' => ['min' => 1, 'max' => 5],
                'label' => 'Puntuación (1-5)'
            ])
            ->add('comentario', TextareaType::class, [
                'label' => 'Tu opinión'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Valoracion::class,
        ]);
    }
}
