<?php

namespace App\Form;

use App\Entity\RankingItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RankingItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tier', ChoiceType::class, [
            'choices'  => [
                'S - Élite (La mejor)' => 'S',
                'A - Muy Buena'        => 'A',
                'B - Normalita'       => 'B',
                'C - Malisima'         => 'C',
            ],
            'attr' => [
                'class' => 'form-select bg-primary text-white border-0'
            ],
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RankingItem::class,
        ]);
    }
}
