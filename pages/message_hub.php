<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/user_db.php');

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | Messages</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/message_hub.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>
<main>
    <div class="message-hub-container">
        <aside class="sidebar" id="sidebar">
            <h2>Messages</h2>
            <!-- Search for other users to message -->
            <div class="search-users">
                <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchUsers()">
                <div id="searchResults" class="search-results"></div>
            </div>

            <!-- List of open chats -->
            <ul class="conversation-list" id="conversationList">
            </ul>
        </aside>
        
        <section class="messages-area" id="messagesArea">
            <div class="no-chat-selected">
                <p>Select a conversation to start messaging</p>
            </div>
        </section>
    </div>
</main>

<script>
let currentChat = null;
let messagePollingInterval = null;
const currentUser = '<?= $_SESSION['username'] ?>';

// Load chats while loading the page
document.addEventListener('DOMContentLoaded', function() {
    loadConversations();
    
    // Check if we need to start a conversation with a specific user
    const urlParams = new URLSearchParams(window.location.search);
    const targetUser = urlParams.get('user');
    const action = urlParams.get('action');
    
    if (targetUser && action === 'start_conversation') {
        // Start chat with the specified user
        setTimeout(() => {
            startChat(targetUser);
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500); // Some delay
    }
});

function searchUsers() {
    const searchTerm = document.getElementById('userSearch').value;
    const resultsDiv = document.getElementById('searchResults');
    
    if (searchTerm.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }
    
    fetch(`../actions/action_chat.php?action=search_users&search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.users);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('searchResults');
    
    if (users.length === 0) {
        resultsDiv.innerHTML = '<div class="no-results">No users found</div>';
        return;
    }
    
    resultsDiv.innerHTML = users.map(user => `
        <div class="search-result" onclick="startChat('${user.username}')">
            <img src="../docs/${user.profile_photo || 'user_icon.png'}" alt="${user.name}" class="user-avatar">
            <div>
                <div class="user-name">${user.name}</div>
                <div class="username">@${user.username}</div>
            </div>
        </div>
    `).join('');
}

function startChat(username) {
    currentChat = username;
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('userSearch').value = '';
    
    displayChat(username, []); // Create an empty chat interface immediately
    loadMessages(username); // Try to load existing messages
    loadConversations(); // Refresh conversations list
}

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
        <li onclick="loadMessages('${conv.username}')" class="conversation-item ${currentChat === conv.username ? 'active' : ''}">
            <img src="../docs/${conv.profile_photo}" alt="${conv.name}" class="user-avatar">
            <div class="conversation-info">
                <div class="user-name">${conv.name}</div>
                <div class="username">@${conv.username}</div>
            </div>
        </li>
    `).join('');
}

function loadMessages(username) {
    currentChat = username;
    
    // Update active conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
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
        <form class="message-form" onsubmit="sendMessage(event)">
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

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});
</script>
</body>
</html>