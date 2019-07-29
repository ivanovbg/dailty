<?php
/**
 * Created by PhpStorm.
 * User: Krasimir
 * Date: 24.11.2018 г.
 * Time: 21:22
 */

namespace App\Controller;


use App\Services\Accounts;
use App\Services\Companies;
use App\Services\Events;
use App\Services\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;


class GlobalController extends AbstractController
{
    public $container, $account, $service, $company_service, $account_service, $events_service, $account_companies, $request, $translator, $selected_company;

    function __construct(Service $service, Accounts $accounts, Companies $companies, Events $events, TranslatorInterface $translator)
    {
        $this->service = $service;
        $this->account_service = $accounts;
        $this->company_service = $companies;
        $this->events_service = $events;
        $this->request = $this->service->request;
        $this->translator = $translator;

        $this->account = $this->account_service->getAccount();
        $this->companies = $this->account ? $this->account->getCompanyAccounts() : false;
        $this->selected_company = $this->company_service->getSelectedCompany();

        $this->assignGlobals();

    }

    private function assignGlobals(){
        $this->service->assignGlobal("account", $this->account);
        $this->service->assignGlobal("companies", $this->companies);
        $this->service->assignGlobal("selected_company", $this->selected_company);
        $this->service->assignGlobal("unread_messages", $this->account_service->unReadMessages());
        $this->service->assignGlobal("days", $this->service->settings->days);
    }
}