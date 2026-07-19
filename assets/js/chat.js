(function() {
    'use strict';
    
    // Check if chat should be disabled on certain pages
    const disabledPages = ['index.php', 'login.php', 'register.php'];
    const currentPage = window.location.pathname.split('/').pop();
    
    if (disabledPages.includes(currentPage)) {
        return;
    }
    
    const CONFIG = {
        chatUrl: '/includes/chat.php',
        pollInterval: 3000,
        maxMessages: 100
    };
    
    let isOpen = false;
    let isMinimized = false;
    let lastMessageId = 0;
    let userName = '';
    let pollInterval = null;
    let isSending = false;
    let chatWidget = null;
    let chatToggle = null;
    let chatWindow = null;
    let chatMessages = null;
    let chatInput = null;
    let chatSendBtn = null;
    let chatHeader = null;
    
    function createChatWidget() {
        chatWidget = document.createElement('div');
        chatWidget.id = 'chat-widget';
        chatWidget.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        `;
        
        chatToggle = document.createElement('div');
        chatToggle.id = 'chat-toggle';
        chatToggle.style.cssText = `
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00f5a0, #00d9f5);
            color: #0a1628;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 245, 160, 0.4);
            transition: all 0.3s ease;
            border: none;
            user-select: none;
        `;
        chatToggle.innerHTML = '<i class="fas fa-comment-dots"></i>';
        chatToggle.onmouseover = function() {
            this.style.transform = 'scale(1.1)';
            this.style.boxShadow = '0 8px 30px rgba(0, 245, 160, 0.5)';
        };
        chatToggle.onmouseout = function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '0 4px 20px rgba(0, 245, 160, 0.4)';
        };
        
        chatWindow = document.createElement('div');
        chatWindow.id = 'chat-window';
        chatWindow.style.cssText = `
            position: absolute;
            bottom: 75px;
            right: 0;
            width: 380px;
            max-width: calc(100vw - 40px);
            height: 500px;
            max-height: calc(100vh - 140px);
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
            animation: chatSlideIn 0.3s ease;
        `;
        
        chatHeader = document.createElement('div');
        chatHeader.style.cssText = `
            background: linear-gradient(135deg, #0a1628, #1a2a4a);
            padding: 16px 20px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        `;
        chatHeader.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:8px; height:8px; border-radius:50%; background:#00f5a0; animation: pulse 1.5s infinite;"></div>
                <span style="font-weight:600; font-size:1rem;">Live Support</span>
                <span style="font-size:0.7rem; background:rgba(0,245,160,0.15); padding:2px 10px; border-radius:30px; color:#00f5a0;">Online</span>
            </div>
            <div style="display:flex; gap:8px;">
                <button id="chat-minimize" style="background:none; border:none; color:#fff; cursor:pointer; font-size:16px; opacity:0.7; transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    <i class="fas fa-minus"></i>
                </button>
                <button id="chat-close" style="background:none; border:none; color:#fff; cursor:pointer; font-size:16px; opacity:0.7; transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        chatMessages = document.createElement('div');
        chatMessages.id = 'chat-messages';
        chatMessages.style.cssText = `
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
            background: #f8faff;
            display: flex;
            flex-direction: column;
            gap: 8px;
        `;
        
        const welcomeMsg = document.createElement('div');
        welcomeMsg.className = 'welcome-msg';
        welcomeMsg.style.cssText = `
            text-align: center;
            color: #6b7a93;
            font-size: 0.85rem;
            padding: 20px 0;
        `;
        welcomeMsg.innerHTML = `
            <i class="fas fa-robot" style="font-size: 2rem; display:block; margin-bottom:10px; color:#dce3ec;"></i>
            <strong>Welcome to Chipper Stock!</strong>
            <p style="margin-top:4px; font-size:0.8rem;">Ask us anything about your investments.</p>
        `;
        chatMessages.appendChild(welcomeMsg);
        
        const chatInputArea = document.createElement('div');
        chatInputArea.style.cssText = `
            padding: 12px 16px;
            border-top: 1px solid #e9edf2;
            background: #fff;
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        `;
        
        chatInput = document.createElement('input');
        chatInput.type = 'text';
        chatInput.placeholder = 'Type your message...';
        chatInput.style.cssText = `
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #dce3ec;
            border-radius: 30px;
            outline: none;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            font-family: inherit;
        `;
        chatInput.onfocus = function() {
            this.style.borderColor = '#0d6efd';
            this.style.boxShadow = '0 0 0 3px rgba(13, 110, 253, 0.1)';
        };
        chatInput.onblur = function() {
            this.style.borderColor = '#dce3ec';
            this.style.boxShadow = 'none';
        };
        chatInput.onkeydown = function(e) {
            if (e.key === 'Enter') sendMessage();
        };
        
        chatSendBtn = document.createElement('button');
        chatSendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        chatSendBtn.style.cssText = `
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #00f5a0, #00d9f5);
            color: #0a1628;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        `;
        chatSendBtn.onmouseover = function() {
            this.style.transform = 'scale(1.05)';
        };
        chatSendBtn.onmouseout = function() {
            this.style.transform = 'scale(1)';
        };
        chatSendBtn.onclick = sendMessage;
        
        chatInputArea.appendChild(chatInput);
        chatInputArea.appendChild(chatSendBtn);
        
        chatWindow.appendChild(chatHeader);
        chatWindow.appendChild(chatMessages);
        chatWindow.appendChild(chatInputArea);
        
        chatWidget.appendChild(chatToggle);
        chatWidget.appendChild(chatWindow);
        document.body.appendChild(chatWidget);
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes chatSlideIn {
                from { opacity: 0; transform: translateY(20px) scale(0.95); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }
            .chat-message {
                max-width: 85%;
                padding: 10px 14px;
                border-radius: 14px;
                font-size: 0.9rem;
                line-height: 1.4;
                word-wrap: break-word;
                animation: chatSlideIn 0.2s ease;
            }
            .chat-message.user {
                align-self: flex-end;
                background: linear-gradient(135deg, #00f5a0, #00d9f5);
                color: #0a1628;
                border-bottom-right-radius: 4px;
            }
            .chat-message.admin {
                align-self: flex-start;
                background: #ffffff;
                color: #0d1a2b;
                border: 1px solid #e9edf2;
                border-bottom-left-radius: 4px;
            }
            .chat-message .msg-name {
                font-size: 0.7rem;
                opacity: 0.7;
                margin-bottom: 2px;
                font-weight: 600;
            }
            .chat-message .msg-time {
                font-size: 0.6rem;
                opacity: 0.5;
                margin-top: 4px;
                text-align: right;
            }
            #chat-messages::-webkit-scrollbar {
                width: 4px;
            }
            #chat-messages::-webkit-scrollbar-track {
                background: transparent;
            }
            #chat-messages::-webkit-scrollbar-thumb {
                background: #dce3ec;
                border-radius: 10px;
            }
        `;
        document.head.appendChild(style);
        
        chatToggle.onclick = toggleChat;
        document.getElementById('chat-minimize').onclick = minimizeChat;
        document.getElementById('chat-close').onclick = closeChat;
    }
    
    function toggleChat() {
        if (isOpen) {
            if (isMinimized) {
                restoreChat();
            } else {
                minimizeChat();
            }
        } else {
            openChat();
        }
    }
    
    function openChat() {
        isOpen = true;
        isMinimized = false;
        chatWindow.style.display = 'flex';
        chatToggle.innerHTML = '<i class="fas fa-times"></i>';
        chatToggle.style.background = 'linear-gradient(135deg, #f87171, #ef4444)';
        chatToggle.style.boxShadow = '0 4px 20px rgba(239, 68, 68, 0.4)';
        document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(fetchMessages, CONFIG.pollInterval);
        fetchMessages();
        setTimeout(() => chatInput.focus(), 300);
    }
    
    function minimizeChat() {
        isMinimized = true;
        chatWindow.style.display = 'none';
        chatToggle.innerHTML = '<i class="fas fa-comment-dots"></i>';
        chatToggle.style.background = 'linear-gradient(135deg, #00f5a0, #00d9f5)';
        chatToggle.style.boxShadow = '0 4px 20px rgba(0, 245, 160, 0.4)';
        
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }
    
    function restoreChat() {
        isMinimized = false;
        chatWindow.style.display = 'flex';
        chatToggle.innerHTML = '<i class="fas fa-times"></i>';
        chatToggle.style.background = 'linear-gradient(135deg, #f87171, #ef4444)';
        chatToggle.style.boxShadow = '0 4px 20px rgba(239, 68, 68, 0.4)';
        
        pollInterval = setInterval(fetchMessages, CONFIG.pollInterval);
        fetchMessages();
        setTimeout(() => chatInput.focus(), 300);
    }
    
    function closeChat() {
        isOpen = false;
        isMinimized = false;
        chatWindow.style.display = 'none';
        chatToggle.innerHTML = '<i class="fas fa-comment-dots"></i>';
        chatToggle.style.background = 'linear-gradient(135deg, #00f5a0, #00d9f5)';
        chatToggle.style.boxShadow = '0 4px 20px rgba(0, 245, 160, 0.4)';
        
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }
    
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message || isSending) return;
        
        isSending = true;
        chatSendBtn.disabled = true;
        chatSendBtn.style.opacity = '0.6';
        
        if (!userName) {
            userName = prompt('Please enter your name:', 'Guest') || 'Guest';
        }
        
        fetch(CONFIG.chatUrl + '?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: userName,
                message: message,
                is_admin: false
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chatInput.value = '';
                fetchMessages();
            }
        })
        .catch(err => console.error('Send error:', err))
        .finally(() => {
            isSending = false;
            chatSendBtn.disabled = false;
            chatSendBtn.style.opacity = '1';
            chatInput.focus();
        });
    }
    
    function fetchMessages() {
        fetch(CONFIG.chatUrl + '?action=get&last_id=' + lastMessageId)
            .then(response => response.json())
            .then(messages => {
                if (messages.length > 0) {
                    messages.forEach(msg => {
                        addMessageToUI(msg);
                        if (msg.id > lastMessageId) {
                            lastMessageId = msg.id;
                        }
                    });
                    scrollToBottom();
                }
            })
            .catch(err => console.error('Fetch error:', err));
    }
    
    function addMessageToUI(msg) {
        const welcome = chatMessages.querySelector('.welcome-msg');
        if (welcome) welcome.remove();
        
        const div = document.createElement('div');
        div.className = 'chat-message ' + (msg.is_admin ? 'admin' : 'user');
        div.innerHTML = `
            ${!msg.is_admin ? `<div class="msg-name">${msg.name}</div>` : `<div class="msg-name">🛡️ Support</div>`}
            ${msg.message}
            <div class="msg-time">${msg.time || msg.timestamp}</div>
        `;
        chatMessages.appendChild(div);
    }
    
    function scrollToBottom() {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 50);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        createChatWidget();
    });
    
})();
