<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-07-01 11:58:58
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-10 18:07:15
*/
include('../conf/check_pastas.php');
include_once ('functions.php');

use phpFastCache\CacheManager;
include_once('../phpfastcache/src/autoload.php');
CacheManager::setDefaultConfig([
  'path' => '/tmp',
  'securityKey' => 'SimEmp'
]);

if (session_id() == '') {
    session_start();
}

date_default_timezone_set('Europe/London');

if ($_POST['tipo'] == "eventos") {
    if ($_SESSION['tipo'] == "user") {
        $query_meses = $connection->prepare("SELECT c.mes, c.ano, c.data_inicio, c.data_fim, c.cor, c.editavel FROM calendario c INNER JOIN grupo g ON c.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $query_meses->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $arr = array();
        $meses = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
        while ($linha_meses = $query_meses->fetch(PDO::FETCH_ASSOC)) {
            $mes = $linha_meses['mes'];
            $ano = $linha_meses['ano'];
            $data_inicio = $linha_meses['data_inicio'];
            $data_fim = $linha_meses['data_fim'];
            $color = $linha_meses['cor'];
            $editavel = $linha_meses['editavel'];
            if ($editavel == '0') {
                $editavel = false;
            }
            $evento[] = array(
                'title' => $meses[$mes] . '/' . $ano,
                'start' => $data_inicio,
                'end' => $data_fim,
                'color' => $color,
                'editable' => $editavel,
                'allDay' => true,
//                'url' =>'http://google.com/'
            );
            $arr = $evento;
        }
    } elseif ($_SESSION['tipo'] == "admin") {
        if ($_POST['id_grupo'] == 0) {
            // $query_meses = $connection->prepare("SELECT cal.mes, cal.ano, cal.data_inicio, cal.data_fim, cal.cor, cal.editavel FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador");
            // $query_meses->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
//            $query_meses = $connection->prepare("SELECT c.mes, c.ano, c.data_inicio, c.data_fim, c.cor, c.editavel FROM (SELECT g.nome, last_estado_grupos.* FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id WHERE last_estado_grupos.estado='1') AS grupos_active INNER JOIN user_grupo ug ON grupos_active.id_grupo=ug.id_grupo INNER JOIN calendario c ON grupos_active.id_grupo=c.id_grupo WHERE ug.id_user=:id_utilizador ORDER BY grupos_active.nome ASC, c.ano ASC, c.mes ASC;"); // estava esta
            $query_meses = $connection->prepare("SELECT c.mes , c.ano, c.data_inicio, c.data_fim, c.cor, c.editavel FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON last_estado_grupos.id_grupo=ug.id_grupo INNER JOIN calendario c ON last_estado_grupos.id_grupo=c.id_grupo WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC, c.ano ASC, c.mes ASC"); // meti esta
            $query_meses->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));

            $arr = array();
            $meses = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
            while ($linha_meses = $query_meses->fetch(PDO::FETCH_ASSOC)) {
                $mes = $linha_meses['mes'];
                $ano = $linha_meses['ano'];
                $data_inicio = $linha_meses['data_inicio'];
                $data_fim = $linha_meses['data_fim'];
                $color = $linha_meses['cor'];
                $editavel = $linha_meses['editavel'];
                if ($editavel == '0') {
                    $editavel = false;
                }
                $evento[] = array(
                    'title' => $meses[$mes] . '/' . $ano,
                    'start' => $data_inicio,
                    'end' => $data_fim,
                    'color' => $color,
                    'editable' => $editavel,
                    'allDay' => true
                );
                $arr = $evento;
            }
        } else {
            $query_meses = $connection->prepare("SELECT cal.mes, cal.ano, cal.data_inicio, cal.data_fim, cal.cor, cal.editavel FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE g.id=:id_grupo AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_meses->execute(array(':tipo' => "admin", ':id_grupo' => $_POST['id_grupo'], ':id_utilizador' => $_SESSION['id_utilizador']));

            $arr = array();
            $meses = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");

            while ($linha_meses = $query_meses->fetch(PDO::FETCH_ASSOC)) {
				$mes = $linha_meses['mes'];
                $ano = $linha_meses['ano'];
                $data_inicio = $linha_meses['data_inicio'];
                $data_fim = $linha_meses['data_fim'];
                $color = $linha_meses['cor'];
                $editavel = $linha_meses['editavel'];

                if ($editavel == '0') {
                    $editavel = false;
                }

                $evento[] = array(
                    'title' => $meses[$mes] . '/' . $ano,
                    'start' => $data_inicio,
                    'end' => $data_fim,
                    'color' => $color,
                    'editable' => $editavel,
                    'allDay' => true
                );
                $arr = $evento;
            }
        }
    }
} elseif ($_POST['tipo'] == "data_virtual") {
    /* */
	$query = $connection->prepare("SELECT c.mes, c.ano, c.data_inicio, c.hora_inicio, c.data_fim, c.hora_fim FROM calendario c INNER JOIN grupo g ON c.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND NOW() BETWEEN CONCAT(c.data_inicio, ' ', c.hora_inicio) AND CONCAT(c.data_fim, ' ', c.hora_fim) LIMIT 1");
    if ($_SESSION['tipo'] == "user") {
        $query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    } elseif ($_SESSION['tipo'] == "admin") {
        $query->execute(array(':id_empresa' => $_POST['id_destinatario']));
    }
    $linha = $query->fetch(PDO::FETCH_ASSOC);
    if ($query->rowCount() != 0) {
        $mes = $linha['mes'];
        $ano = $linha['ano'];
        $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
        $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
        $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
        $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
        $agora = date("Y-m-d H:i:s");
        $diferenca_datas = time_diff($data_fim, $data_inicio);
        $diff_agr = time_diff($agora, $data_inicio);
        $factor = $diff_agr / $diferenca_datas;
        $distancia = time_diff(date("Y-m-d H:i:s", strtotime("$ano-$mes-$ultimo_dia 23:59:59")), date("Y-m-d H:i:s", strtotime("$ano-$mes-$primeiro_dia 00:00:00")));
        $tempo_referencia = strtotime(date("$ano-$mes-01 00:00:00"));
        $data_virtual = ($factor * $distancia) + $tempo_referencia;
        $arr = array('sucesso' => true, 'mensagem' => date("m/d/Y H:i:s", $data_virtual));
    } else {
        date_default_timezone_set('Europe/London');
        $arr = array('sucesso' => true, 'mensagem' => date("m/d/Y H:i:s"));
    }
	/* */
	
	/* * /
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
    } elseif ($_SESSION['tipo'] == "admin") {
        $id_empresa = $_POST['id_destinatario'];
    }
    
    $arr = data_virtual_cache($connection, $id_empresa);
    /* */	
	
} elseif ($_POST['tipo'] == "emp_grupos") {
    if (isset($_POST['tipo']) && $_SESSION['tipo'] == "admin") {
        if ($_POST['id'] == 0) {
            $query_grupos = $connection->prepare("SELECT emp.id_empresa AS id_empresa, g.id AS id_grupo, emp.nome AS nome_empresa, num_conta, g.nome AS nome_grupo FROM empresa emp INNER JOIN conta c ON emp.id_empresa=c.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1' AND emp.id_empresa<>:cc AND emp.id_empresa<>:cf AND emp.id_empresa<>:cp");
            $query_grupos->execute(array(':cc' => 1, ':cf' => 2, ':cp' => 3));
        } else {
            $query_grupos = $connection->prepare("SELECT emp.id_empresa AS id_empresa, g.id AS id_grupo, emp.nome AS nome_empresa, num_conta, g.nome AS nome_grupo FROM empresa emp INNER JOIN conta c ON emp.id_empresa=c.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1' AND emp.id_empresa<>:cc AND emp.id_empresa<>:cf AND emp.id_empresa<>:cp AND g.id=:id_grupo");
            $query_grupos->execute(array(':cc' => 1, ':cf' => 2, ':cp' => 3, ':id_grupo' => $_POST['id']));
        }
		
    } elseif (isset($_POST['tipo']) && $_SESSION['tipo'] == "user") {
        /*//-- Obter "atividade" da empresa
        $query_empresa = $connection->prepare("SELECT atividade FROM empresa WHERE id_empresa=:id_empresa");
        $query_empresa->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $my_empresa = $query_empresa->fetch(PDO::FETCH_ASSOC);
        $atividade_id = $my_empresa['atividade'];
		*/
        
        if ($_POST['id'] == 0) {
            //-- Todas as empresas dos grupos ATIVOS, de QUALQUER entidade
            // $query_grupos = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, em.id_empresa, em.nome, c.num_conta FROM (SELECT * FROM (SELECT * FROM grupo) AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
            // $query_grupos->execute(array(':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));

            //-- Todas as empresas, da MESMA atividade, dos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
            // $query_grupos = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, em.id_empresa, em.nome, c.num_conta FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND em.atividade=:atividade_id AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
            // $query_grupos->execute(array(':atividade_id' => $atividade_id, ':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));
			
			//-- Todas as empresas, dos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
            $query_grupos = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, em.id_empresa, em.nome AS nome_empresa, c.num_conta FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC"); //pedente
            $query_grupos->execute(array(':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));
        } else {
            //-- Todas as empresas, da MESMA atividade, do grupo indicado
            // $query_grupos = $connection->prepare("SELECT g.id AS id_grupo, g.nome AS nome_grupo, em.id_empresa, em.nome, c.num_conta FROM empresa em INNER JOIN grupo g ON em.id_grupo=g.id INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE g.id=:id_grupo AND em.ativo='1' AND em.atividade=:atividade_id AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
            // $query_grupos->execute(array(':id_grupo' => $_POST['id'], ':atividade_id' => $atividade_id, ':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));
			
			//-- Todas as empresas do grupo indicado
            $query_grupos = $connection->prepare("SELECT g.id AS id_grupo, g.nome AS nome_grupo, em.id_empresa, em.nome AS nome_empresa, c.num_conta FROM empresa em INNER JOIN grupo g ON em.id_grupo=g.id INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE g.id=:id_grupo AND em.ativo='1' AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
            $query_grupos->execute(array(':id_grupo' => $_POST['id'], ':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));
        }
    }
    
	$num_dados = $query_grupos->rowCount();
    for ($i = 0; $i < $num_dados; $i++) {
        $linha_dados = $query_grupos->fetch(PDO::FETCH_ASSOC);
        $arr_dados[] = array('id_empresa' => $linha_dados['id_empresa'], 'id_grupo' => $linha_dados['id_grupo'], 'nome' => $linha_dados['nome_empresa'], 'num_conta' => $linha_dados['num_conta'], 'nome_grupo' => $linha_dados['nome_grupo']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    
} elseif ($_POST['tipo'] == "modify_pass") {
    $query_login = $connection->prepare("SELECT u.id, u.id_entidade FROM utilizador u WHERE u.id=:id_username AND u.pass=UNHEX(SHA2(CONCAT(:password, HEX(u.salt)), 256)) AND u_ldap='0'");
    $query_login->execute(array(':password' => $_POST['password_old'], ':id_username' => $_SESSION['id_utilizador']));
    $linha_login = $query_login->fetch(PDO::FETCH_ASSOC);
    $count = $query_login->rowCount();
    if ($count == 1) {
        $connection->beginTransaction();
        $query_geraSalt = $connection->prepare("SET @salt = UNHEX(SHA2(UUID(), 256))");
        $query_geraSalt->execute();
        $query_update = $connection->prepare("UPDATE utilizador SET pass=UNHEX(SHA2(CONCAT(:password, HEX(@salt)), 256)), salt=@salt WHERE id=:id_utilizador");
        $query_update->execute(array(':password' => $_POST['password_new'], ':id_utilizador' => $_SESSION['id_utilizador']));
        $num_update = $query_update->rowCount();
        if ($linha_login == "1" && $_SESSION['tipo'] == "user") {
            $encrypted_data = mc_encrypt($password, ENCRYPTION_KEY);
            $query_conexoes = $connection->prepare("SELECT cg.id_conn FROM conexao_guacamole cg INNER JOIN entidade ent ON cg.id_entidade=ent.id AND ent.id=:id_entidade");
            $query_conexoes->execute(array(':id_entidade' => $_SESSION['id_entidade']));
            $num = $query_conexoes->rowCount();
            if ($num > 0) {
                while ($values = $query_conexoes->fetch(PDO::FETCH_ASSOC)) {
                    $id_conn[] = $values['id_conn'];
                }
                for ($i = 0; $i < $num; $i++) {
                    $stmt = $connection->prepare("UPDATE guac_user SET password=:password WHERE id_conexao=:id_conexao AND id_user=:id_user");
                    $stmt->execute(array(':id_conexao' => $id_conn[$i], ':id_user' => $_SESSION['id_utilizador'], ':password' => $encrypted_data));
                }
            }
        }
        $connection->commit();
        if ($num_update == 1) {
            $arr = array('sucesso' => true);
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Não se conseguiu modificar a palavra-passe");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "A palavra-passe antiga está errada");
    }
    logClicks($connection, "40");
} elseif($_POST['tipo'] == "heartbeat") {
    $query_sessao=$connection->prepare("UPDATE sessao SET data_logout=NOW() WHERE session_id=:session_id AND ip=:ip AND user=:id_utilizador");
    $query_sessao->execute(array(':session_id' => session_id(), ':ip' => encode_ip($_SERVER['REMOTE_ADDR']), ':id_utilizador' => $_SESSION['id_utilizador']));
    $arr = array('sucesso' => true);
}

echo json_encode($arr);
$connection = null;
