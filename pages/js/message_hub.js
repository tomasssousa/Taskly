let currentChat = null;
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
        list.innerHTML = '<li class="no-conversations">No conversations yet</li>';
        return;
    }
    
    list.innerHTML = conversations.map(conv => `
        <li class="conversation-item ${currentChat === conv.username ? 'active' : ''}" data-username="${conv.username}">
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
    displayChat(username, []);
    loadMessages(username);
    loadConversations();
}

function loadMessages(username) {
    currentChat = username;
    
    // Update active conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.username === username) {
            item.classList.add('active');
        }
    });
    
    fetch(`../actions/action_chat.php?action=get_messages&user=${encodeURIComponent(username)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayChat(username, data.messages);
                startMessagePolling(username);
            } else {
                displayChat(username, []); // Show the chat interface, even if no messages exist
                startMessagePolling(username);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayChat(username, []);
        });
}

function displayChat(username, messages) {
    const messagesArea = document.getElementById('messagesArea');
    
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
        <form class="message-form">
            <input type="text" id="messageInput" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
    `;

    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
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
            loadMessages(currentChat); // Refresh messages
        } else {
            alert('Failed to send message: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send message');
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
            fetch(`../actions/action_chat.php?action=get_messages&user=${encodeURIComponent(username)}`)
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

// Conversation item click
document.addEventListener('click', function(event) {
    const conversationItem = event.target.closest('.conversation-item');
    if (conversationItem) {
        const username = conversationItem.dataset.username;
        loadMessages(username);
    }
});

// Submitting a message
document.addEventListener('submit', function(event) {
    if (event.target.classList.contains('message-form')) {
        sendMessage(event);
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});