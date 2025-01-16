<?php

// src/Controller/ExceptionController.php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionController extends AbstractController
{
    public function showException(\Throwable $exception): Response
    {
        // Customize this method to handle various exceptions

        if ($exception instanceof NotFoundHttpException) {
            // Handle 404 Not Found errors
            return new Response($this->renderView('exception/404.html.twig', []), Response::HTTP_NOT_FOUND);
        }

        // Handle other exceptions...

        // Render a generic error page for unhandled exceptions
        return new Response($this->renderView('exception/404.html.twig', []), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
