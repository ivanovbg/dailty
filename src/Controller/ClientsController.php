<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 21.12.18
 * Time: 11:22
 */

namespace App\Controller;


use App\Entity\AccountsCompanies;
use App\Entity\Client;
use App\Entity\Clients;
use App\Entity\CompanyClients;
use App\Entity\Event;
use App\Forms\AddClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientsController extends GlobalController
{
    /**
     * @Route("/company/{slug}/clients", name="company_clients", methods={"POST", "GET"})
     */
    public function clients($slug){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        #check user access(if not exist redirect it)
        $info = $this->company_service->companyAccess($slug, 'getClientAccess');

        $company = $info->getCompany();

        $search_word = $this->request->get("search");

        $clients = $this->getDoctrine()->getRepository(Client::class)->getClients($search_word, $company);
        $clients = $this->service->pages($clients, 2);

        $path = $company->getName().", Клиенти";
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_clients'];


        return $this->render("/clients/clients.html.twig", [
            'company' => $company,
            'info' => $info,
            'paths' => $paths,
            'menu' => $menu,
            'slug' => $slug,
            'clients' => $clients
        ]);
    }


    /**
     * @Route("/company/{slug}/clients/add/{id}", defaults={"id" = null}, name="client_add", methods={"POST", "GET"})
     */
    public function add_client($slug, $id, Request $request){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getClientManage', 'company_clients', ['slug' => $slug]);

        $company = $info->getCompany();


        $exist_client = false;

        if($id && $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['id' => $id])){
            $exist_client = $client;
        }

        $client = $exist_client ? $exist_client : new Client();

        $form = $this->createForm(AddClient::class, $client);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            if(!$exist_client){
                $client->setDateAdded(new \DateTime());
                $client->setCompany($company);
            }

            $this->service->saveData($client);

            $this->addFlash("success", $exist_client ? 'Клиентският профил е редактиран успешно' : 'Клиентът е добавен успешно!');
            return $this->redirectToRoute('company_clients', ['slug' => $slug]);
        }


        $path = $company->getName().", Клиенти, ";
        $path .= $exist_client ? "Редактиране на клиент" : "Добавяне на клиент";
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_add_client'];

        return $this->render("/clients/add_client.html.twig", [
           'paths' => $paths,
           'slug' => $slug,
           'menu' => $menu,
           'form' => $form->createView()
        ]);

    }

    /**
     * @param $slug
     * @param $client_id
     * @Route("/company/{slug}/clients/remove/{client_id}", name="remove_client", methods={"GET", "POST"})
     */
    public function remove_client($slug, $client_id){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getClientManage', 'company_clients', ['slug' => $slug]);

        $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['id' => $client_id, 'company' => $info->getCompany()]);

        if(!$client){
            $this->addFlash('danger', 'Клиентът, които се опитвате да изтриете не съществува');
            return $this->redirectToRoute('company_clients', ['slug' => $slug]);
        }

        $delete_events = $this->getDoctrine()->getRepository(Event::class)->deleteEvents($client);

        $this->service->deleteData($client);


        $this->addFlash('success', 'Клиентът е изтрит успешно!');

        return $this->redirectToRoute('company_clients', ['slug' => $slug]);

    }

    /**
     * @Route("/company/{slug}/clients/history/{id}", name="client_history", methods={"GET", "POST"})
     */
    public function client_history($slug, $id){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getClientAccess', 'company_clients', ['slug' => $slug]);



        $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['company' => $info->getCompany(),
        'id' => $id]);

        if(!$client){
            $this->addFlash("danger", "Клиентът, който се опитвате да достъпите не съществува!");
            return $this->redirectToRoute("company_clients", ['slug' => $slug]);
        }

        $search_word = $this->request->get("search");

        $events = $this->getDoctrine()->getRepository("App:Event")->getClientEvents($search_word, $client);
        $events = $this->service->pages($events);


        $path = $info->getCompany()->getName().", Клиенти, ".$client->getName();
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_client_history'];

        return $this->render("/clients/history.html.twig", [
            'paths' => $paths,
            'slug' => $slug,
            'menu' => $menu,
            'events' => $events
        ]);

    }

}