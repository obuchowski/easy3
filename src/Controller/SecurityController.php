<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(): Response
    {
        $response = $this->render('security/login.html.twig', [
            // the translation_domain to use (define this option only if you are
            // rendering the login template in a regular Symfony controller; when
            // rendering it from an EasyAdmin Dashboard this is automatically set to
            // the same domain as the rest of the Dashboard)
            'translation_domain' => 'admin',

            // the title visible above the login form (define this option only if you are
            // rendering the login template in a regular Symfony controller; when rendering
            // it from an EasyAdmin Dashboard this is automatically set as the Dashboard title)
            'page_title' => 'ACME login',

            'username_label' => 'Your username',
            'password_label' => 'Your password',
            'sign_in_label' => 'Log in',
            'username_parameter' => '_username',
            'password_parameter' => '_password',

            'hinclude_inline' => file_get_contents('assets/hinclude.min.js')
        ]);

        $response->setPublic();
        $response->setMaxAge(86400);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        return $response;
    }

    /**
     * @Route("/login_dynamic", name="app_login_dynamic")
     */
    public function loginDynamic(AuthenticationUtils $authenticationUtils)
    {
        if (!$isAuthorized = (bool)$this->getUser()) {
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();
        }
        return $this->render('hinclude/login.html.twig', [
            'error' => $error ?? false,
            'last_username' => $lastUsername ?? false,
            'is_authorized' => $isAuthorized //return $this->redirectToRoute('dashboard') via JS
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}