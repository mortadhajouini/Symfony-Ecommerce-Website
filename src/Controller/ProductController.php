<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\CartItem;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_product_front", methods={"GET"})
     */
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        // Retrieve all categories
        $categories = $categoryRepository->findAll();

        // Initialize an array to store counts for each category
        $categoryCounts = [];

        // Calculate the number of products for each category
        foreach ($categories as $categoryItem) {
            $categoryId = $categoryItem->getId();
            $categoryCounts[$categoryId] = count($productRepository->findBy(['category' => $categoryItem]));
        }
        // Calculate the total number of products
        $totalProducts = $productRepository->countProducts();



        return $this->render('product/index_front.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
            'categoryCounts' => $categoryCounts,
            'totalProducts' => $totalProducts,
        ]);
    }

    /**
     * @Route("/admin", name="app_product_index", methods={"GET"})
     */
    public function indexAdmin(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // Retrieve all categories
        $categories = $categoryRepository->findAll();

        // Initialize an array to store counts for each category
        $categoryCounts = [];

        // Calculate the number of products for each category
        foreach ($categories as $categoryItem) {
            $categoryId = $categoryItem->getId();
            $categoryCounts[$categoryId] = count($productRepository->findBy(['category' => $categoryItem]));
        }


        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_product_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProductRepository $productRepository, FileUploader $fileUploader): Response
    {

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $image = $fileUploader->upload($imageFile);
                $product->setImage($image);
            }

            $productRepository->add($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     */
    public function show(Product $product, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $quantity = 1; // Initialize the quantity variable
        // Retrieve products with the same category
        $category = $product->getCategory();
        $relatedProducts = $productRepository->findBy(['category' => $category]);

        // Retrieve all categories
        $categories = $categoryRepository->findAll();

        // Initialize an array to store counts for each category
        $categoryCounts = [];

        // Calculate the number of products for each category
        foreach ($categories as $categoryItem) {
            $categoryId = $categoryItem->getId();
            $categoryCounts[$categoryId] = count($productRepository->findBy(['category' => $categoryItem]));
        }


        return $this->render('product/show.html.twig', [
            'product' => $product,
            'categories' => $categoryRepository->findAll(),
            'relatedProducts' => $relatedProducts,
            'categoryCounts' => $categoryCounts,
            'quantity' => $quantity, // Include the quantity variable
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product, ProductRepository $productRepository, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();


            if ($imageFile) {
                $image = $fileUploader->upload($imageFile);
                $product->setImage($image);
            }

            $productRepository->add($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {


            // Get the related CartItems
            $cartItems = $this->getDoctrine()->getRepository(CartItem::class)->findBy(['Product' => $product]);

            // Remove each CartItem
            foreach ($cartItems as $cartItem) {
                $this->getDoctrine()->getManager()->remove($cartItem);
            }

            // Flush the changes
            $this->getDoctrine()->getManager()->flush();


            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/search/{category}", name="app_search", methods={"GET"})
     */
    public function search($category, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {

        // Initialize an array to store counts for each category
        $categoryCounts = [];

        $categories = $categoryRepository->findAll();
        // Calculate the number of products for each category
        foreach ($categories as $categoryItem) {
            $categoryId = $categoryItem->getId();
            $categoryCounts[$categoryId] = count($productRepository->findBy(['category' => $categoryItem]));
        }

        $selectedCategory = $categoryRepository->findOneById($category);

        // Calculate the total number of products
        $totalProducts = $productRepository->countProducts();

        return $this->render('product/index_front.html.twig', [
            'products' => $productRepository->findBy(['category' => $selectedCategory]),
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'totalProducts' => $totalProducts,
        ]);
    }
}
