/**
 * Hostel Complaint Assistant (rule-based AI chatbot)
 * - Keyword-matches the student's free-text description to a category
 * - Estimates urgency/priority from risk keywords
 * - Can auto-fill the complaint form (category, priority, subject) if present on the page
 * No external API needed — fully self-contained pattern-matching "AI".
 */

(function () {
    const KEYWORD_MAP = {
        'Electrical': ['light', 'bulb', 'socket', 'switch', 'wire', 'wiring', 'short circuit', 'spark', 'fan not', 'power cut', 'electric', 'plug', 'mcb', 'fuse'],
        'Plumbing': ['tap', 'leak', 'leaking', 'pipe', 'water not', 'no water', 'toilet', 'flush', 'drainage', 'drain', 'bathroom', 'washbasin', 'overflow', 'seepage'],
        'Cleanliness': ['dirty', 'garbage', 'trash', 'clean', 'cleaning', 'dust', 'cockroach', 'insect', 'smell', 'stink', 'mosquito', 'pest', 'rat'],
        'Internet/WiFi': ['wifi', 'wi-fi', 'internet', 'network', 'lan', 'router', 'connectivity', 'slow internet'],
        'Mess/Food': ['food', 'mess', 'meal', 'canteen', 'quality of food', 'stale', 'hygiene of food', 'breakfast', 'lunch', 'dinner'],
        'Furniture': ['bed', 'chair', 'table', 'cupboard', 'almirah', 'broken', 'furniture', 'mattress', 'door lock', 'window', 'hinge'],
        'Security': ['theft', 'stolen', 'security', 'guard', 'stranger', 'unlock', 'cctv', 'unsafe', 'lock broken', 'entry'],
        'Noise': ['noise', 'loud', 'music', 'disturb', 'shouting', 'party'],
    };

    const URGENT_KEYWORDS = ['fire', 'spark', 'short circuit', 'smoke', 'flood', 'overflow', 'stolen', 'theft', 'unsafe', 'gas leak', 'injury', 'shock', 'electrocut'];
    const MEDIUM_KEYWORDS = ['leak', 'leaking', 'no water', 'broken', 'not working', 'stale', 'insect', 'pest'];

    let state = {
        stage: 'greet', // greet -> ask_issue -> confirm_category -> ask_room -> done
        detectedCategory: null,
        detectedPriority: 'Low',
        userText: '',
        roomNo: null,
    };

    function detectCategory(text) {
        const lower = text.toLowerCase();
        let scores = {};
        for (const [cat, keywords] of Object.entries(KEYWORD_MAP)) {
            scores[cat] = keywords.filter(k => lower.includes(k)).length;
        }
        const best = Object.entries(scores).sort((a, b) => b[1] - a[1])[0];
        return best && best[1] > 0 ? best[0] : 'Other';
    }

    function detectPriority(text) {
        const lower = text.toLowerCase();
        if (URGENT_KEYWORDS.some(k => lower.includes(k))) return 'High';
        if (MEDIUM_KEYWORDS.some(k => lower.includes(k))) return 'Medium';
        return 'Low';
    }

    function addMessage(text, sender) {
        const messagesEl = document.getElementById('chatbot-messages');
        const msg = document.createElement('div');
        msg.className = 'chat-msg ' + sender;
        msg.textContent = text;
        messagesEl.appendChild(msg);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addOptions(options) {
        const messagesEl = document.getElementById('chatbot-messages');
        const wrap = document.createElement('div');
        wrap.className = 'chat-options';
        options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'chat-option-btn';
            btn.textContent = opt;
            btn.onclick = () => handleUserInput(opt);
            wrap.appendChild(btn);
        });
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function botSay(text, options) {
        setTimeout(() => {
            addMessage(text, 'bot');
            if (options) addOptions(options);
        }, 300);
    }

    function handleUserInput(text) {
        if (!text.trim()) return;
        addMessage(text, 'user');
        const input = document.getElementById('chatbot-text-input');
        if (input) input.value = '';

        switch (state.stage) {
            case 'greet':
            case 'ask_issue': {
                state.userText = text;
                state.detectedCategory = detectCategory(text);
                state.detectedPriority = detectPriority(text);
                state.stage = 'confirm_category';
                botSay(
                    `Thanks! Based on what you described, this looks like a "${state.detectedCategory}" issue with "${state.detectedPriority}" priority. Is that correct?`,
                    ['Yes, correct', 'No, let me pick category']
                );
                break;
            }
            case 'confirm_category': {
                if (text.toLowerCase().startsWith('no')) {
                    state.stage = 'pick_category';
                    botSay('No problem — which category fits best?', Object.keys(KEYWORD_MAP).concat(['Other']));
                } else {
                    state.stage = 'ask_room';
                    botSay('Got it. What is your room number? (e.g. A-204)');
                }
                break;
            }
            case 'pick_category': {
                state.detectedCategory = text;
                state.stage = 'ask_room';
                botSay(`Great, marked as "${text}". What is your room number? (e.g. A-204)`);
                break;
            }
            case 'ask_room': {
                state.roomNo = text;
                state.stage = 'done';
                fillComplaintForm();
                botSay(`Perfect! I've pre-filled your complaint form with:\n• Category: ${state.detectedCategory}\n• Priority: ${state.detectedPriority}\n• Room: ${state.roomNo}\n\nJust review the description, adjust anything, and hit Submit!`);
                break;
            }
            case 'done': {
                botSay("Your form is filled in below — feel free to edit any field before submitting. Need help with anything else? Type 'restart' to file another issue.");
                if (text.toLowerCase().includes('restart')) {
                    resetChat();
                }
                break;
            }
        }
    }

    function fillComplaintForm() {
        const form = document.getElementById('complaintForm');
        if (!form) return; // not on the complaint page, that's fine

        if (form.elements['category']) {
            const catSelect = form.elements['category'];
            const validOption = Array.from(catSelect.options).find(o => o.value === state.detectedCategory);
            catSelect.value = validOption ? state.detectedCategory : 'Other';
        }
        if (form.elements['priority']) {
            form.elements['priority'].value = state.detectedPriority;
        }
        if (form.elements['description'] && !form.elements['description'].value) {
            form.elements['description'].value = state.userText;
            form.elements['description'].dispatchEvent(new Event('input'));
        }
        if (form.elements['subject'] && !form.elements['subject'].value) {
            form.elements['subject'].value = `${state.detectedCategory} issue in room ${state.roomNo || ''}`.trim();
        }
        if (form.elements['room_no'] && state.roomNo) {
            form.elements['room_no'].value = state.roomNo;
        }
        // trigger validation styling refresh
        ['category', 'priority', 'subject', 'room_no'].forEach(f => {
            if (form.elements[f]) form.elements[f].dispatchEvent(new Event('input'));
        });
    }

    function resetChat() {
        state = { stage: 'ask_issue', detectedCategory: null, detectedPriority: 'Low', userText: '', roomNo: null };
        document.getElementById('chatbot-messages').innerHTML = '';
        botSay("Sure! Describe the new issue you're facing and I'll help categorize it.");
    }

    function initChatbot() {
        const toggle = document.getElementById('chatbot-toggle');
        const win = document.getElementById('chatbot-window');
        const closeBtn = document.getElementById('chatbot-close');
        const sendBtn = document.getElementById('chatbot-send');
        const inputField = document.getElementById('chatbot-text-input');

        if (!toggle || !win) return;

        toggle.addEventListener('click', () => {
            win.classList.toggle('open');
            if (win.classList.contains('open') && document.getElementById('chatbot-messages').childElementCount === 0) {
                state.stage = 'ask_issue';
                botSay("Hi! I'm your Hostel Complaint Assistant 🤖. Briefly describe the problem you're facing (e.g. 'the tap in my bathroom is leaking') and I'll help categorize and fill out your complaint.");
            }
        });
        closeBtn.addEventListener('click', () => win.classList.remove('open'));
        sendBtn.addEventListener('click', () => handleUserInput(inputField.value));
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') handleUserInput(inputField.value);
        });
    }

    document.addEventListener('DOMContentLoaded', initChatbot);
})();
