document.getElementById('createNewBlog').addEventListener('click', newBlog);
            
function newBlog() {
    window.location.href = 'admin.php?page=create-blog'; // Redirect to the provided URL
}

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

// Handle submission of settings form
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('bifm-settings-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(form);
        formData.append('action', 'bifm_save_settings');
        formData.append('bifm_nonce', my_script_object.nonce);
        console.log("nonce: " + my_script_object.nonce);

        fetch(my_script_object.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('Success:', result.data);
                //display success message
                var warningDiv = document.getElementById('warningMessage');
                warningDiv.textContent = 'Settings saved successfully.';
                warningDiv.style.display = 'flex';
                //cleanup all fields
                document.getElementById('bifm-password').value = '';
            } else {
                console.error('Error:', result.data);
                //display error message
                var warningDiv = document.getElementById('warningMessage');
                warningDiv.textContent = result.data;
                warningDiv.style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
