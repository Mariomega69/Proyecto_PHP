<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField; // <--- ESTA ES LA QUE FALTABA
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField; // <--- Y ESTA PARA LAS MOTOS

class CategoriaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categoria::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nombre', 'Nombre de la Categoría'),
            UrlField::new('imagen', 'URL de Imagen (Icono)'),

            // Esto mostrará cuántas motos hay en el listado principal
            AssociationField::new('motos', 'Motos vinculadas')
                ->onlyOnIndex(),

            // Esto mostrará la lista de nombres cuando entres al detalle (lupa)
            AssociationField::new('motos', 'Lista de Modelos')
                ->onlyOnDetail(),
        ];
    }
}
