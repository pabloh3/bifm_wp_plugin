document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('bifm-settings-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(form);
        var blogLanguageSelect = document.getElementById('blog_language');
        var otherBlogLanguage = document.getElementById('other_blog_language');

        // If the selected language is "Other", use the custom input value
        if (blogLanguageSelect.value === 'other') {
            formData.set('blog_language', otherBlogLanguage.value);
        }

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
                // Display success message
                var warningDiv = document.getElementById('warningMessage');
                warningDiv.textContent = 'Settings saved successfully.';
                warningDiv.style.display = 'flex';
                // Clean up all fields
                try {
                    document.getElementById('blog_author_password').value = '';
                } catch (error) {
                    console.log("No password field, I assume this is an admin user");
                }
            } else {
                console.error('Error:', result.data);
                // Display error message
                var warningDiv = document.getElementById('warningMessage');
                warningDiv.textContent = result.data;
                warningDiv.style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Handle blog language selection
    var blogLanguageSelect = document.getElementById('blog_language');
    var otherLanguageWrapper = document.getElementById('other_language_wrapper');
    var otherBlogLanguage = document.getElementById('other_blog_language');

    blogLanguageSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            otherLanguageWrapper.style.display = 'block';
            otherBlogLanguage.setAttribute('required', 'required');
        } else {
            otherLanguageWrapper.style.display = 'none';
            otherBlogLanguage.removeAttribute('required');
        }
    });

});
