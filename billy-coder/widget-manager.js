document.getElementById('createNewWidget').addEventListener('click', requestFolderName);
function requestFolderName() {
    fetch(ajaxurl + '?action=get_folder_name')
    .then(response => response.json()) // Parse the response as JSON
    .then(data => {
        if (data.success && data.data.redirectUrl) {
            window.location.href = data.data.redirectUrl; // Redirect to the provided URL
        } else {
            displayWarning(data.data);
        }
    })
    .catch(error => {
        console.error('Error fetching new page:', error);
    });
}

document.querySelectorAll('.delete-widget').forEach(button => {
    button.addEventListener('click', function() {
        var widgetName = this.getAttribute('data-widget-name');
        if (confirm('Are you sure you want to delete the widget? This is irreversible.')) {
        deleteWidget(widgetName);
        }
    });
});

function deleteWidget(widgetName) {
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=delete_custom_widget&widget_name=' + widgetName + '&nonce=' + my_script_object.nonce,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(widgetName).remove();
            alert('Widget deleted successfully.');
        } else {
            alert('Error deleting widget.');
        }
    });
}
function displayWarning(message) {
    var warningDiv = document.getElementById('warningMessage');
    warningDiv.textContent = message;

    // Make the warning box visible
    warningDiv.style.display = 'flex';
}
