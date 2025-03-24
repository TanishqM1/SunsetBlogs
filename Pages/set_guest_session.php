<?php
session_start();

// Set user_id to "guest"
$_SESSION['user_id'] = 'guest';
$_SESSION['username'] = 'Guest';

// Redirect to the guest home page
header("Location: home_guest.html");
exit();
?>
