<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Form\CartItemType;
use App\Repository\CartItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Entity\User;



/**
 * @Route("/cart/item")
 */
class CartItemController extends AbstractController
{
    /**
     * @Route("/", name="app_cart_item_index", methods={"GET"})
     */
    public function index(CartItemRepository $cartItemRepository): Response
    {
        return $this->render('cart_item/index.html.twig', [
            'cart_items' => $cartItemRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_cart_item_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CartItemRepository $cartItemRepository): Response
    {
        $cartItem = new CartItem();
        $form = $this->createForm(CartItemType::class, $cartItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartItemRepository->add($cartItem, true);

            return $this->redirectToRoute('app_cart_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart_item/new.html.twig', [
            'cart_item' => $cartItem,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_cart_item_show", methods={"GET"})
     */
    public function show(CartItem $cartItem): Response
    {
        return $this->render('cart_item/show.html.twig', [
            'cart_item' => $cartItem,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_cart_item_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, CartItem $cartItem, CartItemRepository $cartItemRepository): Response
    {
        $form = $this->createForm(CartItemType::class, $cartItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartItemRepository->add($cartItem, true);

            return $this->redirectToRoute('app_cart_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart_item/edit.html.twig', [
            'cart_item' => $cartItem,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_cart_item_delete", methods={"POST"})
     */
    public function delete(Request $request, CartItem $cartItem, CartItemRepository $cartItemRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cartItem->getId(), $request->request->get('_token'))) {
            $cartItemRepository->remove($cartItem, true);
        }

        return $this->redirectToRoute('app_cart_item_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/add-to-cart/{productId}", name="app_cart_item_add_to_cart", methods={"GET"})
     */
    public function addToCart(Request $request, int $productId, CartItemRepository $cartItemRepository, ProductRepository $productRepository): Response
    {
        // Get the current user
        $user = $this->getUser();
        if ($user instanceof User) {
            $cart = $user->getCart();
        }

        // Find the product by ID
        $product = $productRepository->find($productId);

        // Get the quantity from the request parameters (default to 1 if not provided)
        $quantity = $request->query->getInt('quantity', 1);

        // Check if the product is already in the cart

        dump($cart, $product);
        $existingCartItem = $cartItemRepository->findOneBy(['Cart' => $cart, 'Product' => $product]);
        dump($existingCartItem);

        if ($existingCartItem instanceof CartItem) {
            // Update the quantity of the existing CartItem
            $existingCartItem->setQuantity($existingCartItem->getQuantity() + $quantity);

            // Persist the updated CartItem to the database
            $cartItemRepository->add($existingCartItem, true);
        } else {
            // Create a new CartItem and set the user, product, and quantity
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addCartItem($cartItem);

            // Persist the new CartItem to the database
            $cartItemRepository->add($cartItem, true);
        }

        // Redirect to the cart page
        return $this->redirectToRoute('app_cart_show', ['id' => $cart->getId()]);
    }

    /**
     * @Route("/remove-from-cart/{cartitemId}", name="app_cart_item_remove_from_cart", methods={"GET"})
     */
    public function removeFromCart(Request $request, int $cartitemId, CartItemRepository $cartItemRepository): Response
    {
        // Get the current user
        $user = $this->getUser();
        if ($user instanceof User) {
            $cart = $user->getCart();
        }


        // Find the cart item by ID
        $cartItem = $cartItemRepository->find($cartitemId);


        // Check if the cart item belongs to the current user's cart
        if ($cartItem && $cartItem->getCart() === $cart) {
            // Remove the cart item
            $cart->removeCartItem($cartItem);

            // Persist changes to the database
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('app_cart_show', [
            'id' => $cart->getId(),
        ]);
    }
}
