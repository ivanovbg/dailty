<?php
namespace App\Controller;
use App\Entity\Account;
use App\Forms\EditProfile;
use App\Services\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends GlobalController{

    /**
     * @Route("/account/login", name="account_login", methods={"GET"})
     */
    public function login(){
        if($this->account){
            return $this->redirectToRoute("index");
        }

        return $this->render("/account/login.html.twig",[
            'pageTitle' => 'Вход'
        ]);
    }

    
    /**
     * @Route("/account/logout", name="account_logout", methods={"GET"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logout(){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $this->get('session')->remove("account_id");

        if($this->request->cookies->has("account_id")){
            $this->service->clearCookie("account_id");
        }

        return $this->redirectToRoute("account_login");
    }

    /**
     * @Route("/account/profile", name="account", methods={"GET"})
     */
    public function profile(){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $form = $this->createForm(EditProfile::class);

        $paths[] = ['url' => '/index', 'title' => 'Начало'];
        $paths[] = ['url' => '/account/my_profile', 'title' => 'Моят профил'];

        $menu = ['view' => 'account', 'active' => 'my_profile'];

        return $this->render("/account/profile.html.twig", [
            'pageName' => 'Моят профил',
            'paths' => $paths,
            'menu' => $menu,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/account/profile", name="account_post", methods={"POST"})
     */
    public function profilePost(Request $request){
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $form = $this->createForm(EditProfile::class, $this->account);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();

            if($this->account->getPassword() != md5($form->get('password')->getData())){
                $this->addFlash('danger', "Грешна текуща парола!");
                return $this->redirectToRoute("account");
            }

            $email_check = $entityManager->getRepository(Account::class)->findOneBy(['email' => $form->get('email')->getData()]);

            if($email_check && $this->account->getId() != $email_check->getId()){
                $this->addFlash('error' ,  'Този емейл адрес вече се използва от друг потребител.');
                return $this->redirectToRoute("account");
            }

            if($form->get("new_password")->getData()){
                $this->account->setPassword(md5($form->get("new_password")->getData()));
            }

            $this->service->saveData($this->account); //save account

            $this->addFlash('success', 'Профилът е обновен успешно!');
            return $this->redirectToRoute("account");
        }
    }

    /**
     * @Route("/account/forgot-password/{key}", name="forgot-password", methods={"GET"}, defaults={"key"=null})
     */
    function forgot_password($key = null){
        if($this->account){
            return $this->redirectToRoute("account");
        }

        $error = $this->get('session')->getFlashBag()->get('alert-danger');


        if($key){
            $request = $this->getDoctrine()->getRepository(\App\Entity\Request::class)->forgotPasswordRequest($key);

            if(!$request){
                $this->addFlash('alert-danger', 'Невалиден код. Моля, опитайте отново!');
                return $this->redirectToRoute("forgot-password");
            }
        }

        return $this->render("account/forgot-password.html.twig", [
            'pageTitle' => 'Забравена парола',
            'request' => $key && $request ? $request : false,
            'error' => $error ? $error[0] : false
        ]);
    }

    /**
     * @Route("/account/invitation/{key}", name="invitation", methods={"GET", "POST"}, defaults={"key"=null})
     */
    public function invitation($key, Request $requestl){
        if($this->account){
            $this->get('session')->remove("account_id");

            if($this->get("request_stack")->getCurrentRequest()->cookies->has("account_id")){
                $this->get(Service::class)->clearCookie("account_id");
            }
        }

        if(!$key){
            return $this->redirectToRoute("account_login");
        }


        $request = $this->getDoctrine()->getRepository(\App\Entity\Request::class)->invitationRequest($key);
        $form = false;
        if($request){
            $account = $request->getAccount();
            $form = $this->createForm(EditProfile::class, $account);
            $form->handleRequest($requestl);

            if($form->isSubmitted() && $form->isValid()){
                $password = $form->get('password')->getData();
                $password_again = $form->get('new_password')->getData();
                $name = $form->get('name')->getData();

                if(strlen($password)<8 || ($password != $password_again) || $name == 'new_invitation_account'){
                    if(strlen($password)<8)
                        $this->addFlash('danger', 'Паролата трябва да съдържа минимум 8 символа.');
                    elseif($name == 'new_invitation_account')
                        $this->addFlash('danger', 'Моля, въведете своето име');
                    else
                        $this->addFlash('danger', 'Паролите не съвпадат, моля опитайте отново');
                    return $this->redirectToRoute('invitation', ['key' => $key]);
                }else{
                    $account->setPassword(md5($password));
                    $account->setIsActive(1);
                    $this->service->saveData($account);

                    $this->service->deleteData($request);

                    return $this->redirectToRoute('account_login');
                }
            }
        }

        return $this->render("account/invitation.html.twig", [
           'request' => $request,
           'pageTitle' => 'Покана за присъединяване',
           'form' => $form ? $form->createView() : false
        ]);

    }

}
