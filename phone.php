<?php
require_once "./ithome/phone.php";

$num = 5;
if (isset($_GET['n']) && is_numeric($_GET['n'])) {
    $num = $_GET['n'];
    if ($num > 270) { // max
        $num = 270;
    }
}

$ithome = new ItHome();
$ithome->start($num);