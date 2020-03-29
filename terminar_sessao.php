<?php
/*
 *
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-28 12:44:34
*/

include('./conf/connect.php');
if (session_id() == '') {
    session_start();
}

$_SESSION = array();
session_destroy();
/* UNSET COOKIE (UNTIL PAGE TRANSLATION ISN'T COMPLETE) */
if (isset($_COOKIE['lingua'])) {
    unset($_COOKIE['lingua']);
    setcookie('lingua', null, -1, '/');
}
/* */
header('Location: index.php');
exit;