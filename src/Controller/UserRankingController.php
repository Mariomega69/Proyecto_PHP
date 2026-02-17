<?php

namespace App\Controller;

use App\Entity\Ranking;
use App\Entity\Categoria;
use App\Entity\RankingItem;
use App\Form\RankingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserRankingController extends AbstractController
{
    #[Route('/ranking/nuevo/{id}', name: 'app_ranking_nuevo')]
    #[IsGranted('ROLE_USER')]
    public function nuevo(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $categoria = $em->getRepository(Categoria::class)->find($id);
        if (!$categoria) throw $this->createNotFoundException();

        $ranking = new Ranking();
        $ranking->setUsuario($this->getUser());
        $ranking->setCategoria($categoria);
        $ranking->setNombre('Ranking ' . $categoria->getNombre());
        $ranking->setFecha(new \DateTime());

        foreach ($categoria->getMotos() as $moto) {
            $item = new RankingItem();
            $item->setMoto($moto);
            $item->setTier('B');
            $ranking->addItem($item);
        }

        $form = $this->createForm(RankingType::class, $ranking, ['categoria' => $categoria]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ranking);
            $em->flush();
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('ranking/ranking.html.twig', [
            'form' => $form->createView(),
            'categoria' => $categoria,
            'modo' => 'crear'
        ]);
    }

    #[Route('/ranking/editar/{id}', name: 'app_ranking_editar')]
    #[IsGranted('ROLE_USER')]
    public function editar(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $ranking = $em->getRepository(Ranking::class)->find($id);
        if (!$ranking || $ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(RankingType::class, $ranking, ['categoria' => $ranking->getCategoria()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('ranking/ranking.html.twig', [
            'form' => $form->createView(),
            'categoria' => $ranking->getCategoria(),
            'modo' => 'editar'
        ]);
    }
}
