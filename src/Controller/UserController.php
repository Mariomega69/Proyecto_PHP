<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use App\Repository\MotoRepository;
use App\Repository\RankingRepository;
use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Importante añadir esto
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/mi-cuenta', name: 'app_user_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request, // Inyectamos el Request para leer el selector
        ValoracionRepository $valRepo,
        MotoRepository $motoRepo,
        CategoriaRepository $catRepo,
        RankingRepository $rankRepo
    ): Response {
        $user = $this->getUser();
        $categorias = $catRepo->findAll();

        // 1. Capturamos la categoría elegida en el selector de la comunidad
        $idCategoriaSeleccionada = $request->query->get('id_categoria_ranking');
        $rankingPosicionesMedia = [];

        if ($idCategoriaSeleccionada) {
            // Usamos la nueva función que calcula la media de los niveles S, A, B, C
            $rankingPosicionesMedia = $motoRepo->findMediaPosicionesPorCategoria((int)$idCategoriaSeleccionada);
        }

        $estadisticasGlobales = [
            'total_motos' => $motoRepo->count([]),
            'total_valoraciones' => $valRepo->count([]),
        ];

        // Top 5 basado en estrellas (valoraciones directas)
        $topMotos = $motoRepo->findTopVotadas(5);
        $mediasCategorias = $motoRepo->findMediasPorCategoria();

        $topsPorCategoria = [];
        foreach ($categorias as $cat) {
            $topsPorCategoria[$cat->getNombre()] = $motoRepo->findTopByCategoria($cat->getId(), 3);
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'misRankings' => $rankRepo->findBy(['usuario' => $user]),
            'categorias' => $categorias,
            'stats' => $estadisticasGlobales,
            'topMotos' => $topMotos,
            'mediasCategorias' => $mediasCategorias,
            'topsPorCategoria' => $topsPorCategoria,
            // Nuevas variables para el ranking de posiciones media
            'rankingPosicionesMedia' => $rankingPosicionesMedia,
            'idCatActual' => $idCategoriaSeleccionada
        ]);
    }
}
