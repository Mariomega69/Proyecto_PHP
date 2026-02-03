<?php

namespace App\Command;

use App\Entity\Moto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:cargar-motos', description: 'Carga 5 motos BMW de prueba')]
class CargarMotosCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Lista de motos reales para tu proyecto
        $motosParaCargar = [
            ['modelo' => 'R 1250 GS', 'img' => 'https://mcn-images.gs-static.it/wp-content/uploads/2018/09/BMW-R1250GS-2019-01.jpg', 'api_id' => 'BMW001'],
            ['modelo' => 'S 1000 RR', 'img' => 'https://motos-bayerische.com/wp-content/uploads/2023/02/bmw-s1000rr-2023.jpg', 'api_id' => 'BMW002'],
            ['modelo' => 'F 850 GS Adventure', 'img' => 'https://motos-bayerische.com/wp-content/uploads/2021/04/F-850-GS-Adventure.jpg', 'api_id' => 'BMW003'],
            ['modelo' => 'R NineT', 'img' => 'https://www.bmw-motorrad.es/content/dam/bmwmotorradnsc/marketES/common/images/models/heritage/rninet/2023/ninet-p0751.jpg', 'api_id' => 'BMW004'],
            ['modelo' => 'M 1000 R', 'img' => 'https://p.vitalmtb.com/photos/users/2/photos/142353/s1600_BMW_M_1000_R.jpg', 'api_id' => 'BMW005'],
        ];

        foreach ($motosParaCargar as $m) {
            // Solo la añadimos si no existe ya
            $existe = $this->entityManager->getRepository(Moto::class)->findOneBy(['id_api' => $m['api_id']]);

            if (!$existe) {
                $moto = new Moto();
                $moto->setMarca('BMW');
                $moto->setModelo($m['modelo']);
                $moto->setUrlImagen($m['img']);
                $moto->setIdApi($m['api_id']);

                $this->entityManager->persist($moto);
            }
        }

        $this->entityManager->flush();
        $io->success('¡Las 5 motos BMW han sido cargadas en la base de datos!');

        return Command::SUCCESS;
    }
}
