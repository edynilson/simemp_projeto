<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-09 14:24:01
*/

include_once ('functions/functions.php');

if (!isset($_POST["username"], $_POST["password"])) {
    $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal, não foi possível fazer o login");
} elseif (empty($_POST["username"])) {
    $arr = array('sucesso' => false, 'mensagem' => "Escreva o nome de utilizador");
} elseif (empty($_POST["password"])) {
    $arr = array('sucesso' => false, 'mensagem' => "Escreva a palavra-passe");
} elseif (strlen($_POST["username"]) > 30) {
    $arr = array('sucesso' => false, 'mensagem' => "O nome de utilizador é demasiado extenso");
} else {
    if (session_id() == '') {
        session_start();
    }
    include('./conf/connect.php');

    function registarSessao() {
        if (session_id() == '') {
            session_start();
        }
        include('./conf/connect.php');
        $verificacao = $connection->prepare("SELECT id_sessao, session_id FROM sessao s WHERE s.ip=:ip AND session_id=:session_id AND user=:id_utilizador");
        $verificacao->execute(array(':ip' => encode_ip($_SERVER['REMOTE_ADDR']), ':session_id' => session_id(), ':id_utilizador' => $_SESSION['id_utilizador']));
        if($verificacao->rowCount() > 0) {
            $linha = $verificacao->fetch(PDO::FETCH_ASSOC);
            $sessao = $linha['session_id'];
            $query_sessao=$connection->prepare("UPDATE sessao SET data_logout=NOW() WHERE session_id=:session_id AND ip=:ip AND user=:id_utilizador");
            $query_sessao->execute(array(':session_id' => session_id(), ':ip' => encode_ip($_SERVER['REMOTE_ADDR']), ':id_utilizador' => $_SESSION['id_utilizador']));
            $_SESSION['sessao'] = $linha['id_sessao'];
        } else {
            $query_sessao=$connection->prepare("INSERT INTO sessao (user, session_id, browser, ip, uri, data_login, data_logout) VALUES (:user, :session_id, :browser, :ip, :uri, NOW(), DATE_ADD(NOW(),INTERVAL 1 MINUTE))");
            $query_sessao->execute(array(':user' => $_SESSION['id_utilizador'], ':session_id' => session_id(), ':browser' => $_SERVER['HTTP_USER_AGENT'], ':ip' => encode_ip($_SERVER['REMOTE_ADDR']), ':uri' => $_SERVER['REQUEST_URI']));
            $_SESSION['sessao'] = $connection->lastInsertId();
        }
    }

    $username = $_POST["username"];
    $password = $_POST["password"];
    $query_ldap_ex = $connection->prepare("SELECT ent.ldap, u.u_ldap, tg.designacao FROM utilizador u LEFT JOIN entidade ent ON u.id_entidade=ent.id LEFT JOIN empresa emp ON u.id_empresa=emp.id_empresa LEFT JOIN grupo g ON emp.id_grupo=g.id LEFT JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE u.login=:login");
    $query_ldap_ex->execute(array(':login' => $username));
    $num_registos = $query_ldap_ex->rowCount();
    if ($num_registos > 0) {
        $linha_ldap_ex = $query_ldap_ex->fetch(PDO::FETCH_ASSOC);
        if ($linha_ldap_ex['u_ldap'] == "1") {
            $valido = true;
            $query_entidade = $connection->prepare("SELECT ent.id FROM entidade ent INNER JOIN utilizador u ON u.id_entidade=ent.id WHERE u.login=:login");
            $query_entidade->execute(array(':login' => $username));
            $linha_entidade = $query_entidade->fetch(PDO::FETCH_ASSOC);
            $id_entidade_rec = $linha_entidade['id'];
            $query_ldap = $connection->prepare("SELECT ent.ldap_host, ent.ldap_port, ent.ldap_rdn_s, ent.ldap_rdn_p, ent.ldap_search_rdn_s, ent.ldap_search_rdn_p, ent.ldap_filter_person, ent.ldap_name, ent.ldap_complete_name, ent.ldap_mail FROM entidade ent WHERE ent.id=:id_entidade LIMIT 1");
            $query_ldap->execute(array(':id_entidade' => $id_entidade_rec));
            $linha_ldap = $query_ldap->fetch(PDO::FETCH_ASSOC);
            $ldaphost = $linha_ldap['ldap_host'];
            $ldapport = $linha_ldap['ldap_port'];
            $ldapconn = ldap_connect($ldaphost, $ldapport);
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            $ldaprdns = $linha_ldap['ldap_rdn_s'];
            $ldaprdnp = $linha_ldap['ldap_rdn_p'];
            $ldaprdnsearchs = $linha_ldap['ldap_search_rdn_s'];
            $ldaprdnsearchp = $linha_ldap['ldap_search_rdn_p'];
            $ldap_givenname = $linha_ldap['ldap_name'];
            $ldap_cn = $linha_ldap['ldap_complete_name'];
            $ldap_mail = $linha_ldap['ldap_mail'];
            $filter_person = $linha_ldap['ldap_filter_person'];
            if ($ldapconn) {
                $ldapbind = @ldap_bind($ldapconn, str_replace('$username', $username, $ldaprdns), $password);
                if (!$ldapbind) {
                    $ldapbind = @ldap_bind($ldapconn, str_replace('$username', $username, $ldaprdnp), $password);
                    if (!$ldapbind) {
                        $valido = false;
                        // $arr = array('sucesso' => false, 'mensagem' => "Nome de utilizador e/ou palavra-passe erradas");
						$arr = array('sucesso' => false, 'mensagem' => "Estamos tendo alguns problemas na ligação. Por favor, tente mais tarde");
                    } else {
                        $sr_person = ldap_search($ldapconn, $ldaprdnsearchp, str_replace('$username', $username, $filter_person));
                        $sr = ldap_get_entries($ldapconn, $sr_person);
                        $primeiro_nome = $sr[0][$ldap_givenname][0];
                        $nome_completo = $sr[0][$ldap_cn][0];
                        $email = $sr[0][$ldap_mail][0];
                    }
                } else {
                    $sr_person = ldap_search($ldapconn, $ldaprdnsearchs, str_replace('$username', $username, $filter_person));
                    $sr = ldap_get_entries($ldapconn, $sr_person);
                    $primeiro_nome = $sr[0][$ldap_givenname][0];
                    $nome_completo = $sr[0][$ldap_cn][0];
                    $email = $sr[0][$ldap_mail][0];
                }
            } else {
                $valido = false;
                $arr = array('sucesso' => false, 'mensagem' => "Não conseguimos ligar-nos ao servidor LDAP.");
            }
            if ($valido == true) {
                $query_login = $connection->prepare("SELECT u.id, u.tipo, u.admin, u.id_entidade FROM utilizador u WHERE u.login=:username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) LIMIT 1");
                $query_login->execute(array(':username' => $username, ':password' => $password));
                $count = $query_login->rowCount();
                if ($count == 1) {
                    $row = $query_login->fetch(PDO::FETCH_ASSOC);
                    if ($row['tipo'] == "admin") {
                        $_SESSION['tipo'] = $row['tipo'];
                        $_SESSION['admin'] = $row['admin'];
                        $_SESSION['id_utilizador'] = $row['id'];
                        $_SESSION['ldap'] = "1";
                        registarSessao();
                        $arr = array('sucesso' => true, 'pagina' => "pag_admin.php");
                    } else {
                        $query_dados_user = $connection->prepare("SELECT c.id AS `conta`, u.id, emp.id_empresa, u.tipo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN conta c ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u.login=:username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) LIMIT 1");
                        $query_dados_user->execute(array(':username' => $username, ':password' => $password));
                        $linha = $query_dados_user->fetch(PDO::FETCH_ASSOC);
                        $_SESSION['tipo'] = $linha['tipo'];
                        $_SESSION['id_utilizador'] = $linha['id'];
                        $_SESSION['id_empresa'] = $linha['id_empresa'];
                        $_SESSION['id_conta'] = $linha['conta'];
                        $_SESSION['tipo_grupo'] = $linha_ldap_ex['designacao'];
                        $_SESSION['ldap'] = "1";
                        registarSessao();
                        if ($linha_ldap_ex['designacao'] == "Bolsa") {
                            $arr = array('sucesso' => true, 'pagina' => "pag_banco.php");
                        } else {
                            $arr = array('sucesso' => true, 'pagina' => "pag_user.php");
                        }
                    }
                } else {
                    $query_user = $connection->prepare("SELECT u.id, u.id_entidade FROM utilizador u WHERE u.login=:utilizador LIMIT 1");
                    $query_user->execute(array(':utilizador' => $username));
                    $row_count = $query_user->rowCount();
                    if ($row_count == 1) {
                        include_once './functions/functions.php';
                        $connection->beginTransaction();
                        $query_geraSalt = $connection->prepare("SET @salt = UNHEX(SHA2(UUID(), 256))");
                        $query_geraSalt->execute();
                        $query_reg_update = $connection->prepare("UPDATE utilizador u SET u.p_nome=:p_nome, u.nome=:nome, u.email=:email, u.pass=UNHEX(SHA2(CONCAT(:password, HEX(@salt)), 256)), u.salt=@salt WHERE u.login=:username");
                        $query_reg_update->execute(array(':p_nome' => $primeiro_nome, ':nome' => $nome_completo, ':email' => $email, ':password' => $password, ':username' => $username));
                        $num_registos = $query_reg_update->rowCount();
                        $row = $query_user->fetch(PDO::FETCH_ASSOC);
                        $user_id = $row['id'];
                        $encrypted_data = mc_encrypt($password, ENCRYPTION_KEY);
                        $query_conexoes = $connection->prepare("SELECT cg.id_conn FROM conexao_guacamole cg INNER JOIN entidade ent ON cg.id_entidade=ent.id AND ent.id=:id_entidade");
                        $query_conexoes->execute(array(':id_entidade' => $row['id_entidade']));
                        $num = $query_conexoes->rowCount();
                        if ($num > 0) {
                            while ($values = $query_conexoes->fetch(PDO::FETCH_ASSOC)) {
                                $id_conn[] = $values['id_conn'];
                            }
                            for ($i = 0; $i < $num; $i++) {
                                $stmt = $connection->prepare("UPDATE guac_user gu SET gu.password=:password WHERE gu.id_conexao=:id_conexao AND gu.id_user=:id_user");
                                $stmt->execute(array(':password' => $encrypted_data, ':id_conexao' => $id_conn[$i], ':id_user' => $user_id));
                            }
                        }
                        $connection->commit();
                        $query_utilizador = $connection->prepare("SELECT u.id, u.tipo, u.admin FROM utilizador u WHERE u.id=:user_id");
                        $query_utilizador->execute(array(':user_id' => $user_id));
                        $linha_user = $query_utilizador->fetch(PDO::FETCH_ASSOC);
                        if ($linha_user['tipo'] == "admin") {
                            $_SESSION['tipo'] = $linha_user['tipo'];
                            $_SESSION['admin'] = $linha_user['admin'];
                            $_SESSION['id_utilizador'] = $linha_user['id'];
                            $_SESSION['ldap'] = "1";
                            registarSessao();
                            $arr = array('sucesso' => true, 'pagina' => "pag_admin.php");
                        } else {
                            $query_dados_user = $connection->prepare("SELECT c.id AS `conta`, u.id, emp.id_empresa, u.tipo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN conta c ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u.login=:username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) LIMIT 1");
                            $query_dados_user->execute(array(':username' => $username, ':password' => $password));
                            $linha = $query_dados_user->fetch(PDO::FETCH_ASSOC);
                            $_SESSION['tipo'] = $linha['tipo'];
                            $_SESSION['id_utilizador'] = $linha['id'];
                            $_SESSION['id_empresa'] = $linha['id_empresa'];
                            $_SESSION['id_conta'] = $linha['conta'];
                            $_SESSION['tipo_grupo'] = $linha_ldap_ex['designacao'];
                            $_SESSION['ldap'] = "1";
                            registarSessao();
                            if ($linha_ldap_ex['designacao'] == "Bolsa") {
                                $arr = array('sucesso' => true, 'pagina' => "pag_banco.php");
                            } else {
                                $arr = array('sucesso' => true, 'pagina' => "pag_user.php");
                            }
                        }
                    } else {
                        $arr = array('sucesso' => false, 'mensagem' => "Não existe no SimEmp, por favor efetue o registo");
                    }
                }
            }
        } elseif ($linha_ldap_ex['u_ldap'] == "0") {
            // $query_login = $connection->prepare("SELECT u.id, u.tipo, u.admin, u.id_entidade FROM utilizador u WHERE u.login=:username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) LIMIT 1");
            
			/* ALTERAÇÃO TEMPORÁRIA, PARA BLOQUEAR ACESSO A UTILIZADORES DO IPCA */
            $query_login = $connection->prepare("SELECT u.id, u.tipo, u.admin, u.id_entidade, e.nome FROM utilizador u INNER JOIN entidade e ON u.id_entidade=e.id WHERE u.login=:username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) LIMIT 1");
			
			$query_login->execute(array(':username' => $username, ':password' => $password));
            $count = $query_login->rowCount();
            if ($count == 1) {
                $row = $query_login->fetch(PDO::FETCH_ASSOC);
                if ($row['tipo'] == "admin") {
                    $_SESSION['tipo'] = $row['tipo'];
                    $_SESSION['admin'] = $row['admin'];
                    $_SESSION['id_utilizador'] = $row['id'];
                    $_SESSION['ldap'] = "0";
                    registarSessao();
                    $arr = array('sucesso' => true, 'pagina' => "pag_admin.php");
                } 
				
				/* ADDED */
                else if ($row['nome'] == 'IPCA' || $row['id'] == 24 || $row['id'] == 287 || $row['id'] == 291) {
                    $arr = array('sucesso' => false, 'mensagem' => "Sua conta está limitada. Por favor, contacte o admin");
                }
                /* */
				
				else {
                    $query_dados_user = $connection->prepare("SELECT c.id AS `conta`, u.id, emp.id_empresa, u.tipo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN conta c ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u.login=:username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) LIMIT 1");
                    $query_dados_user->execute(array(':username' => $username, ':password' => $password));
                    $linha = $query_dados_user->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['tipo'] = $linha['tipo'];
                    $_SESSION['id_utilizador'] = $linha['id'];
                    $_SESSION['id_empresa'] = $linha['id_empresa'];
                    $_SESSION['id_conta'] = $linha['conta'];
                    $_SESSION['tipo_grupo'] = $linha_ldap_ex['designacao'];
                    $_SESSION['ldap'] = "0";
                    registarSessao();
                    if ($linha_ldap_ex['designacao'] == "Bolsa") {
                        $arr = array('sucesso' => true, 'pagina' => "pag_banco.php");
                    } else {
                        $arr = array('sucesso' => true, 'pagina' => "pag_user.php");
                    }
                }
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Nome de utilizador e/ou palavra-passe erradas");
            }
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Não existe no SimEmp, por favor efetue o registo");
    }
}

$connection = null;
echo json_encode($arr);