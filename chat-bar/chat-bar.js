


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

    function appendUserMessage(message) {
        $('#billy_chat_messages').append('<div class="message bubble billy-user">' + md_bifm.render(message) + '</div>');
        $('#billy_chat_messages').scrollTop($('#billy_chat_messages')[0].scrollHeight);
    }

    function appendBotMessage(message) {
        $('#billy_chat_messages').append('<div class="message bubble billy-bot">' + md_bifm.render(message) + '</div>');
        $('#billy_chat_messages').scrollTop($('#billy_chat_messages')[0].scrollHeight);
    }

    function loadCurrentSessionThread() {
        // call bifm_load_billy_chat 
        $.ajax({
            url: billy_chat_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'bifm_load_billy_chat',
                nonce: billy_chat_vars.nonce
            },
            success: function(response) {
                console.log("Current session thread loaded successfully:", response);
                response.data.reverse().forEach(function(message) {
                    if (message.role === 'user') {
                        appendUserMessage(message.text);
                    } else {
                        appendBotMessage(message.text);
                    }
                });
            },
            error: function(error) {
                console.error('Error loading current session thread:', error);
            }
        });
    }
    
    function sendMessage(message) {
        console.log("Sending message:", message);
        document.getElementById('billy_chat_messages').appendChild(billy_processingMessage);
        $('#billy_chat_messages').scrollTop($('#billy_chat_messages')[0].scrollHeight);
        $.ajax({
            url: billy_chat_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'send_chat_message',
                nonce: billy_chat_vars.nonce,
                message: message
            },
            success: function(response, textStatus, jqXHR) {
                console.log("response.status: ", response.status);
                if (jqXHR.status === 202) {
                    console.log("Job submitted successfully. Polling for result...");
                    const jobId = response.data.jobId;
                    pollForResult(jobId, message);
                } else {
                    console.log("not 202 response received");
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
            $.ajax({
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

    function minimize_unminimize(){
        const chatWidget = $('#billy_chat_widget');
        chatWidget.toggle();
        const targetElement = $('#wp-admin-bar-billy-chat-button');
        const widgetOffset = chatWidget.offset();
        const targetOffset = targetElement.offset();
        const translateX = targetOffset.left - (widgetOffset.left + chatWidget.outerWidth());
        const translateY = targetOffset.top - (widgetOffset.top + chatWidget.outerHeight());
        if (chatWidget.hasClass('collapsed')) {
            chatWidget.removeClass('collapsed');
            setTimeout(() => {
                chatWidget.css('opacity', '1');
                chatWidget.css('width', '38%');
                chatWidget.css('height', '80%');
                chatWidget.css({
                    right: '20px',
                    bottom: '20px'});
                chatWidget.css({transform: `translate(0px, 0px) scale(1)`});
                
            }, 10); // Delay to trigger transition effect
        } else {
            chatWidget.addClass('collapsed');
            setTimeout(() => {
                chatWidget.css('opacity', '0');
                chatWidget.css('width', '0');
                chatWidget.css('height', '0');
                chatWidget.css({transform: `translate(${translateX}px, ${translateY}px) scale(0.1)`});
            }, 10); // Wait for transition to complete before fully collapsing
        }
    }

});


