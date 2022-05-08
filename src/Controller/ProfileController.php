<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(private UserRepository $repository)
    {
    }
    #[Route('/profile', name: 'app_profile')]
    public function index(SessionInterface $session): Response
    {
        $session->set("logged",0);
        if (!$session->has("userId")){
            return $this->redirectToRoute('app_login');
        }
        $user=$this->repository->find($session->get("userId"));
        $session->set("logged",1);
        return $this->render('profile/index.html.twig',[
            "user"=>$user
        ]);
    }
}
