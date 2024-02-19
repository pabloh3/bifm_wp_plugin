
// Fetch the categories when the page loads
(function($) {
    $(document).ready(function() {
        //load the right tab
        console.log("tab clicked");
        var elems = document.querySelectorAll(".tabs");
        var instances = M.Tabs.init(elems, {});


        //fetch the categories
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
                    let categoryInput2 = $('#category_input2');
                    categories.forEach(category => {
                        //categoryInput.append('<option value="' + category.id + '">' + category.name + '</option>');
                        categoryInput.append($('<option>').val(category.id).text(category.name));
                        categoryInput2.append($('<option>').val(category.id).text(category.name));
                    });
                    categoryInput.append('<option value="other">Other...</option>');
                    categoryInput.formSelect(); // Re-initialize the Materialize dropdown

                    categoryInput2.append('<option value="other">Other...</option>');
                    categoryInput2.formSelect(); // Re-initialize the Materialize dropdown
                }
            }
        });
    });
})(jQuery);

//handle create post submissions
jQuery(document).ready(function($) {
    let jobId = null;
    console.log("loaded JS 1.0.67");
    let submit_single = $('#submit_single_post');
    $('#cbc_form').on('submit', function(e) {
        e.preventDefault();
        let categoryValue = $('#category_input').val();
        let categoryName = $('#category_input option:selected').text();
        // Check if categoryValue is an empty string and handle it (no need to check keyphrase, it's checked by the form)
        if(!categoryValue) {
            $('#cbc_response').html('Please select a category before submitting.');
            return; // Don't proceed further
        }
        let data = {
            action: 'cbc_create_blog',
            nonce: cbc_object.single_post_nonce,
            keyphrase: $('input[name="keyphrase"]').val(),
            category: categoryValue,
            category_name: categoryName
        };

        $.post(cbc_object.ajax_url, data, function(response) {
            if(response.status === 202) {
                let innerData;
                try {
                    innerData = JSON.parse(response.data);
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    return; // Exit the function
                }
                jobId = innerData.jobId;  // Assuming the response returns a jobId
                $('#cbc_response').html('The blog is being created. This might take up to 2 minutes...');
                submit_single.prop('disabled', true);
                pollForResults(jobId);
            } else {
                if (response.data.message){
                    $('#cbc_response').html(response.data.message);
                }
                else{
                    console.log("Error response from submitting single post");
                    $('#cbc_response').html(response.message ? response.message : 'An unknown error occurred.');
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            let parsedData = (jqXHR.responseJSON && jqXHR.responseJSON.data) ? JSON.parse(jqXHR.responseJSON.data) : null;
            errorMessage = 'Failed to connect to the backend.';
            if (parsedData && parsedData.message){
                errorMessage = parsedData.message;
            } else if (parsedData && parsedData.error){
                errorMessage = parsedData.error;
            }
            $('#cbc_response').html(errorMessage);
            submit_single.prop('disabled', false);
        });
    });

    // check if the single blog post is ready
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
                    if (innerData.blogpost_id == null){
                        $('#cbc_response').html('Your post failed to be created. <br>'+innerData.message);
                    }
                    else if(innerData.message){
                        $('#cbc_response').html('Your post was successfully created. <a href="' + '/?p='+ innerData.blogpost_id + '">Review Post</a><br>'+innerData.message);    
                    }
                    else{
                        $('#cbc_response').html('Your post was successfully created. <a href="' + '/?p='+ innerData.blogpost_id + '">Review Post</a><br>');    
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
    window.location.href = 'admin.php?page=bifm-plugin';
}



// Create a new category if the user selects "Other..." from the dropdown
(function($) {
    $('#category_input, #category_input2').change(function() {
        let selected = $(this).val();
        if (selected === 'other') {
            let newCategory = $("<div>").text(prompt("Please enter the new category:")).text();
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
        let categoryName2 = $('#category_input2 option:selected').text();
        
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
        form_data.append('category_name', categoryName2);

        $.ajax({
            url: cbc_object.ajax_url,
            type: 'POST',
            contentType: false,
            processData: false,
            data: form_data,
            success: function(response) {
                if (response.status == 200 || response.status === 202) {
                    console.log("Successful response from submitting csv");
                    try{
                        inner_data = JSON.parse(response.data);
                    } catch {
                        inner_data = response.data;
                    }
                    // Handle success. If there are categories to validate, prompt the user.
                    $('#cbc_response').html('Upload successful! ' + inner_data.message);
                }
                else {
                    console.log("Error response from submitting csv");
                    try{
                        inner_data = response.data.message;
                    } catch {
                        inner_data = response.data;
                    }
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
