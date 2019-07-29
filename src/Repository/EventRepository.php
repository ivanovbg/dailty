<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    function getEvents($start, $end, $company){
        $events = $this->getEntityManager()->createQueryBuilder()
            ->select('event')
            ->from("App:Event", 'event')
            ->where("event.company = :company")
            ->andWhere("event.start >= :start")
            ->andWhere("event.end <= :end")
            ->setParameter("company", $company)
            ->setParameter("start", $start)
            ->setParameter("end", $end)
            ->getQuery()
            ->getResult();

        return $events;
    }

    function countDayEvents($start, $end, $company){
        $events = $this->getEntityManager()->createQueryBuilder()
            ->select('count(event)')
            ->from("App:Event", 'event')
            ->where("event.company = :company")
            ->andWhere("event.start >= :start")
            ->andWhere("event.end < :end")
            ->setParameter("company", $company)
            ->setParameter("start", $start)
            ->setParameter("end", $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $events;
    }

    function countEventsByCompany($company){
        $events = $this->getEntityManager()->createQueryBuilder()
            ->select('count(event)')
            ->from("App:Event", 'event')
            ->where("event.company = :company")
            ->setParameter("company", $company)
            ->getQuery()
            ->getSingleScalarResult();

        return $events;
    }

    function getAccountTodayEvents($company, $account){
        $date = new \DateTime();
        $date = new \DateTime($date->format('Y-m-d'));
        $events = $this->getEntityManager()->createQueryBuilder()
            ->select("event")
            ->from("App:Event", 'event')
            ->where("event.company = :company")
            ->andWhere("event.provider = :provider")
            ->andWhere("event.start >= :start_date")
            ->andWhere("event.end <= :end_date")
            ->setParameter("company", $company)
            ->setParameter("provider", $account)
            ->setParameter("start_date", $date->format("c"))
            ->setParameter("end_date", $date->modify('+1 days')->format('c'))
            ->getQuery()
            ->getResult();

        return $events;
    }

    function getClientEvents($search, $client){
        return $this->getEntityManager()->createQueryBuilder()
            ->select("event")
            ->from("App:Event", "event")
            ->innerJoin("event.service", "service")
            ->where("event.client = :client")
            ->andWhere("service.name LIKE :search")
            ->setParameter("client", $client)
            ->setParameter("search", '%'.$search.'%')
            ->orderBy("event.start", "DESC")
            ->getQuery()
            ->getResult();
    }

    function getAllCompaniesAccountEvents($account){
        $date = new \DateTime();
        $date = new \DateTime($date->format('Y-m-d'));
        $events = $this->getEntityManager()->createQueryBuilder()
            ->select("event")
            ->from("App:Event", 'event')
            ->andWhere("event.provider = :provider")
            ->andWhere("event.start >= :start_date")
            ->andWhere("event.end <= :end_date")
            ->setParameter("provider", $account)
            ->setParameter("start_date", $date->format("c"))
            ->setParameter("end_date", $date->modify('+1 days')->format('c'))
            ->orderBy("event.start", "ASC")
            ->getQuery()
            ->getResult();

        return $events;
    }

    function deleteEvents($client = false, $provider = false, $company = false, $service = false){
        if(!$client && !$provider && !$company && !$service){
            return false;
        }

        if($client) {
            $this->getEntityManager()->createQueryBuilder()
                ->delete("App:Event", "event")
                ->where("event.client = :client")
                ->setParameter("client", $client)
                ->getQuery()
                ->execute();
        }elseif($company && $provider){
            $this->getEntityManager()->createQueryBuilder()
                ->delete("App:Event", "event")
                ->where("event.provider = :provider")
                ->andWhere("event.company = :company")
                ->setParameter("provider", $provider)
                ->setParameter("company", $company)
                ->getQuery()
                ->execute();
        }elseif($provider && !$company){
            $this->getEntityManager()->createQueryBuilder()
                ->delete("App:Event", "event")
                ->where("event.provider = :provider")
                ->setParameter("provider", $provider)
                ->getQuery()
                ->execute();
        }elseif($company && !$provider){
            $this->getEntityManager()->createQueryBuilder()
                ->delete("App:Event", "event")
                ->where("event.company = :company")
                ->setParameter("company", $company)
                ->getQuery()
                ->execute();
        }elseif($service){
            $this->getEntityManager()->createQueryBuilder()
                ->delete("App:Event", "event")
                ->where("event.service = :service")
                ->setParameter("service", $service)
                ->getQuery()
                ->execute();
        }
        return true;
    }
}
