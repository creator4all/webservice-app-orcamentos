<?php
namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'] ?? 'user@example.com';
        $mail->Password = $_ENV['SMTP_PASSWORD'] ?? 'password';
        $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'] ?? 'tls';
        $mail->Port = $_ENV['SMTP_PORT'] ?? 587;
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@example.com', $_ENV['SMTP_FROM_NAME'] ?? 'Sistema de Orçamentos');
        $mail->CharSet = 'UTF-8';
        return $mail;
    }

    public static function enviarEmailRecuperacaoSenha($email, $nome, $token) {
        try {
            $mail = self::getMailer();
            $mail->addAddress($email, $nome);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha - Multimídia Parceiros: (B2B)';
            
            $corpo = "
                <html>
                <head>
                    <title>Recuperação de Senha</title>
                </head>
                <body>
                    <p>Olá {$nome},</p>
                    <p>Recebemos uma solicitação para redefinir sua senha.</p>
                    <p>Seu código de verificação é: <strong>{$token}</strong></p>
                    <p>Este código é válido por 30 minutos.</p>
                    <p>Se você não solicitou esta recuperação de senha, ignore este e-mail.</p>
                    <p>Atenciosamente,<br>Equipe do Sistema de Orçamentos</p>
                </body>
                </html>
            ";
            
            $mail->Body = $corpo;
            $mail->AltBody = "Olá {$nome}, seu código de recuperação de senha é: {$token}. Este código é válido por 30 minutos.";
            
            return $mail->send();
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
