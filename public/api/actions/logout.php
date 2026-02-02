<?php
session_start();
session_unset();
require_once "../../../includes/security.php";
verify_csrf_api();
session_destroy();
header("Location: ../../login.php");
exit;
?>
