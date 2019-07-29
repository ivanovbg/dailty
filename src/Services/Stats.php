<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 20.01.19
 * Time: 17:53
 */

namespace App\Services;


use App\Entity\Client;
use App\Entity\Event;

class Stats extends Service
{
    /**
     * @param $company
     * @return false|string
     * @get stats for days - last month
     */
    public function eventsByDay($company){
        $today = date("d");
        $days = cal_days_in_month( 0, date("m"), date("Y"));
        $days = $days>$today ? $today : $days;

        $events = [];


        for($i = 1; $i<=$days; $i++){
           $start = new \DateTime($i.'-'.date("m").'-'.date("Y"));
           $end = new \DateTime(($i).'-'.date("m").'-'.date("Y"));
           $end = $end->modify("+1 days");

           $count_events = $this->entityManager->getRepository(Event::class)->countDayEvents($start, $end, $company->getCompany());

           $events[] = [
               'y' => $i.'-'.date('m'),
               'events' => $count_events
           ];
        }

        return json_encode($events);
    }

    /**
     * @param $company
     * @return false|string
     * stats for client by months - last year
     */
    public function clientsByMonths($company){
        $this_month = date("n", time());
        $this_year = date("Y", time());


        $clients = [];
        for ($i = $this_month; $i > ($this_month - 13); $i--) {

            $month = $i<1 ? $i + 12 : $i;
            $year = $i<1 ? $this_year -1 : $this_year;

            $last_day = cal_days_in_month( 0, $month, $year);
            $first_day = 01;
            $month_name = $this->translateMonths()[date("n", strtotime($first_day.'-'.$month.'-'.$year))];
            $first_day = new \DateTime($first_day.'-'.$month.'-'.$year);
            $last_day = new \DateTime($last_day.'-'.$month.'-'.$year);

            $count = $this->entityManager->getRepository(Client::class)->countClientsByMonth($first_day, $last_day, $company->getCompany());

            $clients[] = ['y' => $month_name.' '.$year, 'clients' => $count];

        }


        return json_encode($clients);
    }

}