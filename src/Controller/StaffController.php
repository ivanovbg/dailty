<?php
/**
 * Created by PhpStorm.
 * User: krasimir
 * Date: 06.01.19
 * Time: 13:37
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\CompanyAccounts;
use App\Entity\Event;
use App\Entity\Message;
use App\Entity\ServiceProviders;
use App\Forms\AddStaff;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StaffController extends GlobalController
{
    /**
     * @Route("/company/{slug}/staff", name="company_staff", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function company_staff($slug){
        if (!$this->account) {
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getStaffAccess', false);

        $company = $info->getCompany();

        $search_word = $this->request->get("search");

        $staffs = $this->getDoctrine()->getRepository("App:CompanyAccounts")->getAccounts($search_word, $company);
        $staffs = $this->service->pages($staffs, 10);


        $path = $company->getName() .", Служители";
        $paths = $this->service->paths("Табло, Компания, ". $path);
        $menu = ['view' => 'companies', 'active' => 'company_staff'];

        return $this->render('staff/staff.html.twig', [
            'paths' => $paths,
            'menu' => $menu,
            'staffs' => $staffs,
            'company' => $company,
            'slug' => $company->getSlug(),
            'info' => $info

        ]);
    }

    /**
     * @Route("/company/{slug}/staff/add", name="add_staff", methods={"GET", "POST"})
     */
    public function add_staff($slug){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getStaffManage', 'company_staff', ['slug' => $slug]);

        $form = $this->createForm(AddStaff::class);
        $form->handleRequest($this->request);
        if($form->isSubmitted()){
            $create = $this->company_service->createCompanyAccount($form, $this->request, $info);
            return $this->redirectToRoute("company_staff", ['slug' => $slug]);
        }

        $company = $info->getCompany();

        $path = $company->getName() .", Добавяне на служител";
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_add_staff'];

        return $this->render("staff/add_staff.html.twig", [
            'paths' => $paths,
            'menu' => $menu,
            'slug' => $company->getSlug(),
            'form' => $form->createView(),
            'info' => $info,
        ]);
    }

    /**
     * @Route("/company/{slug}/staff/{id}", name="remove_staff", methods={"GET", "POST"})
     */
    public function remove_staff($slug, $id){
        if(!$this->account){
            return $this->redirectToRoute('account_login');
        }

        $info = $this->company_service->companyAccess($slug, 'getStaffManage', 'company_staff', ['slug' => $slug]);


        $account = $this->getDoctrine()->getRepository(Account::class)->findOneBy(['id' => $id]);

        #check account exist
        if(!$account){
            $this->addFlash("danger", "Достъп отказан! Служителският профил не съществува!");
            return $this->redirectToRoute("company_staff", ['slug' => $slug]);
        }

        #check company account exist
        $company_account  = $this->getDoctrine()->getRepository(CompanyAccounts::class)->findOneBy(['account' => $account, 'company' => $info->getCompany()]);

        if(!$company_account || $company_account->getMainAccess()){
            $this->addFlash("danger", "Опитвате се да изтриете акаунт, които не е част от тази компания!");
            return $this->redirectToRoute("company_staff", ['slug' => $slug]);
        }



        #delete providers account
        //$this->getDoctrine()->getRepository(ServiceProviders::class)->deleteProviderByAccount($account, $info->getCompany());

        #delete events with this provider
        $this->getDoctrine()->getRepository(Event::class)->deleteEvents(false, $account, $info->getCompany(), false);

        #delete company account
        $this->service->deleteData($company_account);

        #if not paid account and don't exist in more companies - delete account
        $more_accounts = $this->getDoctrine()->getRepository(CompanyAccounts::class)->findBy(['account' => $account]);

        if(!$account->getIsMain() && !$more_accounts){
            $this->getDoctrine()->getRepository(Message::class)->deleteAllAccountMessages($account);

            $this->getDoctrine()->getRepository(\App\Entity\Note::class)->clearNotes($account);
          $this->getDoctrine()->getRepository(\App\Entity\Request::class)->clearRequest($account);
          $this->service->deleteData($account);
        }

        $this->addFlash('success', 'Служителят е премахнат успешно!');
        return $this->redirectToRoute('company_staff', ['slug' => $slug]);
    }

    /**
     * @Route("company/{slug}/staff/{id}/edit", name="edit_staff", methods={"POST", "GET"})
     */
    public function edit_staff($slug, $id){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $info = $this->company_service->companyAccess($slug, 'getStaffManage', 'company_staff', ['slug' => $slug]);

        $account = $this->getDoctrine()->getRepository(CompanyAccounts::class)->checkAccountByIDandCompanyId($info->getCompany()->getId(), $id);

        if(!$account){
            $this->addFlash('danger', 'Опитвате се да редактирате профил който не съществува!');
            return $this->redirectToRoute('company_staff', ['slug' => $slug]);
        }


        if(!$info->getStaffManage() || (($account->getMainAccess() && $account->getAccount()->getIsMain()) && ($account->getAccount()->getId() != $info->getAccount()->getId()))){
            $this->addFlash('danger', 'Нямате нужните права за редактиране на този профил!');
            return $this->redirectToRoute('company_staff', ['slug' => $slug]);
        }


        $form = $this->createForm(AddStaff::class,  ['edit' => true]);
        $form->handleRequest($this->request);

        if($form->isSubmitted()){
            $this->company_service->createCompanyAccount($form, $this->request, $info, $account);
        }

        $working_time = $account->getWorkingTime() ? json_decode($account->getWorkingTime()) : [];
        $company_working_time = $info->getCompany()->getWorkingTime() ? json_decode($info->getCompany()->getWorkingTime()) : [];

        $company = $info->getCompany();

        $path = $company->getName() .", Редактиране на  служител";
        $paths = $this->service->paths("Табло, Компания, ".$path);
        $menu = ['view' => 'companies', 'active' => 'company_add_staff'];

        return $this->render("staff/edit_staff.html.twig", [
            'paths' => $paths,
            'menu' => $menu,
            'slug' => $company->getSlug(),
            'info' => $info,
            'staff' => $account,
            'form' => $form->createView(),
            'working_time' => $working_time,
            'company_working_time' => $company_working_time
        ]);
    }
}