<?php
namespace  App\Services;


use App\Entity\Company;
use App\Entity\CompanyAccounts;
use Symfony\Component\HttpFoundation\JsonResponse;

class Events extends Service{

    public function prepareProviders($providers, $company){
        $providers_json = [];

        if($providers){
            foreach($providers as $provider){
                $working_time = $this->prepareWorkingTime($provider, $company);
                $providers_json[] = ['id' => $provider->getId(), 'title' => $provider->getName(),
                    'businessHours' => $working_time
                ];
            }
        }

        return new JsonResponse($providers_json);
    }

    public function prepareWorkingTime($account, $company){
        $working_time = $this->entityManager->getRepository(CompanyAccounts::class)->getWorkingTime($company, $account);
        $working_time = json_decode($working_time['working_time'], true);
        $working_time_new  = [];
        $i = 1;
        $day_without_time = 0;
        if($working_time) {
            foreach ($working_time as $day) {
                if ($day['enable']) {
                    $working_time_new[] = [
                        'dow' => [$i == 7 ? 0 : $i], 'start' => $day['start'], 'end' => $day['end']
                    ];
                } else {
                    $day_without_time += 1;
                }
                $i++;
            }
        }

        if($day_without_time==7 || !$working_time){
            $working_time = $company->getWorkingTime();
            $working_time = json_decode($working_time, true);
            $i = 1;
            if($working_time) {
                foreach ($working_time as $day) {
                    if ($day['enable']) {
                        $working_time_new[] = [
                            'dow' => [$i == 7 ? 0 : $i], 'start' => $day['start'], 'end' => $day['end']
                        ];
                    }
                    $i++;
                }
            }
        }

        return $working_time_new;
    }


    public function prepareCompanyWorkingTime($time){
        $working_time = [];

        if($time){
            $time = json_decode($time, true);
            $i = 1;
            foreach($time as $day){
                if($day['enable']){
                    $working_time[] = [
                        'dow' => [$i == 7 ? 0 : $i], 'start' => $day['start'], 'end' => $day['end']
                    ];
                }
                $i++;
            }
        }

        return new JsonResponse($working_time);
    }

    public function statusColor($status){
        $default_color = ['#88ade8'];
        $colors = [
          '0' => '#88ade8',
          '1' => '#ff9823',
          '2' => '#2ab215',
          '3' => '#af052d'
        ];

        return array_key_exists($status, $colors) ? $colors[$status] : $default_color;
    }
}

