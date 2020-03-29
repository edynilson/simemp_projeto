<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-07-24 14:52:45
*/
?>
<?php

/*
  Created on : 22/Out/2013, 11:08:51
  Author     : Ricardo Órfão
 */

if (session_id() == '') {
    session_start();
}

if (empty($_SESSION['id_utilizador']) || empty($_SESSION['tipo']) || $_SESSION['tipo'] != "admin") {
    include('./terminar_sessao.php');
} else {
    include('./conf/connect.php');
}