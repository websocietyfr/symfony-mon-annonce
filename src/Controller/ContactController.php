<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EmailService $mailService): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mailData = $form->getData();
            // VERSION TWIG
            $mailService->sendTwigMail(
                $this->getParameter('admin_email'),
                $mailData['email'],
                '[MonAnnonce.fr] Contact : '.$mailData['subject'],
                'contact',
                [
                    'mailData' => $mailData
                ]
            );
        }
        return $this->renderForm('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
