<?php
session_start();
$_SESSION = array();// empties stored vars like session user_id,role etc
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); //get me cookie details like path etc to detail properly
    setcookie(session_name(), '', time() - 42000, // delete cookie by setting past time
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy(); //logout success
header("Location: citizen_login.php");
exit();
?>