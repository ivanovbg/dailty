<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Service::class);
    }



    public function countCompanyServices($company){
        $count = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(service.id)')
            ->from('App:Service', 'service')
            ->where("service.company = :company")
            ->setParameter("company", $company)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    public function getServices($search_word, $company, $array = false){
        $services = $this->getEntityManager()->createQueryBuilder()
            ->select("service")
            ->from("App:Service", 'service')
            ->where('service.company = :company')
            ->andWhere("service.name LIKE :search")
            ->setParameter("company", $company)
            ->setParameter("search", '%'.$search_word.'%')
            ->orderBy("service.id", 'DESC')
            ->getQuery();

        return $services->getResult();
    }

    public function removeCompanyServices($company){
        $this->getEntityManager()->createQueryBuilder()
            ->delete("App:Service", "service")
            ->where("service.company = :company")
            ->setParameter("company", $company)
            ->getQuery()
            ->execute();
    }


}
