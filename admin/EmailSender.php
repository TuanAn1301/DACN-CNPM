<?php
/**
 * EmailSender - Class gửi email qua SMTP Gmail
 * Không cần Composer, sử dụng fsockopen để kết nối SMTP
 */

class EmailSender {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    private $debug;
    private $socket;
    private $error;
    private $log_buffer;
    
    public function __construct($config = []) {
        $this->smtp_host = $config['host'] ?? 'smtp.gmail.com';
        $this->smtp_port = $config['port'] ?? 587;
        $this->smtp_username = $config['username'] ?? '';
        $this->smtp_password = $config['password'] ?? '';
        $this->from_email = $config['from_email'] ?? $this->smtp_username;
        $this->from_name = $config['from_name'] ?? '';
        $this->debug = $config['debug'] ?? false;
        $this->error = '';
        $this->log_buffer = [];
    }
    
    /**
     * Gửi email
     */
    public function send($to, $to_name, $subject, $html_body, $text_body = '') {
        try {
            // Kết nối SMTP
            if (!$this->connect()) {
                return false;
            }
            
            // EHLO
            if (!$this->command("EHLO " . $this->smtp_host, 250)) {
                return false;
            }
            
            // STARTTLS - Gửi lệnh
            $this->log("CLIENT: STARTTLS");
            fputs($this->socket, "STARTTLS\r\n");
            
            // Đọc tất cả response lines
            $response = '';
            $code = '';
            while ($line = fgets($this->socket, 515)) {
                $this->log("SERVER: " . trim($line));
                $response .= $line;
                
                if (empty($code)) {
                    $code = substr($line, 0, 3);
                }
                
                // Dừng khi gặp dòng cuối (không có dấu -)
                if (isset($line[3]) && $line[3] == ' ') {
                    break;
                }
            }
            
            if ($code != '220' && $code != '250') {
                $this->error = "STARTTLS thất bại: " . trim($response);
                $this->log("ERROR: " . $this->error);
                return false;
            }
            
            // Nâng cấp kết nối lên TLS
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->error = "Không thể bật TLS encryption";
                $this->log("ERROR: " . $this->error);
                return false;
            }
            
            $this->log("TLS encryption enabled successfully");
            
            // EHLO lại sau khi TLS
            if (!$this->command("EHLO " . $this->smtp_host, 250)) {
                return false;
            }
            
            // AUTH LOGIN
            if (!$this->command("AUTH LOGIN", 334)) {
                return false;
            }
            
            // Username
            if (!$this->command(base64_encode($this->smtp_username), 334)) {
                return false;
            }
            
            // Password
            if (!$this->command(base64_encode($this->smtp_password), 235)) {
                return false;
            }
            
            // MAIL FROM
            if (!$this->command("MAIL FROM: <" . $this->from_email . ">", 250)) {
                return false;
            }
            
            // RCPT TO
            if (!$this->command("RCPT TO: <" . $to . ">", 250)) {
                return false;
            }
            
            // DATA
            if (!$this->command("DATA", 354)) {
                return false;
            }
            
            // Email headers và body
            $message = $this->buildMessage($to, $to_name, $subject, $html_body, $text_body);
            
            // Gửi message
            if (!$this->command($message . "\r\n.", 250)) {
                return false;
            }
            
            // QUIT
            $this->command("QUIT", 221);
            
            // Đóng kết nối
            fclose($this->socket);
            
            return true;
            
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->log("EXCEPTION: " . $this->error);
            return false;
        }
    }
    
    /**
     * Kết nối đến SMTP server
     */
    private function connect() {
        $this->log("Đang kết nối đến " . $this->smtp_host . ":" . $this->smtp_port);
        
        $this->socket = @fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
        
        if (!$this->socket) {
            $this->error = "Không thể kết nối: $errstr ($errno)";
            $this->log("ERROR: " . $this->error);
            return false;
        }
        
        // Đọc response ban đầu
        $response = fgets($this->socket, 515);
        $this->log("SERVER: " . trim($response));
        
        if (substr($response, 0, 3) != '220') {
            $this->error = "Kết nối thất bại: " . $response;
            $this->log("ERROR: " . $this->error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Gửi lệnh SMTP và kiểm tra response
     */
    private function command($cmd, $expected_code) {
        $this->log("CLIENT: " . $cmd);
        
        fputs($this->socket, $cmd . "\r\n");
        
        // Đọc tất cả các dòng response (xử lý multi-line response)
        $response = '';
        $code = '';
        
        while ($line = fgets($this->socket, 515)) {
            $this->log("SERVER: " . trim($line));
            $response .= $line;
            
            // Lấy mã response (3 ký tự đầu)
            if (empty($code)) {
                $code = substr($line, 0, 3);
            }
            
            // Nếu ký tự thứ 4 là space (không phải dấu -), đây là dòng cuối
            if (isset($line[3]) && $line[3] == ' ') {
                break;
            }
        }
        
        if ($code != $expected_code) {
            $this->error = "Lỗi lệnh '$cmd': " . trim($response);
            $this->log("ERROR: " . $this->error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Xây dựng email message
     */
    private function buildMessage($to, $to_name, $subject, $html_body, $text_body) {
        $boundary = "----=_Part_" . md5(time());
        
        $message = "";
        $message .= "From: " . $this->encodeMimeHeader($this->from_name) . " <" . $this->from_email . ">\r\n";
        $message .= "To: " . $this->encodeMimeHeader($to_name) . " <" . $to . ">\r\n";
        $message .= "Subject: " . $this->encodeMimeHeader($subject) . "\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"\r\n";
        $message .= "\r\n";
        
        // Text part
        if ($text_body) {
            $message .= "--" . $boundary . "\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $message .= "\r\n";
            $message .= quoted_printable_encode($text_body) . "\r\n";
        }
        
        // HTML part
        $message .= "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $message .= "\r\n";
        $message .= quoted_printable_encode($html_body) . "\r\n";
        
        $message .= "--" . $boundary . "--\r\n";
        
        return $message;
    }
    
    /**
     * Encode MIME header (hỗ trợ tiếng Việt)
     */
    private function encodeMimeHeader($text) {
        if (preg_match('/[^\x20-\x7E]/', $text)) {
            return "=?UTF-8?B?" . base64_encode($text) . "?=";
        }
        return $text;
    }
    
    /**
     * Log debug
     */
    private function log($message) {
        if ($this->debug) {
            $this->log_buffer[] = "[" . date('Y-m-d H:i:s') . "] " . $message;
        }
    }
    
    /**
     * Lấy thông báo lỗi
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Lấy debug log
     */
    public function getLog() {
        return implode("\n", $this->log_buffer);
    }
    
    /**
     * In debug log ra màn hình
     */
    public function printLog() {
        if (!empty($this->log_buffer)) {
            echo implode("\n", $this->log_buffer) . "\n";
        }
    }
}
?>
