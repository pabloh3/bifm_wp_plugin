jQuery(document).ready(function($) {
    $('.billy-chat-button a').on('click', function(e) {
        e.preventDefault();
        console.log("billy chat button clicked");
        $('#billy_chat_widget').toggle();
    });

    $('#billy_chat_send').on('click', function() {
        var message = $('#billy_chat_input').val();
        if (message.trim() !== '') {
            $('#billy_chat_messages').append('<div class="message">' + message + '</div>');
            $('#billy_chat_input').val('');
        }
    });

    /* Minimize when clicked minimize button */
    $('#billy_chat_minimize').on('click', function() {
        $('#billy_chat_widget').toggle();
    });
    /* redirect to https://elquesabemx.local/wp-admin/admin.php?page=bifm-plugin when click in chat close button */
    $('#billy_chat_close').on('click', function() {
        $('#billy_chat_widget').toggle();
        window.location.href = '/wp-admin/admin.php?page=bifm-plugin';
    });

    

});
