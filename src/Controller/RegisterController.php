<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginRegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class RegisterController extends AbstractController
{
    public function __construct(private UserRepository $repository)
    {
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/register', name: 'app_register')]
    public function index(Request $request, SessionInterface $session ,SluggerInterface $slugger): Response
    {
        $user=new User();
        $form=$this->createForm(LoginRegisterType::class,$user);
        $form->remove("image");
        $form->add('password_confirmation',PasswordType::class, ['mapped' => false , "required"=> true]);
        $form->add("image",FileType::class, ["required" => false]);
        $form->add("Register",SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            if ($user->getPassword()!=$form->get("password_confirmation")->getData())
            {
                $this->addFlash("error","Password not match");
                return $this->render('register/index.html.twig',[
                    "form"=>$form->createView()
                ]);
            }
            if (!$this->repository->findOneBy(["email"=>$user->getEmail()]))
            {
                $imageFile=$form->get("image")->getData();
                if ($imageFile){
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeImageName=$slugger->slug($originalFilename);
                    $newImageName=$safeImageName."-".uniqid().".".$imageFile->guessExtension();
                    try {
                        $imageFile->move($this->getParameter("brochures_directory"),$newImageName);
                    }
                    catch (FileException $exception)
                    {
                        $exception->getMessage();
                    }
                    $user->setImage($newImageName);
                }
                $this->repository->add($user);
                $session->set('userId',$this->repository->findOneBy(["email"=>$user->getEmail()])->getId());
                $session->set("logged",1);
                $this->addFlash("success", "Registration Success");
                return $this->redirectToRoute('app_home');
            }
            else{
                $this->addFlash("error","email deja existant");
            }
        }
        return $this->render('register/index.html.twig',[
            "form"=>$form->createView()
        ]);
    }
}
