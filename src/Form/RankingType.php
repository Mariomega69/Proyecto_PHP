<?php

namespace App\Form;

use App\Entity\Ranking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RankingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoria = $options['categoria'];

        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de tu Tier List',
                'attr' => [
                    'class' => 'form-control bg-dark text-white border-secondary mb-4',
                    'placeholder' => 'Ej: Mi ranking de ' . ($categoria ? $categoria->getNombre() : 'motos')
                ]
            ])
            /* CAMBIO CLAVE: Ya no usamos EntityType (Checkboxes).
               Usamos CollectionType para cargar los RankingItems (Moto + Tier)
            */
            ->add('items', CollectionType::class, [
                'entry_type' => RankingItemType::class, // El formulario hijo que creamos antes
                'label' => false,
                'allow_add' => false,    // No se añaden filas nuevas, se usan las precargadas
                'allow_delete' => false, // No se borran filas
                'by_reference' => false, // OBLIGATORIO para que funcione addItem() en la entidad Ranking
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ranking::class,
            'categoria' => null,
        ]);

        // Mantenemos la validación de la categoría
        $resolver->setAllowedTypes('categoria', ['App\Entity\Categoria', 'null']);
    }
}
