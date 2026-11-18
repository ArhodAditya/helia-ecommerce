<?php
session_start();

unset($_SESSION['user_id'], $_SESSION['user_name']);
session_regenerate_id(true);

header('Location: login.php?logged_out=1');
exit;
