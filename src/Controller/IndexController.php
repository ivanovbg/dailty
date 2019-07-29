<?php
namespace App\Controller;
use App\Entity\Event;
use App\Entity\Note;
use App\Services\Accounts as AccountsService;
use App\Services\Accounts;
use App\Services\Service;
use http\Env\Request;
use http\Env\Response;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class IndexController extends GlobalController {


    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        if(!$this->account){
            return $this->redirectToRoute("account_login");
        }

        $events = $this->getDoctrine()->getRepository(Event::class)->getAllCompaniesAccountEvents($this->account);
        $events = $this->service->pages($events,5);

        $notes = $this->getDoctrine()->getRepository(Note::class)->findBy(['account' => $this->account], ['date_added' => 'DESC']);
        $notes = $this->service->pages($notes, 5);


        $paths = $this->service->paths('Dashboard');
        $menu = ['view' => 'dashboard', 'active' => 'dashboard'];

        return $this->render('index/index.html.twig', [
            'paths' => $paths,
            'menu' => $menu,
            'events' => $events,
            'notes' => $notes

        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/notFound", name="not_found", methods={"GET"})
     */
    public function notFound(){

        $paths = $this->service->paths('Грешка');

        return $this->render('index/not_found.html.twig', [
            'paths' => $paths
        ]);
    }

}
