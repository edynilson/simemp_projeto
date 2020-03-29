<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-09 12:12:44
*/

if (session_id() == '') {
    session_start();
}

if (empty($_SESSION['id_utilizador']) || empty($_SESSION['tipo']) || $_SESSION['tipo'] != "user") {
    include('./terminar_sessao.php');
} else {
    include('connect.php');
}