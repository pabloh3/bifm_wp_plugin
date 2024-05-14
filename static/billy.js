const form = document.getElementById('billy-form');
const chatbox = document.getElementById('billy-chatbox');
const submit_chat = document.getElementById('billy-submit_chat');
let commandCount = 0;
const MAX_COMMANDS = 50;
let processingMessage = document.createElement('div');
let prevMessageCounts = [0];
let retryCount = 0;
let displayedMessageIds = new Set(); // To keep track of message ids already displayed

// Extract the 'foldername' parameter from the URL
var urlParams = new URLSearchParams(window.location.search);

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

// Listens to chat submissions
form.addEventListener('submit', (event) => {
    event.preventDefault();
    prevMessageCounts.push(chatbox.getElementsByTagName('p').length);
    if (commandCount < MAX_COMMANDS) {
        let userInput = form.name.value;
        let div = document.createElement('div');
        div.innerHTML = `<b>You:</b> ${userInput}`;
        chatbox.appendChild(div);
    }
    processingMessage.innerHTML = '<div id="billy-responding" class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
    document.getElementById('billy-chatbox').appendChild(processingMessage);
    // Send the message to the server
    sendMessage(form.name.value);
});

// Send the message from either submit or debug to the server
function sendMessage(messageBody) {
    jQuery.ajax({
        url: billy_localize.ajax_url,
        type: 'POST',
        data: {
            action: 'send_chat_message',
            nonce: billy_localize.nonce,
            message: messageBody,
        },
        success: function(response) {
            console.log("success response");
            document.getElementById('billy-responding').style.display = 'none'; // Hide responding animation
            
            // Convert the response message from Markdown to HTML
            const htmlContent = md.render(response.data.message);
            let div = document.createElement('div');
            div.innerHTML = `<b>GPT:</b> ${htmlContent}`;
            chatbox.appendChild(div);

            // Highlight the code blocks
            hljs.highlightAll();
        },
        error: function(error) {
            // Handle errors here
            console.log("error response");
            const errorMessage = error.responseJSON.data.message;
            console.log(errorMessage);
            displayWarning(errorMessage);
            document.getElementById('billy-chatbox').removeChild(processingMessage);
            form.name.disabled = false;
            submit_chat.disabled = false;
        }
    });
    form.name.value = '';
    form.name.style.height = "9px"; 
}


/*// listens for responses on messages sent.
function pollForGptResponse(folderName, jobId, retryCount) {
    jQuery.ajax({
        url: billy_localize.ajax_url,
        type: 'POST',
        // to do figure out where to poll
        data: {
            action: 'my_plugin_poll_action',
            nonce: billy_localize.nonce,
            jobId: jobId
        },
        success: function(response) {
            if (response.data && response.status === 200) {
                form.name.disabled = false;
                submit_chat.disabled = false;
                document.getElementById('billy-chatbox').removeChild(processingMessage);
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
                    document.getElementById('chatbox').innerHTML += `<p>Bot: I got lost, please ask again. :)</p>`;
                }
                commandCount++;
                if (commandCount >= MAX_COMMANDS) {
                    form.name.disabled = true;
                    submit_chat.disabled = true;
                    document.getElementById('chatbox').innerHTML += `<p>Thank you for using our beta product. Sign up for our waitlist <a href="https://www.builditforme.ai">here</a>.</p>`;
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
                setTimeout(pollForGptResponse, 3000, jobId, 0); 
            }
        },
        error: function(error) {
            console.error('Error:', error);
            retryCount += 1;

            if (retryCount < 2) {
                setTimeout(pollForGptResponse, 3000, folderName, jobId, 1);
            } else {
                document.getElementById('billy-chatbox').removeChild(processingMessage);
                form.name.disabled = false;
                submit_chat.disabled = false;
                document.getElementById('billy-chatbox').innerHTML += `<p>GPT: Your request has finished running.</p>`;
            }
        }
    });
}*/

//to do enable reset buton
/*// reset button
document.getElementById('reset-button').addEventListener('click', function() {
    var warningDiv = document.getElementById('warningMessage');
    warningDiv.textContent = "";
    // Make the warning box visible
    warningDiv.style.display = 'none';
    // Prompt user for name
    jQuery.ajax({
        url: billy_localize.ajax_url,  // Assuming you've localized the script with the admin-ajax.php URL
        type: 'POST',
        data: {
            action: 'plugin_reset',  // This should match the action hooked to wp_ajax_ in your PHP
            folderName: folderName,
            nonce: billy_localize.nonce  // Assuming you've localized the script with a nonce for security
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
});*/

/*
document.getElementById('undo-button').addEventListener('click', function() {
    jQuery.ajax({
        url: billy_localize.ajax_url,  // Assuming you've localized the script with the admin-ajax.php URL
        type: 'POST',
        data: {
            action: 'plugin_undo',  // This should match the action hooked to wp_ajax_ in your PHP
            folderName: folderName,
            nonce: billy_localize.nonce  // Assuming you've localized the script with a nonce for security
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
});*/

function displayWarning(message) {
    var warningDiv = document.getElementById('warningMessage');
    warningDiv.textContent = message;

    // Make the warning box visible
    warningDiv.style.display = 'block';
    // set 5 second timeout to remove warning
    setTimeout(function() {
        warningDiv.style.display = 'none';
    }, 5000);
}


