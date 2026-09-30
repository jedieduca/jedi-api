<?php

namespace Util;

use PHPMailer\PHPMailer\PHPMailer;

class MailUtil
{
    /**
     * Envia um e-mail HTML usando as configurações SMTP definidas no ambiente (.env)
     * @throws \RuntimeException quando o SMTP não está configurado ou o envio falha
     */
    public static function enviarHtml(string $para, string $nome, string $assunto, string $html, string $textoAlternativo = ''): void
    {
        $host = getenv('SMTP_HOST') ?: '';
        if ($host === '' || !class_exists(PHPMailer::class)) {
            throw new \RuntimeException('Envio de e-mail não configurado (SMTP_HOST ou PHPMailer ausente)');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->Port       = (int) (getenv('SMTP_PORT') ?: 465);
        $mail->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER') ?: '';
        $mail->Password   = getenv('SMTP_PASS') ?: '';
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->Timeout    = 15;

        $mail->setFrom(getenv('SMTP_FROM') ?: $mail->Username, getenv('SMTP_FROM_NAME') ?: 'JEDi Educa');
        $mail->addAddress($para, $nome);
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $html;
        $mail->AltBody = $textoAlternativo;

        try {
            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            throw new \RuntimeException('Falha ao enviar e-mail: ' . $mail->ErrorInfo, 0, $e);
        }
    }
}
