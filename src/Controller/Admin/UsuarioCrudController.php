<?php

namespace App\Controller\Admin;

use App\Entity\Usuario;
/* ESTA ES LA LÍNEA QUE TE FALTA Y QUE CAUSA EL ERROR */
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

class UsuarioCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Usuario::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nombre', 'Nombre completo'),
            EmailField::new('email', 'Correo electrónico'),
            ArrayField::new('roles', 'Roles / Permisos'),
        ];
    }
}
