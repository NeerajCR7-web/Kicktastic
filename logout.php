<?php
session_start();
session_unset();
session_destroy();
// Redirect back to your public index in /dashboard/
header('Location: index.php');
exit;
