<?php

namespace App\Controller;

use App\Factory\PrixFactory;
use App\Repository\AvisRepository;
use App\Repository\FraisKMRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        PrixFactory $prixFactory,
        AvisRepository $avisRepository,
        FraisKMRepository $fraisKMRepository,
    ): Response {
        return $this->render('home/index.html.twig', [
            'page_title'        => 'Bienvenue, aventuriers !',
            'tarrif'            => $prixFactory->display(),
            'avisList'          => $avisRepository->findAllForDisplay(),
            'fraisKilometrique' => $fraisKMRepository->getInfo(),
        ]);
    }
}
