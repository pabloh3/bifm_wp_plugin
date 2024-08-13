jQuery(document).ready(function($) {
    // Fetch the categories when the page loads
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
                    categoryInput.append($('<option>').val(category.id).text(category.name));
                });
                categoryInput.append('<option value="other">Other...</option>');
                categoryInput.formSelect(); // Re-initialize the Materialize dropdown
            }
        }
    });

    // Create a new category if the user selects "Other..." from the dropdown
    $('#category_input').change(function() {
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
                            showMessage('Failed to create new category. Please try again.');
                        }
                    }
                });
            } else {
                $(this).val(""); // Clear selection
                $(this).formSelect();
            }
        }
    });

    let itemCount = 1;

    // Add more items to be generated on "add more +"
    $('.add-more').on('click', function(e) {
        e.preventDefault();
        addNewInput();
    });

    // Function to add new input
    function addNewInput() {
        itemCount++;
        let newInput = `
        <div class='writer-input'>
            <div class="input-field bifm-col s12">
                <input id="description${itemCount}" type="text" class="writer-input-box validate">
                <label for="description${itemCount}">Keyphrase (online search term) you'd like to capture</label>
                <select id="category_input${itemCount}" class="browser-default category-dropdown">
                    <option value="1" disabled selected>Category</option>
                </select>
            </div>
        </div>`;
        $('#writer-buttons').before(newInput);

        // Fetch categories for new input
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
                    let categoryInput = $(`#category_input${itemCount}`);
                    categories.forEach(category => {
                        categoryInput.append($('<option>').val(category.id).text(category.name));
                    });
                    categoryInput.append('<option value="other">Other...</option>');
                    categoryInput.formSelect(); // Re-initialize the Materialize dropdown
                }
            }
        });
    }

    // Handle create post submissions
    $('#generate-blogposts-button').on('click', function(e) {
        e.preventDefault();
        let items = [];
        $('.writer-input').each(function() {
            let keyphrase = $(this).find('input[type="text"]').val();
            let categoryValue = $(this).find('select').val();
            if (categoryValue === '' || categoryValue === null) {
                categoryValue = 1;
            }
            let categoryName = $(this).find('select option:selected').text();
            if (keyphrase) {
                items.push({ keyphrase, category: categoryValue, category_name: categoryName });
            }
        });

        if (items.length === 0) {
            showMessage('Please add at least one item with a keyphrase and category.');
            return;
        }

        if (items.length === 1) {
            submitSingleItem(items[0]);
        } else {
            console.log("Submitting bulk items");
            console.log(items.length);
            submitBulkItems(items);
        }
    });

    function submitSingleItem(item) {
        let data = {
            action: 'cbc_bifm_create_blog',
            nonce: cbc_object.single_post_nonce,
            keyphrase: item.keyphrase,
            category: item.category,
            category_name: item.category_name
        };

        $.post(cbc_object.ajax_url, data, function(response) {
            handleResponse(response);
        }).fail(handleError);
    }

    function submitBulkItems(items) {
        let data = {
            action: 'cbc_create_bulk_blogs',
            nonce: cbc_object.bulk_upload_nonce,
            items: items
        };

        $.post(cbc_object.ajax_url, data, function(response) {
            handleResponse(response);
        }).fail(handleError);
    }

    function handleResponse(response) {
        console.log(response);
        if (response.status === 202) {
            //json load the response message
            response_data = JSON.parse(response.data);
            if (response_data.message) {
                showMessage(response_data.message);
            } else {
                showMessage('The blog is being created. This might take up to 2 minutes...');
            }
        } else {
            let message =  'An unknown error occurred.';
            if (response.data && response.data.message) {
                message = response.data.message;
            } else if (response.message) {
                message = response.message;
            }
            showMessage(message)
        }
    }

    function handleError(jqXHR, textStatus, errorThrown) {
        let parsedData = (jqXHR.responseJSON && jqXHR.responseJSON.data) ? JSON.parse(jqXHR.responseJSON.data) : null;
        let errorMessage = 'Failed to connect to the backend.';
        if (parsedData && parsedData.message) {
            errorMessage = parsedData.message;
        } else if (parsedData && parsedData.error) {
            errorMessage = parsedData.error;
        }
        showMessage(errorMessage);
    }

    // Process clicks on back button
    $('#backButton').on('click', goBack);
    function goBack() {
        window.location.href = 'admin.php?page=bifm';
    }

    // Delete request
    $('#posts-table-div').on('click', '.delete-post', function(e) {
        e.preventDefault();
        //get confirmation
        if (!confirm('Are you sure you want to delete these request and post?')) {
            return;
        }
        let uuid = this.getAttribute('data-uuid');
        let postId = this.getAttribute('data-post-id');
        console.log("uuid: " + uuid + " post id: " + postId);
        let data = {
            action: 'cbc_delete_blog',
            nonce: cbc_object.single_post_nonce,
            post_id: postId,
            uuid: uuid
        };

        $.post(cbc_object.ajax_url, data, function(response) {
            if (response.success) {
                $(e.target).closest('tr').remove();
            } else {
                showMessage('Failed to delete the post. Please try again.');
            }
        }).fail(function() {
            showMessage('Failed to connect to the backend.');
        });
    });

    // Handle suggestion button clicks
    $('#suggestion-buttons .suggestion-button').on('click', function(e) {
        e.preventDefault();
        let suggestionText = $(this).text();
        fillSuggestion(suggestionText);
    });

    function fillSuggestion(suggestionText) {
        let emptyInput = $('.writer-input').filter(function() {
            return !$(this).find('input[type="text"]').val();
        }).first();

        if (emptyInput.length) {
            emptyInput.find('input[type="text"]').val(suggestionText).next('label').addClass('active');
        } else {
            addNewInput();
            $(`#description${itemCount}`).val(suggestionText).next('label').addClass('active');
        }
    }

    // Define the showMessage function
    function showMessage(message) {
        console.log('Message',message);
        // Scroll to #cbc_response
        $('html, body').animate({
            scrollTop: $('#cbc_response').offset().top + 350
        }, 1000);

        // Display the message and then show the #cbc_response element
        $('#cbc_response').html(message).show();

        // Fade out the message after 5 seconds, then refresh the page
        setTimeout(function() {
            $('#cbc_response').fadeOut('slow', function() {
                // Refresh the page after the fade out is complete
                location.reload();
            });
        }, 5000);
    }
});
