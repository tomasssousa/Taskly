let currentChat = null;
let currentChatId = null;
let messagePollingInterval = null;

function loadConversations() {
    fetch('../actions/action_chat.php?action=get_conversations')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayConversations(data.conversations);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayConversations(conversations) {
    const list = document.getElementById('conversationList');
    
    if (conversations.length === 0) {
        const modeText = userTaskMode ? 'received' : 'initiated';
        list.innerHTML = `<li class="no-conversations">No conversations ${modeText} yet</li>`;
        return;
    }
    
    list.innerHTML = conversations.map(conv => `
        <li class="conversation-item ${currentChat === conv.username ? 'active' : ''}" data-username="${conv.username}" data-chat-id="${conv.chat_id}">
            <img src="../docs/${conv.profile_photo}" alt="${conv.name}" class="user-avatar">
            <div class="conversation-info">
                <div class="user-name">${conv.name}</div>
                <div class="username">@${conv.username}</div>
            </div>
        </li>
    `).join('');
}

function startChat(username) {
    currentChat = username;
    currentChatId = null;
    displayChat(username, []);
    loadMessages(username);
    loadConversations();
}

function loadMessages(username, chatId = null) {
    currentChat = username;
    if (chatId) {
        currentChatId = chatId;
    }
    
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.username === username) {
            item.classList.add('active');
            if (!currentChatId) {
                currentChatId = item.dataset.chatId;
            }
        }
    });
    
    let url = `../actions/action_chat.php?action=get_messages&user=${encodeURIComponent(username)}`;
    if (currentChatId) {
        url += `&chat_id=${currentChatId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.messages.length > 0) {
                    currentChatId = data.messages[0].chat_id;
                }
                displayChat(username, data.messages, data);
                startMessagePolling(username);
            } else {
                displayChat(username, [], data);
                startMessagePolling(username);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayChat(username, []);
        });
}

function displayChat(username, messages, data = {}) {
    const messagesArea = document.getElementById('messagesArea');
    
    const canSendMessages = data.can_start_conversation || messages.length > 0;
    
    let messageForm = '';
    if (canSendMessages) {
        messageForm = `
            <form class="message-form">
                <input type="text" id="messageInput" placeholder="Type a message..." required>
                <button type="submit">Send</button>
            </form>
        `;
    } else {
        messageForm = `
            <div class="no-message-permission">
                <p>You cannot start new conversations. Only task providers can initiate conversations.</p>
            </div>
        `;
    }
    
    messagesArea.innerHTML = `
        <div class="chat-header">
            <h3>Chat with ${username}</h3>
        </div>
        <div class="messages-container" id="messagesContainer">
            ${messages.map(msg => `
                <div class="message ${msg.sender === currentUser ? 'sent' : 'received'}">
                    <div class="message-content">${escapeHtml(msg.text)}</div>
                    <div class="message-info">
                        <span class="sender">${msg.sender}</span>
                    </div>
                </div>
            `).join('')}
        </div>
        ${messageForm}
    `;

    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function sendMessage(event) {
    if (event) {
        event.preventDefault();
    }
    
    const messageInput = document.getElementById('messageInput');
    if (!messageInput) return;
    
    const message = messageInput.value.trim();
    
    if (!message || !currentChat) return;
    
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('receiver', currentChat);
    formData.append('message', message);
    
    fetch('../actions/action_chat.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages(currentChat, currentChatId);
            loadConversations();
        } else {
            alert('Error sending message: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending message');
    });
}

function updateMessages(messages) {
    const container = document.getElementById('messagesContainer');
    if (!container) return;
    
    const wasAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 10;
    
    container.innerHTML = messages.map(msg => `
        <div class="message ${msg.sender === currentUser ? 'sent' : 'received'}">
            <div class="message-content">${escapeHtml(msg.text)}</div>
            <div class="message-info">
                <span class="sender">${msg.sender}</span>
            </div>
        </div>
    `).join('');

    if (wasAtBottom) {
        container.scrollTop = container.scrollHeight;
    }
}

function startMessagePolling(username) {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }

    messagePollingInterval = setInterval(() => {
        if (currentChat === username) {
            let url = `../actions/action_chat.php?action=get_messages&user=${encodeURIComponent(username)}`;
            if (currentChatId) {
                url += `&chat_id=${currentChatId}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMessages(data.messages);
                    }
                })
                .catch(error => console.error('Polling error:', error));
        }
    }, 3000);
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', function() {
    loadConversations();
    
    const urlParams = new URLSearchParams(window.location.search);
    const targetUser = urlParams.get('user');
    const action = urlParams.get('action');
    
    if (targetUser && action === 'start_conversation') {
        setTimeout(() => {
            startChat(targetUser);
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }
});

document.addEventListener('click', function(event) {
    const conversationItem = event.target.closest('.conversation-item');
    if (conversationItem) {
        const username = conversationItem.dataset.username;
        const chatId = conversationItem.dataset.chatId;
        loadMessages(username, chatId);
    }
});

document.addEventListener('submit', function(event) {
    if (event.target.classList.contains('message-form')) {
        sendMessage(event);
    }
});

window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});