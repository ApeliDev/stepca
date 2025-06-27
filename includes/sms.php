<?php
require_once 'config.php';


class SMS {
    private $apiKey;
    private $userid;
    private $password;
    private $senderId;
    private $baseUrl;
    private $db;

    public function __construct() {
        $this->apiKey = $_ENV['SMS_API_KEY'];
        $this->userid = $_ENV['SMS_USERID'];
        $this->password = $_ENV['SMS_PASSWORD'];
        $this->senderId = $_ENV['SMS_SENDER_ID'];
        $this->baseUrl = $_ENV['SMS_SEND_URL'];
        
        // Initialize database connection
        $this->db = (new Database())->connect();
    }

    public function sendSMS($mobile, $message) {
        if (!$_ENV['SMS_ENABLED']) {
            return ['status' => 'success'];
        }

        try {
            $currentDateTime = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
            $date = $currentDateTime->format('Y-m-d H:i:s');
            
            $phone = $this->formatPhone($mobile);

            $url = $this->baseUrl;

            $postData = http_build_query([
                'userid' => $this->userid,
                'password' => $this->password,
                'mobile' => $phone,
                'msg' => $message,
                'senderid' => $this->senderId,
                'msgType' => 'text',
                'duplicatecheck' => 'true',
                'output' => 'json',
                'sendMethod' => 'quick'
            ]);

            $headers = [
                'apikey: ' . $this->apiKey,
                'cache-control: no-cache',
                'content-type: application/x-www-form-urlencoded'
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                throw new Exception('cURL Error: ' . $err);
            }

            $result = json_decode($response, true);

            // Log the SMS
            $this->logSMS($mobile, $message, $httpCode == 200 ? 'sent' : 'failed', $response, $date);

            // Check if the response indicates success
            if ($httpCode == 200) {
                return ['status' => 'success', 'data' => $result, 'response' => $response];
            } else {
                return [
                    'status' => 'error', 
                    'message' => $result['message'] ?? 'Failed to send SMS',
                    'code' => $httpCode,
                    'response' => $response
                ];
            }

        } catch (Exception $e) {
            $this->logSMS($mobile, $message, 'failed', $e->getMessage(), date('Y-m-d H:i:s'));
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    

    
    private function formatPhone($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format phone number for HostPinnacle API (Kenyan format)
        $phone = preg_replace('/^(?:\+?254|0)?/', '254', $phone);
        
        return $phone;
    }

    private function logSMS($recipient, $message, $status, $response, $date) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sms_logs (recipient, message, status, response, sent_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$recipient, $message, $status, $response, $date]);
        } catch (Exception $e) {
            error_log("Failed to log SMS: " . $e->getMessage());
        }
    }


    public function getSMSLogs($limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM sms_logs 
                ORDER BY sent_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get SMS logs: " . $e->getMessage());
            return [];
        }
    }
}
?>