<?php
require_once __DIR__.'/../core.php';
unset($_SESSION['user']);
unset($_SESSION['admin']);
session_regenerate_id(true);
header('Location:/?deconnecte=1');
exit;
