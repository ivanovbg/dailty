<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function countCompanyClients($company){
        $count = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(client.id)')
            ->from('App:Client', 'client')
            ->where("client.company = :company")
            ->setParameter("company", $company)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    public function getClients($search_word, $company){
        $clients = $this->getEntityManager()->createQueryBuilder()
            ->select("client")
            ->from("App:Client", 'client')
            ->where('client.company = :company')
            ->andWhere("client.name LIKE :search")
            ->setParameter("company", $company)
            ->setParameter("search", '%'.$search_word.'%')
            ->orderBy("client.id", 'DESC')
            ->getQuery()->getResult();

        return $clients;
    }

    public function getClientsArray($company){
        return $this->getEntityManager()->createQueryBuilder()
            ->select("client")
            ->from("App:Client", "client")
            ->where("client.company = :company")
            ->setParameter("company", $company)
            ->getQuery()->getArrayResult();
    }

    public function removeClients($company){
        $this->getEntityManager()->createQueryBuilder()
            ->delete("App:Client", "client")
            ->where("client.company = :company")
            ->setParameter("company", $company)
            ->getQuery()
            ->execute();
    }

    public function countClientsByMonth($start, $end, $company){
        $clients = $this->getEntityManager()->createQueryBuilder()
            ->select('count(client)')
            ->from("App:Client", 'client')
            ->where("client.company = :company")
            ->andWhere("client.date_added >= :start")
            ->andWhere("client.date_added < :end")
            ->setParameter("company", $company)
            ->setParameter("start", $start)
            ->setParameter("end", $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $clients;
    }
}
