<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use App\Entity\Moto;
use App\Entity\Valoracion;
use App\Entity\Usuario; // Añadimos tu entidad Usuario
use App\Repository\MotoRepository;
use App\Repository\ValoracionRepository;
use App\Repository\CategoriaRepository;
use App\Repository\UsuarioRepository; // Añadimos el repositorio
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private MotoRepository $motoRepo;
    private ValoracionRepository $valoracionRepo;
    private CategoriaRepository $categoriaRepo;
    private UsuarioRepository $usuarioRepo; // Nueva propiedad

    public function __construct(
        MotoRepository $motoRepo,
        ValoracionRepository $valoracionRepo,
        CategoriaRepository $categoriaRepo,
        UsuarioRepository $usuarioRepo // Inyectamos el repo de Usuario
    ) {
        $this->motoRepo = $motoRepo;
        $this->valoracionRepo = $valoracionRepo;
        $this->categoriaRepo = $categoriaRepo;
        $this->usuarioRepo = $usuarioRepo;
    }

    public function index(): Response
    {
        return $this->render('admin/my_dashboard.html.twig', [
            'totalMotos' => $this->motoRepo->count([]),
            'totalValoraciones' => $this->valoracionRepo->count([]),
            'totalCategorias' => $this->categoriaRepo->count([]),
            'totalUsuarios' => $this->usuarioRepo->count([]), // Enviamos el total a la plantilla
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Panel de Control BMW')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-home');

        yield MenuItem::section('Gestión de Contenido');
        yield MenuItem::linkToCrud('Categorías', 'fas fa-tags', Categoria::class);
        yield MenuItem::linkToCrud('Motos', 'fas fa-motorcycle', Moto::class);
        yield MenuItem::linkToCrud('Valoraciones', 'fas fa-star', Valoracion::class);

        // Nueva sección para Usuarios
        yield MenuItem::section('Usuarios');
        yield MenuItem::linkToCrud('Usuarios Registrados', 'fas fa-users', Usuario::class);

        yield MenuItem::section('Sistema');
        yield MenuItem::linkToRoute('Volver a la Web', 'fas fa-arrow-left', 'app_moto');
    }
}
