<?php
session_start();
require_once "../../../includes/security.php";
verify_csrf_api();
session_unset();
session_destroy();
header("Location: ../../login.php");
exit;
?>
