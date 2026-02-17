<?php

namespace App\Repository;

use App\Entity\Moto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Moto::class);
    }

    public function findTopVotadas(int $limite = 5): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.valoraciones', 'v')
            ->select('m as moto', 'AVG(v.estrellas) as media')
            ->groupBy('m.id')
            ->having('AVG(v.estrellas) > 0')
            ->orderBy('media', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function findMediasPorCategoria(): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.categoria', 'c')
            ->join('m.valoraciones', 'v')
            ->select('c.nombre as categoria', 'AVG(v.estrellas) as media', 'COUNT(v.id) as numVotos')
            ->groupBy('c.id')
            ->orderBy('media', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcula la posición media de las motos en los Tier Lists de la comunidad.
     */
    public function findMediaPosicionesPorCategoria(int $categoriaId): array
    {
        return $this->getEntityManager()->createQuery('
            SELECT m.modelo as modelo,
                   AVG(CASE
                        WHEN ri.tier = \'S\' THEN 1
                        WHEN ri.tier = \'A\' THEN 2
                        WHEN ri.tier = \'B\' THEN 3
                        WHEN ri.tier = \'C\' THEN 4
                        ELSE 5 END) as mediaPosicion,
                   COUNT(ri.id) as totalRankings
            FROM App\Entity\RankingItem ri
            JOIN ri.moto m
            JOIN ri.ranking r
            WHERE r.categoria = :catId
            GROUP BY m.id
            ORDER BY mediaPosicion ASC
        ')->setParameter('catId', $categoriaId)
            ->getResult();
    }

    public function findTopByCategoria(int $categoriaId, int $limite = 5): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.categoria', 'c')
            ->leftJoin('m.valoraciones', 'v')
            ->select('m as moto', 'AVG(v.estrellas) as media')
            ->where('c.id = :catId')
            ->setParameter('catId', $categoriaId)
            ->groupBy('m.id')
            ->having('AVG(v.estrellas) > 0')
            ->orderBy('media', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function existeIdApi(string $idApi): bool
    {
        return $this->createQueryBuilder('m')
                ->select('count(m.id)')
                ->where('m.id_api = :id')
                ->setParameter('id', $idApi)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    /**
     * NUEVO: Este método es vital para el Tier List interactivo del Dashboard.
     * Devuelve todas las motos que tienen votos, su media y el nombre de su categoría.
     */
    public function findMediaTiersTodasLasMotos(): array
    {
        return $this->getEntityManager()->createQuery('
        SELECT m.modelo as modelo,
               c.nombre as categoriaNombre,
               AVG(CASE
                    WHEN ri.tier = \'S\' THEN 1
                    WHEN ri.tier = \'A\' THEN 2
                    WHEN ri.tier = \'B\' THEN 3
                    WHEN ri.tier = \'C\' THEN 4
                    ELSE 5 END) as media,
               COUNT(ri.id) as totalRankings
        FROM App\Entity\RankingItem ri
        JOIN ri.moto m
        JOIN ri.ranking r
        JOIN m.categoria c
        GROUP BY m.id, c.nombre
        ORDER BY media ASC
    ')->getResult();
    }
}
