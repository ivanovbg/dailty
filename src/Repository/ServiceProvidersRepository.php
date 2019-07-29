<?php

namespace App\Repository;

use App\Entity\ServiceProviders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ServiceProviders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceProviders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceProviders[]    findAll()
 * @method ServiceProviders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceProvidersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ServiceProviders::class);
    }

    function getAccountForAddtoServicers($company, $providers){
        $accounts = $this->getEntityManager()->getRepository("App:CompanyAccounts")->findBy(['company' => $company]);

        $pass = [];
        if($providers){
            foreach ($providers as $provider){
                $pass[] = $provider->getAccount()->getId();
            }
        }

        $ids = [];
        if($accounts){
            foreach($accounts as $account){
                if(in_array($account->getAccount()->getId(), $pass)){
                    continue;
                }

                $ids[] = $account->getAccount()->getId();
            }
        }




        $accounts = $this->getEntityManager()->createQueryBuilder()
            ->select('ac')
            ->from('App:Account', 'ac')
            ->join('App:CompanyAccounts', 'a')
            ->where('ac IN(:ids)')
            ->andWhere('ac.is_active = 1')
            ->andWhere('a.company = :company')
            ->setParameter("ids", $ids)
            ->setParameter("company", $company)
            ->getQuery()
            ->getResult();


        return $accounts;

    }

    public function getProviders($service, $search_word){
        $services = $this->getEntityManager()->createQueryBuilder()
            ->select("account")
            ->select("provider")
            ->from("App:ServiceProviders", 'provider')
            ->innerJoin("provider.account", "account")
            ->where('provider.service = :service')
            ->andWhere("account = provider.account")
            ->andWhere("account.name LIKE :search")
            ->setParameter("service", $service)
            ->setParameter("search", '%'.$search_word.'%')
            ->orderBy("provider.id", 'DESC')
            ->getQuery()->getResult();

        return $services;
    }

    public function getServicesProviders($company){
        return $this->getEntityManager()->createQueryBuilder()
            ->select("account")
            ->from("App:ServiceProviders", "service_provider")
            ->innerJoin("service_provider.service", "service")
            ->innerJoin("App:Account", "account")
            ->innerJoin("App:CompanyAccounts", "company_account")
            ->where("service_provider.service = service")
            ->andWhere("service.company = :company")
            ->andWhere("account = service_provider.account")
            ->setParameter("company", $company)
            ->getQuery()
            ->getResult();
    }

    public function getProviderServices($company, $provider_id)
    {

        $services = $this->getEntityManager()->createQueryBuilder()
            ->select("service")
            ->from("App:ServiceProviders", "service_provider")
            ->innerJoin("App:Service", "service")
            ->innerJoin("App:Account", "account")
            ->where("service.company = :company")
            ->andWhere("service_provider.service = service")
            ->andWhere("service_provider.account = account")
            ->andWhere("account.id = :provider_id")
            ->setParameter("company", $company)
            ->setParameter("provider_id", $provider_id)
            ->getQuery()
            ->getArrayResult();


        return $services;
    }


    public function deleteProviders($service){
        $this->getEntityManager()->createQueryBuilder()
            ->delete("App:ServiceProviders", "provider")
            ->where("provider.service = :service")
            ->setParameter("service", $service)
            ->getQuery()
            ->execute();
    }

    public function deleteProviderByAccount($account, $company){
        $this->getEntityManager()->createQueryBuilder()
            ->delete("App:ServiceProviders", "provider")
            ->innerJoin("provider.service", "service")
            ->where("provider.account = :account")
            ->andWhere("service.company = :company")
            ->setParameter("account", $account)
            ->setParameter("company", $company)
            ->getQuery()
            ->execute();
    }

    public function removeAllProviders($company){
        $services = $this->getEntityManager()->getRepository("App:Service")->findBy(['company' => $company]);

        if($services){
            foreach($services as $service){
                $this->deleteProviders($service);
            }
        }

        return true;
    }


}
