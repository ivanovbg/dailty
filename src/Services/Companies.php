<?php
/**
 * Created by PhpStorm.
 * User: Krasimir
 * Date: 14.10.2018 г.
 * Time: 19:39
 */

namespace App\Services;


use App\Entity\Account;
use App\Entity\Company;
use App\Entity\CompanyAccounts;
use App\Entity\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Psr\Container\ContainerInterface;

class Companies extends Service
{

    function generateCompanySlug($name, $lower = true, $number = 0){
        $name = trim($name);
        $symbols = array(" ", " ", " ", " ", "|","!","/", "&");
        $replace = array("-", "-", "-", "-", "","","", "and");
        $name = str_replace($symbols, $replace, $name);
        if($lower){
            $name = mb_strtolower($name, "utf-8");
        }
        $name = preg_replace ( "/[\.?+=%,\";'\:]/", "", $name );
        $name = stripcslashes($name);

        if($lower){
            $name = strtolower($name);
        }

        $find = $this->entityManager->getRepository(Company::class)->findOneBy(['slug' => $name]);

        if($find){
            $number = $number+1;
            $name = $name.'-'.$number;
            $this->generateCompanySlug($name, $lower, $number);
        }

        return $name;
    }


    public function saveCompany(Company $company){
        $company->setDateAdded(new \DateTime());
        $company->setSlug($this->generateCompanySlug($company->getName()));
        $this->saveData($company);

        $account_company = new CompanyAccounts();
        $account_company->setCompany($company);
        $account_company->setAccount($this->container->get(Accounts::class)->getAccount());
        $account_company->setMainAccess(1);
        $account_company->setStaffAccess(1);
        $account_company->setServiceAccess(1);
        $account_company->setClientAccess(1);
        $account_company->setStaffManage(1);
        $account_company->setServiceManage(1);
        $account_company->setClientManage(1);
        $account_company->setEventAccess(1);
        $this->saveData($account_company);
    }

    public function createCompanyAccount($form, $request, $info, $edit = false){

        $email = $form->getData()['email'];


        $fields = ['staff', 'client', 'service', 'event'];
        $fields_pass_manage = ['event'];
        $working_days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];


        $data = [];
        $working_time = [];

        foreach($fields as $field){
            if($request->get($field.'Access')){
                $data[$field.'Access'] = 1;
                if(!in_array($field, $fields_pass_manage)) {
                    $data[$field . 'Manage'] = $request->get($field . 'Manage') ? $request->get($field . 'Manage') : 0;
                }
            }else{
                $data[$field.'Access'] = 0;
                if(!in_array($field, $fields_pass_manage)) {
                    $data[$field . 'Manage'] = 0;
                }
            }
        }

        foreach($working_days as $day){
            if($request->get($day)){
                $working_time[$day] = ['enable' => true, 'start' => $request->get($day."Start"), 'end' => $request->get($day."End")];
            }else{
                $working_time[$day] = ['enable' => false];
            }
        }

        $working_time = json_encode($working_time);

        if(!$edit){
            $account = $this->entityManager->getRepository(Account::class)->findOneBy(['email' => $email]);

            if ($account) {
                $check = $this->entityManager->getRepository(CompanyAccounts::class)->findOneBy(['account' => $account, 'company' => $info->getCompany()]);

                if ($check) {
                    $this->flashbag->add('danger', 'Вече съществува акаунт с такъв емейл адрес, който е добавен към тази компания');
                    $this->redirect($this->router->generate('add_staff', ['slug' => $info->getCompany()->getSlug()]));
                }
            }

            $exists = $account ? true : false;

            if (!$account) {
                $account = new Account();
                $account->setEmail($email);
                $account->setPassword(md5($email . 'default'));
                $account->setDateRegister(new \DateTime());
                $account->setIsActive(0);
                $account->setIsMain(0);
                $account->setName("new_invitation_account");
                $account->setPlan(NULL);

                $this->saveData($account);

                #clear old request(if exist)
                $this->entityManager->getRepository(Request::class)->invitationKeyClearNotExpired($account);

                #generate new request
                $now = new \DateTime();
                $request_key = $this->generateRequestKey();
                $i_request = new Request();
                $i_request->setAccount($account);
                $i_request->setDateCreated($now);
                $i_request->setDateExpired($now->modify("+ 30 days"));
                $i_request->setRequestFrom($this->container->get("App\Services\Accounts")->getAccount());
                $i_request->setRequestKey($request_key);
                $i_request->setType(2);

                $this->saveData($i_request);
            }
        }


        #create new entry for account in company
        $company_accounts = !$edit ? new CompanyAccounts() : $edit;

        foreach($data as $field => $value){
            $field = 'set'.ucfirst($field);
            $company_accounts->$field($value);
        }

        $company_accounts->setWorkingTime($working_time);


        if(!$edit){
            $company_accounts->setCompany($info->getCompany());
            $company_accounts->setMainAccess(0);
            $company_accounts->setAccount($account);
        }
        $this->saveData($company_accounts);

        if(!$edit){
            $url = $exists ? $this->settings->url . '/account/login' : $this->settings->url . '/account/invitation/' . $request_key;

            $this->container->get(Mails::class)->sendInvitationEmail($info->getAccount(), $email, $info->getCompany(), $url, $exists);
        }

        if($edit){
            $message = "Информацията за служителя е обновена успешно";
        }else{
            $message = ($exists) ? 'Служителят е добавен успешно.' : 'Служителят е добавен успешно, но е необходимо потвърждение от него на емейла, който е получил.';
        }

        $this->flashbag->add('success', $message);
        return true;
    }


    public function companyAccess($slug, $access = false, $route = false, $parameters = []){
        $info = $this->entityManager->getRepository(CompanyAccounts::class)
            ->getCompanyBySlugAndAccount($slug, $this->container->get(Accounts::class)->getAccount());

        $forbidden = !$info || ($info && $access && !$info->$access())? true : false;

        if($forbidden){
            if(!$info)
                $message = "Опитвате се да достъпите компания, която не съществува или такава до която Вие нямате достъп";
            else
                $message = "Вие нямате достъп до тази страница. Моля, свържете се с вашият мениджър за повече информация";

            $router = !$info || !$route ? "index_companies" : $route;
            $parameters = !$info || !$parameters ? [] : $parameters;

            $this->flashbag->add('danger', $message);
            $this->redirect($this->router->generate($router, $parameters));
        }

        return $info;
    }

    public function getSelectedCompany(){
        $company = false;

        $account = $this->container->get("App\Services\Accounts")->getAccount();
        $selected_company = $this->getCookie("selected_company");

        if($account && $selected_company){
            $company = $this->entityManager->getRepository(CompanyAccounts::class)->checkAccountByIDandCompanyId($selected_company, $account->getId());
        }

        return $company;
    }

    public function createWorkingTime(){
        $working_days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

        $data = [];
        $working_time = [];

        foreach($working_days as $day){
            if($this->request->get($day)){
                $working_time[$day] = ['enable' => true, 'start' => $this->request->get($day."Start"), 'end' => $this->request->get($day."End")];
            }else{
                $working_time[$day] = ['enable' => false];
            }
        }

        $working_time = json_encode($working_time);
        return $working_time;
    }

}