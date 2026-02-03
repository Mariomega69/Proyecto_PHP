<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use App\Entity\Moto;
use App\Entity\Valoracion;
use App\Repository\MotoRepository;
use App\Repository\ValoracionRepository;
use App\Repository\CategoriaRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    // Definimos las propiedades para los repositorios
    private MotoRepository $motoRepo;
    private ValoracionRepository $valoracionRepo;
    private CategoriaRepository $categoriaRepo;

    // Los inyectamos en el constructor para cumplir con las reglas de EasyAdmin
    public function __construct(
        MotoRepository $motoRepo,
        ValoracionRepository $valoracionRepo,
        CategoriaRepository $categoriaRepo
    ) {
        $this->motoRepo = $motoRepo;
        $this->valoracionRepo = $valoracionRepo;
        $this->categoriaRepo = $categoriaRepo;
    }

    public function index(): Response
    {
        // Renderizamos la plantilla personalizada con los datos reales
        return $this->render('admin/my_dashboard.html.twig', [
            'totalMotos' => $this->motoRepo->count([]),
            'totalValoraciones' => $this->valoracionRepo->count([]),
            'totalCategorias' => $this->categoriaRepo->count([]),
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

        yield MenuItem::section('Sistema');
        yield MenuItem::linkToRoute('Volver a la Web', 'fas fa-arrow-left', 'app_moto');
    }
}
