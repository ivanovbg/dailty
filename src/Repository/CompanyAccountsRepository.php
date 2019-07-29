<?php

namespace App\Repository;

use App\Entity\CompanyAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompanyAccounts|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyAccounts|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyAccounts[]    findAll()
 * @method CompanyAccounts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyAccountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompanyAccounts::class);
    }

    public function getCompanyBySlugAndAccount($slug, $account){
        return $this->createQueryBuilder('company')
            ->innerJoin('company.company', 'c')
            ->innerJoin('company.account', 'a')
            ->addSelect('c')
            ->andWhere('c.slug = :slug')
            ->andWhere('a = :account')
            ->setParameter('slug', $slug)
            ->setParameter('account', $account)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function checkAccountByIDandCompanyId($company_id, $account_id){
        return $this->createQueryBuilder('company')
            ->innerJoin('company.company', 'c')
            ->innerJoin('company.account', 'a')
            ->addSelect('a')
            ->addSelect('c')
            ->andWhere('c.id = :company_id')
            ->andWhere('a.id = :account_id')
            ->setParameter('company_id', $company_id)
            ->setParameter('account_id', $account_id)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function getAccounts($search_word, $company){
        $accounts = $this->getEntityManager()->createQueryBuilder()
            ->select('acc')
            ->from("App:CompanyAccounts", 'acc')
            ->innerJoin("acc.account", "account")
            ->where('acc.company = :company')
            ->andWhere("account = acc.account")
            ->andWhere("account.name LIKE :search")
            ->andWhere("account.is_active = 1")
            ->setParameter("company", $company)
            ->setParameter("search", '%'.$search_word.'%')
            ->orderBy("acc.id", 'DESC')
            ->getQuery()->getResult();

        return $accounts;
    }

//    public function getCompanyBySlugAndAccount($slug, $account){
//
//        $company = $this->getEntityManager()->getRepository(Companies::class)->findOneBy(['slug' => $slug]);
//
//
//        if(!$company || !($access = $this->getEntityManager()->getRepository(AccountsCompanies::class)->findOneBy(['account' => $account, "company" => $company]))){
//            $company = false;
//        }
//
//        return $company;
//    }

    public function getCompanyAccounts($company, $limit = false){
        $sql = "SELECT st FROM App:CompanyAccounts st WHERE st.company= :company";


        if($limit){
            $staff = $this->getEntityManager()->createQuery($sql)->setParameter("company", $company)->setMaxResults($limit);
        } else {
            $staff = $this->getEntityManager()->createQuery($sql)->setParameter("company", $company);
        }

        return $staff;
    }

    public function countCompanyAccounts($company){
        $count = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(st.id)')
            ->from('App:CompanyAccounts', 'st')
            ->innerJoin("st.account", 'account')
            ->Where("account.is_active = 1")
            ->andWhere("st.company = :company")
            ->setParameter("company", $company)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    public function getWorkingTime($company, $account){
        return $this->getEntityManager()->createQueryBuilder()
            ->select("c.working_time")
            ->from("App:CompanyAccounts", "c")
            ->where("c.company = :company")
            ->andWhere("c.account = :account")
            ->setParameter("company", $company)
            ->setParameter("account", $account)
            ->getQuery()
            ->getSingleResult();

    }

}
