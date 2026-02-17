<?php

namespace App\Command;

use App\Entity\Moto;
use App\Repository\MotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:importar-motos',
    description: 'Importa todas las motos de BMW desde la API a la base de datos local evitando duplicados',
)]
class ImportarMotosCommand extends Command
{
    private $entityManager;
    private $client;
    private $motoRepo;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $client, MotoRepository $motoRepo)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->client = $client;
        $this->motoRepo = $motoRepo;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando importación de motos BMW desde la API...');

        try {
            // Usamos la URL correcta según el formato de datos que pasaste
            $response = $this->client->request('GET', 'https://www.bikespecs.org/api/v1/bikes?brand=BMW');

            if ($response->getStatusCode() !== 200) {
                $io->error('No se pudo conectar con la API.');
                return Command::FAILURE;
            }

            $data = $response->toArray();
            $motosApi = $data['data'] ?? [];

            $contador = 0;
            $duplicados = 0;

            foreach ($motosApi as $motoData) {
                // Mapeo según el JSON de la API
                $modeloNombre = $motoData['fullModelName'] ?? $motoData['model'] ?? null;
                $idApiExterno = $motoData['id'] ?? null;

                if (!$modeloNombre || !$idApiExterno) continue;

                // --- DOBLE SEGURIDAD PARA EVITAR REPETIDOS ---
                // Buscamos si existe ya por el ID de la API O por el Nombre del modelo
                $existePorId = $this->motoRepo->findOneBy(['id_api' => $idApiExterno]);
                $existePorNombre = $this->motoRepo->findOneBy(['modelo' => $modeloNombre]);

                if (!$existePorId && !$existePorNombre) {
                    $nuevaMoto = new Moto();
                    $nuevaMoto->setMarca('BMW');
                    $nuevaMoto->setModelo($modeloNombre);
                    $nuevaMoto->setIdApi($idApiExterno);

                    // --- GESTIÓN DE IMAGEN ---
                    // El JSON que pasaste tiene un array "images"
                    if (!empty($motoData['images']) && is_array($motoData['images'])) {
                        $nuevaMoto->setUrlImagen($motoData['images'][0]);
                    } else {
                        // Generamos imagen de relleno si no hay
                        $textoImagen = str_replace(' ', '+', $modeloNombre);
                        $nuevaMoto->setUrlImagen("https://placehold.co/600x400?text=BMW+" . $textoImagen);
                    }

                    $this->entityManager->persist($nuevaMoto);
                    $contador++;
                } else {
                    $duplicados++;
                }
            }

            $this->entityManager->flush();

            $io->section('Resumen del proceso:');
            $io->writeln(" - Nuevas motos añadidas: <info>$contador</info>");
            $io->writeln(" - Motos omitidas (ya existían): <comment>$duplicados</comment>");
            $io->success("¡Proceso terminado con éxito!");

        } catch (\Exception $e) {
            $io->error('Error durante la importación: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
