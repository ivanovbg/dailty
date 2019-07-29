<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 12.01.19
 * Time: 10:51
 */

namespace App\Controller;

use App\Entity\Calendar;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends GlobalController
{

    /**
     * @Route("/calendar", name="calendar_index", methods={"GET"})
     */
    public function index(){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $company = $this->company_service->getSelectedCompany();

        


        if($company) {
            $clients = $this->getDoctrine()->getRepository("App:Client")->findBy(['company' => $company->getCompany()]);
            $providers = $this->getDoctrine()->getRepository("App:ServiceProviders")->getServicesProviders($company->getCompany());
            $providers_json = $this->events_service->prepareProviders($providers, $company->getCompany());
            $working_time = $this->events_service->prepareCompanyWorkingTime($company->getCompany()->getWorkingTime());
            $info = $this->company_service->companyAccess($company->getCompany()->getSlug(), false);

        }

        $paths = $this->service->paths("Календар");
        $menu = ['view' => 'calendar', 'active' => 'index'];

        return $this->render("/calendar/index.html.twig", [
            'info' => $company ? $info : false,
            'paths' => $paths,
            'menu' => $menu,
            'clients' => $company ? $clients : false,
            'providers' => $company ? $providers : false,
            'providers_json' => $company ? $providers_json : false,
            'no_company' => $company ? false : true,
            'working_time' => $company ? $working_time : false
        ]);
    }

}