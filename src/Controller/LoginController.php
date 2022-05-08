<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginRegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;



class LoginController extends AbstractController
{

    public function __construct(private UserRepository $repository)
    {

    }
    #[Route('/login', name: 'app_login')]
    public function index(Request $request , SessionInterface $session): Response
    {
        $session->set("logged",0);
        if ($session->has('userId'))
        {
            return $this->redirectToRoute("app_profile");
        }
        $user=new User();
        $form=$this->createForm(LoginRegisterType::class,$user);
        $form->add("Connect",SubmitType::class);
        $form->remove("name");
        $form->remove("image");
        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            $user1=$this->repository->findOneBy([
                "email"=>$user->getEmail()
            ]);
            if($user->getPassword()==$user1->getPassword()){
                $session->set("logged",1);
                $session->set('userId',$user1->getId());
                if ($user->getEmail()=="aymenbouhaha@yahoo.fr")
                {
                    $session->set("admin",1);
                }
                return $this->redirectToRoute("app_home");
            }else{
                $this->addFlash("error","Les informations sont incorrectes !!!");
            }
        }
        return $this->render('login/index.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    #[Route('/logout', name:'logout')]
    public function logout(SessionInterface $session){
        if ($session->has("userId")){
            $session->remove('userId');
            $session->set("logged",0);
            if ($session->has("admin"))
            {
                $session->set("admin",0);
            }
        }

        return $this->redirectToRoute("app_login");
    }



}
