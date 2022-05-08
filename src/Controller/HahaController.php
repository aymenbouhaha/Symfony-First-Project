<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HahaController extends AbstractController
{
    #[Route('/haha', name: 'app_haha')]
    public function index(): Response
    {
        return $this->render('haha/index.html.twig', [
            'controller_name' => 'HahaController',
        ]);
    }
}
