
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
    openTab('created');
});

