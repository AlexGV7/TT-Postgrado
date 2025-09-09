<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private MailerInterface $mailer;
    private string $fromAddress;
    private string $fromName;
    private string $appUrl;
    private string $useMailer;
    private $logger;

    public function __construct(
        LoggerInterface $mailerLogger, 
        MailerInterface $mailer, 
        string $fromAddress, 
        string $fromName, 
        string $appUrl,
        string $useMailer)
    {
        $this->logger = $mailerLogger;
        $this->mailer = $mailer;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->appUrl = rtrim($appUrl, '/');
        $this->useMailer = $useMailer;
    }

    public function sendEmail(User $user, string $subject, string $htmlBody, bool $failLoudly = false): void
    {
        if ($user->isDeactivatedNotifications()) {
            $currentMonth = (int) date('n'); // 1 = January, 2 = February, etc.
            if ($currentMonth === 2) {
                $this->logger->info(sprintf('Notifications are deactivated for user %s during February, not sending email with subject: %s', $user->getEmail(), $subject));
                return;
            }
        }

        if (!$this->useMailer) {
            $this->logger->info(sprintf('Not sending email to %s with subject: %s', $user->getEmail(), $subject));
            return;
        }

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromAddress))
            ->to($user->getEmail())
            ->subject('[SDI] '.$subject)
            ->html("<html><body> Estimado/a {$user->getName()},<br><br>" . $htmlBody . "<br><br>Cordialmente,<br>Equipo de SDI<br><br>(No responda este email)</body></html>");

        try {
            $this->mailer->send($email);
            $this->logger->info(sprintf('Email sent to %s with subject: %s', $user->getEmail(), $subject));
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Failed to send email to %s with subject: %s. Error: %s', $user->getEmail(), $subject, $e->getMessage()));
            if ($failLoudly) {
                throw new \RuntimeException('Email sending failed: ' . $e->getMessage(), 0, $e);
            }
        }
    }


}
