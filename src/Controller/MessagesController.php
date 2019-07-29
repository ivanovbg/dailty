<?php

namespace App\Controller;

use App\Entity\Message;
use App\Forms\sendMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessagesController extends GlobalController
{
    /**
     * @Route("/mailbox", name="mail_box", methods={"GET"})
     */
    public function mailbox(){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $search_word = $this->request->get("search");

        $messages = $this->get('doctrine')->getRepository(Message::class)->getMessages($this->account, $search_word);
        $messages = $this->service->pages($messages, 10);


        $paths = $this->service->paths("Табло, Mailbox, Входяща кутия");

        return $this->render("messages/mailbox.html.twig",[
            'messages' => $messages,
            'paths' => $paths,
            'active' => 'inbox',
            'box_name' => "Входящи"
        ]);
    }

    /**
     * @Route("/mailbox/outbox", name="mail_box_outbox", methods={"GET"})
     */
    public function mailbox_outbox(){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $search_word = $this->request->get("search");
        $messages = $this->getDoctrine()->getRepository(Message::class)->getMessages($this->account, $search_word, "outbox");
        $messages = $this->service->pages($messages, 10);

        $paths = $this->service->paths("Табло, Mailbox, Изходяща кутия");

        return $this->render("messages/mailbox.html.twig", [
           'messages' => $messages,
           'paths' => $paths,
           'active' => 'outbox',
           'box_name' => "Изходящи"
        ]);
    }

    /**
     * @Route("/mailbox/message/read/{id}", name="message_view", methods={"GET"})
     */
    public function message_view($id){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $message  = $this->getDoctrine()->getRepository(Message::class)->getMessage($this->account, $id);

        if($message->getReceiver() == $this->account){
            $message->setIsRead(1);
            $this->service->saveData($message);
        }

        $paths = $this->service->paths("Табло, Mailbox, Преглед съобщение");

        return $this->render("messages/message.html.twig", [
            'paths' => $paths,
            'message' => $message,
            'read_message' => true,
            'active' => $message->getSender() == $this->account ? "outbox" : "inbox"
        ]);

    }

    /**
     * @Route("/mailbox/message/send/{id}/{receiver_id}", name="send_message", methods={"POST", "GET"}, defaults={"id"=null, "receiver_id" = null})
     */
    public function message_send($id = null, $receiver_id = null, Request $request){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $allowed_receivers = $this->getDoctrine()->getRepository(Message::class)->getMessagesReceivers($this->companies, $this->account);

        if($id || $receiver_id) {
            if ($id)
                $old_message = $this->getDoctrine()->getRepository(Message::class)->messageForReplay($this->account, $id);

            if ($receiver_id)
                $receiver = $this->getDoctrine()->getRepository(Message::class)->getReceiver($this->account, $receiver_id);
        }

        $message = new Message();
        $message->setSender($this->account);
        if($id && $old_message){
            $message->setReceiver($old_message->getSender());
            $message->setSubject("RE:".$old_message->getSubject());
            $message->setIsReply($old_message->getId());
        }elseif($receiver_id && $receiver){
            $message->setReceiver($receiver);
        }

        $message->setIsReply(isset($old_message) && $old_message ? $old_message->getId() : 0);
        $message->setIsRead(0);
        $message->setReceiverDelete(0);
        $message->setSenderDelete(0);

        if(($id && !$old_message) && ($receiver_id && !$receiver)){
            if($receiver_id && ($receiver_id == $this->account->getId())){
                $this->addFlash('danger', 'Не може да изпратите съобщение до себе си.');
            }
            return $this->redirectToRoute("mail_box");
        }

        $form = $this->createForm(sendMessage::class, $message, ['accounts' => $allowed_receivers]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $message->setDateSend(new \DateTime());
            $this->service->saveData($message);

            $this->addFlash('success', 'Съобщението е изпратено успешно!');
            return $this->redirectToRoute("mail_box");
        }

        $paths = $this->service->paths("Табло, Mailbox, Изпращане на съобщение");

        return $this->render("messages/message.html.twig", [
            'paths' => $paths,
            'form' => $form->createView(),
            'is_reply' => isset($id) ? true : false
        ]);
    }

    /**
     * @Route("/mailbox/message/delete/{id}", name="delete_message", methods={"POST", "GET"})
     */
    public function delete_message($id){
        $is_delete = $this->getDoctrine()->getRepository(Message::class)->deleteMessage($this->account, $id);

        if($is_delete) {
            $this->addFlash("success", "Съобщението е изтрито успешно");
        }

        return $this->redirectToRoute("mail_box");
    }


}
