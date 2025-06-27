<?php
/**
 * AI Training Script
 * Manages conversations, messages, and AI context for training purposes
 */

class AITrainingManager {
    private $pdo;
    private $config;
    
    public function __construct($database_config) {
        $this->config = $database_config;
        $this->initDatabase();
    }
    
    /**
     * Initialize database connection
     */
    private function initDatabase() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new conversation
     */
    public function createConversation($user_id, $title = null) {
        $sql = "INSERT INTO chat_conversations (user_id, title) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $title]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Add a message to conversation
     */
    public function addMessage($conversation_id, $sender_type, $sender_id, $message, $message_type = 'text', $metadata = null) {
        $sql = "INSERT INTO chat_messages (conversation_id, sender_type, sender_id, message, message_type, metadata) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id, $sender_type, $sender_id, $message, $message_type, $metadata]);
        
        $message_id = $this->pdo->lastInsertId();
        
        // Update conversation timestamp
        $this->updateConversationTimestamp($conversation_id);
        
        return $message_id;
    }
    
    /**
     * Get conversation messages for AI training
     */
    public function getConversationMessages($conversation_id, $limit = null) {
        $sql = "SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC";
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Update AI context for conversation
     */
    public function updateAIContext($conversation_id, $context_data) {
        $json_context = is_array($context_data) ? json_encode($context_data) : $context_data;
        
        $sql = "INSERT INTO ai_chat_context (conversation_id, context_data) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE context_data = ?, updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id, $json_context, $json_context]);
    }
    
    /**
     * Get AI context for conversation
     */
    public function getAIContext($conversation_id) {
        $sql = "SELECT context_data FROM ai_chat_context WHERE conversation_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id]);
        
        $result = $stmt->fetch();
        return $result ? json_decode($result['context_data'], true) : null;
    }
    
    /**
     * Generate training data from conversations
     */
    public function generateTrainingData($filters = []) {
        $where_conditions = ["c.status = 'active'"];
        $params = [];
        
        // Add filters
        if (!empty($filters['user_id'])) {
            $where_conditions[] = "c.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "c.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "c.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT c.id as conversation_id, c.title, c.created_at,
                       GROUP_CONCAT(
                           CONCAT(m.sender_type, ':', m.message) 
                           ORDER BY m.created_at 
                           SEPARATOR '|||'
                       ) as conversation_flow
                FROM chat_conversations c
                LEFT JOIN chat_messages m ON c.id = m.conversation_id
                WHERE $where_clause
                GROUP BY c.id
                ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Process conversation for AI training format
     */
    public function processConversationForTraining($conversation_id) {
        $messages = $this->getConversationMessages($conversation_id);
        $training_pairs = [];
        
        for ($i = 0; $i < count($messages) - 1; $i++) {
            $current = $messages[$i];
            $next = $messages[$i + 1];
            
            // Create training pairs (user input -> AI response)
            if ($current['sender_type'] === 'user' && $next['sender_type'] === 'ai') {
                $training_pairs[] = [
                    'input' => $current['message'],
                    'output' => $next['message'],
                    'context' => $this->getConversationContext($messages, $i),
                    'timestamp' => $current['created_at']
                ];
            }
        }
        
        return $training_pairs;
    }
    
    /**
     * Get conversation context for training
     */
    private function getConversationContext($messages, $current_index, $context_length = 5) {
        $context = [];
        $start_index = max(0, $current_index - $context_length);
        
        for ($i = $start_index; $i < $current_index; $i++) {
            $context[] = [
                'sender' => $messages[$i]['sender_type'],
                'message' => $messages[$i]['message']
            ];
        }
        
        return $context;
    }
    
    /**
     * Export training data to various formats
     */
    public function exportTrainingData($format = 'json', $filters = []) {
        $conversations = $this->generateTrainingData($filters);
        $training_data = [];
        
        foreach ($conversations as $conv) {
            $training_pairs = $this->processConversationForTraining($conv['conversation_id']);
            if (!empty($training_pairs)) {
                $training_data[] = [
                    'conversation_id' => $conv['conversation_id'],
                    'title' => $conv['title'],
                    'created_at' => $conv['created_at'],
                    'training_pairs' => $training_pairs
                ];
            }
        }
        
        switch ($format) {
            case 'json':
                return json_encode($training_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            case 'csv':
                return $this->convertToCSV($training_data);
            
            case 'txt':
                return $this->convertToText($training_data);
            
            default:
                return $training_data;
        }
    }
    
    /**
     * Convert training data to CSV format
     */
    private function convertToCSV($data) {
        $csv = "conversation_id,input,output,context,timestamp\n";
        
        foreach ($data as $conversation) {
            foreach ($conversation['training_pairs'] as $pair) {
                $context_str = json_encode($pair['context']);
                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",%s\n",
                    $conversation['conversation_id'],
                    addslashes($pair['input']),
                    addslashes($pair['output']),
                    addslashes($context_str),
                    $pair['timestamp']
                );
            }
        }
        
        return $csv;
    }
    
    /**
     * Convert training data to text format
     */
    private function convertToText($data) {
        $text = "";
        
        foreach ($data as $conversation) {
            $text .= "=== Conversation {$conversation['conversation_id']} ===\n";
            $text .= "Title: {$conversation['title']}\n";
            $text .= "Date: {$conversation['created_at']}\n\n";
            
            foreach ($conversation['training_pairs'] as $pair) {
                $text .= "INPUT: {$pair['input']}\n";
                $text .= "OUTPUT: {$pair['output']}\n";
                $text .= "---\n";
            }
            $text .= "\n";
        }
        
        return $text;
    }
    
    /**
     * Update conversation timestamp
     */
    private function updateConversationTimestamp($conversation_id) {
        $sql = "UPDATE chat_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id]);
    }
    
    /**
     * Get conversation statistics
     */
    public function getStatistics($user_id = null) {
        $where_clause = $user_id ? "WHERE user_id = ?" : "";
        $params = $user_id ? [$user_id] : [];
        
        $sql = "SELECT 
                    COUNT(*) as total_conversations,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_conversations,
                    AVG((SELECT COUNT(*) FROM chat_messages WHERE conversation_id = chat_conversations.id)) as avg_messages_per_conversation
                FROM chat_conversations $where_clause";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
}

?>