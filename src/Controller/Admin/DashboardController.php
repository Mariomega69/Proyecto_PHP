<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use App\Entity\Moto;
use App\Entity\Valoracion;
use App\Entity\Usuario;
use App\Repository\MotoRepository;
use App\Repository\ValoracionRepository;
use App\Repository\CategoriaRepository;
use App\Repository\UsuarioRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted("ROLE_ADMIN")]
class DashboardController extends AbstractDashboardController
{
    private MotoRepository $motoRepo;
    private ValoracionRepository $valoracionRepo;
    private CategoriaRepository $categoriaRepo;
    private UsuarioRepository $usuarioRepo;

    public function __construct(
        MotoRepository $motoRepo,
        ValoracionRepository $valoracionRepo,
        CategoriaRepository $categoriaRepo,
        UsuarioRepository $usuarioRepo
    ) {
        $this->motoRepo = $motoRepo;
        $this->valoracionRepo = $valoracionRepo;
        $this->categoriaRepo = $categoriaRepo;
        $this->usuarioRepo = $usuarioRepo;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $categorias = $this->categoriaRepo->findAll();

        return $this->render('admin/my_dashboard.html.twig', [
            'totalMotos' => $this->motoRepo->count([]),
            'totalValoraciones' => $this->valoracionRepo->count([]),
            'totalCategorias' => count($categorias),
            'totalUsuarios' => $this->usuarioRepo->count([]),
            'topMotos' => $this->motoRepo->findTopVotadas(5),
            'categorias' => $categorias,
            // Importante: Usamos el método de Tiers/Rankings
            'todasLasMotos' => $this->motoRepo->findMediaTiersTodasLasMotos()
        ]);
    }

    #[Route('/admin/importar-motos', name: 'admin_importar_motos')]
    public function importarMotos(): Response
    {
        $this->addFlash('success', 'Sincronización con API BMW completada (Simulado).');
        return $this->redirectToRoute('admin');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Panel BMW Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-home');
        yield MenuItem::linkToCrud('Categorías', 'fas fa-tags', Categoria::class);
        yield MenuItem::linkToCrud('Motos', 'fas fa-motorcycle', Moto::class);
        yield MenuItem::linkToCrud('Valoraciones', 'fas fa-star', Valoracion::class);
        yield MenuItem::linkToCrud('Usuarios', 'fas fa-users', Usuario::class);
        yield MenuItem::linkToRoute('Volver a la Web', 'fas fa-arrow-left', 'app_moto');
    }
}
