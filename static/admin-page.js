document.getElementById('createNewBlog').addEventListener('click', newBlog);
            
function newBlog() {
    window.location.href = 'admin.php?page=create-blog'; // Redirect to the provided URL
}

// Handle submission of blog settings form
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
                document.getElementById('blog_author_password').value = '';
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

