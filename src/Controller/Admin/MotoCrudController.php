<?php

namespace App\Controller\Admin;

use App\Entity\Moto;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField; // <--- Clase necesaria

class MotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Moto::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('marca', 'Marca'),
            TextField::new('modelo', 'Modelo'),

            // Este campo crea el selector para elegir la categoría que creaste antes
            AssociationField::new('categoria', 'Asignar a Categoría')
                ->setRequired(true)
                ->setHelp('Selecciona una de las categorías creadas en el apartado de Categorías'),

            UrlField::new('urlImagen', 'URL de la Foto'),
        ];
    }
}
