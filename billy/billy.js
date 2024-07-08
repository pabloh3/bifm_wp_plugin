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

// submit form if enter
form.addEventListener('keypress', function(event) {
    if (event.key === 'Enter' && !(event.shiftKey || event.ctrlKey || event.metaKey)) {
        event.preventDefault();
        submit_chat.click();
    }
});

// Listens to chat submissions
form.addEventListener('submit', (event) => {
    event.preventDefault();
    suggestion = document.getElementById('suggestion-buttons');
    if (suggestion) {
        suggestion.style.display = 'none';
    }
    prevMessageCounts.push(chatbox.getElementsByTagName('p').length);
    if (commandCount < MAX_COMMANDS) {
        let userInput = form.assistant_instructions.value;
        let div = document.createElement('div');
        div.classList.add('user-bubble');
        div.classList.add('bubble');
        div.innerHTML = `${userInput}`;
        chatbox.appendChild(div);
        chatbox.scrollTop = chatbox.scrollHeight;
    }
    processingMessage.innerHTML = '<div id="billy-responding" class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
    document.getElementById('billy-chatbox').appendChild(processingMessage);
    // Send the message to the server
    sendMessage(form.assistant_instructions.value, null, null, null);
});

// Send the message from either submit or debug to the server
function sendMessage(messageBody, widget_name, run_id, tool_call_id) {
    jQuery.ajax({
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
            document.getElementById('billy-responding').style.display = 'none';            
            // Convert the response message from Markdown to HTML
            const htmlContent = md.render(response.data.message);
            if (htmlContent) {
                let div = document.createElement('div');
                //add classes to div
                div.classList.add('billy-bubble');
                div.classList.add('bubble');
                div.innerHTML = `${htmlContent}`;
                chatbox.appendChild(div);
                chatbox.scrollTop = chatbox.scrollHeight;
            }
            

            // if response contain widget, append as html
            if (response.data.widget_object) {
                console.log("contains widget");
                let div = document.createElement('div');
                div.innerHTML = response.data.widget_object.widget;
                chatbox.appendChild(div);
                if (response.data.widget_object.script) {
                    console.log("contains script");
                    // if response contain script, append to the document's existing <script> tag
                    let script = document.createElement('script');
                    script.innerHTML = response.data.widget_object.script;
                    document.body.appendChild(script);
                    chatbox.scrollTop = chatbox.scrollHeight;
                }
                return;
            }
            // Highlight the code blocks
            hljs.highlightAll();
        },
        error: function(error) {
            // Handle errors here
            console.log("error response");
            // check if there's data.message
            let errorMessage = "";
            try {
                errorMessage = error.responseJSON.data.message;
            } catch (error2) {
                try {
                    errorMessage = error.responseText;
                } catch (error3) {
                    errorMessage = "An error occurred. Please try again.";
                }
            }
            console.log(errorMessage);
            displayWarning(errorMessage);
            document.getElementById('billy-responding').style.display = 'none';
            form.assistant_instructions.disabled = false;
            submit_chat.disabled = false;
        }
    });
    form.assistant_instructions.value = ''; 
    // Scroll to the bottom of the chatbox
    chatbox.scrollTop = chatbox.scrollHeight;
}

// when one of the suggestions is clicked
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Handle clicks on suggestion buttons except for cards
        document.querySelectorAll('.suggestion-button:not(.card)').forEach(button => {
            button.addEventListener('click', function() {
                let suggestion = document.getElementById('suggestion-buttons');
                if (suggestion) {
                    suggestion.style.display = 'none';
                }
                let question = this.innerText; // Get the button's text
                let div = document.createElement('div');
                div.classList.add('user-bubble');
                div.classList.add('bubble');
                div.innerHTML = `${question}`;
                chatbox.appendChild(div);
                processingMessage.innerHTML = '<div id="billy-responding" class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';
                document.getElementById('billy-chatbox').appendChild(processingMessage);
                chatbox.scrollTop = chatbox.scrollHeight;
                sendMessage(question, null, null, null); // Send as a question to the bot
            });
        });

        // Handle clicks on card elements
        document.querySelectorAll('.suggestion-button.card').forEach(card => {
            card.addEventListener('click', function() {
                // Prevent the default form submission behavior
                // if id ="use-writer-suggestion" redirect to admin.php?page=create-blog
                if (this.id === "use-writer-suggestion") {
                    window.location.href = "admin.php?page=create-blog";
                } else if (this.id === "use-coder-suggestion") {
                    window.location.href = "admin.php?page=widget-manager";
                }
            });
        });

        // handle clicks on new_chat_button with id new-chat-button
        document.getElementById('new_chat_button').addEventListener('click', function(event) {
            // prevent sending a new request for the page
            event.preventDefault();
            //reset the suggestions
            let suggestion = document.getElementById('suggestion-buttons');
            if (suggestion) {
                suggestion.style.display = 'block';
            }
            cleanupChat();
            // call the API to create a new chat
            jQuery.ajax({
                url: billy_localize.ajax_url,
                type: 'POST',
                data: {
                    action: 'new_chat',
                    nonce: billy_localize.nonce,
                },
                success: function(response) {
                    console.log("success response");
                    // TO do add the new chat button to history
                    // prevent default
                    
                },
                error: function(error) {
                    // Handle errors here
                    console.log("There was an error when trying to create a new chat");
                }
            });
        });
    } catch (error) {
        console.log("Error in adding event listeners: ", error);
    }
});

// when old thread is clicked, load thread
document.addEventListener('DOMContentLoaded', function() {
    var threadButtons = document.querySelectorAll('.thread-list .thread-btn');

    threadButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            var threadId = this.getAttribute('data-thread-id');
            loadThread(threadId);
            // make button slightly darker and return other buttons to their color
            threadButtons.forEach(function(button) {
                button.style.backgroundColor = "#CDD2EA";
            });
            button.style.backgroundColor = "#ECEEF9";

        });
    });
});

function loadThread(threadId) {
    jQuery.ajax({
        url: billy_localize.ajax_url, // Ensure ajaxurl is defined or use admin_url('admin-ajax.php')
        type: 'POST',
        data: {
            action: 'load_billy_chat', // WordPress AJAX action hook
            thread_id: threadId,
            nonce: billy_localize.nonce,
        },
        success: function(response) {
            console.log('Chat loaded successfully');
            if (response.success && response.data) {
                // Clear existing messages
                chatbox.innerHTML = "";
        
                // Reverse the order of the data array to start with the last message
                response.data.reverse().forEach(function(message) {
                    let div = document.createElement('div');
                    div.classList.add('bubble');
                    // Assign class based on the role to differentiate user and assistant messages
                    if (message.role === 'user') {
                        div.classList.add('user-bubble');
                    } else {
                        div.classList.add('billy-bubble');
                    }
        
                    // Convert Markdown to HTML if needed and set it as innerHTML
                    div.innerHTML = md.render(message.text);
        
                    // Append the message bubble to the chatbox
                    chatbox.appendChild(div);
                });
        
                // Optionally scroll to the latest message
                chatbox.scrollTop = chatbox.scrollHeight;
            }
        },
        
        error: function(error) {
            console.error('Error loading thread:', error);
            displayWarning("Failed to load messages. Please try again.");
        }
    });
}


function cleanupChat() {
    // store copy of suggestion-buttons
    let suggestion = document.getElementById('suggestion-buttons');
    chatbox.innerHTML = "";
    prevMessageCounts = [0];
    commandCount = 0;
    displayedMessageIds = new Set();
    form.assistant_instructions.disabled = false;
    submit_chat.disabled = false;
    form.assistant_instructions.value = '';
    // add suggestion-buttons back to chatbox
    if (suggestion) {
        chatbox.appendChild(suggestion);
    }
}

function displayWarning(message) {
    let div = document.createElement('div');
    div.classList.add('warning-bubble');
    div.classList.add('bubble');
    div.innerHTML = message;
    chatbox.appendChild(div);
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
                form.assistant_instructions.disabled = false;
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
                    form.assistant_instructions.disabled = true;
                    submit_chat.disabled = true;
                    document.getElementById('chatbox').innerHTML += `<p>Thank you for using our beta product. Sign up for our waitlist <a href="https://www.builditforme.ai">here</a>.</p>`;
                }
            } else if (response.status === 202) {
                form.assistant_instructions.disabled = true;
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
                form.assistant_instructions.disabled = false;
                submit_chat.disabled = false;
                document.getElementById('billy-chatbox').innerHTML += `<p>GPT: Your request has finished running.</p>`;
            }
        }
    });
}*/