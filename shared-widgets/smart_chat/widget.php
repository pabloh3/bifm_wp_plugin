<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// this file should ONLY CONTAIN PHP
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
</head>
<body>
<div id='chat-widget'>
<div id='welcome-message'><?php echo esc_html(__("You're chatting with a virtual assistant",'bifm')); ?></div> <!-- Welcome message-->
<div id='chat-messages'></div>
<div id='responding' style='display: none;'><?php echo esc_html(__("Responding",'bifm')); ?><span class='dots'>...</span></div> <!-- Animation for responding-->
<textarea id='chat-input' placeholder='<?php echo esc_html(__("Type your message here..",'bifm')); ?>' rows='1'></textarea>
<button id='chat-submit'><div id='submit-icon'>→</div></button>
</div>
</body>
</html>


?>