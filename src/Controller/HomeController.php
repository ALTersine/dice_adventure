<?php

namespace App\Controller;

use App\Entity\FraisKM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $fraisKM = new FraisKM();
        return $this->render('home/index.html.twig', [
            'page_title' => 'Bienvenue, aventuriers !',
            'fraisKilometrique' => $fraisKM->getInfo(),
        ]);
    }
}
