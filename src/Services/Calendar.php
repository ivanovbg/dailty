<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 12.01.19
 * Time: 19:31
 */

namespace App\Services;


use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Clients;
use App\Entity\Event;
use App\Entity\Events;
use App\Entity\ServiceProviders;
use App\Entity\Services;
use Symfony\Component\HttpFoundation\JsonResponse;

class Calendar extends Ajax{

    public function get_events(){
        $response['status'] = true;
        $response['events'] = [];

        if(!($account = $this->container->get(Accounts::class)->getAccount()) || !($company = $this->container->get("App\Services\Companies")->getSelectedCompany())){
            $response['status'] = false;
            $response['msg'] = "Вие не сте логнат!";
        }
        
        $date_start = $this->request->get('start');
        $date_end = $this->request->get('end');

        $events = $this->entityManager->getRepository(Event::class)->getEvents($date_start, $date_end, $company->getCompany());


        if($events){
            foreach($events as $event){
                $response['events'][] = [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'description' => $event->getDescription(),
                    'start' => $event->getStart()->format('c'),
                    'end'  => $event->getEnd()->format('c'),
                    'provider' => $event->getProvider()->getId(),
                    'client' => $event->getClient()->getId(),
                    'client_name' => $event->getClient()->getName(),
                    'service_name' => $event->getService()->getName(),
                    'service' => $event->getService()->getId(),
                    'color' => $this->container->get(\App\Services\Events::class)->statusColor($event->getStatus()),
                    "resourceId" => $event->getProvider()->getId(),
                    'status' => $event->getStatus()
                ];
            }
        }

        return new JsonResponse($response['events']);
    }

    public function get_provider_services(){
        $response['status'] = true;
        $response['services'] = [];

        if(!($account = $this->container->get(Accounts::class)->getAccount()) || !($company = $this->container->get("App\Services\Companies")->getSelectedCompany())){
            $response['status'] = false;
            $response['msg'] = "Вие не сте логнат!";
        }

        $response['services'] = $this->entityManager->getRepository(ServiceProviders::class)->getProviderServices($company->getCompany(), $this->request->get("provider_id"));

        return new JsonResponse($response);
    }

    public function get_clients(){
        $response['status'] = true;

        if(!($account = $this->container->get(Accounts::class)->getAccount()) || !($company = $this->container->get("App\Services\Companies")->getSelectedCompany())){
            $response['status'] = false;
            $response['msg'] = "Вие не сте логнат!";
        }

        $clients = $this->entityManager->getRepository(Client::class)->findBy(['company' => $company->getCompany()]);

        $serializer = $this->container->get('serializer');
        $response['clients'] = $serializer->normalize($clients, 'json');

        return new JsonResponse($response);
    }

    public function save_event(){
        $response['status'] = true;

        if(!($account = $this->container->get(Accounts::class)->getAccount()) || !($company = $this->container->get("App\Services\Companies")->getSelectedCompany())){
            $response['status'] = false;
            $response['msg'] = "Вие не сте логнат!";
        }

        if($response['status']) {
            $fields = ['clients', 'services', 'providers', 'start', 'end'];

            foreach ($fields as $field) {
                if (!$this->request->get($field)) {
                    $response['status'] = false;
                    $response['msg'] = "Задължителните полета не са попълнени, моля опитайте отново!";
                    break;
                }
            }
        }

        if($response['status']){
            $event_id = $this->request->get('id') ? $this->request->get('id') : false;
            $provider_id = $this->request->get('providers');
            $clients  = $this->request->get("clients");
            $services = $this->request->get("services");
            $status = $this->request->get("status");
            $description = $this->request->get("description") ? $this->request->get("description") : "";
            $start_date = new \DateTime($this->request->get('start'));
            $end_date = new \DateTime($this->request->get("end"));
            $company = $company->getCompany();
            $created_by = $account;


            if($event_id && $check_event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $event_id])){
                $event = $check_event;
                $companyy= $this->container->get("App\Services\Companies")->getSelectedCompany();
                if(!$companyy->getEventAccess() && ($event->getProvider()->getId() != $account->getId())){
                    $response['status'] = false;
                    $response['msg'] = "Вие нямате възможност да променяте това събитие!";
                    return new JsonResponse($response);
                }


            }else{
                $event = new Event();
                $event->setDateCreated(new \DateTime());
            }

            if($provider_id && $provider_check= $this->entityManager->getRepository(Account::class)->findOneBy(['id' => $provider_id])){
                $provider = $provider_check;
            }else{
                $response['status'] = false;
                $response['msg'] = "Не сте избрали служител";
            }

            if($response['status'] && $client_check = $this->entityManager->getRepository(Client::class)->findOneBy(['id' => $clients])){
                $client = $client_check;
            }elseif($response['status']){
                $response['status'] = false;
                $response['msg'] = "Не сте избрали клиент";
            }

            if($response['status'] && $service_check = $this->entityManager->getRepository(\App\Entity\Service::class)->findOneBy(['id' => $services])){
                $service = $service_check;
            }elseif($response['status']){
                $response['status'] = false;
                $response['msg'] = "Не сте избрали услуга";
            }


            if($response['status']){

                $event->setStart($start_date);
                $event->setEnd($end_date);
                $event->setDescription($description);
                $event->setCompany($company);
                $event->setProvider($provider);
                $event->setService($service);
                $event->setTitle($client->getName()." | ".$service->getName());
                $event->setCreatedBy($created_by);
                $event->setClient($client);
                $event->setStatus($status);

                $this->container->get(Service::class)->saveData($event);

                $response['event'] = [
                  'title' => $event->getTitle(),
                  'description' => $event->getDescription(),
                  'id' => $event->getId(),
                  'provider'=> $provider->getId(),
                  'client' => $client->getId(),
                  'service' => $service->getId(),
                  'service_name' => $service->getName(),
                  'client_name' => $client->getName(),
                  'resourceId' => $provider->getId(),
                  'status' => $event->getStatus(),
                  'color' => $this->container->get(\App\Services\Events::class)->statusColor($event->getStatus())
                ];


                if(!isset($check_event) || !$check_event) {
                    $this->container->get(Mails::class)->sendEventMail($event, false);
                }


            }
        }

        return new JsonResponse($response);


    }

    #delete event
    public function delete_event(){
        $response['status'] = true;

        if(!($account = $this->container->get(Accounts::class)->getAccount()) || !($company = $this->container->get("App\Services\Companies")->getSelectedCompany())){
            $response['status'] = false;
            $response['msg'] = "Вие не сте логнат!";
        }

        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $this->request->get('id')]);

        if($event && $company->getEventAccess()){
            $this->container->get(Service::class)->deleteData($event);
        }else{
            $response['status'] = false;
            $response['msg'] = "Грешка! Моля, опитайте отново!";
        }

        return new JsonResponse($response);
    }

    #update event
    public function update_event(){
        $response['status'] = true;

        if(!($account = $this->container->get(Accounts::class)->getAccount()) || !($company = $this->container->get("App\Services\Companies")->getSelectedCompany())){
            $response['status'] = false;
            $response['msg'] = "Вие не сте логнат!";
        }

        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $this->request->get('id')]);

        if($event && $company->getEventAccess()){
            $start = new \DateTime($this->request->get("start"));
            $end = new \DateTime($this->request->get("end"));

            $event->setStart($start);
            $event->setEnd($end);
            $this->container->get(Service::class)->saveData($event);

            #send message to client
            $this->container->get("App\Services\Mails")->sendEventMail($event, true);
        }else{
            $response['status'] = false;
            $response['msg'] = "Грешка! Моля, опитайте отново!";
        }

        return new JsonResponse($response);
    }


}