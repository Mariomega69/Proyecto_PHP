<?php

namespace App\Command;

use App\Entity\Moto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:importar-motos',
    description: 'Importa todas las motos de BMW desde la API a la base de datos local',
)]
class ImportarMotosCommand extends Command
{
    private $entityManager;
    private $client;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $client)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando importación de motos BMW...');

        try {
            $response = $this->client->request('GET', 'https://www.bikespecs.org/api/v1/brands/BMW');

            if ($response->getStatusCode() !== 200) {
                $io->error('No se pudo conectar con la API.');
                return Command::FAILURE;
            }

            $data = $response->toArray();
            $motosApi = $data['data'] ?? [];

            $contador = 0;
            foreach ($motosApi as $motoData) {
                $modeloNombre = $motoData['model'] ?? $motoData['modelo'] ?? null;
                $idApiExterno = $motoData['id'] ?? ($contador + 500); // Evitamos duplicar IDs manuales

                if (!$modeloNombre) continue;

                $existe = $this->entityManager->getRepository(Moto::class)->findOneBy(['modelo' => $modeloNombre]);

                if (!$existe) {
                    $nuevaMoto = new Moto();
                    $nuevaMoto->setMarca('BMW');
                    $nuevaMoto->setModelo($modeloNombre);

                    // --- MEJORA DE IMAGEN ---
                    // Si viene imagen de la API la usamos, si no, creamos una URL limpia sin espacios
                    if (!empty($motoData['image'])) {
                        $nuevaMoto->setUrlImagen($motoData['image']);
                    } else {
                        // Reemplazamos espacios por '+' para que la URL sea válida
                        $textoImagen = str_replace(' ', '+', $modeloNombre);
                        $nuevaMoto->setUrlImagen("https://placehold.co/600x400?text=BMW+" . $textoImagen);
                    }

                    $nuevaMoto->setIdApi($idApiExterno);

                    $this->entityManager->persist($nuevaMoto);
                    $contador++;
                }
            }

            $this->entityManager->flush();
            $io->success("¡Proceso terminado! Se han añadido $contador motos nuevas.");

        } catch (\Exception $e) {
            $io->error('Error durante la importación: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
