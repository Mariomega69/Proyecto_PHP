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
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
            'todasLasMotos' => $this->motoRepo->findMediaTiersTodasLasMotos()
        ]);
    }

    #[Route('/admin/importar-motos', name: 'admin_importar_motos')]
    public function importarMotos(HttpClientInterface $client, EntityManagerInterface $em): Response
    {
        try {
            // Nueva URL proporcionada
            $response = $client->request('GET', 'https://www.bikespecs.org/api/v1/brands/bmw', [
                'verify_peer' => false, // Útil si estás en local y da error de SSL
                'verify_host' => false,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->addFlash('danger', 'La API respondió con código: ' . $response->getStatusCode());
                return $this->redirectToRoute('admin');
            }

            $data = $response->toArray();

            // Según la estructura típica de esta API, los modelos suelen venir en 'models' o directamente en el cuerpo
            // Ajustamos al formato de bikespecs:
            $motosApi = $data['models'] ?? $data['data'] ?? $data;

            $contador = 0;

            foreach ($motosApi as $motoData) {
                // Ajuste de nombres según el JSON de la nueva URL
                $modeloNombre = $motoData['name'] ?? $motoData['model'] ?? null;
                $idApiExterno = (string)($motoData['id'] ?? '');

                if (!$modeloNombre || !$idApiExterno) continue;

                // Verificar si ya existe
                $existePorId = $this->motoRepo->findOneBy(['id_api' => $idApiExterno]);
                $existePorNombre = $this->motoRepo->findOneBy(['modelo' => $modeloNombre]);

                if (!$existePorId && !$existePorNombre) {
                    $nuevaMoto = new Moto();
                    $nuevaMoto->setMarca('BMW');
                    $nuevaMoto->setModelo($modeloNombre);
                    $nuevaMoto->setIdApi($idApiExterno);

                    // Imagen por defecto o de la API
                    if (!empty($motoData['image'])) {
                        $nuevaMoto->setUrlImagen($motoData['image']);
                    } else {
                        $nuevaMoto->setUrlImagen("https://placehold.co/600x400?text=BMW+" . str_replace(' ', '+', $modeloNombre));
                    }

                    $em->persist($nuevaMoto);
                    $contador++;
                }
            }

            $em->flush();

            if ($contador > 0) {
                $this->addFlash('success', "¡Conexión exitosa! Se han importado $contador motos nuevas.");
            } else {
                $this->addFlash('info', 'Conexión establecida, pero no había motos nuevas para importar.');
            }

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error crítico de conexión: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Panel BMW Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-home');

        yield MenuItem::section('Gestión de Datos');
        yield MenuItem::linkToCrud('Categorías', 'fas fa-tags', Categoria::class);
        yield MenuItem::linkToCrud('Motos', 'fas fa-motorcycle', Moto::class);

        yield MenuItem::section('Comunidad');
        yield MenuItem::linkToCrud('Valoraciones', 'fas fa-star', Valoracion::class);
        yield MenuItem::linkToCrud('Usuarios', 'fas fa-users', Usuario::class);

        yield MenuItem::section('Herramientas');
        // Este botón ejecuta la importación directamente desde el menú lateral si quieres
        yield MenuItem::linkToRoute('Sincronizar API', 'fas fa-sync', 'admin_importar_motos');

        yield MenuItem::linkToRoute('Volver a la Web', 'fas fa-arrow-left', 'app_moto');
    }
}
