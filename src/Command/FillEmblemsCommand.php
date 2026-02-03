<?php

namespace App\Command;

use App\Repository\MotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fill-emblems',
    description: 'Asigna imágenes automáticas a todas las motos que no tienen una.',
)]
class FillEmblemsCommand extends Command
{
    private $motoRepository;
    private $entityManager;

    // Inyectamos el repositorio y el manager a través del constructor
    public function __construct(MotoRepository $motoRepository, EntityManagerInterface $entityManager)
    {
        $this->motoRepository = $motoRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. Buscamos todas las motos en la base de datos
        $motos = $this->motoRepository->findAll();

        if (!$motos) {
            $io->warning('No se encontraron motos en la base de datos.');
            return Command::FAILURE;
        }

        $io->progressStart(count($motos));

        foreach ($motos as $moto) {
            // 2. Generamos una URL dinámica
            // loremflickr.com nos permite buscar por etiquetas (motorcycle, marca)
            // Añadimos un número aleatorio al final para que no repita siempre la misma imagen
            $marca = strtolower($moto->getMarca());
            $urlImagen = "https://loremflickr.com/640/480/motorcycle," . $marca . "?lock=" . rand(1, 1000);

            // 3. Asignamos la URL al campo imagen (Asegúrate de que este campo existe en tu Entidad)
            $moto->setImagen($urlImagen);

            $io->progressAdvance();
        }

        // 4. Guardamos los cambios en la base de datos
        $this->entityManager->flush();

        $io->progressFinish();
        $io->success('¡Listo! Se han actualizado ' . count($motos) . ' motos con nuevas imágenes.');

        return Command::SUCCESS;
    }
}
