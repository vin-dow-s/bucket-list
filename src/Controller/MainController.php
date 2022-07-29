<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main_home")
     */
    public function home()
    {
        return $this->render('main/home.html.twig');
    }

    /**
     * @Route("/about-us", name="main_about_us")
     */
    public function about()
    {
        $json = file_get_contents("../data/team.json");
        $teamMembers = json_decode($json, true);
        return $this->render('main/about_us.html.twig', [
                'teamMembers' => $teamMembers
            ]);
    }

    /**
     * @Route("/add-wish", name="main_add_wish")
     */
    public function add_wish()
    {
        return $this->render('wish/add_wish.html.twig');
    }
}