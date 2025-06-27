<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Training & Response Interface</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .nav-tabs {
            display: flex;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 5px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .nav-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: transparent;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .nav-tab.active {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .tab-content {
            display: none;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .chat-interface {
            display: flex;
            flex-direction: column;
            height: 600px;
        }

        .chat-messages {
            flex: 1;
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            padding: 20px;
            overflow-y: auto;
            margin-bottom: 20px;
            background: #f9f9f9;
        }

        .message {
            margin-bottom: 15px;
            padding: 12px 18px;
            border-radius: 18px;
            max-width: 80%;
            word-wrap: break-word;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .message.user {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .message.ai {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .message.system {
            background: #e9ecef;
            color: #666;
            font-style: italic;
            text-align: center;
            margin: 0 auto;
        }

        .input-area {
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .message-input:focus {
            border-color: #667eea;
        }

        .send-btn, .train-btn {
            padding: 15px 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .send-btn:hover, .train-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .training-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .training-panel {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            border: 2px solid #e9ecef;
        }

        .training-panel h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .conversation-list {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            padding: 20px;
        }

        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .conversation-item:hover {
            background: #f8f9fa;
        }

        .conversation-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-left: 4px solid #667eea;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .status-active { background: #28a745; }
        .status-training { background: #ffc107; }
        .status-idle { background: #6c757d; }

        .export-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
        }

        .export-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .training-section {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .export-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 AI Training Interface</h1>
            <p>Train your AI and interact with intelligent responses</p>
        </div>

        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('chat')">💬 Chat Interface</button>
            <button class="nav-tab" onclick="switchTab('training')">🎓 Training</button>
            <button class="nav-tab" onclick="switchTab('analytics')">📊 Analytics</button>
            <button class="nav-tab" onclick="switchTab('management')">⚙️ Management</button>
        </div>

        <!-- Chat Interface Tab -->
        <div id="chat" class="tab-content active">
            <div class="chat-interface">
                <div class="chat-messages" id="chatMessages">
                    <div class="message system">
                        <span class="status-indicator status-active"></span>
                        AI Training Interface is ready! Start a conversation to train your AI.
                    </div>
                </div>
                <div class="input-area">
                    <input type="text" class="message-input" id="messageInput" placeholder="Type your message here..." onkeypress="handleKeyPress(event)">
                    <button class="send-btn" onclick="sendMessage()">Send</button>
                    <button class="train-btn" onclick="addToTraining()">Add to Training</button>
                </div>
            </div>
        </div>

        <!-- Training Tab -->
        <div id="training" class="tab-content">
            <div class="training-section">
                <div class="training-panel">
                    <h3>📝 Add Training Data</h3>
                    <div class="form-group">
                        <label>User Input:</label>
                        <textarea class="form-control" id="trainInput" placeholder="Enter user question or input..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Expected AI Response:</label>
                        <textarea class="form-control" id="trainOutput" placeholder="Enter the expected AI response..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Category/Topic:</label>
                        <input type="text" class="form-control" id="trainCategory" placeholder="e.g., general, support, technical">
                    </div>
                    <button class="btn-primary" onclick="addTrainingData()">Add Training Data</button>
                </div>

                <div class="training-panel">
                    <h3>🚀 Training Controls</h3>
                    <div class="form-group">
                        <label>Training Mode:</label>
                        <select class="form-control" id="trainingMode">
                            <option value="supervised">Supervised Learning</option>
                            <option value="reinforcement">Reinforcement Learning</option>
                            <option value="fine-tuning">Fine-tuning</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Batch Size:</label>
                        <input type="number" class="form-control" id="batchSize" value="32" min="1" max="256">
                    </div>
                    <div class="form-group">
                        <label>Learning Rate:</label>
                        <input type="number" class="form-control" id="learningRate" value="0.001" step="0.001" min="0.0001" max="1">
                    </div>
                    <button class="btn-primary" onclick="startTraining()">Start Training</button>
                    <div id="trainingProgress" style="margin-top: 15px;"></div>
                </div>
            </div>

            <div class="export-section">
                <h3>📦 Export Training Data</h3>
                <p>Export your training data in various formats for external use.</p>
                <div class="export-buttons">
                    <button class="btn-primary" onclick="exportData('json')">Export as JSON</button>
                    <button class="btn-primary" onclick="exportData('csv')">Export as CSV</button>
                    <button class="btn-primary" onclick="exportData('txt')">Export as Text</button>
                    <button class="btn-primary" onclick="exportData('xml')">Export as XML</button>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics" class="tab-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="totalConversations">0</div>
                    <div>Total Conversations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalMessages">0</div>
                    <div>Total Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="trainingPairs">0</div>
                    <div>Training Pairs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="aiAccuracy">0%</div>
                    <div>AI Accuracy</div>
                </div>
            </div>

            <div class="training-panel">
                <h3>📈 Performance Metrics</h3>
                <div id="performanceChart" style="height: 300px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #666;">
                    Performance chart will be displayed here
                </div>
            </div>

            <div class="training-panel">
                <h3>🔍 Recent Activity</h3>
                <div id="recentActivity">
                    <div class="conversation-item">
                        <span class="status-indicator status-active"></span>
                        <strong>New conversation started</strong> - 2 minutes ago
                    </div>
                    <div class="conversation-item">
                        <span class="status-indicator status-training"></span>
                        <strong>Training data added</strong> - 5 minutes ago
                    </div>
                    <div class="conversation-item">
                        <span class="status-indicator status-idle"></span>
                        <strong>Export completed</strong> - 10 minutes ago
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tab -->
        <div id="management" class="tab-content">
            <div class="training-section">
                <div class="training-panel">
                    <h3>💾 Conversation Management</h3>
                    <div class="conversation-list" id="conversationList">
                        <!-- Conversations will be loaded here -->
                    </div>
                    <button class="btn-primary" onclick="loadConversations()">Refresh Conversations</button>
                </div>

                <div class="training-panel">
                    <h3>🔧 System Settings</h3>
                    <div class="form-group">
                        <label>AI Response Delay (ms):</label>
                        <input type="number" class="form-control" id="responseDelay" value="1000" min="0" max="10000">
                    </div>
                    <div class="form-group">
                        <label>Max Context Length:</label>
                        <input type="number" class="form-control" id="maxContext" value="10" min="1" max="50">
                    </div>
                    <div class="form-group">
                        <label>Auto-save Training Data:</label>
                        <select class="form-control" id="autoSave">
                            <option value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                    <button class="btn-primary" onclick="saveSettings()">Save Settings</button>
                </div>
            </div>

            <div class="training-panel">
                <h3>🗂️ Data Management</h3>
                <div class="export-buttons">
                    <button class="btn-primary" onclick="backupData()">Backup All Data</button>
                    <button class="btn-primary" onclick="clearTraining()">Clear Training Data</button>
                    <button class="btn-primary" onclick="resetConversations()">Reset Conversations</button>
                    <button class="btn-primary" onclick="importData()">Import Data</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentConversationId = null;
        let trainingData = [];
        let conversations = [];
        let isTraining = false;

        // Initialize the interface
        document.addEventListener('DOMContentLoaded', function() {
            loadSettings();
            updateStats();
            loadConversations();
        });

        // Tab switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all nav tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked nav tab
            event.target.classList.add('active');
            
            // Load tab-specific data
            if (tabName === 'analytics') {
                updateStats();
            } else if (tabName === 'management') {
                loadConversations();
            }
        }

        // Chat functionality
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            addMessageToChat('user', message);
            input.value = '';
            
            // Simulate AI response
            setTimeout(() => {
                const aiResponse = generateAIResponse(message);
                addMessageToChat('ai', aiResponse);
            }, parseInt(document.getElementById('responseDelay')?.value || 1000));
        }

        function addMessageToChat(sender, message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            messageDiv.textContent = message;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Store message for training
            if (!currentConversationId) {
                currentConversationId = Date.now();
            }
            
            storeMessage(currentConversationId, sender, message);
        }

        function generateAIResponse(userMessage) {
            // Simple rule-based responses for demo
            const responses = [
                "That's an interesting question! Let me think about that.",
                "I understand what you're asking. Here's my perspective:",
                "Based on the training data, I would say:",
                "That's a great point. Let me elaborate:",
                "I can help you with that. Here's what I know:",
                "Thank you for that question. My response is:",
            ];
            
            // Add some context-aware responses
            if (userMessage.toLowerCase().includes('hello') || userMessage.toLowerCase().includes('hi')) {
                return "Hello! I'm your AI assistant. How can I help you today?";
            }
            
            if (userMessage.toLowerCase().includes('help')) {
                return "I'm here to help! You can ask me questions, and I'll do my best to provide useful responses based on my training.";
            }
            
            if (userMessage.toLowerCase().includes('train')) {
                return "Training is important for AI development. You can add our conversation to the training data using the 'Add to Training' button.";
            }
            
            return responses[Math.floor(Math.random() * responses.length)] + " " + 
                   "This is a simulated response based on your input: '" + userMessage + "'";
        }

        function addToTraining() {
            const messages = document.querySelectorAll('#chatMessages .message');
            if (messages.length < 2) {
                showAlert('Need at least one user message and one AI response to add to training.', 'error');
                return;
            }
            
            const lastUserMessage = Array.from(messages).reverse().find(msg => msg.classList.contains('user'));
            const lastAIMessage = Array.from(messages).reverse().find(msg => msg.classList.contains('ai'));
            
            if (lastUserMessage && lastAIMessage) {
                addTrainingPair(lastUserMessage.textContent, lastAIMessage.textContent);
                showAlert('Conversation added to training data successfully!', 'success');
            }
        }

        // Training functionality
        function addTrainingData() {
            const input = document.getElementById('trainInput').value.trim();
            const output = document.getElementById('trainOutput').value.trim();
            const category = document.getElementById('trainCategory').value.trim();
            
            if (!input || !output) {
                showAlert('Please fill in both input and output fields.', 'error');
                return;
            }
            
            addTrainingPair(input, output, category);
            
            // Clear form
            document.getElementById('trainInput').value = '';
            document.getElementById('trainOutput').value = '';
            document.getElementById('trainCategory').value = '';
            
            showAlert('Training data added successfully!', 'success');
        }

        function addTrainingPair(input, output, category = 'general') {
            const trainingPair = {
                id: Date.now() + Math.random(),
                input: input,
                output: output,
                category: category,
                timestamp: new Date().toISOString(),
                quality_score: 1.0
            };
            
            trainingData.push(trainingPair);
            saveToLocalStorage('trainingData', trainingData);
            updateStats();
        }

        function startTraining() {
            if (isTraining) {
                showAlert('Training is already in progress.', 'error');
                return;
            }
            
            if (trainingData.length === 0) {
                showAlert('No training data available. Please add some training pairs first.', 'error');
                return;
            }
            
            isTraining = true;
            const progressDiv = document.getElementById('trainingProgress');
            progressDiv.innerHTML = '<div class="loading"></div> Training in progress...';
            
            // Simulate training process
            let progress = 0;
            const trainingInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(trainingInterval);
                    isTraining = false;
                    progressDiv.innerHTML = '✅ Training completed successfully!';
                    showAlert('AI training completed successfully!', 'success');
                    updateStats();
                } else {
                    progressDiv.innerHTML = `<div class="loading"></div> Training progress: ${Math.round(progress)}%`;
                }
            }, 500);
        }

        // Export functionality
        function exportData(format) {
            if (trainingData.length === 0) {
                showAlert('No training data to export.', 'error');
                return;
            }
            
            let content = '';
            let filename = `training_data_${new Date().toISOString().split('T')[0]}`;
            
            switch (format) {
                case 'json':
                    content = JSON.stringify(trainingData, null, 2);
                    filename += '.json';
                    break;
                case 'csv':
                    content = 'Input,Output,Category,Timestamp,Quality Score\n';
                    trainingData.forEach(item => {
                        content += `"${item.input}","${item.output}","${item.category}","${item.timestamp}","${item.quality_score}"\n`;
                    });
                    filename += '.csv';
                    break;
                case 'txt':
                    trainingData.forEach(item => {
                        content += `Input: ${item.input}\nOutput: ${item.output}\nCategory: ${item.category}\nTimestamp: ${item.timestamp}\n\n---\n\n`;
                    });
                    filename += '.txt';
                    break;
                case 'xml':
                    content = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n<training_data>\n';
                    trainingData.forEach(item => {
                        content += `  <training_pair>\n    <input><![CDATA[${item.input}]]></input>\n    <output><![CDATA[${item.output}]]></output>\n    <category>${item.category}</category>\n    <timestamp>${item.timestamp}</timestamp>\n    <quality_score>${item.quality_score}</quality_score>\n  </training_pair>\n`;
                    });
                    content += '</training_data>';
                    filename += '.xml';
                    break;
            }
            
            downloadFile(content, filename);
            showAlert(`Training data exported as ${format.toUpperCase()} successfully!`, 'success');
        }

        // Utility functions
        function downloadFile(content, filename) {
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const container = document.querySelector('.tab-content.active');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        function storeMessage(conversationId, sender, message) {
            const messageData = {
                conversation_id: conversationId,
                sender: sender,
                message: message,
                timestamp: new Date().toISOString()
            };
            
            let messages = JSON.parse(localStorage.getItem('messages') || '[]');
            messages.push(messageData);
            localStorage.setItem('messages', JSON.stringify(messages));
        }

        function saveToLocalStorage(key, data) {
            localStorage.setItem(key, JSON.stringify(data));
        }

        function loadFromLocalStorage(key) {
            const data = localStorage.getItem(key);
            return data ? JSON.parse(data) : [];
        }

        function updateStats() {
            trainingData = loadFromLocalStorage('trainingData');
            const messages = loadFromLocalStorage('messages');
            
            document.getElementById('totalConversations').textContent = new Set(messages.map(m => m.conversation_id)).size;
            document.getElementById('totalMessages').textContent = messages.length;
            document.getElementById('trainingPairs').textContent = trainingData.length;
            document.getElementById('aiAccuracy').textContent = Math.round(85 + Math.random() * 10) + '%';
        }

        function loadConversations() {
            const messages = loadFromLocalStorage('messages');
            const conversationIds = [...new Set(messages.map(m => m.conversation_id))];
            
            const conversationList = document.getElementById('conversationList');
            conversationList.innerHTML = '';
            
            conversationIds.forEach(id => {
                const conversationMessages = messages.filter(m => m.conversation_id === id);
                const firstMessage = conversationMessages[0];
                const lastMessage = conversationMessages[conversationMessages.length - 1];
                
                const item = document.createElement('div');
                item.className = 'conversation-item';
                item.innerHTML = `
                    <span class="status-indicator status-active"></span>
                    <strong>Conversation ${id}</strong><br>
                    <small>Messages: ${conversationMessages.length} | Last: ${new Date(lastMessage.timestamp).toLocaleString()}</small>
                `;
                item.onclick = () => loadConversation(id);
                
                conversationList.appendChild(item);
            });
        }

        function loadConversation(conversationId) {
            const messages = loadFromLocalStorage('messages');
            const conversationMessages = messages.filter(m => m.conversation_id === conversationId);
            
            // Clear current chat
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';
            
            // Load conversation messages
            conversationMessages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${msg.sender}`;
                messageDiv.textContent = msg.message;
                chatMessages.appendChild(messageDiv);
            });
            
            currentConversationId = conversationId;
            
            // Update UI
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Switch to chat tab
            switchTab('chat');
            showAlert(`Loaded conversation ${conversationId}`, 'success');
        }

        function loadSettings() {
            const settings = JSON.parse(localStorage.getItem('aiSettings') || '{}');
            
            if (settings.responseDelay) {
                document.getElementById('responseDelay').value = settings.responseDelay;
            }
            if (settings.maxContext) {
                document.getElementById('maxContext').value = settings.maxContext;
            }
            if (settings.autoSave !== undefined) {
                document.getElementById('autoSave').value = settings.autoSave;
            }
        }

        function saveSettings() {
            const settings = {
                responseDelay: document.getElementById('responseDelay').value,
                maxContext: document.getElementById('maxContext').value,
                autoSave: document.getElementById('autoSave').value
            };
            
            localStorage.setItem('aiSettings', JSON.stringify(settings));
            showAlert('Settings saved successfully!', 'success');
        }

        function backupData() {
            const backup = {
                trainingData: loadFromLocalStorage('trainingData'),
                messages: loadFromLocalStorage('messages'),
                settings: JSON.parse(localStorage.getItem('aiSettings') || '{}'),
                timestamp: new Date().toISOString()
            };
            
            const content = JSON.stringify(backup, null, 2);
            const filename = `ai_backup_${new Date().toISOString().split('T')[0]}.json`;
            downloadFile(content, filename);
            showAlert('Data backup created successfully!', 'success');
        }

        function clearTraining() {
            if (confirm('Are you sure you want to clear all training data? This action cannot be undone.')) {
                trainingData = [];
                localStorage.removeItem('trainingData');
                updateStats();
                showAlert('Training data cleared successfully!', 'success');
            }
        }

        function resetConversations() {
            if (confirm('Are you sure you want to reset all conversations? This action cannot be undone.')) {
                localStorage.removeItem('messages');
                document.getElementById('chatMessages').innerHTML = '<div class="message system"><span class="status-indicator status-active"></span>AI Training Interface is ready! Start a conversation to train your AI.</div>';
                currentConversationId = null;
                updateStats();
                loadConversations();
                showAlert('Conversations reset successfully!', 'success');
            }
        }

        function importData() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            input.onchange = function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const data = JSON.parse(e.target.result);
                            
                            if (data.trainingData) {
                                trainingData = data.trainingData;
                                saveToLocalStorage('trainingData', trainingData);
                            }
                            
                            if (data.messages) {
                                saveToLocalStorage('messages', data.messages);
                            }
                            
                            if (data.settings) {
                                localStorage.setItem('aiSettings', JSON.stringify(data.settings));
                                loadSettings();
                            }
                            
                            updateStats();
                            loadConversations();
                            showAlert('Data imported successfully!', 'success');
                        } catch (error) {
                            showAlert('Error importing data: Invalid file format', 'error');
                        }
                    };
                    reader.readAsText(file);
                }
            };
            input.click();
        }

        // Advanced AI response generation with context
        function generateAdvancedAIResponse(userMessage, conversationHistory = []) {
            const context = conversationHistory.slice(-5); // Last 5 messages for context
            
            // Check training data for similar patterns
            const relevantTraining = trainingData.filter(item => 
                item.input.toLowerCase().includes(userMessage.toLowerCase().split(' ')[0]) ||
                userMessage.toLowerCase().includes(item.input.toLowerCase().split(' ')[0])
            );
            
            if (relevantTraining.length > 0) {
                const bestMatch = relevantTraining.reduce((prev, curr) => 
                    similarity(userMessage, prev.input) > similarity(userMessage, curr.input) ? prev : curr
                );
                return bestMatch.output + " (Based on training data)";
            }
            
            return generateAIResponse(userMessage);
        }

        // Simple similarity function
        function similarity(str1, str2) {
            const longer = str1.length > str2.length ? str1 : str2;
            const shorter = str1.length > str2.length ? str2 : str1;
            const editDistance = levenshteinDistance(longer, shorter);
            return (longer.length - editDistance) / longer.length;
        }

        function levenshteinDistance(str1, str2) {
            const matrix = [];
            for (let i = 0; i <= str2.length; i++) {
                matrix[i] = [i];
            }
            for (let j = 0; j <= str1.length; j++) {
                matrix[0][j] = j;
            }
            for (let i = 1; i <= str2.length; i++) {
                for (let j = 1; j <= str1.length; j++) {
                    if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1,
                            matrix[i][j - 1] + 1,
                            matrix[i - 1][j] + 1
                        );
                    }
                }
            }
            return matrix[str2.length][str1.length];
        }

        // Auto-save functionality
        setInterval(() => {
            const autoSave = document.getElementById('autoSave')?.value;
            if (autoSave === 'true') {
                // Auto-save training data and messages
                if (trainingData.length > 0) {
                    saveToLocalStorage('trainingData', trainingData);
                }
            }
        }, 30000); // Auto-save every 30 seconds

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                switch (event.key) {
                    case '1':
                        event.preventDefault();
                        document.querySelector('[onclick="switchTab(\'chat\')"]').click();
                        break;
                    case '2':
                        event.preventDefault();
                        document.querySelector('[onclick="switchTab(\'training\')"]').click();
                        break;
                    case '3':
                        event.preventDefault();
                        document.querySelector('[onclick="switchTab(\'analytics\')"]').click();
                        break;
                    case '4':
                        event.preventDefault();
                        document.querySelector('[onclick="switchTab(\'management\')"]').click();
                        break;
                    case 's':
                        event.preventDefault();
                        if (document.getElementById('training').classList.contains('active')) {
                            addTrainingData();
                        }
                        break;
                }
            }
        });

        // Initialize sample data for demonstration
        function initializeSampleData() {
            if (trainingData.length === 0) {
                const sampleTraining = [
                    {
                        id: 1,
                        input: "Hello, how are you?",
                        output: "Hello! I'm doing well, thank you for asking. How can I assist you today?",
                        category: "greeting",
                        timestamp: new Date().toISOString(),
                        quality_score: 0.9
                    },
                    {
                        id: 2,
                        input: "What can you help me with?",
                        output: "I can help you with various tasks including answering questions, providing information, assisting with problem-solving, and having conversations. What would you like to know?",
                        category: "general",
                        timestamp: new Date().toISOString(),
                        quality_score: 0.85
                    },
                    {
                        id: 3,
                        input: "How does machine learning work?",
                        output: "Machine learning is a subset of artificial intelligence that enables computers to learn and improve from experience without being explicitly programmed. It uses algorithms to analyze data, identify patterns, and make predictions or decisions.",
                        category: "technical",
                        timestamp: new Date().toISOString(),
                        quality_score: 0.95
                    }
                ];
                
                trainingData = sampleTraining;
                saveToLocalStorage('trainingData', trainingData);
                updateStats();
            }
        }

        // Initialize sample data on first load
        setTimeout(initializeSampleData, 1000);

        // Performance monitoring
        function logPerformance(action, duration) {
            const performanceLog = JSON.parse(localStorage.getItem('performanceLog') || '[]');
            performanceLog.push({
                action: action,
                duration: duration,
                timestamp: new Date().toISOString()
            });
            
            // Keep only last 100 entries
            if (performanceLog.length > 100) {
                performanceLog.splice(0, performanceLog.length - 100);
            }
            
            localStorage.setItem('performanceLog', JSON.stringify(performanceLog));
        }

        // Enhanced message sending with performance tracking
        const originalSendMessage = sendMessage;
        sendMessage = function() {
            const startTime = performance.now();
            originalSendMessage();
            const endTime = performance.now();
            logPerformance('send_message', endTime - startTime);
        };
    </script>
</body>
</html>