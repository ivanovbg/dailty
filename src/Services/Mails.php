<?php
/**
 * Created by PhpStorm.
 * User: Krasimir
 * Date: 10/29/2018
 * Time: 19:25
 */
namespace App\Services;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class Mails extends Service
{
    public $mailer;

    function __construct(ContainerInterface $container,  \Twig_Environment $twig, \Swift_Mailer $mailer, RouterInterface $router)
    {
        parent::__construct($container, $twig, $router);
        $this->mailer = $mailer;
    }

    public function sendMail($subject, $send_to, $data, $type, $sender_email = null, $sender_name = null){
        $template = [
            'forgot_password' => "mails/forgot_password.html.twig",
            "changed_password" => "mails/changed_password.html.twig"
        ];

        $sender_email = !$sender_email ? $this->settings->no_reply_mail : $sender_email;
        $sender_name = !$sender_name ? $this->settings->no_reply_name : $sender_name;

        $message = (new \Swift_Message($subject))
            ->setFrom($sender_email, $sender_name)
            ->setTo($send_to)
            ->setBody(
                $this->container->get("twig")->render($template[$type],
                    ['data' => $data, 'subject' => $subject]
                ),
                'text/html'
            )
        ;

        $this->mailer->send($message);
    }


    public function sendEmailToClient($account, $client, $company, $subject, $message){
        $message = (new \Swift_Message($subject))
            ->setFrom($this->settings->no_reply_mail, $this->settings->no_reply_name)
            ->setTo($client->getEmail())
            ->setBody(
                $this->container->get("twig")->render("mails/email_to_client.html.twig",[
                    'account' => $account ,
                    'client' => $client,
                    'subject' => $subject,
                    'message' => $message,
                    'company' => $company
                ]),
                'text/html'
            );
        $this->mailer->send($message);
    }


    public function sendInvitationEmail($from, $to, $company, $url, $exist_account = false){
        $subject = "Показа за присъединяване към ".$company->getName();
        $message = (new \Swift_Message($subject))
            ->setFrom($this->settings->no_reply_mail, $this->settings->no_reply_name)
            ->setTo($to)
            ->setBody(
                $this->container->get("twig")->render("mails/invitation.html.twig",[
                    'from' => $from ,
                    'company' => $company,
                    'exists_account' => $exist_account,
                    'url' => $url,
                    'subject' => $subject
                ]),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendEventMail($event, $edit = false){
        if($event->getClient()->getEmail() && $event->getClient()->getSendNotifications()) {
            $subject = $edit ? "Промяна на час за посещение" : "Нов час за посещение";
            $message = (new \Swift_Message($subject))
                ->setFrom($this->settings->no_reply_mail, $this->settings->no_reply_name)
                ->setTo($event->getClient()->getEmail())
                ->setBody(
                    $this->container->get("twig")->render("mails/event.html.twig", [
                        'event' => $event,
                        'edit' => $edit
                    ]),
                    'text/html'
                );
            $this->mailer->send($message);
        }
    }



}