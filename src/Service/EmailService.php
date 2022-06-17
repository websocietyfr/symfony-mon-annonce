<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;
    private $fromEmail;
    
    public function __construct($fromEmail, MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->fromEmail = $fromEmail;
    }

    public function sendTwigMail($to,$replyto = null,$subject,$template,$params): void
    {
        // VERSION TWIG
        $email = (new TemplatedEmail())
        ->from($this->fromEmail)
        ->to($to)
        ->replyTo($replyto)
        ->subject($subject)
        ->htmlTemplate('email/'.$template.'.html.twig')
        ->context($params);

        $this->mailer->send($email);
    }

    public function sendMail($to,$replyto = null,$subject,$text,$html): void
    {
        // VERSION TEXT/HTML
        $email = (new Email())
        ->from($this->fromEmail)
        ->to($to)
        ->replyTo($replyto)
        ->subject($subject)
        ->text($text)
        ->html($html);

        $this->mailer->send($email);
    }
}