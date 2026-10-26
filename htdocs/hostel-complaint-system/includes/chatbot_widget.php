<!-- AI Chatbot Widget -->
<button id="chatbot-toggle" title="Complaint Assistant">💬</button>
<div id="chatbot-window">
    <div id="chatbot-header">
        <div>
            Complaint Assistant
            <span class="sub">Powered by rule-based AI</span>
        </div>
        <button id="chatbot-close">✕</button>
    </div>
    <div id="chatbot-messages"></div>
    <div id="chatbot-input-row">
        <input type="text" id="chatbot-text-input" placeholder="Type your issue here...">
        <button id="chatbot-send">➤</button>
    </div>
</div>
<script src="<?= $basePath ?>/assets/js/chatbot.js"></script>
