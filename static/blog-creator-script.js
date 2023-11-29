
// Fetch the categories when the page loads
(function($) {
    $(document).ready(function() {
        $.ajax({
            url: cbc_object.ajax_url,
            type: 'post',
            data: {
                action: 'cbc_get_categories',
                nonce: cbc_object.single_post_nonce
            },
            success: function(response) {
                if (response.success) {
                    let categories = response.data;
                    let categoryInput = $('#category_input');
                    categories.forEach(category => {
                        categoryInput.append('<option value="' + category.id + '">' + category.name + '</option>');
                    });
                    categoryInput.append('<option value="other">Other...</option>');
                    categoryInput.formSelect(); // Re-initialize the Materialize dropdown
                    
                    let categoryInput2 = $('#category_input2');
                    categories.forEach(category => {
                        categoryInput2.append('<option value="' + category.id + '">' + category.name + '</option>');
                    });
                    categoryInput2.append('<option value="other">Other...</option>');
                    categoryInput2.formSelect(); // Re-initialize the Materialize dropdown
                }
            }
        });
    });
})(jQuery);


jQuery(document).ready(function($) {
    let jobId = null;
    console.log("loaded JS 1.0.32");
    let submit_single = $('#submit_single_post');

    
    $('#cbc_form').on('submit', function(e) {
        e.preventDefault();
        let categoryValue = $('#category_input').val();
            // Check if categoryValue is an empty string and handle it
        if(!categoryValue) {
            $('#cbc_response').html('Please select a category before submitting.');
            return; // Don't proceed further
        }
        let data = {
            action: 'cbc_create_blog',
            nonce: cbc_object.single_post_nonce,
            keyphrase: $('input[name="keyphrase"]').val(),
            category: categoryValue === 'other' ? $('input[name="category"]').val() : categoryValue
        };

        $.post(cbc_object.ajax_url, data, function(response) {
            if(response.status === 202) {
                let innerData = JSON.parse(response.data);
                jobId = innerData.jobId;  // Assuming the response returns a jobId
                $('#cbc_response').html('The blog is being created. This might take up to 2 minutes...');
                submit_single.prop('disabled', true);
                pollForResults(jobId);
            } else {
                $('#cbc_response').html(response.message ? response.message : 'An unknown error occurred.');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            let parsedData = (jqXHR.responseJSON && jqXHR.responseJSON.data) ? JSON.parse(jqXHR.responseJSON.data) : null;
            let errorMessage = (parsedData && parsedData.message) ? parsedData.message : 'Failed to connect to the backend.';
            $('#cbc_response').html(errorMessage);
            submit_single.prop('disabled', false);
        });
    });

    function pollForResults(jobId) {
        if(jobId) {
            
            let pollingData = {
                action: 'cbc_poll_for_results',  // Assuming this is your AJAX action to poll results
                nonce: cbc_object.single_post_nonce,
                jobId: jobId
            };

            $.post(cbc_object.ajax_url, pollingData, function(response) {
                if(response.status === 200) {
                    submit_single.prop('disabled', false);
                    let innerData = JSON.parse(response.data);
                    if(innerData.message){
                        $('#cbc_response').html('Your post was successfully created. <a href="' + 'https://www.elquesabe.mx/wp-admin/post.php?post='+ innerData.blogpost_id + '&action=elementor' + '">Review Post</a><br>'+innerData.message);    
                    }
                    else{
                        $('#cbc_response').html('Your post was successfully created. <a href="' + 'https://www.elquesabe.mx/wp-admin/post.php?post='+ innerData.blogpost_id + '&action=elementor' + '">Review Post</a><br>');    
                    }
                } else if(response.status === 202) {
                    setTimeout(() => pollForResults(jobId), 5000);
                } else {
                    submit_single.prop('disabled', false);
                    $('#cbc_response').html(response.message ? response.message : 'An unknown error occurred while polling.');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                submit_single.prop('disabled', false);
                let parsedData = (jqXHR.responseJSON && jqXHR.responseJSON.data) ? JSON.parse(jqXHR.responseJSON.data) : null;
                let errorMessage = (parsedData && parsedData.error) ? parsedData.error : 'Failed to connect to the backend while polling.';
                errorMessage = 'The BIFM API returned an error: '+ errorMessage;
                $('#cbc_response').html(errorMessage);
            });
        }
    }
});

// process clicks on back button
document.getElementById('backButton').addEventListener('click', goBack);
function goBack() {
    window.location.href = 'admin.php?page=elementor-widget-manager';
}




(function($) {
    $('#category_input').change(function() {
        let selected = $(this).val();
        if (selected === 'other') {
            let newCategory = prompt("Please enter the new category:");
            if (newCategory) {
                // Send AJAX request to create the new category
                $.ajax({
                    url: cbc_object.ajax_url,
                    type: 'post',
                    data: {
                        action: 'cbc_create_category',
                        nonce: cbc_object.single_post_nonce,
                        category_name: newCategory
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add the new category to the dropdown with the returned ID as value
                            let newCategoryId = response.data.id;
                            $('#category_input').append('<option value="' + newCategoryId + '" selected>' + newCategory + '</option>');
                            $('#category_input').formSelect();
                        } else {
                            alert('Failed to create new category. Please try again.');
                        }
                    }
                });
            } else {
                $(this).val(""); // Clear selection
                $(this).formSelect();
            }
        }
    });
})(jQuery);

// bulk creation of blogsposts
jQuery(document).ready(function($) {
    // When the file input changes, handle the file upload
       $('#cbc_csv_upload_form').on('submit', function(e) {
        // Prevent the default form submission
        e.preventDefault();
        let categoryValue2 = $('#category_input2').val();
        
        // Check if categoryValue is an empty string and handle it
        if(!categoryValue2) {
            $('#cbc_response').html('Please select a category before submitting.');
            return; // Don't proceed further
        }    
        var fileInput = $('#cbc_csv_file')[0];
        if (fileInput.files.length === 0) {
            alert('Please select a file before submitting.');
            return; // Exit the function if no file is selected
        }
        var file = fileInput.files[0];
        var form_data = new FormData();
        form_data.append('cbc_csv_file', file);
        form_data.append('action', 'cbc_file_upload');
        form_data.append('nonce', cbc_object.bulk_upload_nonce);
        form_data.append('category', categoryValue2);

        $.ajax({
            url: cbc_object.ajax_url,
            type: 'POST',
            contentType: false,
            processData: false,
            data: form_data,
            success: function(response) {
                if (response.status == 200 || response.status === 202) {
                    console.log("Successful response from submitting csv");
                    inner_data = JSON.parse(response.data);
                    // Handle success. If there are categories to validate, prompt the user.
                    $('#cbc_response').html('Upload successful! ' + inner_data.message);
                }
                else {
                    console.log("Error response from submitting csv");
                    inner_data = JSON.parse(response.data);
                    // Handle success. If there are categories to validate, prompt the user.
                    $('#cbc_response').html('An error occurred during upload: ' + inner_data);
                    
                }
            },
            error: function(error) {
                console.log("Error response from submitting csv");
                if (error.responseJSON && error.responseJSON.message) {
                    $('#cbc_response').html('Upload failed: ' + error.responseJSON.message);
                }
                else {
                    $('#cbc_response').html('Upload failed: ' + error.responseText);
                }
            }
        });
    });
});

