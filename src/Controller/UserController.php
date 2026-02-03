<?php

namespace App\Controller;

use App\Repository\MotoRepository;
use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/mi-cuenta', name: 'app_user_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(ValoracionRepository $valRepo, MotoRepository $motoRepo): Response
    {
        $user = $this->getUser();

        // 1. Estadísticas Globales (RF9): Las sacamos del IF para que el usuario normal
        // también las vea y no de error en la plantilla Twig.
        $estadisticasGlobales = [
            'total_motos' => count($motoRepo->findAll()),
            'total_valoraciones' => count($valRepo->findAll()),
            // Podrías añadir aquí 'total_usuarios' si inyectas el UserRepository
        ];

        // 2. Valoraciones del usuario actual (RF7)
        $misValoraciones = $valRepo->findBy(['usuario' => $user]);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'misValoraciones' => $misValoraciones,
            'stats' => $estadisticasGlobales,
        ]);
    }
}
