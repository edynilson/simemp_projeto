<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-09 12:12:06
*/

if (session_id() == '') {
    session_start();
}

header('Cache-control: private');

if (isset($_GET['lingua'])) {
    $lingua = $_GET['lingua'];
    $_SESSION['lingua'] = $lingua;
    setcookie("lingua", "", time() - 60000);
    setcookie("lingua", $lingua, time() + (3600 * 24 * 5));
} else if (isset($_SESSION['lingua'])) {
    $lingua = $_SESSION['lingua'];
} else if (isset($_COOKIE['lingua'])) {
    $lingua = $_COOKIE['lingua'];
} else {
    $lingua = 'pt';
}

switch ($lingua) {
    case 'pt':
        $lang_file = 'lingua.pt.php';
        break;

    case 'en':
        $lang_file = 'lingua.en.php';
        break;

    case 'es':
        $lang_file = 'lingua.es.php';
        break;

    default:
        $lang_file = 'lingua.pt.php';
}

include_once 'languages/' . $lang_file;