const form = document.getElementById('builder-form');
const chatbox = document.getElementById('builder-chatbox');
const submit_chat = document.getElementById('builder-submit-chat');
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
var undoButton = document.getElementById('undo-button');
undoButton.style.display = 'none';
var currentStage = 'visual';




// Initialize markdown-it and highlight.js
const md = window.markdownit({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return '<pre class="hljs"><code>' +
                    hljs.highlight(str, { language: lang }).value +
                    '</code></pre>';
            } catch (__) {}
        }
        return '<pre class="hljs"><code>' + md.utils.escapeHtml(str) + '</code></pre>';
    }
});

// listens to chat submissions
// when document is ready
document.addEventListener('DOMContentLoaded', function() {
    form.addEventListener('keypress', function(event) {
        if (event.key === 'Enter' && !(event.shiftKey || event.ctrlKey || event.metaKey)) {
            event.preventDefault();
            submit_chat.click();
        }
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        prevMessageCounts.push(chatbox.getElementsByClassName('bubble').length);
        let message;
        if (commandCount < MAX_COMMANDS) {
            let userInput = form.builder_instructions.value;
            
            let div = document.createElement('div');
            div.classList.add('bubble');
            div.classList.add('user-bubble');
            div.textContent = `${userInput}`;
            chatbox.appendChild(div);
            message = form.builder_instructions.value;
            form.builder_instructions.value = '';
            // scroll to bottom of chatbox
            chatbox.scrollTop = chatbox.scrollHeight;
        }
        sendMessage(folderName, 'processgpt', message, currentStage);
    });

    // Process clicks on back button
    document.getElementById('backButton').addEventListener('click', goBack);
    function goBack() {
        window.location.href = 'admin.php?page=bifm';
    }

});

// send the message from either submit or debug to the server
function sendMessage(folderName, endpoint, messageBody, currentStage) {
    // try - catch
    try {
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
                if (response.status === 202 || response.status === 200) {
                    let innerData = JSON.parse(response.data);
                    jobId = innerData.jobId;
                    warning = innerData.warning;
                    if(warning){
                        displayWarning(warning);
                    }
                    if (response.status === 202) {
                        processingMessage.innerHTML = '<div class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
                        document.getElementById('builder-chatbox').appendChild(processingMessage);
                        form.builder_instructions.disabled = true;
                        submit_chat.disabled = true;
                        setTimeout(pollForGptResponse, 5000, folderName, jobId);
                    } else if (response.status === 200) {
                        addCoderBubble(`I've completed your request, please review.`,false);
                    }

                } else if (response.success === false) {
                    if(response.data.message) {
                        displayWarning(response.data.message);
                    }
                    else { 
                        displayWarning(response);
                    }
                }else {
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
                document.getElementById('builder-chatbox').removeChild(processingMessage);
                form.builder_instructions.disabled = false;
                submit_chat.disabled = false;
            }
        });
    } catch (error) {
        displayWarning(error);
        document.getElementById('builder-chatbox').removeChild(processingMessage);
        form.builder_instructions.disabled = false;
        submit_chat.disabled = false;
    }
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
                form.builder_instructions.disabled = false;
                submit_chat.disabled = false;
                document.getElementById('builder-chatbox').removeChild(processingMessage);
                let response_gpt = JSON.parse(response.data);
                // Get the last message id and message from response_gpt.gpt_says_dict
                let lastMsgId = Object.keys(response_gpt.gpt_says_dict).pop();
                if (lastMsgId) {
                    let lastMsg = response_gpt.gpt_says_dict[lastMsgId];
                    if (!displayedMessageIds.has(lastMsgId)) {
                        while (chatbox.getElementsByClassName('bubble').length > prevMessageCounts[prevMessageCounts.length - 1]+1){
                            // Remove the last bubble
                            let bubbles = chatbox.querySelectorAll('.bubble');
                            bubbles[bubbles.length - 1].remove();
                        }
                        addCoderBubble(lastMsg, true);
                        displayedMessageIds.add(lastMsgId);
                    }
                } else {
                    addCoderBubble(`I've completed your request, please review.`,false);
                }
    
                var iframe = document.querySelector('#bifm-builder-frame');
                iframe.src = iframe.src;
                commandCount++;
                if (commandCount >= MAX_COMMANDS) {
                    form.builder_instructions.disabled = true;
                    submit_chat.disabled = true;
                    addCoderBubble(`This request is now too long. Please save your work and start a new request. Contact us at www.builditforme.ai if you need help.`,false);
                }
                if (response.data.logs) {
                    document.getElementById('terminal-output').innerHTML = response.data.logs;
                }
            } else if (response.status === 202) {
                form.builder_instructions.disabled = true;
                submit_chat.disabled = true;
                let response_gpt = JSON.parse(response.data);
                // Get the last message id and message from response_gpt.gpt_says_dict
                let lastMsgId = Object.keys(response_gpt.gpt_says_dict).pop();
                if (lastMsgId) {
                    let lastMsg = response_gpt.gpt_says_dict[lastMsgId];
                    if (!displayedMessageIds.has(lastMsgId)) {
                        while(chatbox.getElementsByClassName('bubble').length > prevMessageCounts[prevMessageCounts.length - 1]+1){
                            let bubbles = chatbox.querySelectorAll('.bubble');
                            bubbles[bubbles.length - 1].remove();
                        }
                        addCoderBubble(lastMsg, true);
                        displayedMessageIds.add(lastMsgId);
                    }
                    processingMessage.innerHTML = '<div class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
                    document.getElementById('builder-chatbox').appendChild(processingMessage);
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
                document.getElementById('builder-chatbox').removeChild(processingMessage);
                form.builder_instructions.disabled = false;
                submit_chat.disabled = false;
                iframe = document.querySelector('#bifm-builder-frame');
                iframe.src = iframe.src;
                addCoderBubble(`I've completed your request, please review.`,false);
            }
        }
    });
}


// save button when document is ready
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('save-button').addEventListener('click', function(event) {
        event.preventDefault();
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
    document.getElementById('reset-button').addEventListener('click', function(event) {
        // prevent default
        event.preventDefault();
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
                    document.getElementById('builder-chatbox').innerHTML = '';
                    undoButton.style.display = 'none';
                    // reset iframe
                    var iframe = document.querySelector('#bifm-builder-frame');
                    commandCount = 0;
                    prevMessageCounts = [0];
                    //wait 1 second
                    setTimeout(function() {
                        iframe.src = iframe.src;
                    }, 1000);
                } else {
                    console.error('Error:', response.data);
                    displayWarning('An error occurred while resetting the widget. Please try again.');
                }
            },
            error: function(errorThrown) {
                console.error('Error:', errorThrown);
                displayWarning('An error occurred while resetting the widget. Please try again.');
            }
        });
    });

    // HANDLE UNDO BUTTON
    undoButton.addEventListener('click', function() {
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
                    // Remove the messages added in the last operation while elemetns with class bubble are more than the last message count)
                    while (chatbox.getElementsByClassName('bubble').length > prevMessageCounts[prevMessageCounts.length - 1]) {                    
                        let bubbles = chatbox.querySelectorAll('.bubble');
                        bubbles[bubbles.length - 1].remove();
                    }
                    attachUndo();
                    prevMessageCounts.pop();
                    var iframe = document.querySelector('#bifm-builder-frame');
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


    document.getElementById('controls-stage').addEventListener('click', function(event) {
        event.preventDefault();
        // reset iframe
        displayWarning('You\'re asking the Builder for Elementor controls for your widget, so you can adapt it each time you\'re dragging it into a page. If you add text controls, don\'t worry if the preview breaks. Save and test in your pages. Once you\'re done, save your widget.', false);
        commandCount = 0;
        prevMessageCounts = [0];
        document.getElementById('controls-stage').style.display = 'none';
        document.getElementById('visual-stage').style.display = 'block';
        currentStage = 'controls';
    }); 

    document.getElementById('visual-stage').addEventListener('click', function(event) {
        event.preventDefault();
        // reset iframe
        displayWarning('You\'re back at editing the widget. Ask the bot for changes on your widget that will apply every time you use the widget.', false);
        //scroll to top
        commandCount = 0;
        prevMessageCounts = [0];
        document.getElementById('visual-stage').style.display ='none';
        document.getElementById('controls-stage').style.display = 'block';
        currentStage = 'visual';
    });
});

// Creates and adds a red bubble to display a warning
function displayWarning(message) {
    // display warning message as a warning bubble in the chat
    var div = document.createElement('div');
    div.classList.add('bubble');
    div.classList.add('warning-bubble');
    div.textContent = message;
    chatbox.appendChild(div);
    chatbox.scrollTop = chatbox.scrollHeight;
}


function addCoderBubble(message, isMarkdown) {
    let div = document.createElement('div');
    div.classList.add('bubble');
    div.classList.add('coder-bubble');
    if (isMarkdown) {
        message = md.render(message);
        div.innerHTML = `${message}`;
    } else {
        div.textContent = message;
    }
    chatbox.appendChild(div);
    // scroll to bottom of chatbox
    chatbox.scrollTop = chatbox.scrollHeight + 15;
    attachUndo();
}

// Attaches the undo button to the last coder-bubble in the chatbox (end of chatbox with width of last bubble as left margin)
function attachUndo() {
    undoButton.style.display = 'block';
    // fetch the last coder-bubble
    let bubbles = chatbox.querySelectorAll('.bubble');
    lastBubble = bubbles[bubbles.length - 1];
    chatbox.appendChild(undoButton);
    //make the undo button's margin-left the same as the last coder-bubble's width
    try {    
        let offset = lastBubble.offsetWidth + 10;
        undoButton.style.marginLeft = offset + 'px';
        //regenerate the undo button
    } catch (error) {
        console.error('Error:', error);
    }
}


document.addEventListener('DOMContentLoaded', (event) => {
    const modal = document.getElementById("myModal");
    const continueButton = document.getElementById("continueButton");
    const closeButton = document.querySelector(".bifm-close-button");

    // Show the modal when the page loads
    modal.style.display = "block";

    // Function to close the modal
    function closeModal() {
        modal.style.display = "none";
    }

    // When the user clicks on <span> (x), close the modal
    closeButton.onclick = function() {
        closeModal();
    }

    // When the user clicks on the continue button, close the modal and do something else
    continueButton.onclick = function(event) {
        event.preventDefault();
        closeModal();
        // Add any additional actions for the continue button here
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
});
