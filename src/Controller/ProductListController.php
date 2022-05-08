<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\AddProductType;
use App\Repository\ImageRepository;
use App\Repository\ProductRepository;
use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/product")]

class ProductListController extends AbstractController
{

    public function __construct(private ProductRepository $productRepository, private ImageRepository $imageRepository)
    {
    }

    #[Route('/list', name: 'app_product_list')]
    public function index(SessionInterface $session): Response
    {
        $session->set("logged",0);
        if ($session->has("userId")){
            $session->set("logged",1);
        }
        $products=$this->productRepository->findAll();

        for ($i=0;$i<count($products);$i++){
            $images=$this->imageRepository->findBy(["product"=>$products[$i]]);
            for ($j=0;$j<count($images);$j++){
                $products[$i]->addImage($images[$j]);
            }
        }
        return $this->render('product_list/index.html.twig',[
            "products"=>$products
        ]);
    }

    #[Route('/add', name: 'add')]
    public function AddImage(Request $request, EntityManagerInterface $manager ){
        $product=new Product();
        $form=$this->createForm(AddProductType::class,$product);
        $form->add("Add",SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            if (!$this->productRepository->findBy(["name"=>$product->getName()])){
                $imageFiles=$form->get("image")->getData();
                if ($imageFiles){
                    for ($i=0;$i<count($imageFiles);$i++){
                        $image=new Image();
                        $newImageName=uniqid().".".$imageFiles[$i]->guessExtension();
                        try {
                            $imageFiles[$i]->move($this->getParameter("brochures_directory"),$newImageName);
                        }
                        catch (FileException $exception)
                        {
                            $exception->getMessage();
                            dd($product);
                        }
                        $image->setSrc($newImageName);
                        $image->setProduct($product);
                        $product->addImage($image);
                        $manager->persist($image);
                    }
                    $manager->persist($product);
                    $manager->flush();
                    $this->addFlash("success", "Produit ajouté avec Success");
                }
            }
        }
        return $this->render('product_list/add_product.html.twig',[
            "form"=>$form->createView()
        ]);
    }

    #[Route('/remove/{product}', name: 'remove')]
    public function Remove(Product $product =null, EntityManagerInterface $manager){
        if ($product){
            $manager->remove($product);
            $manager->flush();
            $this->addFlash("success","le produit supprimé avec succés");
        }else{
            $this->addFlash("error","le produit n'est pas supprimé");
        }
        return $this->redirectToRoute("app_product_list");
    }


}
