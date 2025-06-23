<?php
session_start();
session_unset();
session_destroy();
header('Location: index.php'); // Redirects the user back to homepage
exit;
