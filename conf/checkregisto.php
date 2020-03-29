<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-01 20:53:08
*/

if (session_id() == '') {
    session_start();
}

if (empty($_SESSION['id_entidade']) || empty($_SESSION['tipo'])) {
    header("Location:index.php");
} else {
    include('connect.php');
}