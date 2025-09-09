<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\NotificationService;
use App\Entity\User;

final class MailerController extends AbstractController
{
    #[Route('/mailer/test', name: 'test_mailer', methods: ['GET'])]
    public function testMailer(NotificationService $notificationService): Response
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $currentUserEmail = $currentUser ? $currentUser->getEmail() : null;

        $envMailer = $_ENV['USE_MAILER'] ?? null;
        $envMailerDsn = $_ENV['MAILER_DSN'] ?? null;

        $info = [
            'USE_MAILER' => $envMailer,
            'MAILER_DSN' => $envMailerDsn,
            'USER_EMAIL' => $currentUserEmail,
        ];

        if ($envMailer !== 'true') {
            return $this->render('mailer/test.html.twig', [
                'message' => "Email sending is disabled in the environment configuration.",
                'success' => false,
                'info' => $info,
            ]);
        } elseif (!$envMailerDsn) {
            return $this->render('mailer/test.html.twig', [
                'message' => "Mailer DSN is not configured.",
                'success' => false,
                'info' => $info,
            ]);
        } else {
            try {
                $notificationService->sendEmail(
                    $currentUser,
                    "Test",
                    "This is a test email from Symfony-App.",
                    true
                );
                return $this->render('mailer/test.html.twig', [
                    'message' => "Test email sent to $currentUserEmail successfully.",
                    'success' => true,
                    'info' => $info,
                ]);
            } catch (\Exception $e) {
                return $this->render('mailer/test.html.twig', [
                    'message' => "Error sending email: " . $e->getMessage(),
                    'success' => false,
                    'info' => $info,
                ]);
            }
        }
    }
}
