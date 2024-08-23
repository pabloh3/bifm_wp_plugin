// Handle submission of blog settings form
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('bifm-settings-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(form);
        formData.append('action', 'bifm_save_settings');
        formData.append('bifm_nonce', my_script_object.bifm_nonce);
        console.log("bifm_nonce: " + my_script_object.bifm_nonce);

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
                try {
                    document.getElementById('blog_author_password').value = '';
                } catch (error) {
                    console.log("no password field, I assume this is an admin user");
                }
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

