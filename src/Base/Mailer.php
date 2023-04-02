<?php

namespace Lepton\Base;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Lepton\Base\Application;

class Mailer
{
    private $mailer;
    public $error;

    public function __construct()
    {
        $config = Application::getEmailConfig();
        $this->mailer = new PHPMailer();
        $this->mailer->isSMTP();
        //$this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;

        $this->mailer->Host = $config->host;
        $this->mailer->Port = 465;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->setFrom($config->username, 'AGA 2023');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config->username;
        $this->mailer->Password = $config->password;
        $this->mailer->addReplyTo($config->replyTo, $config->replyToName);
    }

    public function send($to, $subject, $body, $attachments=array())
    {
        $this->mailer->addAddress($to);
        $this->mailer->Subject = $subject;
        $this->mailer->msgHTML($body);
        foreach ($attachments as $att) {
            $file = $_SERVER['DOCUMENT_ROOT'].Application::getDir()."/resources/".$att;
            $this->mailer->addAttachment($file);
        }

        $sent = $this->mailer->send();
        $this->error = $this->mailer->ErrorInfo;
        return $sent;
    }
}
