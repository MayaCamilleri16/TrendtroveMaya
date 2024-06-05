document.addEventListener('DOMContentLoaded', function () {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');
    const chatIcon = document.getElementById('chatIcon');
    const chatPanel = document.getElementById('chatPanel');
    const searchInput = document.getElementById('searchInput');
    const suggestions = document.getElementById('suggestions');

    notificationIcon.addEventListener('click', function (event) {
        event.preventDefault();
        if (notificationPanel.style.display === 'none' || notificationPanel.style.display === '') {
            notificationPanel.style.display = 'block';
            chatPanel.style.display = 'none';
        } else {
            notificationPanel.style.display = 'none';
        }
    });

    chatIcon.addEventListener('click', function (event) {
        event.preventDefault();
        if (chatPanel.style.display === 'none' || chatPanel.style.display === '') {
            chatPanel.style.display = 'block';
            notificationPanel.style.display = 'none';
        } else {
            chatPanel.style.display = 'none';
        }
    });

    // Close the notification and chat panels if clicked outside
    document.addEventListener('click', function (event) {
        if (!notificationIcon.contains(event.target) && !notificationPanel.contains(event.target)) {
            notificationPanel.style.display = 'none';
        }
        if (!chatIcon.contains(event.target) && !chatPanel.contains(event.target)) {
            chatPanel.style.display = 'none';
        }
    });

    document.getElementById('chatForm').addEventListener('submit', function (event) {
        event.preventDefault();
        sendMessage();
    });

    // Fetch messages 
    fetchMessages();
    setInterval(fetchMessages, 5000);

    // Function to open chat with a user
    window.openChat = function (userId) {
        document.getElementById('chatContainer').style.display = 'block';
        document.getElementById('chatReceiverId').value = userId;
        const userName = document.querySelector(`#messageList li[onclick="openChat(${userId})"] strong`).innerText;
        document.getElementById('chatUserName').innerText = userName;
        fetchChatMessages(userId);
    }

    // Function to send a message
    function sendMessage() {
        const form = document.getElementById('chatForm');
        const formData = new FormData(form);

        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchMessages();
                form.reset();
            } else {
                alert('Error sending message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Function to fetch messages
    function fetchMessages() {
        fetch('fetch_messages.php')
        .then(response => response.json())
        .then(data => {
            const messageList = document.getElementById('messageList');
            messageList.innerHTML = '';
            data.messages.forEach(message => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${message.sender_id === current_user_id ? message.receiver_name : message.sender_name}:</strong><br>${message.content}<br><small>${message.timestamp}</small>`;
                li.setAttribute('onclick', `openChat(${message.sender_id === current_user_id ? message.receiver_id : message.sender_id})`);
                messageList.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Function to fetch chat messages
    function fetchChatMessages(userId) {
        fetch('fetch_chat_messages.php?user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';
            data.messages.forEach(message => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${message.sender_id === current_user_id ? 'You' : message.sender_name}:</strong><br>${message.content}<br><small>${message.timestamp}</small>`;
                chatMessages.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Function to handle search suggestions
    searchInput.addEventListener('input', function () {
        const query = this.value;
        if (query.length > 2) {
            fetch('search_suggestions.php?query=' + query)
            .then(response => response.json())
            .then(data => {
                suggestions.innerHTML = '';
                if (data.length > 0) {
                    suggestions.style.display = 'block';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item.name;
                        li.addEventListener('click', function () {
                            searchInput.value = item.name;
                            suggestions.style.display = 'none';
                        });
                        suggestions.appendChild(li);
                    });
                } else {
                    suggestions.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            suggestions.style.display = 'none';
        }
    });

    // Function to open a tab
    window.openTab = function (tabName) {
        var i;
        var x = document.getElementsByClassName("profile-content");
        var tabButtons = document.getElementsByClassName("tab-btn");
        for (i = 0; i < x.length; i++) {
            x[i].classList.remove("active");
        }
        for (i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        event.target.classList.add("active");
    }

    openTab('created');
});
