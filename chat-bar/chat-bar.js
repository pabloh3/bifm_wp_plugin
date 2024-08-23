


// Initialize markdown-it and highlight.js
const md_bifm = window.markdownit({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return '<pre class="hljs"><code>' +
                    hljs.highlight(str, { language: lang }).value +
                    '</code></pre>';
            } catch (__) {}
        }
        return '<pre class="hljs"><code>' + md_bifm.utils.escapeHtml(str) + '</code></pre>';
    }
});

let billy_processingMessage = document.createElement('div');
billy_processingMessage.innerHTML = '<div id="billy-responding" class="processing-message">Processing<span class="processing-dot">.</span><span class="processing-dot">.</span><span class="processing-dot">.</span></div>';


jQuery(document).ready(function($) {
    // Toggle chat widget visibility
    $('.billy-chat-button a').on('click', function(e) {
        e.preventDefault();
        minimize_unminimize();
        loadCurrentSessionThread();
    });

    // Send message on button click
    $('#billy_chat_send').on('click', function() {
        var message = $('#billy_chat_input').val();
        if (message.trim() !== '') {
            appendUserMessage(message);
            $('#billy_chat_input').val('');
            sendMessage(message);
        }
    });

    // Minimize chat widget
    $('#billy_chat_minimize').on('click', function() {
        minimize_unminimize();
    });

    // Close chat widget and redirect
    $('#billy_chat_close').on('click', function() {
        window.location.href = '/wp-admin/admin.php?page=bifm';
    });

    // Handle enter key submission
    $('#billy_chat_input').on('keypress', function(event) {
        if (event.key === 'Enter' && !event.shiftKey && !event.ctrlKey && !event.commandKey) {
            event.preventDefault();
            $('#billy_chat_send').click();
        }
    });

});

function appendUserMessage(message) {
    const chatMessages = document.getElementById('billy_chat_messages');
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'message bubble billy-user';
    userMessageDiv.innerHTML = md_bifm.render(message);
    chatMessages.appendChild(userMessageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function appendBotMessage(message) {
    const chatMessages = document.getElementById('billy_chat_messages');
    const botMessageDiv = document.createElement('div');
    botMessageDiv.className = 'message bubble billy-bot';
    botMessageDiv.innerHTML = md_bifm.render(message);
    chatMessages.appendChild(botMessageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function loadCurrentSessionThread() {
    fetch(billy_chat_vars.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'bifm_load_billy_chat',
            nonce: billy_chat_vars.nonce
        })
    })
    .then(response => response.json())
    .then(response => {
        console.log("Current session thread loaded successfully:", response);
        response.data.reverse().forEach(function(message) {
            if (message.role === 'user') {
                appendUserMessage(message.text);
            } else {
                appendBotMessage(message.text);
            }
        });
    })
    .catch(error => {
        console.error('Error loading current session thread:', error);
    });
}

function minimize_unminimize() {
    const chatWidget = document.getElementById('billy_chat_widget');
    const targetElement = document.getElementById('wp-admin-bar-billy-chat-button');
    const widgetRect = chatWidget.getBoundingClientRect();
    const targetRect = targetElement.getBoundingClientRect();
    const translateX = targetRect.left - (widgetRect.left + widgetRect.width);
    const translateY = targetRect.top - (widgetRect.top + widgetRect.height);

    chatWidget.style.display = (chatWidget.style.display === 'none' || chatWidget.style.display === '') ? 'block' : 'none';

    if (chatWidget.classList.contains('collapsed')) {
        chatWidget.classList.remove('collapsed');
        setTimeout(() => {
            chatWidget.style.opacity = '1';
            chatWidget.style.width = '38%';
            chatWidget.style.height = '80%';
            chatWidget.style.right = '20px';
            chatWidget.style.bottom = '20px';
            chatWidget.style.transform = 'translate(0px, 0px) scale(1)';
        }, 10);
    } else {
        chatWidget.classList.add('collapsed');
        setTimeout(() => {
            chatWidget.style.opacity = '0';
            chatWidget.style.width = '0';
            chatWidget.style.height = '0';
            chatWidget.style.transform = `translate(${translateX}px, ${translateY}px) scale(0.1)`;
        }, 10);
    }
}


    
function sendMessage(messageBody, widget_name, run_id, tool_call_id) {
    var chatbox = document.getElementById('billy_chat_messages'); 
    chatbox.appendChild(billy_processingMessage);
    chatbox.scrollTop = chatbox.scrollHeight;
    jQuery.ajax({
        url: billy_chat_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'send_chat_message',
            nonce: billy_chat_vars.nonce,
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
}

function pollForResult(jobId, message) {
    const pollInterval = 3000; // Poll every 3 seconds

    const poll = setInterval(() => {
        jQuery.ajax({
            url: billy_chat_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'bifm_billy_check_job_status',
                nonce: billy_chat_vars.nonce,
                jobId: jobId,
                message: message
            },
            success: function(response, textStatus, jqXHR) {
                if (jqXHR.status === 200) {
                    clearInterval(poll);
                    handleResponse(response);
                } else if (jqXHR.status === 202) {
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
    document.getElementById('billy_chat_messages').removeChild(billy_processingMessage);
    if (response.data.message) {
        appendBotMessage(response.data.message);
    }
    if (response.data.widget_object) {
        let chatbox = document.getElementById('billy_chat_messages');
        console.log("contains widget");
        let div = document.createElement('div');
        div.innerHTML = response.data.widget_object.widget;
        chatbox.appendChild(div);
        if (response.data.widget_object.script) {
            // if response contain script, append to the document's existing <script> tag
            let script = document.createElement('script');
            script.innerHTML = response.data.widget_object.script;
            document.body.appendChild(script);
            chatbox.scrollTop = chatbox.scrollHeight;
        }
    }
}

function handleError(error) {
    console.error('Error polling for job status:', error);
    let errorMessage = "An error occurred. Please try again.";
    try {
        errorMessage = error.responseJSON.data.message;
    } catch (e) {
        try {
            errorMessage = error.responseText;
        } catch (e) {}
    }
    appendBotMessage(errorMessage);
    document.getElementById('billy_chat_messages').removeChild(billy_processingMessage);
}
