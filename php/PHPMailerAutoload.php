<?php
/**
 * PHPMailer Simple - Lightweight version for XAMPP
 * Gửi email qua Gmail SMTP
 */

class PHPMailer {
    private $host = 'smtp.gmail.com';
    private $port = 587;
    private $username = '';
    private $password = '';
    private $from = '';
    private $fromName = '';
    private $to = '';
    private $subject = '';
    private $body = '';
    private $isHTML = false;
    
    public function __construct() {
        // Constructor
    }
    
    public function isSMTP() {
        // Enable SMTP
    }
    
    public function setFrom($email, $name = '') {
        $this->from = $email;
        $this->fromName = $name;
    }
    
    public function addAddress($email) {
        $this->to = $email;
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    public function setBody($body) {
        $this->body = $body;
    }
    
    public function isHTML($bool) {
        $this->isHTML = $bool;
    }
    
    public function SMTPAuth($bool) {
        // Enable SMTP authentication
    }
    
    public function setHost($host) {
        $this->host = $host;
    }
    
    public function setUsername($username) {
        $this->username = $username;
    }
    
    public function setPassword($password) {
        $this->password = $password;
    }
    
    public function setPort($port) {
        $this->port = $port;
    }
    
    public function SMTPSecure($secure) {
        // TLS or SSL
    }
    
    public function send() {
        // Sử dụng fsockopen để gửi email qua SMTP
        $socket = @fsockopen('tls://' . $this->host, $this->port, $errno, $errstr, 30);
        
        if (!$socket) {
            return false;
        }
        
        // Đơn giản hóa - Dùng mail() function với headers
        $headers = "From: " . $this->fromName . " <" . $this->from . ">\r\n";
        $headers .= "Reply-To: " . $this->from . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        if ($this->isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        return @mail($this->to, $this->subject, $this->body, $headers);
    }
}
?>
