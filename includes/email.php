<?php
require_once  'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['EMAIL_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['EMAIL_USERNAME'];
        $this->mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $this->mail->SMTPSecure = $_ENV['EMAIL_ENCRYPTION'];
        $this->mail->Port       = $_ENV['EMAIL_PORT'];
        
        // From
        $this->mail->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME']);
        $this->mail->isHTML(true);
    }

    public function sendEmail($to, $subject, $template, $data = []) {
        try {
            // Recipients
            $this->mail->addAddress($to);
            
            // Content
            $this->mail->Subject = $subject;
            $this->mail->Body    = $this->renderTemplate($template, $data);
            
            $this->mail->send();
            
            // Log the email
            $this->logEmail($to, $subject, 'sent', '');
            
            return true;
        } catch (Exception $e) {
            $this->logEmail($to, $subject, 'failed', $e->getMessage());
            return false;
        }
    }

    private function renderTemplate($template, $data) {
        $templatePath = __DIR__ . "/../templates/email/{$template}.php";
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found: {$template}");
        }
        
        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    private function logEmail($recipient, $subject, $status, $response) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO email_logs (recipient, subject, status, response) VALUES (?, ?, ?, ?)");
        $stmt->execute([$recipient, $subject, $status, $response]);
    }
}
?>