<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\CartType;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CartItemRepository; // Import CartItemRepository
use Symfony\Component\HttpFoundation\JsonResponse; // Import JsonResponse
use App\Entity\CartItem; // Import CartItem


/**
 * @Route("/cart")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="app_cart_index", methods={"GET"})
     */
    public function index(CartRepository $cartRepository): Response
    {
        return $this->render('cart/index.html.twig', [
            'carts' => $cartRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_cart_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CartRepository $cartRepository): Response
    {
        $cart = new Cart();
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartRepository->add($cart, true);

            return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart/new.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_cart_show", methods={"GET"})
     */
    public function show(Cart $cart): Response
    {
        $subtotal = 0;
        $carttotal = $subtotal + 7.5;
        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
            'subtotal' => $subtotal,

        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_cart_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Cart $cart, CartRepository $cartRepository): Response
    {
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartRepository->add($cart, true);

            return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart/edit.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_cart_delete", methods={"POST"})
     */
    public function delete(Request $request, Cart $cart, CartRepository $cartRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cart->getId(), $request->request->get('_token'))) {
            $cartRepository->remove($cart, true);
        }

        return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/clear", name="app_cart_clear", methods={"GET", "POST"})
     */
    public function clearCart(Request $request, Cart $cart, CartItemRepository $cartItemRepository): Response
    {

        // Get all cart items associated with the cart
        $cartItems = $cart->getCartItems();

        // Remove each cart item from the repository
        foreach ($cartItems as $cartItem) {
            $cart->removeCartItem($cartItem);
            $cartItemRepository->remove($cartItem, true);
        }

        return $this->redirectToRoute('app_cart_show', ['id' => $cart->getId()]);
    }

    /**
     * @Route("/increment/{cartItemId}", name="cart_increment", methods={"POST"})
     */
    public function incrementQuantity(int $cartItemId): JsonResponse
    {
        $cartItem = $this->getDoctrine()->getRepository(CartItem::class)->find($cartItemId);

        if (!$cartItem) {
            throw $this->createNotFoundException('Cart item not found');
        }

        // Implement your logic here
        $cartItem->setQuantity($cartItem->getQuantity() + 1);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['newQuantity' => $cartItem->getQuantity()]);
    }

    /**
     * @Route("/decrement/{cartItemId}", name="cart_decrement", methods={"POST"})
     */
    public function decrementQuantity(int $cartItemId): JsonResponse
    {
        $cartItem = $this->getDoctrine()->getRepository(CartItem::class)->find($cartItemId);

        if (!$cartItem) {
            throw $this->createNotFoundException('Cart item not found');
        }

        // Implement your logic here
        $cartItem->setQuantity(max(1, $cartItem->getQuantity() - 1));
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['newQuantity' => $cartItem->getQuantity()]);
    }
}
