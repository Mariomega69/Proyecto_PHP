<?php

namespace App\Controller;

use App\Entity\Moto;
use App\Entity\Usuario;
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
    #[Route('/moto', name: 'app_moto')]
    public function index(MotoRepository $motoRepo, Request $request): Response
    {
        $termino = $request->query->get('q');

        if ($termino) {
            $motos = $motoRepo->createQueryBuilder('m')
                ->where('m.modelo LIKE :t')
                ->setParameter('t', '%'.$termino.'%')
                ->getQuery()
                ->getResult();
        } else {
            $motos = $motoRepo->findAll();
        }

        return $this->render('moto/index.html.twig', [
            'motos' => $motos,
        ]);
    }

    #[Route('/moto/{modelo}', name: 'app_moto_detalle')]
    public function detalle(
        string $modelo,
        EntityManagerInterface $em,
        HttpClientInterface $client,
        Request $request
    ): Response {
        $motoLocal = $em->getRepository(Moto::class)->findOneBy(['modelo' => $modelo]);
        $origen = "Base de Datos local";
        $motoMostrar = $motoLocal;

        if (!$motoLocal) {
            try {
                $url = "https://www.bikespecs.org/api/v1/bikes/BMW/" . urlencode($modelo);
                $response = $client->request('GET', $url, ['timeout' => 2.0]);

                if ($response->getStatusCode() === 200) {
                    $data = $response->toArray();
                    $item = $data['data'] ?? $data;
                    $motoMostrar = new Moto();
                    $motoMostrar->setMarca("BMW");
                    $motoMostrar->setModelo($item['model'] ?? $modelo);
                    $motoMostrar->setUrlImagen($item['image'] ?? 'https://placehold.co/600x400?text=BMW+' . $modelo);
                    $origen = "API (BikeSpecs.org)";
                }
            } catch (\Exception $e) {
                $motoMostrar = null;
            }
        }

        if (!$motoMostrar) {
            throw $this->createNotFoundException('La moto no existe.');
        }

        $nuevaValoracion = new Valoracion();
        $form = $this->createForm(ValoracionType::class, $nuevaValoracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pillamos el usuario logueado actualmente
            $user = $this->getUser();

            if (!$user) {
                $this->addFlash('danger', 'Debes iniciar sesión para valorar.');
                return $this->redirectToRoute('app_login');
            }

            if ($motoLocal) {
                $nuevaValoracion->setMoto($motoLocal);
                // ESTA ES LA LÍNEA CLAVE PARA QUE SALGA EN "MI CUENTA"
                $nuevaValoracion->setUsuario($user);

                $em->persist($nuevaValoracion);
                $em->flush();

                $this->addFlash('success', '¡Valoración guardada! Ahora aparecerá en tu perfil.');
            } else {
                $this->addFlash('warning', 'Solo se pueden valorar motos guardadas localmente.');
            }

            return $this->redirectToRoute('app_moto_detalle', ['modelo' => $modelo]);
        }

        $valoraciones = $motoLocal ? $em->getRepository(Valoracion::class)->findBy(['moto' => $motoLocal]) : [];

        return $this->render('moto/detalle.html.twig', [
            'moto' => $motoMostrar,
            'origen' => $origen,
            'formulario' => $form->createView(),
            'valoraciones' => $valoraciones
        ]);
    }
}
