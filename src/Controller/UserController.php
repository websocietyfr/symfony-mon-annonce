<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\UploadFileService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods:['GET'])]
    public function index(UserRepository $repository): Response
    {
        $users = $repository->findAll();

        return $this->render('user/index.html.twig', [
            'controller_name' => "Page d'accueil",
            'users' => $users
        ]);
    }

    #[Route('/user/my', name: 'app_user_show', methods:['GET', 'POST'])]
    public function show(Request $request, UserRepository $repository, UploadFileService $uploadService): Response
    {
        $initial_profil_pic = $this->getUser()->getProfilPicture();
        $form = $this->createForm(UserType::class, $this->getUser());
        
        $form->handleRequest($request);

        // save User
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return new Response('Erreur, le formulaire est incomplet !');
            }
            $user = $form->getData();
            $picture = $form->get('profil_picture')->getData();
            if ($picture) {
                $newFilename = $uploadService->uploadFile($picture);
                $user->setProfilPicture($newFilename);
            } else {
                $user->setProfilPicture($initial_profil_pic);
            }
            try {
                $repository->add($user, true);
                $this->addFlash(
                    'success',
                    'Votre profil à bien été mis à jour.'
                );
                return $this->redirectToRoute('app_user_show');
            } catch (UniqueConstraintViolationException $e) {
                dump($e);die;
                $this->addFlash(
                    'danger',
                    "L'adresse mail est déjà utilisée pour un autre compte"
                );
            }
        }

        return $this->renderForm('user/edit.html.twig', [
            'form' => $form,
            'user' => $this->getUser()
        ]);
    }

    #[Route('/user/{id}', name: 'app_user_profile', methods:['GET'])]
    public function profile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/registration', name: 'app_user_registration', methods: ['GET', 'POST'])]
    public function registration(Request $request, UserRepository $repository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        // save User
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return new Response('Erreur, le formulaire est incomplet !');
            }
            $user = $form->getData();
            $user->setRoles(['ROLE_SELLER']);
            $user->setPassword($repository->hashPassword($user->getPassword(),$user));
            // $user = $repository->createUser($user->firstname,$user->lastname,$user->email,$user->phone_number,$user->profil_picture,$user->password);
            try {
                $repository->add($user, true);
                return $this->redirectToRoute('app_user');
            } catch (UniqueConstraintViolationException $e) {
                return $this->render('error/exception.html.twig', [
                    "message" => "L'adresse mail est déjà utilisé pour un autre compte",
                    "referer" => $request->server->get('HTTP_REFERER')
                ]);
            }
        }

        return $this->renderForm('user/registration.html.twig', [
            "form" => $form
        ]);
    }
    
    #[Route('/login', name: 'app_login', methods:['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            "last_username" => $lastUsername,
            "error" => $error
        ]);
    }
    
    #[Route('/logout', name: 'app_logout', methods:['GET'])]
    public function logout()
    {
        return $this->redirectToRoute('app_login');
    }
}
