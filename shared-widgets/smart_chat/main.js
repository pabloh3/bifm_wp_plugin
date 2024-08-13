jQuery(document).ready(function($) {
    var chatInput = document.getElementById('chat-input');
    var chatSubmit = document.getElementById('chat-submit');
    var chatMessages = document.getElementById('chat-messages');

    var chatWidget = document.getElementById('chat-widget');
    var chatMinimize = document.getElementById('chat-minimize');
    var chatLogo = document.getElementById('chat-bubble');

    // Minimize the chat widget
    chatMinimize.addEventListener('click', function() {
        chatWidget.style.display = 'none';
        chatLogo.style.display = 'block';
    });

    // Restore the chat widget
    chatLogo.addEventListener('click', function() {
        chatWidget.style.display = 'block';
        chatLogo.style.display = 'none';
    });



    // allow user to press enter to submit message
    chatInput.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            chatSubmit.click();
        }
    });

    // auto resize chat input
    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    //handle user input
    chatSubmit.addEventListener('click', function() {
        console.log('message sent to back end');
        var message = chatInput.value.trim();
        chatInput.value = '';
        chatInput.style.height = 'auto'; // Reset height to auto first
    
        // Give the browser time to reset the scroll height
        setTimeout(function() {
            chatInput.style.height = '36px'; // Set to original height
        }, 0);
        // ...
        console.log("returning input to original size");
        if (message) {
            displayMessage(message, true); // Display user message
            document.getElementById('responding').style.display = 'block'; // Show responding animation
        }
        ajaxurl = ajax_object.ajaxurl;
        console.log("ajax_object: ", ajax_object);
        $.ajax({
            url: ajaxurl, // This variable is automatically defined by WordPress
            type: 'POST',
            data: {
                'action': 'send_chat_message', // This should match the action in add_action
                'message': message,
                'nonce': ajax_object.nonce,
            },
            success: function(response) {
                // Handling the response here
                console.log("success response");
                response = response.data;
                document.getElementById('responding').style.display = 'none'; // Hide responding animation
                displayMessage(response.message); // Display response
            },
            error: function(error) {
                // Handle errors here
                console.log("error response");
                error_message = error.responseJSON.data.message;
                console.log(error_message);
                document.getElementById('responding').style.display = 'none'; // Hide responding animation
                displayError(error_message);
            }
        });
    });
    
    //display message in chat window
    function displayMessage(message, isUser = false) {
        var messageElement = document.createElement('div');
        var currentTime = new Date().toLocaleTimeString();
        messageElement.innerHTML = '<div class="bubble ' + (isUser ? 'user' : 'bot') + '">' +
                                   '<span class="message-text">' + message + '</span>' +
                                   '<div class="message-info">' + (isUser ? 'You' : 'Virtual Assistant') + 
                                   ' at ' + currentTime + '</div></div>';
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function displayError(message) {
        var messageElement = document.createElement('div');
        messageElement.textContent = message;
        messageElement.style.color = 'red';
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

});

