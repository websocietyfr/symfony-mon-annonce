<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    #[Route('/registration', name: 'app_user_registration', methods: ['GET', 'POST'])]
    public function registration(Request $request, UserRepository $repository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

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
    
    #[Route('/user', name: 'app_user', methods:['GET'])]
    public function index(UserRepository $repository): Response
    {
        $users = $repository->findAll();

        return $this->render('user/index.html.twig', [
            'controller_name' => "Page d'accueil",
            'users' => $users
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
