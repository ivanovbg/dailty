<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 19.12.18
 * Time: 13:46
 */

namespace App\Controller;
use App\Entity\AccountsCompanies;
use App\Entity\CompanyServices;
use App\Entity\Service;
use App\Entity\ServiceProviders;
use App\Entity\Services;
use App\Entity\ServicesProviders;
use App\Forms\AddService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ServicesController extends GlobalController
{
    /**
     * @Route("/company/{slug}/services", name="company_services", methods={"GET"})
     */

    public function company_services($slug){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getServiceAccess');

        $company = $info->getCompany();

        $search_word = $this->request->get("search");

        $services = $this->getDoctrine()->getRepository(Service::class)->getServices($search_word, $company);
        $services = $this->service->pages($services, 10);

        $path = $company->getName().", Услуги";
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_services'];

        return $this->render("services/services.html.twig", [
            'paths' => $paths,
            'menu' => $menu,
            'slug' => $company->getSlug(),
            'services' => $services,
            'info' => $info
        ]);
    }

    /**
     * @Route("/company/{slug}/services/add/{id}", defaults={"id" = null}, name="company_add_service", methods={"GET", "POST"})
     */
    public function add_service($slug, $id, Request $request){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getServiceManage', 'company_services', ['slug' => $slug]);

        $service = $id ? $this->getDoctrine()->getRepository("App:Service")->findOneBy(['id' =>$id]) : new Service();

        $company = $info->getCompany();


        $form = $this->createForm(AddService::class, $service);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$id){
                $service->setCompany($company);
            }

            $this->service->saveData($service);
            $this->addFlash("success", $id ? 'Успешно обновихте услугата':'Успешно добавихте услугата');
            return $this->redirectToRoute("company_services", ['slug' => $slug]);
        }


        $path = $company->getName().", Услуги, Добавяне на услуга";
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_services'];

        return $this->render("services/service_add.html.twig", [
            'paths' => $paths,
            'menu' => $menu,
            'slug' => $company->getSlug(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("company/{slug}/services/remove/{service_id}", name="remove_service", methods={"POST", "GET"})
     */
    public function remove_service($slug, $service_id){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getServiceManage', 'company_services', ['slug' => $slug]);

        $service = $this->getDoctrine()->getRepository(Service::class)->findOneBy(['id' => $service_id, 'company' => $info->getCompany()]);

        if(!$service){
            $this->addFlash('danger', 'Упссс! Опитвате се изтриете услуга, която не съществува');
            return $this->redirectToRoute("company_services", ['slug' => $slug]);
        }

        #delete events
        $this->getDoctrine()->getRepository("App:Event")->deleteEvents(false, false, false, $service);

        #delete providers
        $this->getDoctrine()->getRepository("App:ServiceProviders")->deleteProviders($service);

        #delete service
        $this->service->deleteData($service); //delete service from company

        return $this->redirectToRoute("company_services", ['slug' => $slug]);
    }

    /**
     * @Route("company/{slug}/services/{id}/providers", name="service_providers", methods={"GET"})
     */
    public function service_providers($slug, $id){
        if(!$this->account){
            $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getServiceAccess');

        $service = $this->getDoctrine()->getRepository(Service::class)->findOneBy(['id' => $id, 'company' => $info->getCompany()]);


        #check company exists
        if(!$service) {
            $this->addFlash('danger', 'Упссссс! Грешка! Нещо се обърка, моля опитайте отново!');
            return $this->redirectToRoute("index_companies");
        }

        $company = $info->getCompany();


        $search_word = $this->request->get("search");
        $providers = $this->getDoctrine()->getRepository(ServiceProviders::class)->getProviders($service, $search_word);
        $providers = $this->service->pages($providers, 10);


        $path = $company->getName().", Услуги, ".$service->getName().", Служители";
        $paths = $this->service->paths("Табло, Компания, ".$path);

        $menu = ['view' => "companies", 'active' => 'company_services'];

        $add_providers = $this->getDoctrine()->getRepository(ServiceProviders::class)->getAccountForAddtoServicers($company, $providers);


        return $this->render("services/service_providers.html.twig", [
            'info' => $info,
            'slug' => $slug,
            'paths' => $paths,
            'providers' => $providers,
            'add_providers' => $add_providers,
            'service' => $service,
            'company' => $company,
            'menu' => $menu
        ]);
    }

    /**
     * @Route("company/{slug}/services/{service_id}/providers/remove/{provider_id}", name="remove_provider", methods={"POST", "GET"})
     */
    public function remove_provider($slug, $service_id, $provider_id){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getServiceManage', 'service_providers', ['slug' => $slug, 'id' => $service_id]);

        $service = $this->getDoctrine()->getRepository(Service::class)->findOneBy(['id' => $service_id, 'company' => $info->getCompany()]);

        if(!$service){
            $this->addFlash('danger', 'Упссс! Услугата не съществува!');
            return $this->redirectToRoute("company_services", ['slug' => $slug]);
        }

        $provider = $this->getDoctrine()->getRepository(ServiceProviders::class)->findOneBy(['service' => $service, 'id' => $provider_id]);

        if(!$provider){
             $this->addFlash('danger', 'Упссс! Опитвате се изтриете провайдър, която не съществува');
             return $this->redirectToRoute("service_providers", ['slug' => $slug, 'id' => $service->getId()]);
        }

        $this->service->deleteData($provider);
        $this->addFlash('success', 'Провайдърът е изтрит успешно');
        return $this->redirectToRoute("service_providers", ['slug' => $slug, 'id' => $service->getId()]);
    }

}