<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-07-24 14:40:32
*/
?>
<?php

/*
  Created on : 12/Mar/2013, 10:09:33
  Author     : Ricardo Órfão
 */

if (!isset($_POST["username"], $_POST["password"])) {
    $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal, não foi possível fazer o login");
} elseif (empty($_POST["username"])) {
    $arr = array('sucesso' => false, 'mensagem' => "Escreva o nome de utilizador");
} elseif (empty($_POST["password"])) {
    $arr = array('sucesso' => false, 'mensagem' => "Escreva a palavra-passe");
} elseif (strlen($_POST["username"]) > 30) {
    $arr = array('sucesso' => false, 'mensagem' => "O nome de utilizador é demasiado extenso");
} elseif (strlen($_POST["password"]) > 50) {
    $arr = array('sucesso' => false, 'mensagem' => "A palavra-passe é demasiado extensa");
} else {
    if (session_id() == '') {
        session_start();
    }
    include_once('./conf/connect.php');
    $username = $_POST["username"];
    $password = $_POST["password"];
    $query_escola = $connection->prepare("SELECT ent.id, ent.login, ent.pass, ent.licencas, ent.valido, ent.ldap FROM entidade ent WHERE ent.login=:username AND ent.pass=UNHEX(SHA2(CONCAT(:password, HEX(ent.salt)), 256)) LIMIT 1");
    $query_escola->execute(array(':username' => $username, ':password' => $password));
    $count = $query_escola->rowCount();
    if ($count == 1) {
        $row = $query_escola->fetch(PDO::FETCH_ASSOC);
        if ($row['valido'] == '1') {
            if ($row['licencas'] > '0') {
                $_SESSION['tipo'] = 'reg';
                $_SESSION['id_entidade'] = $row['id'];
                $_SESSION['ldap'] = $row['ldap'];
                $arr = array('sucesso' => true, 'pagina' => 'pag_reguser.php');
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Não existem licenças disponíveis");
            }
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Não tem permissões para entrar");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Nome de utilizador e/ou palavra passe erradas");
    }
}
$connection = null;
echo json_encode($arr);