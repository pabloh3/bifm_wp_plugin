<?php
// this file should ONLY CONTAIN PHP

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
//echo "<link rel='stylesheet' type='text/css' href='styles.css'>";  // ALWAYS INCLUDE THIS LINE TO INLCUDE THE CSS file
//echo "<script src='main.js' defer></script>";  // ALWAYS INCLUDE THIS LINE TO INLCUDE THE JS file
echo "</head>";
echo "<body>";
echo "<div id='chat-widget'>";
echo "<div id='welcome-message'>You're chatting with a virtual assistant.</div>"; // Welcome message
echo "<div id='chat-messages'></div>";
echo "<div id='responding' style='display: none;'>Responding<span class='dots'>...</span></div>"; // Animation for responding
echo "<textarea id='chat-input' placeholder='Type your message here...' rows='1'></textarea>";
echo "<button id='chat-submit'><div id='submit-icon'>→</div></button>";
echo "</div>";
echo "</body>";
echo "</html>";


?>