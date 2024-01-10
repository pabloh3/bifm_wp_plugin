jQuery(document).ready(function($) {
    var chatInput = document.getElementById('chat-input');
    var chatSubmit = document.getElementById('chat-submit');
    var chatMessages = document.getElementById('chat-messages');

    // allow user to press enter to submit message
    chatInput.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
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
        chatInput.style.height = '36px';
        console.log("returning input to original size");
        if (message) {
            displayMessage(message, true); // Display user message
            document.getElementById('responding').style.display = 'block'; // Show responding animation
        }
        ajaxurl = ajax_object.ajaxurl;
        $.ajax({
            url: ajaxurl, // This variable is automatically defined by WordPress
            type: 'POST',
            data: {
                'action': 'send_chat_message', // This should match the action in add_action
                'message': message
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

