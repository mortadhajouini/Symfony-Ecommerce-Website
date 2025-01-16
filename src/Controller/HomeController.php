<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(ProductRepository $productRepository): Response
    {

        $products = $productRepository->findAll();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
        ]);
    }

    /**
     * @Route("/admin", name="app_dashboard")
     */
    public function index2(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $this->getUser(); // Get the currently logged-in user
        return $this->render('home/index2.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $user,
        ]);
    }

    /**
     * @Route("/404", name="app_404")
     */
    public function error(): Response
    {
        return $this->render('home/404.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
