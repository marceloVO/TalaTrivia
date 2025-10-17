<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET','POST'])]
    public function login(Request $request): Response
    {
        // If already logged in, redirect
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('login.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Controller can be blank: it will be intercepted by the logout key on your firewall
        throw new \Exception('This should never be reached!');
    }
    //creacion de cuentas player
    #[Route('/register', name: 'app_register', methods: ['GET','POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                return $this->render('register.html.twig', ['error' => 'All fields required']);
            }

            $existing = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existing) {
                return $this->render('register.html.twig', ['error' => 'Email already registered']);
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setRoles(['ROLE_PLAYER']);
            $user->setPassword($hasher->hashPassword($user, $data['password']));

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('register.html.twig');
    }

    //creacion de cuentas admin
    #[Route('/register-admin', name: 'app_registerAdmin', methods: ['GET','POST'])]
    public function registerAdmin(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                return $this->render('register.html.twig', ['error' => 'All fields required']);
            }

            $existing = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existing) {
                return $this->render('register.html.twig', ['error' => 'Email already registered']);
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($hasher->hashPassword($user, $data['password']));

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registerAdmin.html.twig');
    }
}
