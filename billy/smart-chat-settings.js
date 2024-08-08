
// Handle submission of smart-chat form
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('smart-chat-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        var warningDiv = document.getElementById('warningMessage');
        warningDiv.textContent = 'Saving...';
        warningDiv.style.display = 'flex';

        var formData = new FormData(form);
        formData.append('action', 'bifm_smart_chat_settings');
        formData.append('nonce', cbc_script_object_chat.nonce);
        //extract the list of files from the uploaded_files div and append them to the form
        var uploadedFiles = document.getElementsByClassName('file-name-line');
        //store files list as array
        var files_list = [];
        for (var i = 0; i < uploadedFiles.length; i++) {
            // extract attribute file-name
            var file = uploadedFiles[i].getAttribute('file-name');
            files_list.push(file);
        }
        formData.append('files_list', JSON.stringify(files_list));
        fetch(cbc_script_object_chat.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                var warningDiv = document.getElementById('warningMessage');
                warningDiv.textContent = 'Smart chat settings saved successfully.';
                warningDiv.style.display = 'flex';
            } else {
                console.error('Error returned from server:', result.data);
                var warningDiv = document.getElementById('warningMessage');
                if (result.data.message) {
                    warningDiv.textContent = result.data.message;
                }
                else {
                    warningDiv.textContent = 'An error occurred. Please try again.';
                }
                // Remove files from UI if they are not successfully saved
                var uploadedFilesList = document.getElementById('uploadedFiles');
                var uploadedFiles = uploadedFilesList.children;
                // Iterate over childern of uploadedFiles and create list items
                for (var i = 0; i < uploadedFiles.length; i++) {
                    // only remove files if class is newfile
                    if (uploadedFiles[i].classList.contains('newfile')) {
                        uploadedFilesList.removeChild(uploadedFiles[i]);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            var warningDiv = document.getElementById('warningMessage');
            warningDiv.textContent = 'An error occurred. Please try again.';
            warningDiv.style.display = 'flex';
        });
    });
});

var uploadedFiles = document.getElementById('uploadedFiles');
//check if files have already been uploaded, else hide this section
if (uploadedFiles.innerHTML == '') {
    document.getElementById('uploadedFilesSection').style.display = 'none';
}

//file upload for smart chat
var fileInput = document.getElementById('fileUpload');
if (fileInput) {
    fileInput.addEventListener('change', function() {
        var uploadedFilesList = document.getElementById('uploadedFiles');

        // Create a <ul> element for the list
        var list = document.createElement('ul');
        list.className = 'collection newfile'; // Materialize class for lists

        // Iterate over files and create list items
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            var listItem = document.createElement('li');
            listItem.className = 'collection-item newfile'; // Materialize class for list items
            listItem.innerHTML = '<div class="file-name-line" file-name="' + file.name + '">' + file.name + '<a href="#!" class="secondary-content"><i class="material-icons" onclick="removeFile(this)">delete</i></a></div>';
            list.appendChild(listItem);
        }

        uploadedFilesList.appendChild(list);
    });
} else {
    console.error('File upload element not found');
}

function removeFile(element) {
    var listItem = element.closest('li'); // Finds the closest ancestor <li>
    if (listItem) {
        listItem.parentNode.removeChild(listItem);
    }
}


document.getElementById('reset_chat').addEventListener('click', resetChat);
function resetChat(event){
    //prevent default
    event.preventDefault();
    //alert the user this will delete the chatbot, are they sure?
    if (!confirm('Are you sure you want to delete the chatbot? This is irreversible.')) {
        return;
    }
    // submit an ajax request to reset the chat, don't send form data
    console.log("asking to delete chatbot");
    fetch(cbc_script_object_chat.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=bifm_smart_chat_reset&nonce=' + cbc_script_object_chat.nonce,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Chatbot deleted successfully.');
            //refresh the page
            window.location.href = 'admin.php?page=create-chat';
        } else {
            alert("Chatbot couldn't be deleted.");
        }
    });
}

// process clicks on back button
document.getElementById('backButton').addEventListener('click', goBack);
function goBack() {
    window.location.href = 'admin.php?page=bifm';
}
