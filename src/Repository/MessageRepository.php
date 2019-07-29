<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function unReadMessagesCount($account){
        $count = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(ms.id)')
            ->from('App:Message', 'ms')
            ->where("ms.receiver = :account")
            ->andWhere('ms.is_read = 0')
            ->andWhere('ms.receiver_delete = 0')
            ->setParameter("account", $account)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }


    public function getMessages($account, $search = null, $type = "inbox"){
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ms')
            ->from('App:Message', 'ms');

        if($type == "inbox"){
            $query->where("ms.receiver = :account");
            $query->andWhere("ms.receiver_delete = 0");
        } elseif($type=="outbox"){
            $query->where("ms.sender = :account");
            $query->andWhere("ms.sender_delete = 0");
        }

        if($search){
            $query->andWhere("ms.subject LIKE :search");
            $query->orWhere("ms.message LIKE :search");
        }


        $query->setParameter("account", $account);

        if($search){
            $query->setParameter("search", '%'.$search.'%');
        }

        $query->orderBy("ms.date_send", "DESC");

        return $query
            ->getQuery()
            ->getResult();
    }

    function getMessage($account, $message_id){
        $sql = "SELECT ms FROM App:Message ms WHERE ms.id = :message_id AND (ms.receiver = :account OR ms.sender = :account)";
        return $message = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter("message_id", $message_id)
            ->setParameter("account", $account)
            ->getSingleResult();
    }

    function messageForReplay($account, $message_id){
        $message = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("ms")
            ->from("App:Message", "ms")
            ->where("ms.receiver = :receiver")
            ->andWhere("ms.id = :message_id")
            ->setParameter("receiver", $account)
            ->setParameter("message_id", $message_id)
            ->getQuery()
            ->getOneOrNullResult();

        return $message;
    }

    function getReceiver($current_account, $receiver_id){
        return $qb = $this->getEntityManager()->createQueryBuilder()
            ->select("account")
            ->from("App:Account", "account")
            ->where("account != :current_account")
            ->andWhere("account.id = :receiver_id")
            ->setParameter("current_account", $current_account)
            ->setParameter("receiver_id", $receiver_id)
            ->getQuery()
            ->getOneOrNullResult();
    }


    function checkMessageForDelete($account, $message_id){
        $message = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("ms")
            ->from("App:Message", 'ms')
            ->Where("ms.id = :message_id")
            ->andwhere("ms.sender = :account")
            ->orWhere("ms.receiver = :account")
            ->setParameter("account", $account)
            ->setParameter("message_id", $message_id)
            ->getQuery()
            ->getResult();

        if(!$message){
            return false;
        }

        return $message;
    }

    function deleteMessage($account, $message_id){
        $response = false;

        $message = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("ms")
            ->from("App:Message", "ms")
            ->where("ms.receiver = :account")
            ->orWhere("ms.sender = :account")
            ->andWhere("ms.id = :message")
            ->setParameter("account", $account)
            ->setParameter("message", $message_id)
            ->getQuery()
            ->getOneOrNullResult();

        if($message){
            if($message->getSender() == $account)
                $message->setSenderDelete(1);
            if($message->getReceiver() == $account)
                $message->setReceiverDelete(1);

            $this->getEntityManager()->persist($message);
            $this->getEntityManager()->flush();
            $response = true;
        }

        return $response;
    }

    public function getMessagesReceivers($companies, $account) {

        $ids = [];

        foreach($companies as $company){
            $ids[] = $company->getCompany();
        }

        $qb = $this->getEntityManager()->createQueryBuilder();


        $qb->select('ac')
            ->from('App:Account', 'ac')
            ->join('App:CompanyAccounts', 'a')
            ->where('a.account = ac')
            ->andWhere('a.company IN(:ids)')
            ->andWhere('ac != :account')
            ->andWhere('ac.is_active=1')
            ->setParameter("ids", $ids)
            ->setParameter("account", $account);
        return $qb;
    }

    public function deleteAllAccountMessages($account){
        if(!$account->getId()){
            return;
        }


        $this->getEntityManager()->createQueryBuilder()
            ->delete("App:Message", "message")
            ->where("message.sender = :account")
            ->orWhere("message.receiver = :account")
            ->setParameter("account", $account)
            ->getQuery()
            ->execute();
    }
}

