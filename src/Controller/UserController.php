<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/admin", name="app_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        // Check if the current user has the 'ROLE_ADMIN' role
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }



    /**
     * @Route("/{id}", name="app_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/give-admin-role/{userId}", name="give_admin_role")
     */
    public function giveAdminRole($userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set or replace roles
        $user->setRoles(['ROLE_ADMIN']);

        // If using Symfony 5.3 or newer, you can also use the Security component:
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/remove-admin-role/{userId}", name="remove_admin_role")
     */
    public function removeAdminRole($userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Get current roles
        $roles = $user->getRoles();

        // Remove 'ROLE_ADMIN' from roles array
        $key = array_search('ROLE_ADMIN', $roles, true);
        if ($key !== false) {
            unset($roles[$key]);
        }

        // Set the updated roles
        $user->setRoles(array_values($roles));

        // If using Symfony 5.3 or newer, you can also use the Security component:
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
