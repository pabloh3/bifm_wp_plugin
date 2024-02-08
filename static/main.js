const form = document.getElementById('my-form');
const chatbox = document.getElementById('chatbox');
const submit_chat = document.getElementById('submit_chat');
let commandCount = 0;
const MAX_COMMANDS = 50;
let processingMessage = document.createElement('div');
let prevMessageCounts = [0];
let retryCount = 0;
let displayedMessageIds = new Set(); // To keep track of message ids already displayed
// Extract the 'foldername' parameter from the URL
var urlParams = new URLSearchParams(window.location.search);
var folderName = urlParams.get('foldername');
// Remove any letters from the folderName to get client_folder
var client_folder = folderName.replace(/[a-zA-Z]/g, '');
console.log('JS loaded v1.0.71');

//stages = {visuals, functional, controls}

// listens to chat submissions
form.addEventListener('submit', (event) => {
    event.preventDefault();
    prevMessageCounts.push(chatbox.getElementsByTagName('p').length);
    if (commandCount < MAX_COMMANDS) {
        let userInput = form.name.value;
        let p = document.createElement('p');
        p.textContent = `You: ${userInput}`;
        chatbox.appendChild(p);
    }
    var stageDisplay = document.getElementById('stageDisplay');
    var currentStage = stageDisplay.getAttribute('data-stage');
    sendMessage(folderName, 'processgpt', form.name.value, currentStage);
});

// process clicks on back button
document.getElementById('backButton').addEventListener('click', goBack);
function goBack() {
    window.location.href = 'admin.php?page=bifm-plugin';
}

// send the message from either submit or debug to the server
function sendMessage(folderName, endpoint, messageBody, currentStage) {
    jQuery.ajax({
        url: my_plugin.ajax_url,
        type: 'POST',
        data: {
            action: 'plugin_send_message',
            nonce: my_plugin.nonce,
            folderName: folderName,
            endpoint: endpoint,
            messageBody: messageBody,
            stage: currentStage
        },
        success: function(response) {
            let innerData = JSON.parse(response.data);
            jobId = innerData.jobId;
            warning = innerData.warning;
            if(warning){
                displayWarning(warning);
            }
            if (response.status === 202) {
                processingMessage.innerHTML = '<div class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
                document.getElementById('chatbox').appendChild(processingMessage);
                form.name.disabled = true;
                submit_chat.disabled = true;
                setTimeout(pollForGptResponse, 5000, folderName, jobId);
            } else if (response.status === 200) {
                let p = document.createElement('p');
                p.textContent = `GPT: I've completed your request, please review.`;
                chatbox.appendChild(p);
            } else {
                throw new Error('An error occurred while processing your request');
            }
        },
        error: function(error) {
            if(JSON.parse(error.responseJSON.data).message) {
                displayWarning(JSON.parse(error.responseJSON.data).message);
            }
            else { 
                displayWarning(error);
            }
            document.getElementById('chatbox').removeChild(processingMessage);
            form.name.disabled = false;
            submit_chat.disabled = false;
        }
    });
    form.name.value = '';
    form.name.style.height = "9px"; 
}



// listens for responses on messages sent.
function pollForGptResponse(folderName, jobId, retryCount) {
    jQuery.ajax({
        url: my_plugin.ajax_url,
        type: 'POST',
        data: {
            action: 'my_plugin_poll_action',
            nonce: my_plugin.nonce,
            folderName: folderName,
            jobId: jobId
        },
        success: function(response) {
            if (response.data && response.status === 200) {
                form.name.disabled = false;
                submit_chat.disabled = false;
                document.getElementById('chatbox').removeChild(processingMessage);
                let response_gpt = JSON.parse(response.data);
                // Get the last message id and message from response_gpt.gpt_says_dict
                let lastMsgId = Object.keys(response_gpt.gpt_says_dict).pop();
                if (lastMsgId) {
                    let lastMsg = response_gpt.gpt_says_dict[lastMsgId];
                    if (!displayedMessageIds.has(lastMsgId)) {
                        while (chatbox.getElementsByTagName('p').length > prevMessageCounts[prevMessageCounts.length - 1]+1){
                            chatbox.removeChild(chatbox.lastChild);
                        }
                        let p = document.createElement('p');
                        p.textContent = `GPT: ${lastMsg}`;
                        chatbox.appendChild(p);
                        displayedMessageIds.add(lastMsgId);
                    }
                } else {
                    document.getElementById('chatbox').innerHTML += `<p>Bot: Ok, I completed your request :)</p>`;
                }
    
                var iframe = document.querySelector('.future-column iframe');
                iframe.src = iframe.src;
                commandCount++;
                if (commandCount >= MAX_COMMANDS) {
                    form.name.disabled = true;
                    submit_chat.disabled = true;
                    document.getElementById('chatbox').innerHTML += `<p>Thank you for using our beta product. Sign up for our waitlist <a href="https://www.builditforme.ai">here</a>.</p>`;
                }
                if (response.data.logs) {
                    document.getElementById('terminal-output').innerHTML = response.data.logs;
                }
            } else if (response.status === 202) {
                form.name.disabled = true;
                submit_chat.disabled = true;
                let response_gpt = JSON.parse(response.data);
                // Get the last message id and message from response_gpt.gpt_says_dict
                let lastMsgId = Object.keys(response_gpt.gpt_says_dict).pop();
                if (lastMsgId) {
                    let lastMsg = response_gpt.gpt_says_dict[lastMsgId];
                    if (!displayedMessageIds.has(lastMsgId)) {
                        while(chatbox.getElementsByTagName('p').length > prevMessageCounts[prevMessageCounts.length - 1]+1){
                            chatbox.removeChild(chatbox.lastChild);
                        }
                        let p = document.createElement('p');
                        p.textContent = `GPT: ${lastMsg}`;
                        chatbox.appendChild(p);
                        displayedMessageIds.add(lastMsgId);
                    }
                    processingMessage.innerHTML = '<div class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
                    document.getElementById('chatbox').appendChild(processingMessage);
                }
                setTimeout(pollForGptResponse, 3000, folderName, jobId, 0); 
            }
        },
        error: function(error) {
            console.error('Error:', error);
            retryCount += 1;

            if (retryCount < 2) {
                setTimeout(pollForGptResponse, 3000, folderName, jobId, 1);
            } else {
                document.getElementById('chatbox').removeChild(processingMessage);
                form.name.disabled = false;
                submit_chat.disabled = false;
                iframe.src = iframe.src;
                document.getElementById('chatbox').innerHTML += `<p>GPT: Your request has finished running.</p>`;
            }
        }
    });
}


// save button
document.getElementById('save-button').addEventListener('click', function() {
    // Prompt user for name
    const name = prompt('Name for this version (max 60 chars):');
    if (name && name.length <= 60 && /^[A-Za-z]/.test(name)) {
        // Send save request to the server with the name using jQuery's AJAX method
        jQuery.ajax({
            url: my_plugin.ajax_url,  // Assuming you've localized the script with the admin-ajax.php URL
            type: 'POST',
            data: {
                action: 'plugin_save',  
                name: name,
                folderName: folderName,
                nonce: my_plugin.nonce  
            },
            success: function(response) {
                // Handle the response if needed
                if(response.success) {
                    alert('Your widget has been saved.');
                } else {
                    alert(response.data['message']);
                    console.error('Error:', response.data);
                }
            },
            error: function(errorThrown) {
                alert('There was an error saving your widget.');
                console.error('Error:', errorThrown);
            }
        });
    } else {
        alert('Invalid name. Please enter a name starting with a letter and with a maximum of 40 characters.');
    }
});


// reset button
document.getElementById('reset-button').addEventListener('click', function() {
    var warningDiv = document.getElementById('warningMessage');
    warningDiv.textContent = "";
    // Make the warning box visible
    warningDiv.style.display = 'none';
    // Prompt user for name
    jQuery.ajax({
        url: my_plugin.ajax_url,  // Assuming you've localized the script with the admin-ajax.php URL
        type: 'POST',
        data: {
            action: 'plugin_reset',  // This should match the action hooked to wp_ajax_ in your PHP
            folderName: folderName,
            nonce: my_plugin.nonce  // Assuming you've localized the script with a nonce for security
        },
        success: function(response) {
            // Handle the response if needed
            if(response.success) {
                // Empty the chat on the front end
                document.getElementById('chatbox').innerHTML = '';
                // reset iframe
                var iframe = document.querySelector('.future-column iframe');
                commandCount = 0;
                prevMessageCounts = [0];
                iframe.src = iframe.src;
            } else {
                console.error('Error:', response.data);
            }
        },
        error: function(errorThrown) {
            console.error('Error:', errorThrown);
        }
    });
});


document.getElementById('undo-button').addEventListener('click', function() {
    jQuery.ajax({
        url: my_plugin.ajax_url,  // Assuming you've localized the script with the admin-ajax.php URL
        type: 'POST',
        data: {
            action: 'plugin_undo',  // This should match the action hooked to wp_ajax_ in your PHP
            folderName: folderName,
            nonce: my_plugin.nonce  // Assuming you've localized the script with a nonce for security
        },
        success: function(response) {
            // Handle the response if needed
            if(response.success) {
                // Refresh the iframe
                // Remove the messages added in the last operation
                while (chatbox.getElementsByTagName('p').length > prevMessageCounts[prevMessageCounts.length - 1]) {
                    chatbox.removeChild(chatbox.lastChild);
                }
                prevMessageCounts.pop();
                var iframe = document.querySelector('.future-column iframe');
                iframe.src = iframe.src;
            } else {
                console.error('Error:', response.data);
            }
        },
        error: function(errorThrown) {
            console.error('Error:', errorThrown);
        }
    });
});


document.getElementById('next-stage').addEventListener('click', function() {
    document.getElementById('chatbox').innerHTML = '';
    // reset iframe
    commandCount = 0;
    prevMessageCounts = [0];
    
    var stageDisplay = document.getElementById('stageDisplay');
    var currentStage = stageDisplay.getAttribute('data-stage');
    if (currentStage === 'visual') {
        stageDisplay.innerHTML = 'Add controls to your widget so you can modify it when dragging it into a page.';
        stageDisplay.setAttribute('data-stage', 'controls');
        document.getElementById('next-stage').style.display = 'none';
        document.getElementById('previous-stage').style.display = 'inline';
    } 
});

document.getElementById('previous-stage').addEventListener('click', function() {
    document.getElementById('chatbox').innerHTML = '';
    // reset iframe
    commandCount = 0;
    prevMessageCounts = [0];
    
    var stageDisplay = document.getElementById('stageDisplay');
    var currentStage = stageDisplay.getAttribute('data-stage');
    if (currentStage === 'controls') {
        stageDisplay.innerHTML = 'Modify how your widget looks.';
        stageDisplay.setAttribute('data-stage', 'visual');
        document.getElementById('next-stage').style.display = 'inline';
        document.getElementById('previous-stage').style.display = 'none';
    }
});

function displayWarning(message) {
    var warningDiv = document.getElementById('warningMessage');
    warningDiv.textContent = message;

    // Make the warning box visible
    warningDiv.style.display = 'block';
}


