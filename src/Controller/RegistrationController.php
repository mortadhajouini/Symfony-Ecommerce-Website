<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\EditUserFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\Security\Core\User\UserInterface;


class RegistrationController extends AbstractController
{

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit-profile", name="app_edit_profile")
     */
    public function editProfile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Check if the user is authenticated
        if (!$user) {
            throw new AccessDeniedException('This action is not allowed for non-authenticated users.');
        }

        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update other fields as needed, but do not update the email
            // ...

            // If a new password is set, encode and update it
            if ($newPassword = $form->get('newPassword')->getData()) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // Redirect to a profile view or wherever you want
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);

            //return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/edit_profile.html.twig', [
            'editProfileForm' => $form->createView(),
        ]);
    }
}