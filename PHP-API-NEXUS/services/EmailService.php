<?php
class EmailService {
    private $fromEmail;
    private $fromName;
    private $baseUrl;

    public function __construct() {
        // Configuración desde variables de entorno
        $this->fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@nexusastralis.com';
        $this->fromName = $_ENV['SMTP_FROM_NAME'] ?? 'Nexus Astralis';
        $this->baseUrl = $_ENV['APP_BASE_URL'] ?? 'http://localhost:8080/Skeletons/PHP-API-NEXUS';
    }

    // Enviar email de confirmación de registro
    public function sendEmailConfirmation($userEmail, $userName, $confirmationToken) {
        $confirmationUrl = $this->baseUrl . "/api/Auth/ConfirmEmail?token=" . urlencode($confirmationToken);
        
        $subject = "Confirma tu registro en Nexus Astralis";
        
        $htmlBody = $this->getConfirmationEmailTemplate($userName, $confirmationUrl);
        
        $textBody = $this->getConfirmationEmailText($userName, $confirmationUrl);
        
        return $this->sendEmail($userEmail, $subject, $htmlBody, $textBody);
    }

    // Plantilla HTML para email de confirmación
    private function getConfirmationEmailTemplate($userName, $confirmationUrl) {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirma tu registro</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .button:hover { background: #218838; }
                .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #666; }
                .logo { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
                .stars { font-size: 20px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='logo'>🌌 Nexus Astralis</div>
                <div class='stars'>✨ Conectando con las estrellas ✨</div>
            </div>
            <div class='content'>
                <h2>¡Bienvenido " . htmlspecialchars($userName) . "!</h2>
                
                <p>Gracias por registrarte en <strong>Nexus Astralis</strong>, tu plataforma para explorar el cosmos y conectar con otros amantes de la astronomía.</p>
                
                <p>Para completar tu registro y activar tu cuenta, haz clic en el botón de abajo:</p>
                
                <div style='text-align: center;'>
                    <a href='" . htmlspecialchars($confirmationUrl) . "' class='button'>
                        🚀 CONFIRMAR MI REGISTRO
                    </a>
                </div>
                
                <p><strong>¿No puedes hacer clic en el botón?</strong> Copia y pega este enlace en tu navegador:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace;'>" . htmlspecialchars($confirmationUrl) . "</p>
                
                <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <strong>⚠️ Importante:</strong> Este enlace es válido por 24 horas. Si no confirmas tu registro dentro de este tiempo, deberás registrarte nuevamente.
                </div>
                
                <p>Si no te has registrado en Nexus Astralis, puedes ignorar este mensaje.</p>
                
                <p>¡Esperamos verte pronto explorando las maravillas del universo!</p>
                
                <p>Saludos estelares,<br>
                <strong>El equipo de Nexus Astralis</strong> 🌟</p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " Nexus Astralis. Todos los derechos reservados.</p>
                <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            </div>
        </body>
        </html>";
    }

    // Versión texto plano del email
    private function getConfirmationEmailText($userName, $confirmationUrl) {
        return "
¡Bienvenido " . $userName . "!

Gracias por registrarte en Nexus Astralis, tu plataforma para explorar el cosmos.

Para completar tu registro y activar tu cuenta, visita este enlace:
" . $confirmationUrl . "

IMPORTANTE: Este enlace es válido por 24 horas.

Si no te has registrado en Nexus Astralis, puedes ignorar este mensaje.

¡Esperamos verte pronto explorando las maravillas del universo!

Saludos estelares,
El equipo de Nexus Astralis

© " . date('Y') . " Nexus Astralis. Todos los derechos reservados.
        ";
    }

    // Método principal para enviar emails usando sendmail
    private function sendEmail($to, $subject, $htmlBody, $textBody = null) {
        try {
            // Headers del email
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'X-Mailer: PHP/' . phpversion(),
                'X-Priority: 3',
                'Return-Path: ' . $this->fromEmail
            ];

            // Si hay versión texto, crear email multipart
            if ($textBody) {
                $boundary = uniqid('np');
                
                $headers[1] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
                
                $body = "--" . $boundary . "\r\n";
                $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $textBody . "\r\n\r\n";
                
                $body .= "--" . $boundary . "\r\n";
                $body .= "Content-Type: text/html; charset=UTF-8\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $htmlBody . "\r\n\r\n";
                
                $body .= "--" . $boundary . "--";
            } else {
                $body = $htmlBody;
            }

            // Enviar usando mail() que usará sendmail
            $result = mail(
                $to,
                $subject,
                $body,
                implode("\r\n", $headers)
            );

            if ($result) {
                error_log("Email enviado exitosamente a: " . $to);
                return [
                    'success' => true,
                    'message' => 'Email enviado exitosamente'
                ];
            } else {
                error_log("Error enviando email a: " . $to);
                return [
                    'success' => false,
                    'message' => 'Error enviando email'
                ];
            }

        } catch (Exception $e) {
            error_log("Excepción enviando email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno enviando email: ' . $e->getMessage()
            ];
        }
    }

    // Enviar email de bienvenida después de confirmar
    public function sendWelcomeEmail($userEmail, $userName) {
        $subject = "¡Bienvenido a Nexus Astralis! 🌟";
        
        $htmlBody = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🎉 ¡Registro Confirmado! 🎉</h1>
            </div>
            <div style='padding: 30px;'>
                <h2>¡Hola " . htmlspecialchars($userName) . "!</h2>
                <p>Tu cuenta en Nexus Astralis ha sido confirmada exitosamente. ¡Ya puedes comenzar a explorar el universo!</p>
                <p>Ahora puedes:</p>
                <ul>
                    <li>🌟 Explorar constelaciones</li>
                    <li>💫 Guardar tus favoritos</li>
                    <li>💬 Dejar comentarios</li>
                    <li>👤 Personalizar tu perfil</li>
                </ul>
                <p>¡Disfruta tu viaje por las estrellas!</p>
                <p>Saludos estelares,<br><strong>El equipo de Nexus Astralis</strong></p>
            </div>
        </body>
        </html>";

        return $this->sendEmail($userEmail, $subject, $htmlBody);
    }
}
?>