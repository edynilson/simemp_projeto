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
  Created on : 27/Nov/2013, 12:00:21
  Author     : Ricardo Órfão
 */

if (session_id() == '') {
    session_start();
}

// if (empty($_SESSION['id_utilizador']) || empty($_SESSION['tipo'])) {
if (empty($_SESSION['id_utilizador'])) {
    if (!array_key_exists('tipo', $_SESSION) || empty($_SESSION['tipo']) || (!empty($_SESSION['tipo']) && $_SESSION['tipo'] != "user" && $_SESSION['tipo'] != "admin")) {
        $_SESSION = array();
        /**
         * destruir a sessão
         */
        session_destroy();

        /**
         * redirecciona para a página principal
         */
        header('Location: ../index.php');
        exit;
    }
	/* elseif ($_SESSION['tipo'] != "admin") {
        $_SESSION = array();
        /**
         * destruir a sessão
         * /
        session_destroy();

        /**
         * redirecciona para a página principal
         * /
        header('Location: ../index.php');
        exit;
    }
	*/
	
} else {
    include('connect.php');
}