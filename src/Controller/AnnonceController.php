<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Form\ContactSellerType;
use App\Form\SearchType;
use App\Repository\AnnonceRepository;
use App\Service\EmailService;
use App\Service\UploadFileService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'app_annonce_index', methods: ['GET'])]
    public function index(AnnonceRepository $annonceRepository, Request $request): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);
        $search = $request->query->get('search');
        $annonces = [];
        if ($search && $search['title']) {
            $key = $search['title'];
            $annonces = $annonceRepository->findByTitleField($key);
        } else {
            $annonces = $annonceRepository->findAll();
        }

        return $this->renderForm('annonce/index.html.twig', [
            'annonces' => $annonces,
            'form' => $form
        ]);
    }

    #[Route('/admin/annonce/new', name: 'app_annonce_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AnnonceRepository $annonceRepository, UploadFileService $uploadService): Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // upload de la photo de produit
            $picture = $form->get('picture')->getData();
            if ($picture) {
                $newFilename = $uploadService->uploadFile($picture);

                $annonce->setPicture($newFilename);
            }
            // Définition automatique de l'auteur de l'annonce
            $annonce->setUser($this->getUser());
            // persistance de la nouvelle annonce sur la base de données
            $annonceRepository->add($annonce, true);

            return $this->redirectToRoute('app_user_profile', ['id' => $annonce->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonce/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/annonce/{id}', name: 'app_annonce_show', methods: ['GET', 'POST'])]
    public function show(Annonce $annonce, Request $request, EmailService $mailService): Response
    {
        $form = $this->createForm(ContactSellerType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $mailData = $form->getData();
            
            // VERSION TEXT/HTML
            // $mailService->sendMail(
            //     $annonce->getUser()->getEmail(),
            //     $mailData['email'],
            //     'Prise de contact pour votre annonce : '.$annonce->getTitle(),
            //     'Bonjour vous avez été contacté par '.
            //         $mailData['firstname'].' '.
            //         $mailData['lastname'].', qui vous adresse le message suivant : '.
            //         $mailData['body'].', vous pouvez lui répondre a l\'adresse mail suivante : '.
            //         $mailData['email'],
            //     ''
            // );
            
            // VERSION TWIG
            $mailService->sendTwigMail(
                $annonce->getUser()->getEmail(),
                $mailData['email'],
                'Prise de contact pour votre annonce : '.$annonce->getTitle(),
                'contact_seller',
                [
                    'mailData' => $mailData,
                    'annonce' => $annonce
                ]
            );
        }

        return $this->renderForm('annonce/show.html.twig', [
            'annonce' => $annonce,
            'form' => $form
        ]);
    }

    #[Route('/admin/annonce/{id}/edit', name: 'app_annonce_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonce $annonce, AnnonceRepository $annonceRepository, UploadFileService $uploadService): Response
    {
        $originalPicture = $annonce->getPicture();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            if ($picture) {
                $newFilename = $uploadService->uploadFile($picture);

                $annonce->setPicture($newFilename);
            } else {
                $annonce->setPicture($originalPicture);
            }
            $annonceRepository->add($annonce, true);

            return $this->redirectToRoute('app_user_profile', ['id' => $annonce->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    #[Route('/admin/annonce/{id}', name: 'app_annonce_delete', methods: ['POST'])]
    public function delete(Request $request, Annonce $annonce, AnnonceRepository $annonceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $annonceRepository->remove($annonce, true);
        }

        return $this->redirectToRoute('app_annonce_index', [], Response::HTTP_SEE_OTHER);
    }
}
