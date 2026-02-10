<?php

namespace App\Controller;

use App\Entity\Moto;
use App\Entity\Valoracion;
use App\Form\ValoracionType;
use App\Repository\MotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MotoController extends AbstractController
{
    /**
     * Esta ruta ocupa la raíz (/) para eliminar la página de inicio de Symfony
     * y redirigir automáticamente al login.
     */
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route('/moto', name: 'app_moto')]
    public function index(MotoRepository $motoRepo, Request $request): Response
    {
        $termino = $request->query->get('q');
        $motos = $termino ? $motoRepo->createQueryBuilder('m')
            ->where('m.modelo LIKE :t')
            ->setParameter('t', '%'.$termino.'%')
            ->getQuery()->getResult() : $motoRepo->findAll();

        return $this->render('moto/index.html.twig', ['motos' => $motos]);
    }

    #[Route('/moto/{modelo}', name: 'app_moto_detalle')]
    public function detalle(string $modelo, EntityManagerInterface $em, HttpClientInterface $client, Request $request): Response
    {
        $motoLocal = $em->getRepository(Moto::class)->findOneBy(['modelo' => $modelo]);
        $user = $this->getUser();

        if (!$motoLocal) {
            throw $this->createNotFoundException('La moto no está en la base de datos local para valorar.');
        }

        // 1. Buscar si el usuario ya valoró esta moto
        $valoracion = $user ? $em->getRepository(Valoracion::class)->findOneBy([
            'usuario' => $user,
            'moto' => $motoLocal
        ]) : null;

        $haValorado = ($valoracion !== null);

        // 2. Si no existe, preparamos una nueva; si existe, editamos la encontrada
        if (!$valoracion) {
            $valoracion = new Valoracion();
        }

        $form = $this->createForm(ValoracionType::class, $valoracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user) {
                $this->addFlash('danger', 'Debes iniciar sesión.');
                return $this->redirectToRoute('app_login');
            }

            $valoracion->setMoto($motoLocal);
            $valoracion->setUsuario($user);

            $em->persist($valoracion);
            $em->flush();

            $this->addFlash('success', $haValorado ? 'Valoración actualizada.' : '¡Gracias por tu opinión!');
            return $this->redirectToRoute('app_moto_detalle', ['modelo' => $modelo]);
        }

        $valoraciones = $em->getRepository(Valoracion::class)->findBy(['moto' => $motoLocal]);

        return $this->render('moto/detalle.html.twig', [
            'moto' => $motoLocal,
            'formulario' => $form->createView(),
            'valoraciones' => $valoraciones,
            'haValorado' => $haValorado
        ]);
    }
}
