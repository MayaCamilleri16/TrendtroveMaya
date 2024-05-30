function openTab(tabName) {
    const createdTab = document.getElementById('created');
    const savedTab = document.getElementById('saved');
    if (tabName === 'created') {
        createdTab.style.display = 'block';
        savedTab.style.display = 'none';
    } else {
        createdTab.style.display = 'none';
        savedTab.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize the default tab
    openTab('created');

    // Notification panel logic
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');

    notificationIcon.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent the default anchor click behavior
        if (notificationPanel.style.display === 'none' || notificationPanel.style.display === '') {
            notificationPanel.style.display = 'block';
        } else {
            notificationPanel.style.display = 'none';
        }
    });

    // Close the notification panel if clicked outside
    document.addEventListener('click', function (event) {
        if (!notificationIcon.contains(event.target) && !notificationPanel.contains(event.target)) {
            notificationPanel.style.display = 'none';
        }
    });
});
