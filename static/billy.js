jQuery(document).ready(function($) {
    const form = $('#billy-form');
    const chatbox = $('#billy-chatbox');
    const submit_chat = $('#billy-submit_chat');
    let commandCount = 0;
    const MAX_COMMANDS = 50;
    let processingMessage = $('<div>');
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
    form.on('submit', function(event) {
        console.log("submit event");
        event.preventDefault();
        prevMessageCounts.push(chatbox.find('p').length);
        if (commandCount < MAX_COMMANDS) {
            let userInput = form.find('[name="assistant_instructions"]').val();
            let user_bubble = $('<div class="bubble user-bubble">').html(`${userInput}`);
            chatbox.append(user_bubble);
        }
        processingMessage.html('<div id="billy-responding" class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>');
        chatbox.append(processingMessage);
        // Send the message to the server
        sendMessage(form.find('[name="assistant_instructions"]').val(), null, null, null);
    });

    // Send the message from either submit or debug to the server
    function sendMessage(messageBody, widget_name, run_id, tool_call_id) {
        $.ajax({
            url: billy_localize.ajax_url,
            type: 'POST',
            data: {
                action: 'send_chat_message',
                nonce: billy_localize.nonce,
                message: messageBody,
                widget_name: widget_name,
                run_id: run_id,
                tool_call_id: tool_call_id
            },
            success: function(response) {
                console.log("success response");
                $('#billy-responding').hide(); // Hide responding animation
                
                // Convert the response message from Markdown to HTML
                const htmlContent = md.render(response.data.message);
                let div = $('<div class="bubble billy-bubble">').html(`${htmlContent}`);
                chatbox.append(div);

                // if response contain widget, append as html
                if (response.data.widget_object) {
                    console.log("contains widget");
                    let div = $('<div>').html(response.data.widget_object.widget);
                    chatbox.append(div);
                    if (response.data.widget_object.script) {
                        console.log("contains script");
                        // if response contain script, append to the document's existing <script> tag
                        $('<script>').html(response.data.widget_object.script).appendTo('body');
                    }
                    return;
                }

                // Highlight the code blocks
                hljs.highlightAll();
            },
            error: function(error) {
                // Handle errors here
                console.log("error response");
                const errorMessage = error.responseJSON.data.message;
                console.log(errorMessage);
                displayWarning(errorMessage);
                processingMessage.remove();
                form.find('[name="assistant_instructions"]').prop('disabled', false);
                submit_chat.prop('disabled', false);
            }
        });
        form.find('[name="assistant_instructions"]').val('').css('height', '9px');
    }

    // Note: The displayWarning function is not defined in the original code snippet.
    // You may need to implement it separately if it's used elsewhere in your application.
});
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


