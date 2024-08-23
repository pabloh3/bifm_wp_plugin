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


// Custom link renderer to add target="_blank"
const defaultRender = md.renderer.rules.link_open || function(tokens, idx, options, env, self) {
    return self.renderToken(tokens, idx, options);
};
md.renderer.rules.link_open = function(tokens, idx, options, env, self) {
    // Add target="_blank" to all links
    const aIndex = tokens[idx].attrIndex('target');

    if (aIndex < 0) {
        tokens[idx].attrPush(['target', '_blank']); // add new attribute
    } else {
        tokens[idx].attrs[aIndex][1] = '_blank';    // replace value of existing attr
    }

    // pass token to default renderer.
    return defaultRender(tokens, idx, options, env, self);
};

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
        success: function(response, textStatus, jqXHR) {
            if (jqXHR.status === 202) {
                const jobId = response.data.jobId;
                pollForResult(jobId, messageBody);
            } else {
                handleResponse(response);
            }
        },
        error: function(error) {
            handleError(error);
        }
    });
    form.assistant_instructions.value = '';
    chatbox.scrollTop = chatbox.scrollHeight;
}

function pollForResult(jobId, messageBody) {
    const pollInterval = 3000; // Poll every 3 seconds

    const poll = setInterval(() => {
        jQuery.ajax({
            url: billy_localize.ajax_url,
            type: 'POST',
            data: {
                action: 'bifm_billy_check_job_status',
                nonce: billy_localize.nonce,
                jobId: jobId,
                message: messageBody
            },
            success: function(response, textStatus, jqXHR) {
                if (jqXHR.status === 200) {
                    clearInterval(poll);
                    handleResponse(response);
                } else if (jqXHR.status === 202) {
                    // Keep polling
                    console.log('Job is still processing. Polling will continue.');
                } else {
                    clearInterval(poll);
                    handleError({ responseJSON: { data: { message: 'Unexpected status. Please try again.' } } });
                }
            },
            error: function(error) {
                clearInterval(poll);
                handleError(error);
            }
        });
    }, pollInterval);
}

function handleResponse(response) {
    document.getElementById('billy-responding').remove();
    const htmlContent = md.render(response.data.message);
    if (htmlContent) {
        let div = document.createElement('div');
        div.classList.add('billy-bubble', 'bubble');
        div.innerHTML = htmlContent;
        chatbox.appendChild(div);
        chatbox.scrollTop = chatbox.scrollHeight;
    }
    if (response.data.widget_object) {
        let div = document.createElement('div');
        div.innerHTML = response.data.widget_object.widget;
        chatbox.appendChild(div);
        if (response.data.widget_object.script) {
            let script = document.createElement('script');
            script.innerHTML = response.data.widget_object.script;
            document.body.appendChild(script);
            chatbox.scrollTop = chatbox.scrollHeight;
        }
    }
    hljs.highlightAll();
}

function handleError(error) {
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
    displayWarning(errorMessage);
    document.getElementById('billy-responding').remove();
    form.assistant_instructions.disabled = false;
    submit_chat.disabled = false;
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

        // handle clicks on bifm_new_chat_button with id new-chat-button
        document.getElementById('bifm_new_chat_button').addEventListener('click', function(event) {
            // prevent sending a new request for the page
            event.preventDefault();
            //reset the suggestions
            let suggestion = document.getElementById('suggestion-buttons');
            if (suggestion) {
                suggestion.style.display = 'block';
            }
            cleanupChat();
            // de-select the previous thread
            let threadButtons = document.querySelectorAll('.thread-list .thread-btn');
            for (let i = 0; i < threadButtons.length; i++) {
                threadButtons[i].style.backgroundColor = "#CDD2EA";
            }
            // call the API to create a new chat
            jQuery.ajax({
                url: billy_localize.ajax_url,
                type: 'POST',
                data: {
                    action: 'bifm_new_chat',
                    nonce: billy_localize.nonce,
                },
                success: function(response) {
                    console.log("created new chat");
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
            button.style.backgroundColor = "#FAFAFC";

        });
    });
});

function loadThread(threadId) {
    jQuery.ajax({
        url: billy_localize.ajax_url, // Ensure ajaxurl is defined or use admin_url('admin-ajax.php')
        type: 'POST',
        data: {
            action: 'bifm_load_billy_chat', // WordPress AJAX action hook
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
            chatbox.innerHTML = "";
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


document.addEventListener('DOMContentLoaded', (event) => {
    const modal = document.getElementById("myModal");
    const modal2 = document.getElementById("myModal2");
    const agreeButton = document.getElementById("iAgreeButton");
    const laterButton = document.getElementById("laterButton");
    const closeButton = document.querySelectorAll(".bifm-close-button");


    // Show the modal 1  when the page loads if the modal is found
    if (modal) {
        modal.style.display = "block";
        setupModal(modal, modal2, closeButton, agreeButton, laterButton, closeButton);
    }
});

function setupModal(modal, modal2, closeButton, agreeButton, laterButton, closeButton) {
    // Function to close the modal
    function closeModal() {
        modal.style.display = "none";
        // console log the display style of modal 2
        console.log("modal 2 display style: ", modal2.style.display);
        if (modal2.style.display === "block") {
            console.log("closing modal 2");
            modal2.style.display = "none";
        } else {
            modal2.style.display = "block";
        }
    }

    // When the user clicks on any of the <span> (x), close the modal
    for (let i = 0; i < closeButton.length; i++) {
        closeButton[i].onclick = function() {
            closeModal();
        }
    }

    // When the user clicks on the continue button, close the modal and do something else
    agreeButton.onclick = function(event) {
        event.preventDefault();
        saveAgreement();
        closeModal();
        // Add any additional actions for the continue button here
    }

    laterButton.onclick = function(event) {
        event.preventDefault();
        closeModal();
        // Add any additional actions for the later button here
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        console.log("clicked outside");
        if (event.target == modal || event.target == modal2) {
            closeModal();
        }
    }
};

function saveAgreement() {
    // call the API to save the agreement
    jQuery.ajax({
        url: billy_localize.ajax_url,
        type: 'POST',
        data: {
            action: 'bifm_save_agreement',
            nonce: billy_localize.nonce,
        },
        success: function(response) {
            console.log("Agreement saved successfully");
        },
        error: function(error) {
            // Handle errors here
            console.log("There was an error when trying to save the agreement");
        }
    });
}
