<?php
session_start();

session_unset();
session_destroy();

header("Location: /Pampeers_copyRepo/login");
exit();
?>