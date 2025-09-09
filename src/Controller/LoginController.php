<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginController extends AbstractController
{

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response | RedirectResponse
    {
        // Check if external login is enabled
        if ($this->getParameter('USE_CAS_LOGIN')) {
            if ($this->getUser()) {
                return $this->redirectToRoute('homepage');
            }

            $target = urlencode($this->getParameter('cas_login_target') . '/force');
            $url = 'https://' . $this->getParameter('cas_host') .
                (((($this->getParameter('cas_port') != 80) || ($this->getParameter('cas_port') != 443)) 
                ? ":" . $this->getParameter('cas_port') : "")) .
                $this->getParameter('cas_path') . '/login?service=';

            return $this->redirect($url . $target);
        }

        // Redirect to user_login route if external login is disabled
        return $this->redirectToRoute('user_login');
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request, UrlGeneratorInterface $urlGenerator): Response | RedirectResponse
    {
        if ($this->getParameter('USE_CAS_LOGIN')) {
           // Invalidate local session
            $session = $request->getSession();
            $session->invalidate();

            // Build the CAS logout URL, specifying the callback service URL (e.g. homepage)
            $casHost = $this->getParameter('cas_host');
            $casPort = $this->getParameter('cas_port');
            $casPath = $this->getParameter('cas_path');
            $serviceUrl = $urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $casLogoutUrl = 'https://' . $casHost .
                (((int)$casPort !== 80 && (int)$casPort !== 443) ? ':' . $casPort : '') .
                $casPath . '/logout';

            return $this->redirect($casLogoutUrl);
        }

        $session = $request->getSession();
        $session->invalidate();

        return $this->redirectToRoute('homepage');
    }

    #[Route('/force', name: 'force', methods: ['GET'])]
    public function force(Request $request): Response
    {
        if ($this->getParameter("cas_gateway")) {
            if (!isset($_SESSION)) {
                session_start();
            }

            session_destroy();
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    #[Route('/user_login', name: 'user_login')]
    public function userLogin(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if external login is enabled
        if ($this->getParameter('USE_CAS_LOGIN')) {
            // Redirect to homepage if external login is enabled
            return $this->redirectToRoute('homepage');
        }
        
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/login-disabled', name: 'disabled_user_notice')]
    public function disabled(): Response
    {
        return $this->render('login/disabled_account.html.twig');
    }
}
