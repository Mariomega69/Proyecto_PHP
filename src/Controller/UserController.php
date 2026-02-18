<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use App\Repository\MotoRepository;
use App\Repository\RankingRepository;
use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/mi-cuenta', name: 'app_user_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        ValoracionRepository $valRepo,
        MotoRepository $motoRepo,
        CategoriaRepository $catRepo,
        RankingRepository $rankRepo
    ): Response {
        $user = $this->getUser();
        $categorias = $catRepo->findAll();

        // 1. Mapear rankings existentes del usuario para cambiar botones CREAR/EDITAR
        $rankingsUser = $rankRepo->findBy(['usuario' => $user]);
        $mapaRankings = [];
        foreach ($rankingsUser as $r) {
            $mapaRankings[$r->getCategoria()->getId()] = $r->getId();
        }

        // 2. Consenso Tier List (Media de la comunidad)
        $idCategoriaSeleccionada = $request->query->get('id_categoria_ranking');
        $rankingPosicionesMedia = [];

        if ($idCategoriaSeleccionada) {
            $rankingPosicionesMedia = $motoRepo->findMediaPosicionesPorCategoria((int)$idCategoriaSeleccionada);
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'misRankings' => $rankingsUser,
            'mapaRankings' => $mapaRankings, // Nueva variable
            'categorias' => $categorias,
            'stats' => [
                'total_motos' => $motoRepo->count([]),
                'total_valoraciones' => $valRepo->count([]),
            ],
            'topMotos' => $motoRepo->findTopVotadas(5),
            'rankingPosicionesMedia' => $rankingPosicionesMedia,
            'idCatActual' => $idCategoriaSeleccionada
        ]);
    }
}
