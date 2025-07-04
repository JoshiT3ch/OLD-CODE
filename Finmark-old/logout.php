<?php
require_once 'config.php';
session_unset();
session_destroy();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/', '', false, true);
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Mon, 01 Jan 1990 00:00:00 GMT');
header('Clear-Site-Data: "cache", "cookies", "storage"');
header("Location: index.php");
exit;
?>