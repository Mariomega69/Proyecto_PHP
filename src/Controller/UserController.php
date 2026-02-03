<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use App\Repository\MotoRepository;
use App\Repository\RankingRepository;
use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/mi-cuenta', name: 'app_user_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(
        ValoracionRepository $valRepo,
        MotoRepository $motoRepo,
        CategoriaRepository $catRepo,
        RankingRepository $rankRepo
    ): Response
    {
        $user = $this->getUser();

        // 1. Estadísticas Globales (RF9)
        $estadisticasGlobales = [
            'total_motos' => count($motoRepo->findAll()),
            'total_valoraciones' => count($valRepo->findAll()),
        ];

        // 2. Valoraciones del usuario actual (RF7)
        $misValoraciones = $valRepo->findBy(['usuario' => $user]);

        // 3. Obtener todas las categorías para que el usuario elija (RF8 - Punto 27)
        $categorias = $catRepo->findAll();

        // 4. Rankings ya creados por el usuario (RF8 - Punto 29)
        $misRankings = $rankRepo->findBy(['usuario' => $user]);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'misValoraciones' => $misValoraciones,
            'misRankings' => $misRankings,
            'categorias' => $categorias,
            'stats' => $estadisticasGlobales,
        ]);
    }
}
