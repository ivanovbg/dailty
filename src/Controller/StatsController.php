<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 20.01.19
 * Time: 17:28
 */

namespace App\Controller;

use App\Services\Stats;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends GlobalController
{

    /**
     * @Route("/stats", name="stats_index", methods={"POST", "GET"})
     */
    public function index(Stats $stats){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $company = $this->company_service->getSelectedCompany();

        if($company) {
            $events_by_days = $stats->eventsByDay($company);
            $clients_by_months = $stats->clientsByMonths($company);
        }

        $paths = $this->service->paths("Статистика");
        $menu = ['view' => 'stats', 'active' => 'stats'];

        return $this->render("/stats/index.html.twig", [
            'paths' => $paths,
            'menu' => $menu,
            'events_by_days' => $company ? $events_by_days : false,
            'clients_by_months' => $company ? $clients_by_months : false,
            'month'=> $this->service->translateMonths()[date('n')],
            'company' => $company

        ]);
    }
}