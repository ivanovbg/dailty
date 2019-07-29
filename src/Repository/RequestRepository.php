<?php

namespace App\Repository;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Request::class);
    }

    function forgotPasswordRequest($key){
        $request = $this->getEntityManager()->createQueryBuilder()
            ->select("request")
            ->from("App:Request", "request")
            ->where("request.request_key = :key")
            ->andWhere("request.date_expired >= :expired")
            ->andWhere("request.type = 1")
            ->setParameter("key", $key)
            ->setParameter("expired", new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();

        return $request;
    }

    function invitationRequest($key){
        $request = $this->getEntityManager()->createQueryBuilder()
            ->select('request')
            ->from("App:Request", "request")
            ->where("request.request_key = :key")
            ->andWhere("request.date_expired >= :expired")
            ->andWhere("request.type = 2")
            ->setParameter("key", $key)
            ->setParameter("expired", new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();

        return $request;
    }

    function forgotPasswordClearNotExpired($account){
        $clear = $this->getEntityManager()->createQueryBuilder()
            ->delete("App:Request", "request")
            ->where("request.account = :account")
            ->andWhere("request.type = 1")
            ->andWhere("request.date_expired >= :expired")
            ->setParameter("account", $account)
            ->setParameter("expired", new \DateTime())
            ->getQuery()
            ->execute();
    }

    function invitationKeyClearNotExpired($account){
        $clear = $this->getEntityManager()->createQueryBuilder()
            ->delete("App:Request", "request")
            ->where("request.account = :account")
            ->andWhere("request.type = 2")
            ->andWhere("request.date_expired >= :expired")
            ->setParameter("account", $account)
            ->setParameter("expired", new \DateTime())
            ->getQuery()
            ->execute();
    }


    function clearRequest($account){
        $this->getEntityManager()->createQueryBuilder()
            ->delete("App:Request", "request")
            ->where("request.account = :account")
            ->orWhere("request.request_from = :account")
            ->setParameter("account", $account)
            ->getQuery()
            ->execute();
    }


}
