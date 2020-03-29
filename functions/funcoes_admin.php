<?php

/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-01 16:12:30
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-08-27 12:48:36
 */

include('../conf/check_pastas.php');
include_once('functions.php');

use phpFastCache\CacheManager;
include_once('../phpfastcache/src/autoload.php');
CacheManager::setDefaultConfig([
  'path' => '/tmp',
  'securityKey' => 'SimEmp'
]);

$pwd_se_acoes = '';

$query_moeda = $connection->prepare("SELECT mo.simbolo, mo.ISO4217 FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

date_default_timezone_set('Europe/London');

if ($_POST['tipo'] == "dados_empresas") {
    $query_dados_empresas = $connection->prepare("SELECT emp.id_empresa, emp.niss, emp.nipc, emp.nome, te.tipo, a.designacao, emp.morada, emp.cod_postal, emp.localidade, emp.pais, emp.email, g.nome AS grupo FROM empresa emp INNER JOIN tipo_empresa te ON emp.tipo=te.id INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa");
    $query_dados_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id']));
    $linha_q_dados_emp = $query_dados_empresas->fetch(PDO::FETCH_ASSOC);

    $query_dados_users = $connection->prepare("SELECT u.id, u.login FROM empresa emp INNER JOIN utilizador u ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
    $query_dados_users->execute(array('id_empresa' => $linha_q_dados_emp['id_empresa']));
    while ($linha_dados_users = $query_dados_users->fetch(PDO::FETCH_ASSOC)) {
        $arr_users[] = array('id_user' => $linha_dados_users['id'], 'nome_user' => $linha_dados_users['login']);
    }
    $arr = array('sucesso' => true, 'id_empresa' => $linha_q_dados_emp['id_empresa'], 'niss' => $linha_q_dados_emp['niss'], 'nipc' => $linha_q_dados_emp['nipc'], 'nome' => $linha_q_dados_emp['nome'], 'tipo' => $linha_q_dados_emp['tipo'], 'designacao' => $linha_q_dados_emp['designacao'], 'morada' => $linha_q_dados_emp['morada'], 'cod_postal' => $linha_q_dados_emp['cod_postal'], 'localidade' => $linha_q_dados_emp['localidade'], 'pais' => $linha_q_dados_emp['pais'], 'email' => $linha_q_dados_emp['email'], 'grupo' => $linha_q_dados_emp['grupo'], 'dados_u' => $arr_users);
    logClicks($connection, "10");
} elseif ($_POST['tipo'] == "dados_user") {
    $query_user = $connection->prepare("SELECT u.id, u.login, emp.id_empresa, emp.nome AS nome_empresa FROM utilizador u INNER JOIN empresa emp ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND u.id=:id");
    $query_user->execute(array(':id' => $_POST['id']));
    $linha_dados = $query_user->fetch(PDO::FETCH_ASSOC);
    $arr = array('id_user' => $linha_dados['id'], 'login' => $linha_dados['login'], 'nome_empresa' => $linha_dados['nome_empresa']);
    logClicks($connection, "28");
} elseif ($_POST['tipo'] == "update_dados_empresa") {
    $valido = true;
    $query_verificacao = $connection->prepare("SELECT emp.niss, emp.nipc, emp.nome, emp.morada, emp.cod_postal, emp.localidade, emp.pais, emp.email FROM empresa emp WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
    $query_verificacao->execute(array(':id_empresa' => $_POST['id']));
    while ($linha = $query_verificacao->fetch(PDO::FETCH_ASSOC)) {
        if ($_POST['niss'] == $linha['niss'] && $_POST['nipc'] == $linha['nipc'] && $_POST['nome'] == $linha['nome'] && $_POST['morada'] == $linha['morada'] && $_POST['cod_postal'] == $linha['cod_postal'] && $_POST['localidade'] == $linha['localidade'] && $_POST['pais'] == $linha['pais'] && $_POST['email'] == $linha['email']) {
            $valido = false;
        }
    }
    if ($valido == true) {
        $query_up_empresa = $connection->prepare("UPDATE empresa SET niss=:niss, nipc=:nipc, nome=:nome, morada=:morada, cod_postal=:cp, localidade=:loc, pais=:pais, email=:mail WHERE id_empresa=:id_empresa");
        $query_up_empresa->execute(array(':niss' => $_POST['niss'], ':nipc' => $_POST['nipc'], ':nome' => $_POST['nome'], ':morada' => $_POST['morada'], ':cp' => $_POST['cod_postal'], ':loc' => $_POST['localidade'], ':pais' => $_POST['pais'], ':mail' => $_POST['email'], ':id_empresa' => $_POST['id']));
        $query_grupo = $connection->prepare("SELECT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND g.id=:id_grupo AND u.id=:id_utilizador ORDER BY emp.nome");
        $query_grupo->execute(array(':tipo' => "admin", ':id_grupo' => $_POST['id_grupo'], ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_dados = $query_grupo->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_dados['id_empresa'], 'nome' => $linha_dados['nome']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Está a tentar inserir dados repetidos");
    }
    logClicks($connection, "11");
} elseif ($_POST['tipo'] == "up_dados_user") {
    $valido = true;
    if ($_POST['modo'] == "pass") {
        if ($_POST['password'] != $_POST['conf_pass']) {
            $valido = false;
            $arr = array('sucesso' => false, 'mensagem' => "As palavras-passe não correspondem");
        }
    } else {
        $query_user = $connection->prepare("SELECT u.login FROM utilizador u WHERE u.id=:id LIMIT 1");
        $query_user->execute(array(':id' => $_POST['id']));
        $linha_user = $query_user->fetch(PDO::FETCH_ASSOC);
        if ($_POST['login'] != $linha_user['login']) {
            $query_grupo = $connection->prepare("SELECT u.login FROM utilizador u WHERE u.id<>:id");
            $query_grupo->execute(array(':id' => $_POST['id']));
            while ($linha = $query_grupo->fetch(PDO::FETCH_ASSOC)) {
                if ($_POST['login'] == $linha['login']) {
                    $valido = false;
                }
            }
            if ($valido == false) {
                $arr = array('sucesso' => false, 'mensagem' => "O nome já existe, escolha outro");
            }
        }
        if ($_POST['modo'] == "login_pass") {
            if ($_POST['password'] != $_POST['conf_pass']) {
                $valido = false;
                $arr = array('sucesso' => false, 'mensagem' => "As palavras-passe não correspondem");
            }
        }
    }
    if ($valido == true) {
        if ($_POST['modo'] == "login") {
            $query_up_user = $connection->prepare("UPDATE utilizador SET login=:login WHERE id=:id");
            $query_up_user->execute(array(':login' => $_POST['login'], ':id' => $_POST['id']));
        } elseif ($_POST['modo'] == "pass") {
            $connection->beginTransaction();
            $query_geraSalt = $connection->prepare("SET @salt = UNHEX(SHA2(UUID(), 256))");
            $query_geraSalt->execute();
            $query_up_user = $connection->prepare("UPDATE utilizador SET pass=UNHEX(SHA2(CONCAT(:password, HEX(@salt)), 256)), salt=@salt WHERE id=:id");
            $query_up_user->execute(array(':password' => $_POST["password"], ':id' => $_POST['id']));
            $connection->commit();
        } elseif ($_POST['modo'] == "login_pass") {
            $connection->beginTransaction();
            $query_geraSalt = $connection->prepare("SET @salt = UNHEX(SHA2(UUID(), 256))");
            $query_geraSalt->execute();
            $query_up_user = $connection->prepare("UPDATE utilizador SET login=:login, pass=UNHEX(SHA2(CONCAT(:password, HEX(@salt)), 256)), salt=@salt WHERE id=:id");
            $query_up_user->execute(array(':login' => $_POST['login'], ':password' => $_POST["password"], ':id' => $_POST['id']));
            $connection->commit();
        }

        $query_utilizadores = $connection->prepare("SELECT users.id, users.nome FROM (SELECT DISTINCT u.id, u.tipo, g.id AS id_grupo FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON u.id=ug.id_user) AS adm INNER JOIN (SELECT u.id, u.nome, g.id AS id_grupo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1') AS users ON adm.id_grupo=users.id_grupo WHERE adm.tipo=:tipo AND adm.id=:id_utilizador ORDER BY users.nome");
        $query_utilizadores->execute(array(':tipo' => "admin", 'id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_dados = $query_utilizadores->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_dados['id'], 'nome' => $linha_dados['nome']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "29");
} elseif ($_POST['tipo'] == "up_grupo") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_grupo = $dados[$key]["id_grupo"];
        $nome = isset($dados[$key]["nome"]) ? $dados[$key]["nome"] : "";
        $estado = isset($dados[$key]["estado"]) ? $dados[$key]["estado"] : "";
        $tipo = $dados[$key]["tipo"];
        $date = new DateTime();
        $data = $date->format('Y-m-d H:i:s');
        if ($nome != "" && $estado == "") {
            $query_up_nome_grupo = $connection->prepare("UPDATE grupo SET nome=:nome WHERE id=:id AND id_tipo=:tipo");
            $query_up_nome_grupo->execute(array(':nome' => $nome, ':id' => $id_grupo, ':tipo' => $tipo));
        } elseif ($nome == "" && $estado != "") {
            $query_ins_estado_grupo = $connection->prepare("INSERT INTO estado_grupo (estado, data, id_user, id_grupo) VALUES (:estado, :data, :id_user, :id_grupo)");
            $query_ins_estado_grupo->execute(array(':estado' => $estado, ':data' => $data, ':id_user' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));
        } elseif ($nome != "" && $estado != "") {
            $connection->beginTransaction();
            $query_up_nome_grupo = $connection->prepare("UPDATE grupo SET nome=:nome WHERE id=:id AND id_tipo=:tipo");
            $query_up_nome_grupo->execute(array(':nome' => $nome, ':id' => $id_grupo, ':tipo' => $tipo));
            $query_ins_estado_grupo = $connection->prepare("INSERT INTO estado_grupo (estado, data, id_user, id_grupo) VALUES (:estado, :data, :id_user, :id_grupo)");
            $query_ins_estado_grupo->execute(array(':estado' => $estado, ':data' => $data, ':id_user' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));
            $connection->commit();
        }
    }
    $arr = array('sucesso' => true);
    logClicks($connection, "16");
} elseif ($_POST['tipo'] == "inserir_grupo") {
    $valido = true;
    $query_grupo = $connection->prepare("SELECT g.nome, u.id AS id_admin FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id");
    $query_grupo->execute();
    while ($linha = $query_grupo->fetch(PDO::FETCH_ASSOC)) {
        if ($_POST['nome'] == $linha['nome'] && $_SESSION['id_utilizador'] == $linha['id_admin']) {
            $valido = false;
        }
    }
    if ($valido == true) {
        try {
            $date = new DateTime();
            $data = $date->format('Y-m-d H:i:s');
            $connection->beginTransaction();
            $query_insert_grupo = $connection->prepare("INSERT INTO grupo (nome, id_tipo) VALUES (:nome, :tipo_grupo)");
            $query_insert_grupo->execute(array(':nome' => $_POST['nome'], ':tipo_grupo' => $_POST['grupo']));
            $id_grupo = $connection->lastInsertId();
            $query_insert_user_grupo = $connection->prepare("INSERT INTO user_grupo (id_user, id_grupo) VALUES (:id_utilizador, :id_grupo)");
            $query_insert_user_grupo->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));
            $query_ins_estado_grupo = $connection->prepare("INSERT INTO estado_grupo (estado, data, id_user, id_grupo) VALUES (:estado, :data, :id_user, :id_grupo)");
            $query_ins_estado_grupo->execute(array(':estado' => "0", ':data' => $data, ':id_user' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));
            $connection->commit();
            $arr = array('sucesso' => true);
        } catch (PDOExecption $e) {
            $connection->rollback();
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na criação do grupo " . $e->getMessage());
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "O nome já existe, escolha outro");
    }
    logClicks($connection, "18");
} elseif ($_POST['tipo'] == "guardar_afet_grupo") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_grupo = $dados[$key]["id_grupo"];
        $id_empresa = $dados[$key]["id_empresa"];
        $query_afetar = $connection->prepare("UPDATE empresa SET id_grupo=:id_grupo WHERE id_empresa=:id_empresa");
        $query_afetar->execute(array(':id_grupo' => $id_grupo, ':id_empresa' => $id_empresa));
    }
    $arr = array('sucesso' => true);
    logClicks($connection, "14");
} elseif ($_POST['tipo'] == "alt_validar") {
    $date = new DateTime();
    $data = $date->format('Y-m-d H:i:s');
    if ($_POST['valor'] == "false") {
        $valor = "0";
        logClicks($connection, "2");
    } else {
        $valor = "1";
        logClicks($connection, "3");
    }
    $query_update = $connection->prepare("UPDATE entidade ent INNER JOIN utilizador u ON u.id_entidade=ent.id SET ent.valido=:valido WHERE u.id=:id");
    $query_update->execute(array(':valido' => $valor, ':id' => $_SESSION['id_utilizador']));
    $query_op = $connection->prepare("INSERT INTO sessao_operacao (id_sessao, id_operacao, data) VALUES (:id_sessao, :id_operacao, :data)");
    $arr = array('sucesso' => true);
} elseif ($_POST['tipo'] == "guardar_afet_user") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_empresa = $dados[$key]["id_empresa"];
        $id_utilizador = $dados[$key]["id_utilizador"];
        $query_update = $connection->prepare("UPDATE utilizador SET id_empresa=:id_empresa WHERE id=:id_user");
        $query_update->execute(array(':id_empresa' => $id_empresa, ':id_user' => $id_utilizador));
    }
    $arr = array('sucesso' => true);
    logClicks($connection, "26");
} elseif ($_POST['tipo'] == "inserir_tipo_ent") {
    $valido = true;
    $query_tipos_entrega = $connection->prepare("SELECT designacao FROM tipo_entrega");
    $query_tipos_entrega->execute();
    while ($linha_dados = $query_tipos_entrega->fetch(PDO::FETCH_ASSOC)) {
        if ($_POST['nome'] == $linha_dados['designacao']) {
            $valido = false;
        }
    }
    if ($valido == true) {
        $query_insert_tipo_entrega = $connection->prepare("INSERT INTO tipo_entrega (designacao) VALUES (:designacao)");
        $query_insert_tipo_entrega->execute(array(':designacao' => $_POST['nome']));
        $count_insert = $query_insert_tipo_entrega->rowCount();
        if ($count_insert == 1) {
            $arr = array('sucesso' => true);
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na criação do tipo de entrega");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "O nome já existe, escolha outro");
    }
    logClicks($connection, "55");
} elseif ($_POST['tipo'] == "up_tipo_ent") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_entrega = $dados[$key]["id_entrega"];
        $nome = $dados[$key]["nome"];
        $query_up_tipo_entrega = $connection->prepare("UPDATE tipo_entrega SET designacao=:nome WHERE id=:id");
        $query_up_tipo_entrega->execute(array(':nome' => $nome, ':id' => $id_entrega));
        $arr = array('sucesso' => true);
    }
    logClicks($connection, "54");
} elseif ($_POST['tipo'] == "ver_entregas") {
    $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, emp.nome, e.valor, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, e.mes, e.ano FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND e.id=:id_entrega");
    $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_entrega' => $_POST['id_entrega']));
    $linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC);
    $arr = array('id' => $linha_entregas['id'], 'data_entrega' => $linha_entregas['data'], 'ficheiro' => $linha_entregas['ficheiro'], 'designacao' => $linha_entregas['designacao'], 'pago' => $linha_entregas['pago'], 'nome' => $linha_entregas['nome'], 'valor' => $linha_entregas['valor'], 'f_prazo' => $linha_entregas['f_prazo'], 'mes' => $linha_entregas['mes'], 'ano' => $linha_entregas['ano']);
    logClicks($connection, "45");
} elseif ($_POST['tipo'] == "inserir_atividade") {
    $valido = true;
    $query_atividade = $connection->prepare("SELECT designacao FROM atividade");
    $query_atividade->execute();
    while ($linha = $query_atividade->fetch(PDO::FETCH_ASSOC)) {
        if ($_POST['nome'] == $linha['designacao']) {
            $valido = false;
        }
    }
    if ($valido == true) {
        $query_insert_atividade = $connection->prepare("INSERT INTO atividade (designacao, capital_social_monetario) VALUES (:desig, :cap_soc)");
        $query_insert_atividade->execute(array(':desig' => $_POST['nome'], ':cap_soc' => $_POST['cap_soc']));
        $count_insert = $query_insert_atividade->rowCount();

        if ($count_insert == 1) {
            $arr = array('sucesso' => true);
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na criação da atividade");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "O nome já existe, escolha outro");
    }
    logClicks($connection, "23");
} elseif ($_POST['tipo'] == "up_atividades") {
    $dados = json_decode($_POST['dados'], true);
    $valido = true;
    foreach ($dados as $key => $value) {
        $id_atividade = $dados[$key]["id_atividade"];
        $cap_soc = $dados[$key]["cap_soc"];
        $nome = $dados[$key]["nome"];
        $query_up_atividade = $connection->prepare("UPDATE atividade SET designacao=:nome, capital_social_monetario=:cap_soc WHERE id=:id");
        $query_up_atividade->execute(array(':nome' => $nome, ':cap_soc' => $cap_soc, ':id' => $id_atividade));
    }
    $arr = array('sucesso' => true);
    logClicks($connection, "21");
} elseif ($_POST['tipo'] == "ver_emprestimo") {
    $query_emprestimo = $connection->prepare("SELECT e.emprest, date_format(e.data_emprestimo, '%d-%m-%Y') AS data, IF(strcmp(e.pago, '1'), 'Não', 'Sim') AS pago, emp.nome, e.capital_pendente, e.juros, e.amortizacao, e.prestacao FROM emprestimo e INNER JOIN conta c ON e.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND e.emprest=:emprest AND emp.id_empresa=:id_empresa");
    $query_emprestimo->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':emprest' => $_POST['id_emprest'], ':id_empresa' => $_POST['id_empresa']));
    $num_linhas = $query_emprestimo->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_emprestimo->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('emprest' => $linha['emprest'], 'data' => $linha['data'], 'pago' => $linha['pago'], 'nome' => $linha['nome'], 'cap_p' => $linha['capital_pendente'], 'juros' => $linha['juros'], 'amort' => $linha['amortizacao'], 'prest' => $linha['prestacao']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "58");
} elseif ($_POST['tipo'] == "ver_leasing") {
    $query_leasing = $connection->prepare("SELECT l.leas, date_format(l.data_leasing, '%d-%m-%Y') AS data, IF(strcmp(l.pago, '1'), 'Não', 'Sim') AS pago, emp.nome, l.capital_pendente, l.juros, l.amortizacao, l.prestacao_s_iva, l.iva, l.prestacao_c_iva FROM leasing l INNER JOIN conta c ON l.id_conta=c.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND l.leas=:id_leas AND emp.id_empresa=:id_empresa");
    $query_leasing->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_leas' => $_POST['id_leas'], ':id_empresa' => $_POST['id_empresa']));
    $num_linhas = $query_leasing->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_leasing->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('leas' => $linha['leas'], 'data' => $linha['data'], 'nome' => $linha['nome'], 'pago' => $linha['pago'], 'cap_p' => $linha['capital_pendente'], 'juros' => $linha['juros'], 'amort' => $linha['amortizacao'], 'prest_s_iva' => $linha['prestacao_s_iva'], 'iva' => $linha['iva'], 'prest_c_iva' => $linha['prestacao_c_iva']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "62");
} elseif ($_POST['tipo'] == "ver_extrato") {
    $query_extrato = $connection->prepare("SELECT emp.nome, date_format(m.data_op, '%d-%m-%Y') AS data_op, m.id, m.tipo, m.descricao, m.debito, m.credito, m.saldo_controlo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN conta c ON c.id_empresa=emp.id_empresa INNER JOIN movimento m ON m.id_conta=c.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem'");
    $query_extrato->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    $num_linhas = $query_extrato->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_extrato->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('nome' => $linha['nome'], 'data' => $linha['data_op'], 'id_mov' => $linha['id'], 'tipo' => $linha['tipo'], 'descricao' => $linha['descricao'], 'debito' => $linha['debito'], 'credito' => $linha['credito'], 'saldo' => $linha['saldo_controlo']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "60");
} elseif ($_POST['tipo'] == "ver_titulo") {
//    $query_titulo = $connection->prepare("SELECT emp.nome AS empresa, a.nome, date_format(a_t.`data`, '%d-%m-%Y') AS data_virtual, a_t.preco, a_t.quantidade, a_t.subtotal, IF(strcmp(a_t.tipo, 'V'), 'Compra', 'Venda') AS tipo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN conta c ON c.id_empresa=emp.id_empresa INNER JOIN acao_trans a_t ON a_t.id_empresa=emp.id_empresa INNER JOIN acao a ON a.id=a_t.id_acao WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa"); //estava esta
    $query_titulo = $connection->prepare("SELECT emp.nome AS empresa, a.nome, date_format(a_t.`data`, '%d-%m-%Y') AS data_virtual, a_t.preco, a_t.quantidade, a_t.subtotal, IF(strcmp(a_t.tipo, 'V'), 'Compra', 'Venda') AS tipo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id /*INNER JOIN conta c ON c.id_empresa=emp.id_empresa*/ INNER JOIN acao_trans a_t ON a_t.id_empresa=emp.id_empresa INNER JOIN acao a ON a.id=a_t.id_acao WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa"); //meti esta (comentei o INNER JOIN conta c ON c.id_empresa=emp.id_empresa)
    $query_titulo->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    $num_linhas = $query_titulo->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_titulo->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('empresa' => $linha['empresa'], 'nome' => $linha['nome'], 'data' => $linha['data_virtual'], 'preco' => $linha['preco'], 'quantidade' => $linha['quantidade'], 'subtotal' => $linha['subtotal'], 'tipo' => $linha['tipo']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "70");
} elseif ($_POST['tipo'] == "regras") {
    $id_grupo = $_POST['id_grupo'];
    $id_empresa = $_POST['id_empresa'];
    $id_regra = $_POST['id_regra'];
    $vazio = false;
    $arr_empresas = array();
    $arr_grupos = array();
    
    /* // Case 1 - Só selecionou grupo
    if ($id_grupo != 0 && $id_empresa == 0 && $id_regra == 0) {
        $query_taxas = $connection->prepare("SELECT emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));
        $num_regras = $query_taxas->rowCount();

        $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo ORDER BY emp.nome, r.nome_regra");
        $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));

        if ($num_regras > 0) {
            while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
                if ($linha['simbolo'] == null) {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
                } else {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
                }
            }
            while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
                $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'empresa' => $linha_empresa['nome']);
            }
        } else {
            $vazio = true;
            $arr_dados = array();
        }
    }

    // Case 2 - Selecionou grupo e empresa
    elseif ($id_grupo != 0 && $id_empresa != 0 && $id_regra == 0) {
        $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo AND emp.id_empresa=:id_empresa ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $id_empresa, ':id_grupo' => $id_grupo));
        $num_regras = $query_taxas->rowCount();

        if ($num_regras > 0) {
            while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
                if ($linha['simbolo'] == null) {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
                } else {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
                }
            }
        } else {
            $vazio = true;
            $arr_dados = array();
        }
    }
    // Case 3 - Selecionou grupo e regra
    elseif ($id_grupo != 0 && $id_empresa == 0 && $id_regra != 0) {
        $query_taxas = $connection->prepare("SELECT emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo AND r.id_regra=:id_regra ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], 'id_grupo' => $id_grupo, ':id_regra' => $id_regra));

        $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo ORDER BY emp.nome, r.nome_regra");
        $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));

        while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['simbolo'] == null) {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
            } else {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
            }
        }
        while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'empresa' => $linha_empresa['nome']);
        }
    }
    // Case 4 - Selecionou os 3
    elseif ($id_grupo != 0 && $id_empresa != 0 && $id_regra != 0) {
        $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND r.id_regra=:id_regra AND emp.id_empresa=:id_empresa AND g.id=:id_grupo ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_regra' => $id_regra, ':id_empresa' => $id_empresa, ':id_grupo' => $id_grupo));
        $num_regras = $query_taxas->rowCount();

        if ($num_regras > 0) {
            while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
                if ($linha['simbolo'] == null) {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
                } else {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
                }
            }
        } else {
            $vazio = true;
            $arr_dados = array();
        }
    }
    // Case 5 - Só selecionou empresa
    elseif ($id_grupo == 0 && $id_empresa != 0 && $id_regra == 0) {
        $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $id_empresa));
        $num_regras = $query_taxas->rowCount();

        if ($num_regras > 0) {
            while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
                if ($linha['simbolo'] == null) {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
                } else {
                    $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
                }
            }
        } else {
            $vazio = true;
            $arr_dados = array();
        }
    }
    // Case 6 - Só selecionou regra **
    elseif ($id_grupo == 0 && $id_empresa == 0 && $id_regra != 0) {
        $query_taxas = $connection->prepare("SELECT emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND r.id_regra=:id_regra ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_regra' => $id_regra));

        while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['simbolo'] == null) {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
            } else {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
            }
        }
        // Restabelecer filtro Grupo
        $query_grupos = $connection->prepare("SELECT g.id, g.nome FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao='Normal'");
        $query_grupos->execute();
        while ($linha_grupo = $query_grupos->fetch(PDO::FETCH_ASSOC)) {
            $arr_grupos[] = array('id_grupo' => $linha_grupo['id'], 'nome_grupo' => $linha_grupo['nome']);
        }
        // Restabelecer filtro Empresas
        $query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
        $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'empresa' => $linha_empresa['empresa']);
        }
    }
    // Case 7 - Selecionou empresa e regra
    elseif ($id_grupo == 0 && $id_empresa != 0 && $id_regra != 0) {
        $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND r.id_regra=:id_regra AND emp.id_empresa=:id_empresa ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_regra' => $id_regra, ':id_empresa' => $id_empresa));

        while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['simbolo'] == null) {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
            } else {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
            }
        }
    }
    // Case 8 - Não selecionou nenhuma **
    elseif ($id_grupo == 0 && $id_empresa == 0 && $id_regra == 0) {
        $query_taxas = $connection->prepare("SELECT emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

        while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['simbolo'] == null) {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
            } else {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
            }
        }
        // Restabelecer filtro Grupo
        $query_grupos = $connection->prepare("SELECT g.id, g.nome FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao='Normal'");
        $query_grupos->execute();
        while ($linha_grupo = $query_grupos->fetch(PDO::FETCH_ASSOC)) {
            $arr_grupos[] = array('id_grupo' => $linha_grupo['id'], 'nome_grupo' => $linha_grupo['nome']);
        }
        // Restabelecer filtro Empresas
        $query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
        $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'empresa' => $linha_empresa['empresa']);
        }
    } */

    /* */
    if ($id_grupo == 0) {
        // $id_grupo = 'g.id > '.$id_grupo;
        $opr_gr = '>';
    } elseif ($id_grupo > 0) {
        // $id_grupo = 'g.id = '.$id_grupo;
        $opr_gr = '=';
    }
    if ($id_empresa == 0) {
        // $id_empresa = 'emp.id_empresa > '.$id_empresa;
        $opr_emp = '>';
    } elseif ($id_empresa > 0) {
        // $id_empresa = 'emp.id_empresa = '.$id_empresa;
        $opr_emp = '=';
    }
    if ($id_regra == 0) {
        // $id_regra = 'r.id_regra > '.$id_regra;
        $opr_r = '>';
    } elseif ($id_regra > 0) {
        // $id_regra = 'r.id_regra = '.$id_regra;
        $opr_r = '=';
    }
    
    /* $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND :id_regra AND :id_empresa AND :id_grupo ORDER BY emp.nome, r.nome_regra");
    $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_regra' => $id_regra, ':id_empresa' => $id_empresa, ':id_grupo' => $id_grupo)); */
    
    /* $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND r.id_regra ".$opr_r." :id_regra AND emp.id_empresa ".$opr_emp." :id_empresa AND g.id ".$opr_gr." :id_grupo ORDER BY emp.nome, r.nome_regra");
    $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_regra' => $id_regra, ':id_empresa' => $id_empresa, ':id_grupo' => $id_grupo));
    */
    $query_taxas = loadby($connection, $opr_r, $id_regra, $opr_emp, $id_empresa, $opr_gr, $id_grupo);
    $num_regras = $query_taxas->rowCount();

    if ($id_grupo == 0 && $id_empresa == 0 && $id_regra == 0) { // Para mostrar tabela vazia caso não seja escolhido nenhum filtro
        $vazio = true;
        while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) { // Necessário para, na fase de guardar na BD, poder descobrir alterações
            if ($linha['simbolo'] == null) {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
            } else {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
            }
        }
    } elseif ($num_regras > 0) {
        while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
            if ($linha['simbolo'] == null) {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => "");
            } else {
                $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'id_banco' => $linha['id_banco'], 'empresa' => $linha['empresa'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
            }
        }
    } else {
        $vazio = true;
        $arr_dados = array();
    }
    
    /* Restabelecer filtro Grupo
    $query_grupos = $connection->prepare("SELECT * FROM (SELECT * FROM (SELECT eg.id_grupo, g.nome, eg.estado FROM utilizador u LEFT JOIN user_grupo ug ON u.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id LEFT JOIN estado_grupo eg ON g.id=eg.id_grupo WHERE u.tipo='admin' AND u.id=:id_utilizador AND tg.designacao='Normal' ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_grupos GROUP BY estado_grupos.id_grupo) AS estado_grupos WHERE estado_grupos.estado='1'");
    $query_grupos->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    while ($linha_grupo = $query_grupos->fetch(PDO::FETCH_ASSOC)) {
        $arr_grupos[] = array('id_grupo' => $linha_grupo['id_grupo'], 'nome_grupo' => $linha_grupo['nome']);
    }
    */
    // Restabelecer filtro Empresas
//    $query_empresas = $connection->prepare("SELECT estado_grupos.id_grupo, estado_grupos.nome AS nome_grupo, estado_grupos.estado, e.id_empresa, e.nome AS empresa FROM (SELECT * FROM (SELECT eg.id_grupo, g.nome, eg.estado FROM utilizador u LEFT JOIN user_grupo ug ON u.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id LEFT JOIN estado_grupo eg ON g.id=eg.id_grupo WHERE u.tipo='admin' AND u.id=:id_utilizador AND g.id=:id_grupo AND tg.designacao='Normal' ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_grupos GROUP BY estado_grupos.id_grupo) AS estado_grupos LEFT JOIN empresa e ON estado_grupos.id_grupo=e.id_grupo WHERE estado_grupos.estado='1' ORDER BY e.nome ASC");//estava esta
    $query_empresas = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, last_estado_grupos.estado, e.id_empresa, e.nome AS empresa FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id LEFT JOIN empresa e ON g.id=e.id_grupo WHERE last_estado_grupos.estado='1' AND e.ativo='1' AND ug.id_user=:id_utilizador AND g.id=:id_grupo AND tp.designacao='Normal'  ORDER BY g.nome ASC, e.nome ASC");//meti esta
    $query_empresas->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $id_grupo));
    while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
        $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'empresa' => $linha_empresa['empresa']);
    }
    /* */
    
    // $arr = array('sucesso' => true, 'dados_in' => $arr_dados, 'empresa_grupo' => $arr_empresas, 'grupos' => $arr_grupos, 'vazio' => $vazio);
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados, 'empresa_grupo' => $arr_empresas, 'vazio' => $vazio);
    logClicks($connection, "67");
} elseif ($_POST['tipo'] == "g_regra_empresa") {
    $id_filt_grupo = $_POST['id_filt_grupo'];
    $id_filt_empresa = $_POST['id_filt_empresa'];
    $id_filt_regra = $_POST['id_filt_regra'];
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s');
    foreach ($dados as $key => $value) {
        $id_empresa = $dados[$key]["id_empresa"];
        $id_regra = $dados[$key]["id_regra"];
        $valor = $dados[$key]["valor"];
        $simbolo = $dados[$key]["simbolo"];
        $id_banco = $dados[$key]["id_banco"];
        $query_update_taxa = $connection->prepare("INSERT into regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:regra, :emp, :data, :val, :simb, :banco)");
        $query_update_taxa->execute(array(':regra' => $id_regra, ':emp' => $id_empresa, ':data' => $data, ':val' => $valor, ':simb' => $simbolo, ':banco' => $id_banco));

        //-- Alteração necessária após Conta a Prazo
        if ($id_regra == '41') {
            $query_juro = $connection->prepare("SELECT j.id_juro, j.montante, j.tx_irc FROM juros_dp j WHERE pago='0'");
            $query_juro->execute();
            $num_juro = $query_juro->rowCount();
            if ($num_juro > 0) {
                while ($linha_juro = $query_juro->fetch(PDO::FETCH_ASSOC)) {
                    $id = $linha_juro['id_juro'];
                    $montante = str_replace(",", ".", $linha_juro['montante']);
                    $tx_juro_m = pow((1 + $valor / 100), (1 / 12)) - 1;
                    $juro = floatval($montante * $tx_juro_m);
                    $tx_irc = str_replace(",", ".", $linha_juro['tx_irc']);
                    $irc = $juro * $tx_irc / 100;
                    $juro_liq = floatval($juro - $irc);

                    $query_update_juro = $connection->prepare("UPDATE juros_dp j SET j.tx_juro = :tx_juro, valor = :valor, irc = :irc WHERE j.id_juro = :id_juro");
                    $query_update_juro->execute(array(':tx_juro' => $valor, ':valor' => $juro_liq, ':irc' => $irc, ':id_juro' => $id));
                }
            }
        } elseif ($id_regra == '42') {
            $query_juro = $connection->prepare("SELECT j.id_juro, j.montante, j.tx_juro FROM juros_dp j WHERE pago='0'");
            $query_juro->execute();
            $num_juro = $query_juro->rowCount();
            if ($num_juro > 0) {
                while ($linha_juro = $query_juro->fetch(PDO::FETCH_ASSOC)) {
                    $id = $linha_juro['id_juro'];
                    $montante = str_replace(",", ".", $linha_juro['montante']);
                    $tx_juro = str_replace(",", ".", $linha_juro['tx_juro']);
                    $juro = floatval($montante * $tx_juro);
                    $irc = $juro * $valor / 100;
                    $juro_liq = floatval($juro - $irc);

                    $query_update_juro = $connection->prepare("UPDATE juros_dp j SET valor = :valor, j.tx_irc = :tx_irc, irc = :irc, date_reg = NOW() WHERE j.id_juro = :id_juro");
                    $query_update_juro->execute(array(':valor' => $juro_liq, ':tx_irc' => $valor, ':irc' => $irc, ':id_juro' => $id));
                }
            }
        }
        //--
    }
    
    if ($id_filt_grupo == 0) {
        $opr_gr = '>';
    } elseif ($id_filt_grupo > 0) {
        $opr_gr = '=';
    }
    if ($id_filt_empresa == 0) {
        $opr_emp = '>';
    } elseif ($id_filt_empresa > 0) {
        $opr_emp = '=';
    }
    if ($id_filt_regra == 0) {
        $opr_r = '>';
    } elseif ($id_filt_regra > 0) {
        $opr_r = '=';
    }
    
    /* $query_taxas = $connection->prepare("SELECT emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador");
    $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'])); */
    $query_taxas = loadby($connection, $opr_r, $id_filt_regra, $opr_emp, $id_filt_empresa, $opr_gr, $id_filt_grupo);
    while ($linha = $query_taxas->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_regra' => $linha['id_regra'], 'empresa' => $linha['empresa'], 'id_banco' => $linha['id_banco'], 'banco' => $linha['banco'], 'nome_regra' => $linha['nome_regra'], 'data' => $linha['data'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    logClicks($connection, "68");
} elseif ($_POST['tipo'] == "ver_categorias") {
    $query_categoria = $connection->prepare("SELECT id, designacao FROM familia WHERE parent=:parent ORDER BY designacao");
    $query_categoria->execute(array(':parent' => $_POST['id_categ']));
    $num_linhas = $query_categoria->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
    }
    logClicks($connection, "78");
} elseif ($_POST['tipo'] == "guardar_categoria") {
    $query_sel_categ = $connection->prepare("SELECT designacao FROM familia WHERE parent IS NULL AND designacao LIKE :desig");
    $query_sel_categ->execute(array(':desig' => $_POST['nome']));
    $num_linhas = $query_sel_categ->rowCount();

    if ($num_linhas > 0) {
        $arr = array('sucesso' => false, 'mensagem' => "O nome da categoria já existe");
    } else {
        $query_ins_categ = $connection->prepare("INSERT INTO familia (designacao) VALUES (:nome)");
        $query_ins_categ->execute(array(':nome' => $_POST['nome']));
        $arr = array('sucesso' => true);
    }
    logClicks($connection, "76");
} elseif ($_POST['tipo'] == "guardar_subcategoria") {
    $query_sel_subcateg = $connection->prepare("SELECT designacao FROM familia WHERE parent=:parent AND designacao LIKE :desig");
    $query_sel_subcateg->execute(array(':desig' => $_POST['nome'], ':parent' => $_POST['id_cat']));
    $num_linhas = $query_sel_subcateg->rowCount();

    if ($num_linhas > 0) {
        $arr = array('sucesso' => false, 'mensagem' => "O nome da subcategoria já existe");
    } else {
        $query_ins_subcat = $connection->prepare("INSERT INTO familia (designacao, parent) VALUES (:nome, :parent)");
        $query_ins_subcat->execute(array(':nome' => $_POST['nome'], ':parent' => $_POST['id_cat']));

        $query_categoria = $connection->prepare("SELECT id, designacao FROM familia WHERE parent IS NULL ORDER BY designacao");
        $query_categoria->execute();
        $num_linhas = $query_categoria->rowCount();

        if ($num_linhas > 0) {
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
        }
    }
    logClicks($connection, "77");
} elseif ($_POST['tipo'] == "guardar_familia") {
    $query_sel_familia = $connection->prepare("SELECT designacao FROM familia WHERE parent=:parent AND designacao LIKE :desig");
    $query_sel_familia->execute(array(':desig' => $_POST['nome'], ':parent' => $_POST['id_subcat']));
    $num_linhas = $query_sel_familia->rowCount();

    if ($num_linhas > 0) {
        $arr = array('sucesso' => false, 'mensagem' => "O nome da família já existe");
    } else {
        $query_ins_familia = $connection->prepare("INSERT INTO familia (designacao, parent) VALUES (:nome, :parent)");
        $query_ins_familia->execute(array(':nome' => $_POST['nome'], ':parent' => $_POST['id_subcat']));

        $query_categoria = $connection->prepare("SELECT id, designacao FROM familia WHERE parent IS NULL ORDER BY designacao");
        $query_categoria->execute();
        $num_linhas = $query_categoria->rowCount();

        if ($num_linhas > 0) {
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
        }
    }
    logClicks($connection, "79");
} elseif ($_POST['tipo'] == "ver_subcategorias") {
    $query_familia = $connection->prepare("SELECT id, designacao FROM familia WHERE parent=:parent ORDER BY designacao");
    $query_familia->execute(array(':parent' => $_POST['id_subcateg']));
    $num_linhas = $query_familia->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_familia->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
    }
    logClicks($connection, "93");
} elseif ($_POST['tipo'] == "guardar_produto") {
    $query_sel_produto = $connection->prepare("SELECT nome FROM produto WHERE familia=:parent AND nome LIKE :desig");
    $query_sel_produto->execute(array(':desig' => $_POST['nome'], ':parent' => $_POST['id_familia']));
    $num_linhas = $query_sel_produto->rowCount();

    if ($num_linhas > 0) {
        $arr = array('sucesso' => false, 'mensagem' => "O nome do produto já existe");
    } else {
        $query_ins_produto = $connection->prepare("INSERT INTO produto (nome, familia, descricao) VALUES (:nome, :parent, :desc)");
        $query_ins_produto->execute(array(':nome' => $_POST['nome'], ':parent' => $_POST['id_familia'], ':desc' => $_POST['descricao']));
        $query_sel_prod = $connection->prepare("SELECT p.id FROM produto p WHERE p.nome=:nome AND p.`familia`=:familia AND p.descricao=:desc");
        $query_sel_prod->execute(array(':nome' => $_POST['nome'], ':familia' => $_POST['id_familia'], ':desc' => $_POST['descricao']));
        $linha_prod = $query_sel_prod->fetch(PDO::FETCH_ASSOC);
        $query_ins_stock = $connection->prepare("INSERT INTO fp_stock (id_fornecedor, id_produto, preco) VALUES (:forn, :prod, :preco)");
        $query_ins_stock->execute(array(':forn' => $_POST['id_fornecedor'], ':prod' => $linha_prod['id'], ':preco' => $_POST['preco']));
        $query_sel_regra = $connection->prepare("SELECT r.valor, r.simbolo FROM regra r WHERE r.id_regra=:id_regra GROUP BY r.id_regra");
        $query_sel_regra->execute(array(':id_regra' => $_POST['iva']));
        $linha_regra = $query_sel_regra->fetch(PDO::FETCH_ASSOC);
        $data = date('Y-m-d H:i:s');
        $query_regra_prod = $connection->prepare("INSERT INTO regra_produto (id_produto, id_regra, data, valor, simbolo) VALUES (:prod, :regra, :data, :val, :simb)");
        $query_regra_prod->execute(array(':prod' => $linha_prod['id'], ':regra' => $_POST['iva'], ':data' => $data, ':val' => $linha_regra['valor'], ':simb' => $linha_regra['simbolo']));

        $arr = array('sucesso' => true);
    }
    logClicks($connection, "94");
} elseif ($_POST['tipo'] == "guardar_data") {
    $query_sel_data_g = $connection->prepare("SELECT cal.id_grupo FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND cal.id_grupo=:id_grupo AND cal.mes=:mes AND cal.ano=:ano GROUP BY cal.id_cal");
    $query_sel_data_g->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo'], ':mes' => $_POST['mes'], ':ano' => $_POST['ano']));
    $count = $query_sel_data_g->rowCount();

    if ($count != 0) {
        $arr = array('sucesso' => false, 'mensagem' => "Já existe data definida para este período de tempo");
    } else {
        // $query_sel_data = $connection->prepare("SELECT cal.id_grupo FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo AND ((CONCAT(cal.data_inicio, ' ', cal.hora_inicio) >= :data_inicio AND CONCAT(cal.data_fim, ' ', cal.hora_fim) <= :data_fim) OR (:data_inicio2 BETWEEN CONCAT(cal.data_inicio, ' ', cal.hora_inicio) AND CONCAT(cal.data_fim, ' ', cal.hora_fim)) OR (:data_fim2 BETWEEN CONCAT(cal.data_inicio, ' ', cal.hora_inicio) AND CONCAT(cal.data_fim, ' ', cal.hora_fim))) OR cal.cor=:cor GROUP BY cal.id_cal");
        // $query_sel_data->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':cor' => "#" . $_POST['cor'], ':id_grupo' => $_POST['id_grupo'], ':data_inicio' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim' => date("Y-m-d H:i:s", strtotime($_POST['data_f'])), ':data_inicio2' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim2' => date("Y-m-d H:i:s", strtotime($_POST['data_f']))));
        $query_sel_data = $connection->prepare("SELECT cal.id_grupo FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo AND ((CONCAT(cal.data_inicio, ' ', cal.hora_inicio) >= :data_inicio AND CONCAT(cal.data_fim, ' ', cal.hora_fim) <= :data_fim) OR (:data_inicio2 BETWEEN CONCAT(cal.data_inicio, ' ', cal.hora_inicio) AND CONCAT(cal.data_fim, ' ', cal.hora_fim)) OR (:data_fim2 BETWEEN CONCAT(cal.data_inicio, ' ', cal.hora_inicio) AND CONCAT(cal.data_fim, ' ', cal.hora_fim))) GROUP BY cal.id_cal");
        $query_sel_data->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo'], ':data_inicio' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim' => date("Y-m-d H:i:s", strtotime($_POST['data_f'])), ':data_inicio2' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim2' => date("Y-m-d H:i:s", strtotime($_POST['data_f']))));
        $num_linhas = $query_sel_data->rowCount();

        if ($num_linhas != 0) {
            $arr = array('sucesso' => false, 'mensagem' => "Os parâmetros que escolheu entram em conflito com outros já existentes");
        } else {
            $data_i_arr = explode(" ", $_POST['data_i']);
            $data_f_arr = explode(" ", $_POST['data_f']);
            $query_ins_data = $connection->prepare("INSERT INTO calendario (id_grupo, mes, ano, data_inicio, hora_inicio, data_fim, hora_fim, cor) VALUES (:id_grupo, :mes, :ano, :data_inicio, :hora_inicio, :data_fim, :hora_fim, :cor)");
            $query_ins_data->execute(array(':id_grupo' => $_POST['id_grupo'], ':mes' => $_POST['mes'], ':ano' => $_POST['ano'], ':data_inicio' => date("Y-m-d", strtotime($data_i_arr[0])), ':hora_inicio' => $data_i_arr[1], ':data_fim' => date("Y-m-d", strtotime($data_f_arr[0])), ':hora_fim' => $data_f_arr[1], ':cor' => "#" . $_POST['cor']));
            $arr = array('sucesso' => true);
        }
    }
    /* Atualizar dados calendário CACHE */
    data_virtual_db($connection);
    
    logClicks($connection, "34");
} elseif ($_POST['tipo'] == "dados_calendario") {
    // $query_calendario = $connection->prepare("SELECT cal.id_cal, g.id, g.nome AS grupo, cal.mes, cal.ano, date_format(cal.data_inicio, '%d-%m-%Y') AS data_inicio, cal.hora_inicio, date_format(cal.data_fim, '%d-%m-%Y') AS data_fim, cal.hora_fim, cal.cor FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND id_cal=:id_cal GROUP BY cal.id_cal");
    $query_calendario = $connection->prepare("SELECT cal.id_cal, g.id, g.nome AS grupo, cal.mes, cal.ano, date_format(cal.data_inicio, '%d-%m-%Y') AS data_inicio, cal.hora_inicio, date_format(cal.data_fim, '%d-%m-%Y') AS data_fim, cal.hora_fim, cal.cor FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador AND id_cal=:id_cal GROUP BY cal.id_cal");
    $query_calendario->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_cal' => $_POST['id_cal']));
    $linha = $query_calendario->fetch(PDO::FETCH_ASSOC);

    $arr = array('sucesso' => true, 'id_cal' => $linha['id_cal'], 'id_grupo' => $linha['id'], 'nome_grupo' => $linha['grupo'], 'mes' => $linha['mes'], 'ano' => $linha['ano'], 'data_inicio' => $linha['data_inicio'], 'hora_inicio' => $linha['hora_inicio'], 'data_fim' => $linha['data_fim'], 'hora_fim' => $linha['hora_fim'], 'cor' => $linha['cor']);
    logClicks($connection, "36");
} elseif ($_POST['tipo'] == "update_data") {
    //-- APARENTEMENTE ERRADO 20190221 -- $query_sel_data = $connection->prepare("SELECT cal.id_cal FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND cor=:cor AND cal.id_cal <> :id_cal1 OR (CONCAT(data_inicio, ' ', hora_inicio) >= :data_inicio AND CONCAT(data_fim, ' ', hora_fim) <= :data_fim AND cal.id_cal <> :id_cal2) OR (:data_inicio2 BETWEEN CONCAT(data_inicio, ' ', hora_inicio) AND CONCAT(data_fim, ' ', hora_fim) AND cal.id_cal <> :id_cal3) OR (:data_fim2 BETWEEN CONCAT(data_inicio, ' ', hora_inicio) AND CONCAT(data_fim, ' ', hora_fim) AND cal.id_cal <> :id_cal4) GROUP BY cal.id_cal");
    // $query_sel_data->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':cor' => "#" . $_POST['cor'], ':data_inicio' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim' => date("Y-m-d H:i:s", strtotime($_POST['data_f'])), ':data_inicio2' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim2' => date("Y-m-d H:i:s", strtotime($_POST['data_f'])), ':id_cal1' => $_POST['id_cal'], ':id_cal2' => $_POST['id_cal'], ':id_cal3' => $_POST['id_cal'], ':id_cal4' => $_POST['id_cal']));
    
    $query_sel_data = $connection->prepare("SELECT cal.id_cal FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id WHERE cal.id_grupo=:id_grupo AND cal.id_cal<>:id_cal /* AND cor=':cor' */ AND ((CONCAT(data_inicio, ' ', hora_inicio) >= :data_inicio1 AND CONCAT(data_fim, ' ', hora_fim) <= :data_fim1) OR (:data_inicio2 BETWEEN CONCAT(data_inicio, ' ', hora_inicio) AND CONCAT(data_fim, ' ', hora_fim)) OR (:data_fim2 BETWEEN CONCAT(data_inicio, ' ', hora_inicio) AND CONCAT(data_fim, ' ', hora_fim))) GROUP BY cal.id_cal");
    $query_sel_data->execute(array(':id_grupo' => $_POST['id_grupo'], ':id_cal' => $_POST['id_cal'], /* ':cor' => "#" . $_POST['cor'],*/ ':data_inicio1' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim1' => date("Y-m-d H:i:s", strtotime($_POST['data_f'])), ':data_inicio2' => date("Y-m-d H:i:s", strtotime($_POST['data_i'])), ':data_fim2' => date("Y-m-d H:i:s", strtotime($_POST['data_f']))));
    $num_linhas = $query_sel_data->rowCount();
    if ($num_linhas != 0) {
        $arr = array('sucesso' => false, 'mensagem' => "Os parâmetros que escolheu entram em conflito com outros já existentes");
    } else {
        $data_i_arr = explode(" ", $_POST['data_i']);
        $data_f_arr = explode(" ", $_POST['data_f']);
        $query_ins_data = $connection->prepare("UPDATE calendario SET id_grupo=:id_grupo, mes=:mes, ano=:ano, data_inicio=:data_inicio, hora_inicio=:hora_inicio, data_fim=:data_fim, hora_fim=:hora_fim, cor=:cor WHERE id_cal=:id_cal");
        $query_ins_data->execute(array(':id_grupo' => $_POST['id_grupo'], ':mes' => $_POST['mes'], ':ano' => $_POST['ano'], ':data_inicio' => date("Y-m-d", strtotime($data_i_arr[0])), ':hora_inicio' => $data_i_arr[1], ':data_fim' => date("Y-m-d", strtotime($data_f_arr[0])), ':hora_fim' => $data_f_arr[1], ':cor' => "#" . $_POST['cor'], ':id_cal' => $_POST['id_cal']));
        $arr = array('sucesso' => true);
    }
    /* Atualizar dados calendário CACHE */
    data_virtual_db($connection);
    
    logClicks($connection, "37");
} elseif ($_POST['tipo'] == "apagar_data") {
    $query_apagar_data = $connection->prepare("DELETE FROM calendario WHERE id_cal=:id_cal");
    $query_apagar_data->execute(array(':id_cal' => $_POST['id_cal']));

    // $query_calendario = $connection->prepare("SELECT cal.id_cal, g.id, g.nome AS grupo, cal.mes, cal.ano FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador ORDER BY g.nome ASC, cal.ano ASC, cal.mes ASC");
    // $query_calendario->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
//    $query_calendario = $connection->prepare("SELECT c.id_cal, c.id_grupo AS id, grupos_active.nome AS grupo, c.mes, c.ano FROM (SELECT g.nome, last_estado_grupos.* FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id WHERE last_estado_grupos.estado='1') AS grupos_active INNER JOIN user_grupo ug ON grupos_active.id_grupo=ug.id_grupo INNER JOIN calendario c ON grupos_active.id_grupo=c.id_grupo WHERE ug.id_user=:id_utilizador ORDER BY grupos_active.nome ASC, c.ano ASC, c.mes ASC");// estava esta
    $query_calendario = $connection->prepare("SELECT c.id_cal,c.id_grupo AS id,g.nome AS grupo,c.mes,c.ano FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON last_estado_grupos.id_grupo=ug.id_grupo INNER JOIN calendario c ON last_estado_grupos.id_grupo=c.id_grupo WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC, c.ano ASC, c.mes ASC");// meti esta
    $query_calendario->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    $num_linhas = $query_calendario->rowCount();

    if ($num_linhas != 0) {
        while ($linha = $query_calendario->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_cal' => $linha['id_cal'], 'id_grupo' => $linha['id'], 'nome_grupo' => $linha['grupo'], 'mes' => conv_mes($linha['mes']), 'ano' => $linha['ano']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    /* Atualizar dados calendário CACHE */
    data_virtual_db($connection);
    
    logClicks($connection, "38");
} elseif ($_POST['tipo'] == "outras_op") {
    $dados = json_decode($_POST['destinatario'], true);
    $valor = $_POST["valor"];
    $descricao = $_POST["descricao"];
    $op = $_POST["op"];

    foreach ($dados as $key => $value) {
        $destinatario = $dados[$key]["destinatario"];
        $data_v = date("Y-m-d H:i:s", strtotime($dados[$key]["data_v"]));
        //$query_saldo = $connection->prepare("SELECT c.id, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa = emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_saldo = $connection->prepare("SELECT c.id, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco = b.id INNER JOIN empresa emp ON c.id_empresa = emp.id_empresa INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND c.tipo_conta='ordem' AND emp.id_empresa=:id_empresa ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_saldo->execute(array(':id_empresa' => $destinatario));
        $linha_saldo = $query_saldo->fetch(PDO::FETCH_ASSOC);

        if ($op == 0) {
            $query_insert_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, debito, saldo_controlo, saldo_contab, saldo_disp, ordenante) VALUES (:id_conta, :data, :tipo, :desc, :deb, :saldo_ct, :saldo_cb, :saldo_d, :ordenante)");
            $query_insert_mov->execute(array(':id_conta' => $linha_saldo['id'], ':data' => $data_v, ':tipo' => "OOP", ':desc' => $descricao, ':deb' => $valor, ':saldo_ct' => $linha_saldo['saldo_controlo'] - $valor, ':saldo_cb' => $linha_saldo['saldo_contab'] - $valor, ':saldo_d' => $linha_saldo['saldo_disp'] - $valor, ':ordenante' => 2));
        } else {
            $query_insert_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp, ordenante) VALUES (:id_conta, :data, :tipo, :desc, :cre, :saldo_ct, :saldo_cb, :saldo_d, :ordenante)");
            $query_insert_mov->execute(array(':id_conta' => $linha_saldo['id'], ':data' => $data_v, ':tipo' => "OOP", ':desc' => $descricao, ':cre' => $valor, ':saldo_ct' => $linha_saldo['saldo_controlo'] + $valor, ':saldo_cb' => $linha_saldo['saldo_contab'] + $valor, ':saldo_d' => $linha_saldo['saldo_disp'] + $valor, ':ordenante' => 2));
        }
    }
    $arr = array('sucesso' => true);
    logClicks($connection, "72");
} elseif ($_POST['tipo'] == "ver_fatura_int") {
    if ($_SESSION['admin'] == "0") {
        $query_faturas = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.desconto, enc.iva, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND enc.id=:id");
        $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id' => $_POST['id_fatura']));
    } else {
        $query_faturas = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.desconto, enc.iva, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador AND enc.id=:id");
        $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id' => $_POST['id_fatura']));
    }
    $linha = $query_faturas->fetch(PDO::FETCH_ASSOC);
    if ($linha['pago'] == '0') {
        $pago = "Não";
    } else {
        $pago = "Sim";
    }
    $arr = array('sucesso' => true, 'id' => $linha['id'], 'ref' => $linha['ref'], 'nome' => $linha['nome'], 'total' => $linha['total'], 'data' => $linha['data'], 'desconto' => $linha['desconto'], 'iva' => $linha['iva'], 'pago' => $pago);
    logClicks($connection, "103");
} elseif ($_POST['tipo'] == "ver_fatura_ext") {
    if ($_SESSION['admin'] == "0") {
        $query_faturas = $connection->prepare("SELECT fa.id_factoring, date_format(fa.`data`, '%d-%m-%Y') AS data_fact, fa.valor AS valor_fact, fa.tempo, fa.recurso, fa.comissao_valor, fa.seguro_valor, fa.juros_valor, fa.valor_recebido FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND fa.id_factoring=:id_factoring");
        $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_factoring' => $_POST['id_factoring']));
    } else {
        $query_faturas = $connection->prepare("SELECT fa.id_factoring, date_format(fa.`data`, '%d-%m-%Y') AS data_fact, fa.valor AS valor_fact, fa.tempo, fa.recurso, fa.comissao_valor, fa.seguro_valor, fa.juros_valor, fa.valor_recebido FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador AND fa.id_factoring=:id_factoring");
        $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_factoring' => $_POST['id_factoring']));
    }
    $linha = $query_faturas->fetch(PDO::FETCH_ASSOC);
    $arr = array('sucesso' => true, 'id_fact' => $linha['id_factoring'], 'data_fact' => $linha['data_fact'], 'valor_fact' => $linha['valor_fact'], 'tempo' => $linha['tempo'], 'recurso' => $linha['recurso'], 'comissao_valor' => $linha['comissao_valor'], 'seguro_valor' => $linha['seguro_valor'], 'juros_valor' => $linha['juros_valor'], 'valor_recebido' => $linha['valor_recebido']);
} elseif ($_POST['tipo'] == "rem_fatura_int") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_fatura = $dados[$key]["id"];
        $query_del_fat = $connection->prepare("DELETE FROM encomenda WHERE id=:id");
        $query_del_fat->execute(array(':id' => $id_fatura));
    }
    if ($_POST['id_empresa'] == 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 1)) {
        $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(enc.`data`) ASC, time(enc.`data`) ASC");
        $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } else if ($_POST['id_empresa'] == 0 && $_POST['id_filtro'] == 2) {
        $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
        $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } else {
        $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
        $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    }
    $num_linhas = $query_faturas_int->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_faturas_int->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'ref' => $linha['ref'], 'nome' => $linha['nome'], 'total' => $linha['total'], 'data' => $linha['data'], 'pago' => $linha['pago']);
        }
        $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
        $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
    } else {
        if ($_POST['id_empresa'] > 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 1)) {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(enc.`data`) ASC, time(enc.`data`) ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } elseif ($_POST['id_empresa'] > 0 && $_POST['id_filtro'] == 2) {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        $num_linhas = $query_faturas_int->rowCount();
        if ($num_linhas > 0) {
            while ($linha = $query_faturas_int->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'ref' => $linha['ref'], 'nome' => $linha['nome'], 'total' => $linha['total'], 'data' => $linha['data'], 'pago' => $linha['pago']);
            }
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
                $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true);
        }
    }
    logClicks($connection, "102");
} elseif ($_POST['tipo'] == "rem_fatura_ext") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_fatura = $dados[$key]["id_fatura"];
        $query_sel_re = $connection->prepare("SELECT f.valor AS valor_fat, re.valor AS ult_valor, r.id_regra, emp.id_empresa, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON re.id_empresa=emp.id_empresa INNER JOIN fatura f ON emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND r.nome_regra LIKE :nome_regra AND f.id_fatura=:id_fatura ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
        $query_sel_re->execute(array(':nome_regra' => "Plafond (faturas)", ':id_fatura' => $id_fatura));
        $linha_re = $query_sel_re->fetch(PDO::FETCH_ASSOC);
        $valor = $linha_re['valor_fat'] + $linha_re['ult_valor'];
        $date = new DateTime();
        $intervalo = new DateInterval('PT' . $key . 'S');
        $date->add($intervalo);
        $data = $date->format('Y-m-d H:i:s');
        $query_update_taxa = $connection->prepare("INSERT into regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:regra, :emp, :data, :val, :simb, :banco)");
        $query_update_taxa->execute(array(':regra' => $linha_re['id_regra'], ':emp' => $linha_re['id_empresa'], ':data' => $data, ':val' => $valor, ':simb' => $linha_re['simbolo'], ':banco' => $linha_re['id_banco']));
        $query_del_fat = $connection->prepare("DELETE FROM fatura WHERE id_fatura=:id");
        $query_del_fat->execute(array(':id' => $id_fatura));
    }
    if ($_POST['id_empresa'] == 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 1)) {
        $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(f.data_virtual) ASC, time(f.data_virtual) ASC");
        $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } else if ($_POST['id_empresa'] == 0 && $_POST['id_filtro'] == 2) {
        $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
        $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } else {
        $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
        $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    }

    $num_linhas = $query_faturas_ext->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_faturas_ext->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_fatura' => $linha['id_fatura'], 'num_fatura' => $linha['num_fatura'], 'cliente' => $linha['cliente'], 'valor_fatura' => $linha['valor_fatura'], 'data_fatura' => $linha['data_fatura'], 'nome' => $linha['nome'], 'id_factoring' => $linha['id_factoring']);
        }
        $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
        $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
    } else {
        if ($_POST['id_empresa'] > 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 1)) {
            $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(f.data_virtual) ASC, time(f.data_virtual) ASC");
            $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } elseif ($_POST['id_empresa'] > 0 && $_POST['id_filtro'] == 2) {
            $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        $num_linhas = $query_faturas_ext->rowCount();
        if ($num_linhas > 0) {
            while ($linha = $query_faturas_ext->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id_fatura' => $linha['id_fatura'], 'num_fatura' => $linha['num_fatura'], 'cliente' => $linha['cliente'], 'valor_fatura' => $linha['valor_fatura'], 'data_fatura' => $linha['data_fatura'], 'nome' => $linha['nome'], 'id_factoring' => $linha['id_factoring']);
            }
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
                $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true);
        }
    }
    logClicks($connection, "106");
} elseif ($_POST['tipo'] == "afet_emp_cat") {
    $query_categ = $connection->prepare("SELECT f.id FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON a.id=af.id_atividade INNER JOIN familia f ON af.id_familia=f.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
    $query_categ->execute(array(':id_empresa' => $_POST['id_empresa']));
    $num_linhas = $query_categ->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_categ->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "89");
} elseif ($_POST['tipo'] == "afet_cat_emp") {
    $query_familias = $connection->prepare("SELECT emp.id_empresa FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON a.id=af.id_atividade INNER JOIN familia f ON af.id_familia=f.id WHERE emp.ativo='1' AND f.id=:id_categoria");
    $query_familias->execute(array(':id_categoria' => $_POST['id_categoria']));
    $num_linhas = $query_familias->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_familias->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id_empresa']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "90");
} elseif ($_POST['tipo'] == "afet_ati_cat") {
    $query_atividade = $connection->prepare("SELECT f.id FROM atividade a INNER JOIN atividade_familia af ON a.id=af.id_atividade INNER JOIN familia f ON af.id_familia=f.id WHERE a.id=:id");
    $query_atividade->execute(array(':id' => $_POST['id_atividade']));
    $num_linhas = $query_atividade->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_atividade->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "81");
} elseif ($_POST['tipo'] == "afet_cat_ati") {
    $query_categoria = $connection->prepare("SELECT a.id FROM atividade a INNER JOIN atividade_familia af ON a.id=af.id_atividade INNER JOIN familia f ON af.id_familia=f.id WHERE f.id=:id");
    $query_categoria->execute(array(':id' => $_POST['id_categoria']));
    $num_linhas = $query_categoria->rowCount();

    if ($num_linhas > 0) {
        while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "83");
} elseif ($_POST['tipo'] == "alt_ati_fam") {
    if ($_POST['checked'] == "false") {
        $query_insert_ati_fam = $connection->prepare("INSERT INTO atividade_familia (id_atividade, id_familia) VALUES (:id_atividade, :id_familia)");
        $query_insert_ati_fam->execute(array(':id_atividade' => $_POST['id_atividade'], ':id_familia' => $_POST['id_categoria']));
        $arr = array('sucesso' => true);
    } else if ($_POST['checked'] == "true") {
        $query_delete_ati_fam = $connection->prepare("DELETE FROM atividade_familia WHERE id_atividade=:id_atividade AND id_familia=:id_familia");
        $query_delete_ati_fam->execute(array(':id_atividade' => $_POST['id_atividade'], ':id_familia' => $_POST['id_categoria']));
        $arr = array('sucesso' => true);
    }
    logClicks($connection, "82");
} elseif ($_POST['tipo'] == "del_entregas") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_declaracao = $dados[$key]["id"];
        $query_sel_file = $connection->prepare("SELECT ficheiro FROM entrega WHERE id=:id");
        $query_sel_file->execute(array(':id' => $id_declaracao));
        $linha_dados = $query_sel_file->fetch(PDO::FETCH_ASSOC);
        $query_del_declaracao = $connection->prepare("DELETE FROM entrega WHERE id=:id");
        $query_del_declaracao->execute(array(':id' => $id_declaracao));
        unlink($linha_dados['ficheiro']);
    }

    if ($_POST['id_empresa'] == 0 && $_POST['id_filtro'] == 1) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(e.`data`) ASC, time(e.`data`) DESC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] == 0 && $_POST['id_filtro'] == 2 && $_POST['id_tipo'] == 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] == 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 3)) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_filtro'] == 2 && $_POST['id_tipo'] == 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_empresa' => $_POST['id_empresa'], ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_filtro'] == 3) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_empresa' => $_POST['id_empresa'], ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_filtro'] == 2 && $_POST['id_tipo'] != 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa AND t.id=:id_tipo ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_empresa' => $_POST['id_empresa'], ':id_utilizador' => $_SESSION['id_utilizador'], ':id_tipo' => $_POST['id_tipo']));
    } elseif ($_POST['id_empresa'] == 0 && $_POST['id_filtro'] == 2 && $_POST['id_tipo'] != 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND t.id=:id_tipo ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_tipo' => $_POST['id_tipo']));
    }

    $num_linhas = $query_entregas->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'data' => $linha['data'], 'tipo' => $linha['designacao'], 'pago' => $linha['pago'], 'f_prazo' => $linha['f_prazo'], 'valor' => $linha['valor'], 'empresa' => $linha['nome']);
        }
        $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
        $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
        }
        if ($_POST['id_empresa'] == 0) {
            $query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY te.designacao");
            $query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY te.designacao");
            $query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
        }
        while ($row = $query_tipo_entrega->fetch(PDO::FETCH_ASSOC)) {
            $arr_tipo[] = array('id' => $row['id'], 'nome' => $row['designacao']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'dados_ti' => $arr_tipo, 'moeda' => $linha_moeda['simbolo']);
    } else {
        if (($_POST['id_empresa'] > 0 || $_POST['id_tipo'] > 0) && $_POST['id_filtro'] == 2) {
            $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY t.designacao ASC");
            $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } elseif (($_POST['id_empresa'] > 0 || $_POST['id_tipo'] > 0) && $_POST['id_filtro'] == 3) {
            $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC, t.designacao ASC");
            $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }

        $num_linhas = $query_entregas->rowCount();
        if ($num_linhas > 0) {
            while ($linha = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'data' => $linha['data'], 'tipo' => $linha['designacao'], 'pago' => $linha['pago'], 'f_prazo' => $linha['f_prazo'], 'valor' => $linha['valor'], 'empresa' => $linha['nome']);
            }
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
                $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
            }
            if ($_POST['id_empresa'] == 0) {
                $query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY te.designacao");
                $query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            } else {
                $query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY te.designacao");
                $query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
            }
            while ($row = $query_tipo_entrega->fetch(PDO::FETCH_ASSOC)) {
                $arr_tipo[] = array('id' => $row['id'], 'nome' => $row['designacao']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'dados_ti' => $arr_tipo, 'moeda' => $linha_moeda['simbolo']);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true);
        }
    }
    logClicks($connection, "46");
} elseif ($_POST['tipo'] == "del_dec_ret") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_dec_ret = $dados[$key]["id"];
        $query_del_dec_ret = $connection->prepare("DELETE FROM dec_retencao WHERE id=:id");
        $query_del_dec_ret->execute(array(':id' => $id_dec_ret));
    }

    if ($_POST['id_empresa'] == 0 && $_POST['id_filtro'] == 1) {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(dr.`data`) ASC, time(dr.`data`) DESC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] == 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 2)) {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } else {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY date(dr.`data`) ASC, time(dr.`data`) DESC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_empresa' => $_POST['id_empresa'], ':id_utilizador' => $_SESSION['id_utilizador']));
    }

    $num_linhas = $query_dec_ret->rowCount();
    if ($num_linhas > 0) {
        while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'residentes' => $linha_dec_ret['residentes'], 'pago' => $linha_dec_ret['pago'], 'total' => $linha_dec_ret['total'], 'empresa' => $linha_dec_ret['nome']);
        }
        $query_filtro = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN dec_retencao dr ON dr.id_empresa=emp.id_empresa LEFT JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
        $query_filtro->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_filtro = $query_filtro->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $linha_filtro['id_empresa'], 'nome' => $linha_filtro['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
    } else {
        if ($_POST['id_empresa'] > 0 && $_POST['id_filtro'] == 1) {
            $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(dr.`data`) ASC, time(dr.`data`) DESC");
            $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } elseif ($_POST['id_empresa'] > 0 && ($_POST['id_filtro'] == 0 || $_POST['id_filtro'] == 2)) {
            $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        $num_linhas = $query_dec_ret->rowCount();
        if ($num_linhas > 0) {
            while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'residentes' => $linha_dec_ret['residentes'], 'pago' => $linha_dec_ret['pago'], 'total' => $linha_dec_ret['total'], 'empresa' => $linha_dec_ret['nome']);
            }
            $query_filtro = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN dec_retencao dr ON dr.id_empresa=emp.id_empresa LEFT JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_filtro->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            while ($linha_filtro = $query_filtro->fetch(PDO::FETCH_ASSOC)) {
                $arr_filtro[] = array('id' => $linha_filtro['id_empresa'], 'nome' => $linha_filtro['nome']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
        } else {
            $arr = array('sucesso' => true, 'vazio' => true);
        }
    }
    logClicks($connection, "52");
} elseif ($_POST['tipo'] == "filtrar_entregas") {
    if ($_POST['id_empresa'] == 0 && $_POST['id_tipo'] == 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] == 0 && $_POST['id_tipo'] != 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND t.id=:tipo_t ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':tipo_t' => $_POST['id_tipo']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_tipo'] != 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND t.id=:tipo_t AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':tipo_t' => $_POST['id_tipo'], ':id_empresa' => $_POST['id_empresa']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_tipo'] == 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    }

    $num_linhas = $query_entregas->rowCount();
    if ($num_linhas > 0) {
        if ($_POST['id_empresa'] == 0) {
            $query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY te.designacao");
            $query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_tipo_entrega = $connection->prepare("SELECT DISTINCT te.id, te.designacao FROM empresa emp INNER JOIN entrega e ON e.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON e.id_tipo_entrega=te.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY te.designacao");
            $query_tipo_entrega->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
        }
        while ($linha = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'data' => $linha['data'], 'tipo' => $linha['designacao'], 'pago' => $linha['pago'], 'f_prazo' => $linha['f_prazo'], 'valor' => $linha['valor'], 'empresa' => $linha['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "47");
} elseif ($_POST['tipo'] == "filtrar_tipo") {
    if ($_POST['id_empresa'] == 0 && $_POST['id_tipo'] == 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($_POST['id_empresa'] == 0 && $_POST['id_tipo'] != 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND t.id=:tipo_t ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':tipo_t' => $_POST['id_tipo']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_tipo'] != 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND t.id=:tipo_t AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':tipo_t' => $_POST['id_tipo'], ':id_empresa' => $_POST['id_empresa']));
    } elseif ($_POST['id_empresa'] != 0 && $_POST['id_tipo'] == 0) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    }

    $num_linhas = $query_entregas->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'data' => $linha['data'], 'tipo' => $linha['designacao'], 'pago' => $linha['pago'], 'f_prazo' => $linha['f_prazo'], 'valor' => $linha['valor'], 'empresa' => $linha['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "48");
} elseif ($_POST['tipo'] == "fat_int_esp") {
    $arr_grupos = array();
    $arr_empresas = array();

    //Não escolheu nenhum
    if ($_POST['id_grupo'] == 0 && $_POST['id_empresa'] == 0) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        //Restabelecer filtro Grupo
        if ($_SESSION['admin'] == "0") {
            $query_grupos = $connection->prepare("SELECT DISTINCT g.id, g.nome FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador ORDER BY g.nome ASC");
            $query_grupos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_grupos = $connection->prepare("SELECT DISTINCT g.id, g.nome FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo WHERE u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY g.nome ASC");
            $query_grupos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        while ($linha_grupo = $query_grupos->fetch(PDO::FETCH_ASSOC)) {
            $arr_grupos[] = array('id_grupo' => $linha_grupo['id'], 'nome_grupo' => $linha_grupo['nome']);
        }
        //Restabelecer filtro Empresas
        if ($_SESSION['admin'] == "0") {
            $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome AS empresa FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome AS empresa FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'nome_empresa' => $linha_empresa['empresa']);
        }
        //Só escolheu empresa
    } elseif ($_POST['id_grupo'] == 0 && $_POST['id_empresa'] != 0) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
        } else {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
        }
        //Só escolheu grupo
    } elseif ($_POST['id_grupo'] != 0 && $_POST['id_empresa'] == 0) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo']));
        } else {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador AND g.id=:id_grupo ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo']));
        }
        $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1' AND g.id=:id_grupo ORDER BY emp.nome ASC");
        $query_empresas->execute(array(':id_grupo' => $_POST['id_grupo']));
        while ($linha_empresas = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_empresas[] = array('id_empresa' => $linha_empresas['id_empresa'], 'nome_empresa' => $linha_empresas['nome']);
        }
        //Escolheu os 2
    } elseif ($_POST['id_grupo'] != 0 && $_POST['id_empresa'] != 0) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo'], ':id_empresa' => $_POST['id_empresa']));
        } else {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador AND g.id=:id_grupo AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo'], ':id_empresa' => $_POST['id_empresa']));
        }
        /*
          $nlin = $query_faturas_int->rowCount();
          if ($nlin == 0) {
          if($_SESSION['admin'] == "0") {
          $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
          $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
          } else {
          $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY emp.nome ASC");
          $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
          }
          //Restabelecer filtro Grupo
          if($_SESSION['admin'] == "0") {
          $query_grupos = $connection->prepare("SELECT DISTINCT g.id, g.nome FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador ORDER BY g.nome ASC");
          $query_grupos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
          } else {
          $query_grupos = $connection->prepare("SELECT DISTINCT g.id, g.nome FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo WHERE u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY g.nome ASC");
          $query_grupos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
          }
          while ($linha_grupo = $query_grupos->fetch(PDO::FETCH_ASSOC)) {
          $arr_grupos[] = array('id_grupo' => $linha_grupo['id'], 'nome_grupo' => $linha_grupo['nome']);
          }
          //Restabelecer filtro Empresas
          if($_SESSION['admin'] == "0") {
          $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome AS empresa FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
          $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
          } else {
          $query_empresas = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome AS empresa FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY emp.nome ASC");
          $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
          }
          while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
          $arr_empresas[] = array('id_empresa' => $linha_empresa['id_empresa'], 'nome_empresa' => $linha_empresa['empresa']);
          }
          }
         */
    }
    //
    $num_linhas = $query_faturas_int->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_faturas_int->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'ref' => $linha['ref'], 'nome' => $linha['nome'], 'total' => $linha['total'], 'data' => $linha['data'], 'pago' => $linha['pago']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'grupos' => $arr_grupos, 'empresas' => $arr_empresas, 'moeda' => $linha_moeda['simbolo'], 'admin' => $_SESSION['admin']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'admin' => $_SESSION['admin']);
    }
    logClicks($connection, "101");
} elseif ($_POST['tipo'] == "fat_ext_esp") {
    if ($_POST['id_empresa'] == 0) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
            $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
    } else {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa");
            $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
        } else {
            $query_faturas_ext = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador AND emp.id_empresa=:id_empresa");
            $query_faturas_ext->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
        }
    }
    $num_linhas = $query_faturas_ext->rowCount();
    while ($linha = $query_faturas_ext->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id_fatura' => $linha['id_fatura'], 'num_fatura' => $linha['num_fatura'], 'cliente' => $linha['cliente'], 'valor_fatura' => $linha['valor_fatura'], 'data_fatura' => $linha['data_fatura'], 'nome' => $linha['nome'], 'id_factoring' => $linha['id_factoring']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo'], 'admin' => $_SESSION['admin']);
    logClicks($connection, "105");
} elseif ($_POST['tipo'] == "ordenar_entregas") {
    $id_filtro = $_POST['id_filtro'];
    if ($id_filtro == 1) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(e.`data`) ASC, time(e.`data`) DESC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($id_filtro == 2) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($id_filtro == 0 || $id_filtro == 3) {
        $query_entregas = $connection->prepare("SELECT e.id, date_format(e.data, '%d-%m-%Y') AS data, e.ficheiro, t.designacao, IF(strcmp(e.f_prazo, 'S'), 'Não', 'Sim') AS f_prazo, IF(strcmp(e.pago, 1), 'Não', 'Sim') AS pago, e.valor, emp.nome FROM entrega e INNER JOIN tipo_entrega t ON e.id_tipo_entrega=t.id INNER JOIN empresa emp ON e.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC, t.designacao ASC");
        $query_entregas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    }

    $num_linhas = $query_entregas->rowCount();
    if ($num_linhas > 0) {
        $query_filtro = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN entrega e ON e.id_empresa=emp.id_empresa LEFT JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
        $query_filtro->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_filtro = $query_filtro->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $linha_filtro['id_empresa'], 'nome' => $linha_filtro['nome']);
        }
        while ($linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_entregas['id'], 'data' => $linha_entregas['data'], 'ficheiro' => $linha_entregas['ficheiro'], 'tipo' => $linha_entregas['designacao'], 'f_prazo' => $linha_entregas['f_prazo'], 'pago' => $linha_entregas['pago'], 'valor' => $linha_entregas['valor'], 'empresa' => $linha_entregas['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "44");
} elseif ($_POST['tipo'] == "ordenar_fat_int") {
    $id_filtro = $_POST['id_filtro'];
    if ($id_filtro == 0 || $id_filtro == 1) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(enc.`data`) ASC, time(enc.`data`) ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY date(enc.`data`) ASC, time(enc.`data`) ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
    } elseif ($id_filtro == 2) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_faturas_int = $connection->prepare("SELECT enc.id, enc.ref, emp.nome, enc.total, date_format(enc.`data`, '%d-%m-%Y') AS `data`, enc.pago FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas_int->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
    }
    $num_linhas = $query_faturas_int->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_faturas_int->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'ref' => $linha['ref'], 'nome' => $linha['nome'], 'total' => $linha['total'], 'data' => $linha['data'], 'pago' => $linha['pago']);
        }
        if ($_SESSION['admin'] == "0") {
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM encomenda enc INNER JOIN empresa emp ON enc.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN encomenda enc ON enc.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo'], 'admin' => $_SESSION['admin']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "100");
} elseif ($_POST['tipo'] == "ordenar_fat_ext") {
    $id_filtro = $_POST['id_filtro'];
    if ($id_filtro == 0 || $id_filtro == 1) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(f.data_virtual) ASC, time(f.data_virtual) ASC");
            $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY date(f.data_virtual) ASC, time(f.data_virtual) ASC");
            $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
    } elseif ($id_filtro == 2) {
        if ($_SESSION['admin'] == "0") {
            $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor AS valor_fatura, date_format(f.data_virtual, '%d-%m-%Y') AS data_fatura, emp.nome, fa.id_factoring FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa LEFT JOIN factoring fa ON f.id_factoring=fa.id_factoring WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador ORDER BY emp.nome ASC");
            $query_faturas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
    }

    $num_linhas = $query_faturas->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_faturas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_fatura' => $linha['id_fatura'], 'num_fatura' => $linha['num_fatura'], 'cliente' => $linha['cliente'], 'valor_fatura' => $linha['valor_fatura'], 'data_fatura' => $linha['data_fatura'], 'nome' => $linha['nome'], 'id_factoring' => $linha['id_factoring']);
        }
        if ($_SESSION['admin'] == "0") {
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        } else {
            $query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN fatura f ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador");
            $query_empresa->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        }
        while ($row = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $row['id_empresa'], 'nome' => $row['nome']);
        }

        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo'], 'admin' => $_SESSION['admin']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "104");
} elseif ($_POST['tipo'] == "ordenar_dec_ret") {
    $id_filtro = $_POST['id_filtro'];
    if ($id_filtro == 1) {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY date(dr.`data`) ASC, time(dr.`data`) DESC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } elseif ($id_filtro == 0 || $id_filtro == 2) {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, '1'), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    }
    $num_linhas = $query_dec_ret->rowCount();
    if ($num_linhas > 0) {
        $query_filtro = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN dec_retencao dr ON dr.id_empresa=emp.id_empresa LEFT JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
        $query_filtro->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_filtro = $query_filtro->fetch(PDO::FETCH_ASSOC)) {
            $arr_filtro[] = array('id' => $linha_filtro['id_empresa'], 'nome' => $linha_filtro['nome']);
        }
        while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'residentes' => $linha_dec_ret['residentes'], 'pago' => $linha_dec_ret['pago'], 'total' => $linha_dec_ret['total'], 'empresa' => $linha_dec_ret['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_fi' => $arr_filtro, 'moeda' => $linha_moeda['simbolo']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "50");
} elseif ($_POST['tipo'] == "filtrar_dec_ret") {
    if ($_POST['id_empresa'] == 0) {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, 1), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome ASC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    } else {
        $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data, '%d-%m-%Y') AS data, IF(strcmp(dr.residentes, 1), 'Não', 'Sim') AS residentes, IF(strcmp(dr.pago, 1), 'Não', 'Sim') AS pago, dr.total, emp.nome FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND emp.id_empresa=:id_empresa ORDER BY emp.nome ASC");
        $query_dec_ret->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_empresa' => $_POST['id_empresa']));
    }

    $num_linhas = $query_dec_ret->rowCount();
    if ($num_linhas > 0) {
        while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'residentes' => $linha_dec_ret['residentes'], 'pago' => $linha_dec_ret['pago'], 'total' => $linha_dec_ret['total'], 'empresa' => $linha_dec_ret['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "53");
} elseif ($_POST['tipo'] == "dec_ret_detalhes") {
    $query_dec_ret_detalhes = $connection->prepare("SELECT cod.rubrica, dre.zona, dre.valor, emp.nome FROM dec_retencao dr INNER JOIN dec_retencao_empresa dre ON dr.id=dre.id_dec_retencao INNER JOIN codigo cod ON dre.rubrica=cod.id INNER JOIN empresa emp ON emp.id_empresa=dr.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND dr.id=:id_dec_ret");
    $query_dec_ret_detalhes->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_dec_ret' => $_POST['id_dec_ret']));

    while ($linha_dec_ret = $query_dec_ret_detalhes->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('rubrica' => $linha_dec_ret['rubrica'], 'zona' => $linha_dec_ret['zona'], 'valor' => $linha_dec_ret['valor'], 'empresa' => $linha_dec_ret['nome']);
    }
    $arr = array('sucesso' => true, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    logClicks($connection, "51");
} elseif ($_POST['tipo'] == "filtrar_empresas") {
    if ($_POST['id_grupo'] != 0) {
        $query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa, emp.nipc, emp.morada, g.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo");
        $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id_grupo']));

        while ($linha_empresas = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_empresa' => $linha_empresas['id_empresa'], 'empresa' => $linha_empresas['empresa'], 'nipc' => $linha_empresas['nipc'], 'morada' => $linha_empresas['morada'], 'nome' => $linha_empresas['nome']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa, emp.nipc, emp.morada, g.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
        $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));

        while ($linha_empresas = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_empresa' => $linha_empresas['id_empresa'], 'empresa' => $linha_empresas['empresa'], 'nipc' => $linha_empresas['nipc'], 'morada' => $linha_empresas['morada'], 'nome' => $linha_empresas['nome']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "7");
} elseif ($_POST['tipo'] == "del_empresas") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $id_empresa = $dados[$key]["id"];
        $query_empresa = $connection->prepare("UPDATE empresa SET ativo='0' WHERE id_empresa=:id");
        $query_empresa->execute(array(':id' => $id_empresa));
    }

    $query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome AS empresa, emp.nipc, emp.morada, g.nome FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador");
    $query_empresas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    $num_empresas = $query_empresas->rowCount();

    if ($num_empresas > 0) {
        while ($linha_empresas = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_empresa' => $linha_empresas['id_empresa'], 'empresa' => $linha_empresas['empresa'], 'nipc' => $linha_empresas['nipc'], 'morada' => $linha_empresas['morada'], 'nome' => $linha_empresas['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "8");
} elseif ($_POST['tipo'] == "filtrar_produtos") {
    $query_taxa_iva = $connection->prepare("SELECT id_regra, valor, simbolo FROM regra WHERE nome_regra LIKE 'Taxa de IVA%' ORDER BY valor");
    $query_taxa_iva->execute();

    while ($linha_taxa_iva = $query_taxa_iva->fetch(PDO::FETCH_ASSOC)) {
        $arr_taxa[] = array('id_regra' => $linha_taxa_iva['id_regra'], 'valor' => $linha_taxa_iva['valor'], 'simbolo' => $linha_taxa_iva['simbolo']);
    }
    if ($_POST['id_fornecedor'] > 0) {
        $query_produto = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM produto p INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL AND f.id=:id_fornecedor ORDER BY p.nome");
        $query_produto->execute(array(':id_fornecedor' => $_POST['id_fornecedor']));

        while ($linha_produto = $query_produto->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_fornecedor' => $linha_produto['id_fornecedor'], 'nome_abrev' => $linha_produto['nome_abrev'], 'id_produto' => $linha_produto['id_produto'], 'nome' => $linha_produto['nome'], 'descricao' => $linha_produto['descricao'], 'preco' => $linha_produto['preco'], 'valor' => $linha_produto['valor'], 'simbolo' => $linha_produto['simbolo'], 'id_regra' => $linha_produto['id_regra']);
        }
    } else {
        $query_produto = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM produto p INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL ORDER BY f.nome_abrev, p.nome");
        $query_produto->execute();

        while ($linha_produto = $query_produto->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_fornecedor' => $linha_produto['id_fornecedor'], 'nome_abrev' => $linha_produto['nome_abrev'], 'id_produto' => $linha_produto['id_produto'], 'nome' => $linha_produto['nome'], 'descricao' => $linha_produto['descricao'], 'preco' => $linha_produto['preco'], 'valor' => $linha_produto['valor'], 'simbolo' => $linha_produto['simbolo'], 'id_regra' => $linha_produto['id_regra']);
        }
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados, 'dados_taxa' => $arr_taxa, 'moeda' => $linha_moeda['simbolo']);
    logClicks($connection, "96");
} elseif ($_POST['tipo'] == "dados_produtos") {
    $query_produto = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, m.simbolo AS simbolo_moeda, r.id_regra, rp1.valor, rp1.simbolo FROM produto p INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN pais_fornecedor pf ON fp.id_fornecedor=pf.id_fornecedor INNER JOIN pais c ON pf.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL ORDER BY f.nome_abrev, p.nome");
    $query_produto->execute();
    $num_rows = $query_produto->rowCount();
    if ($num_rows > 0) {
        while ($linha = $query_produto->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_fornecedor' => $linha['id_fornecedor'], 'nome_fornecedor' => $linha['nome_abrev'], 'id_produto' => $linha['id_produto'], 'nome' => $linha['nome'], 'descricao' => $linha['descricao'], 'preco' => $linha['preco'], 'moeda' => $linha['simbolo_moeda'], 'id_regra' => $linha['id_regra'], 'valor' => $linha['valor'], 'simbolo' => $linha['simbolo']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
} elseif ($_POST['tipo'] == "g_preco_taxa") {
    $dados = json_decode($_POST['dados'], true);
    foreach ($dados as $key => $value) {
        $tipo_tipo = $dados[$key]["tipo_tipo"];

        if ($tipo_tipo == "nome_fornecedor") {
            $id_fornecedor = $dados[$key]["id_fornecedor"];
            $nome_fornecedor = $dados[$key]["nome_fornecedor"];
        } elseif ($tipo_tipo == "nome_produto" || $tipo_tipo == "descricao_produto" || $tipo_tipo == "nome_descricao") {
            $id_produto = $dados[$key]["id_produto"];
            $nome_produto = $dados[$key]["nome_produto"];
            $descricao_produto = $dados[$key]["descricao"];
            $test123=1;
        } elseif ($tipo_tipo == "preco" || $tipo_tipo == "taxa" || $tipo_tipo == "preco_taxa") {
            $id_fornecedor = $dados[$key]["id_fornecedor"];
            $id_produto = $dados[$key]["id_produto"];
            $id_regra = $dados[$key]["id_regra"];
            $preco = $dados[$key]["preco"];
        }

        if ($tipo_tipo == "preco") {
            $query_update_preco = $connection->prepare("UPDATE fp_stock SET preco=:preco WHERE id_fornecedor=:id_fornecedor AND id_produto=:id_produto");
            $query_update_preco->execute(array(':preco' => $preco, ':id_fornecedor' => $id_fornecedor, ':id_produto' => $id_produto));
        } elseif ($tipo_tipo == "taxa") {
            $query_regra = $connection->prepare("SELECT valor FROM regra r WHERE r.id_regra=:id_regra");
            $query_regra->execute(array(':id_regra' => $id_regra));
            $linha_regra = $query_regra->fetch(PDO::FETCH_ASSOC);
            $data = date('Y-m-d H:i:s');
            $query_update_taxa = $connection->prepare("INSERT into regra_produto (id_produto, id_regra, data, valor, simbolo) VALUES (:prod, :regra, :data, :val, :simb)");
            $query_update_taxa->execute(array(':prod' => $id_produto, ':regra' => $id_regra, ':data' => $data, ':val' => $linha_regra['valor'], ':simb' => "%"));
        } elseif ($tipo_tipo == "preco_taxa") {
            $query_update_preco = $connection->prepare("UPDATE fp_stock SET preco=:preco WHERE id_fornecedor=:id_fornecedor AND id_produto=:id_produto");
            $query_update_preco->execute(array(':preco' => $preco, ':id_fornecedor' => $id_fornecedor, ':id_produto' => $id_produto));
            $query_regra = $connection->prepare("SELECT valor FROM regra r WHERE r.id_regra=:id_regra");
            $query_regra->execute(array(':id_regra' => $id_regra));
            $linha_regra = $query_regra->fetch(PDO::FETCH_ASSOC);
            $data = date('Y-m-d H:i:s');
            $query_update_taxa = $connection->prepare("INSERT into regra_produto (id_produto, id_regra, data, valor, simbolo) VALUES (:prod, :regra, :data, :val, :simb)");
            $query_update_taxa->execute(array(':prod' => $id_produto, ':regra' => $id_regra, ':data' => $data, ':val' => $linha_regra['valor'], ':simb' => "%"));
        }
        //
        elseif ($tipo_tipo == "nome_fornecedor") {
            //Testar se Update não foi feito por linha anterior.
            $query_select_nome = $connection->prepare("SELECT nome_abrev FROM fornecedor f WHERE f.id=:id_fornecedor LIMIT 1");
            $query_select_nome->execute(array(':id_fornecedor' => $id_fornecedor));
            $linha_fornecedor = $query_select_nome->fetch(PDO::FETCH_ASSOC);
            if ($linha_fornecedor['nome_abrev'] != $nome_fornecedor) {
                $query_update_nome = $connection->prepare("UPDATE fornecedor SET nome_abrev=:nome_abrev WHERE id=:id_fornecedor");
                $query_update_nome->execute(array(':nome_abrev' => $nome_fornecedor, ':id_fornecedor' => $id_fornecedor));
            }
        } elseif ($tipo_tipo == "nome_produto") {
            $query_update_nome = $connection->prepare("UPDATE produto SET nome=:nome WHERE id=:id_produto");
            $query_update_nome->execute(array(':nome' => $nome_produto, ':id_produto' => $id_produto));
        } elseif ($tipo_tipo == "descricao_produto") {
            $query_update_nome = $connection->prepare("UPDATE produto SET descricao=:descricao WHERE id=:id_produto");
            $query_update_nome->execute(array(':descricao' => $descricao_produto, ':id_produto' => $id_produto));
        } elseif ($tipo_tipo == "nome_descricao") {
            $query_update_nome = $connection->prepare("UPDATE produto SET nome=:nome AND descricao=:descricao WHERE id=:id_produto");
            $query_update_nome->execute(array(':nome' => $nome_produto, ':descricao' => $descricao_produto, ':id_produto' => $id_produto));
        }
        //
    }
    $arr = array('sucesso' => true);
    logClicks($connection, "98");
} elseif ($_POST['tipo'] == "ver_cat_edit") {
    if ($_POST['id_categ'] > 0) {
        $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent=:parent ORDER BY f.designacao");
        $query_categoria->execute(array(':parent' => $_POST['id_categ']));
        $num_linhas = $query_categoria->rowCount();
        if ($num_linhas > 0) {
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
        }
    } else {
        $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent IS NULL ORDER BY f.designacao");
        $query_categoria->execute();
        $num_linhas = $query_categoria->rowCount();

        if ($num_linhas > 0) {
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
        }
    }
    logClicks($connection, "85");
} elseif ($_POST['tipo'] == "ver_subcat_edit") {
    if ($_POST['id_subcateg'] > 0) {
        $query_familia = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent=:parent ORDER BY designacao");
        $query_familia->execute(array(':parent' => $_POST['id_subcateg']));
        $num_linhas = $query_familia->rowCount();

        if ($num_linhas > 0) {
            while ($linha = $query_familia->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
        }
    } else {
        $query_familia = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent=:parent ORDER BY designacao");
        $query_familia->execute(array(':parent' => $_POST['id_categoria']));
        $num_linhas = $query_familia->rowCount();

        if ($num_linhas > 0) {
            while ($linha = $query_familia->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
        } else {
            $arr = array('sucesso' => false, 'id' => 0, 'desig' => "- Não existe -");
        }
    }
    logClicks($connection, "86");
} elseif ($_POST['tipo'] == "categorias") {
    $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent IS NULL ORDER BY f.designacao");
    $query_categoria->execute();
    while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
} elseif ($_POST['tipo'] == "subcategorias") {
    $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent=:parent ORDER BY f.designacao");
    $query_categoria->execute(array(':parent' => $_POST['id']));
    while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
} elseif ($_POST['tipo'] == "familias") {
    $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE f.parent=:parent ORDER BY f.designacao");
    $query_categoria->execute(array(':parent' => $_POST['id']));
    while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
} elseif ($_POST['tipo'] == "g_categorias") {
    $dados = json_decode($_POST['dados'], true);
    $id_cat = $_POST["id_cat"];
    $id_subcat = $_POST["id_subcat"];
    foreach ($dados as $key => $value) {
        $id = $dados[$key]["id"];
        $nome = $dados[$key]["nome"];
        $query_update_cat = $connection->prepare("UPDATE familia SET designacao=:nome WHERE id=:id");
        $query_update_cat->execute(array(':nome' => $nome, ':id' => $id));
        if ($id_cat != 0 && $id_subcat == 0) {
            $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE parent IS NULL ORDER BY f.designacao");
            $query_categoria->execute();
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados_cat[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $query_familia = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE parent=:parent ORDER BY f.designacao");
            $query_familia->execute(array(':parent' => $_POST['id_cat']));
            while ($linha = $query_familia->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados_subcat[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
        } elseif ($id_cat != 0 && $id_subcat != 0) {
            $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE parent IS NULL ORDER BY f.designacao");
            $query_categoria->execute();
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados_cat[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $query_familia = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE parent=:parent ORDER BY f.designacao");
            $query_familia->execute(array(':parent' => $_POST['id_subcat']));
            while ($linha = $query_familia->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados_subcat[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
        } elseif ($id_cat == 0 && $id_subcat == 0) {
            $query_categoria = $connection->prepare("SELECT f.id, f.designacao FROM familia f WHERE parent IS NULL ORDER BY f.designacao");
            $query_categoria->execute();
            while ($linha = $query_categoria->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados_cat[] = array('id' => $linha['id'], 'desig' => $linha['designacao']);
            }
            $arr_dados_subcat[] = array('id' => 0, 'desig' => "- Subcategoria -");
        }
    }
    $arr = array('sucesso' => true, 'dados_cat' => $arr_dados_cat, 'dados_subcat' => $arr_dados_subcat);
    logClicks($connection, "87");
} elseif ($_POST['tipo'] == "cat_prod") {
    if ($_POST['id'] == 0 && $_POST['nivel'] == 0) {
        $query_cat = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM familia f1 INNER JOIN familia f2 ON f1.id=f2.parent INNER JOIN familia f3 ON f2.id=f3.parent INNER JOIN produto p ON p.familia=f3.id INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL ORDER BY f.nome_abrev, p.nome");
        $query_cat->execute();
        $num_linhas = $query_cat->rowCount();
    } elseif ($_POST['id'] == 0 && $_POST['nivel'] == 1) {
        $query_cat = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM familia f1 INNER JOIN familia f2 ON f1.id=f2.parent INNER JOIN familia f3 ON f2.id=f3.parent INNER JOIN produto p ON p.familia=f3.id INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL AND f1.id=:id ORDER BY f.nome_abrev, p.nome");
        $query_cat->execute(array(':id' => $_POST['cat']));
        $num_linhas = $query_cat->rowCount();
    } elseif ($_POST['id'] == 0 && $_POST['nivel'] == 2) {
        $query_cat = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM familia f1 INNER JOIN familia f2 ON f1.id=f2.parent INNER JOIN familia f3 ON f2.id=f3.parent INNER JOIN produto p ON p.familia=f3.id INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL AND f2.id=:id ORDER BY f.nome_abrev, p.nome");
        $query_cat->execute(array(':id' => $_POST['cat']));
        $num_linhas = $query_cat->rowCount();
    } elseif ($_POST['id'] != 0 && $_POST['nivel'] == 0) {
        $query_cat = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM familia f1 INNER JOIN familia f2 ON f1.id=f2.parent INNER JOIN familia f3 ON f2.id=f3.parent INNER JOIN produto p ON p.familia=f3.id INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL AND f1.id=:id_cat ORDER BY f.nome_abrev, p.nome");
        $query_cat->execute(array(':id_cat' => $_POST['id']));
        $num_linhas = $query_cat->rowCount();
    } elseif ($_POST['id'] != 0 && $_POST['nivel'] == 1) {
        $query_cat = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM familia f1 INNER JOIN familia f2 ON f1.id=f2.parent INNER JOIN familia f3 ON f2.id=f3.parent INNER JOIN produto p ON p.familia=f3.id INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL AND f2.id=:id_cat ORDER BY f.nome_abrev, p.nome");
        $query_cat->execute(array(':id_cat' => $_POST['id']));
        $num_linhas = $query_cat->rowCount();
    } elseif ($_POST['id'] != 0 && $_POST['nivel'] == 2) {
        $query_cat = $connection->prepare("SELECT f.id AS id_fornecedor, f.nome_abrev, p.id AS id_produto, p.nome, p.descricao, fp.preco, r.id_regra, rp1.valor, rp1.simbolo FROM familia f1 INNER JOIN familia f2 ON f1.id=f2.parent INNER JOIN familia f3 ON f2.id=f3.parent INNER JOIN produto p ON p.familia=f3.id INNER JOIN fp_stock fp ON p.id=fp.id_produto INNER JOIN regra_produto rp1 ON p.id=rp1.id_produto LEFT JOIN regra_produto rp2 ON (rp1.id_produto=rp2.id_produto AND rp1.`data` < rp2.`data`) INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN regra r ON rp1.id_regra=r.id_regra WHERE rp2.id_produto IS NULL AND rp2.id_regra IS NULL AND f3.id=:id_cat ORDER BY f.nome_abrev, p.nome");
        $query_cat->execute(array(':id_cat' => $_POST['id']));
        $num_linhas = $query_cat->rowCount();
    }

    $query_categoria = $connection->prepare("SELECT id, designacao FROM familia WHERE parent=:parent ORDER BY designacao");
    $query_categoria->execute(array(':parent' => $_POST['id']));
    $rows = $query_categoria->rowCount();

    $query_taxa_iva = $connection->prepare("SELECT id_regra, valor, simbolo FROM regra WHERE nome_regra LIKE 'Taxa de IVA%' ORDER BY valor");
    $query_taxa_iva->execute();

    if ($rows > 0) {
        for ($i = 0; $i < $rows; $i++) {
            $linha_dados = $query_categoria->fetch(PDO::FETCH_ASSOC);
            $arr_cat[] = array('id' => $linha_dados['id'], 'desig' => $linha_dados['designacao']);
        }
    } else {
        $arr_cat[] = array('cat_vazia' => true, 'id' => 0, 'desig' => "- Não existe -");
    }

    while ($linha_taxa_iva = $query_taxa_iva->fetch(PDO::FETCH_ASSOC)) {
        $arr_taxa[] = array('id_regra' => $linha_taxa_iva['id_regra'], 'valor' => $linha_taxa_iva['valor'], 'simbolo' => $linha_taxa_iva['simbolo']);
    }

    if ($num_linhas > 0) {
        for ($i = 0; $i < $num_linhas; $i++) {
            $linha_dados = $query_cat->fetch(PDO::FETCH_ASSOC);
            $arr_dados[] = array('id_fornecedor' => $linha_dados['id_fornecedor'], 'nome_fornecedor' => $linha_dados['nome_abrev'], 'id_produto' => $linha_dados['id_produto'], 'nome' => $linha_dados['nome'], 'descricao' => $linha_dados['descricao'], 'preco' => $linha_dados['preco'], 'id_regra' => $linha_dados['id_regra'], 'valor' => $linha_dados['valor'], 'simbolo' => $linha_dados['simbolo']);
        }
    } else {
        $arr_dados = array('sucesso' => true, 'vazio' => true);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados, 'dados_cat' => $arr_cat, 'dados_taxa' => $arr_taxa, 'moeda' => $linha_moeda['simbolo']);
    logClicks($connection, "97");
} elseif ($_POST['tipo'] == "comparar") {
    // $query_acoes = $connection->prepare("SELECT emp.nome AS nome_empresa, a.nome, p.preco, IF (f.quantidade IS NULL, p.quantidade, p.quantidade-f.quantidade) AS quantidade FROM acao_trans p LEFT JOIN acao_trans f ON p.id=f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=f.id_empresa OR emp.id_empresa=p.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL");
    $query_acoes = $connection->prepare("SELECT e.id_empresa, e.nome AS nome_empresa, a.id, a.nome, p.preco, m.ISO4217, date_format(p.`data`,'%m/%d/%Y') AS `data`, COALESCE(p.quantidade-SUM(f.quantidade), p.quantidade) AS quantidade FROM acao_trans p LEFT JOIN acao_trans f ON p.id=f.parent INNER JOIN acao a ON p.id_acao=a.id INNER JOIN empresa e ON p.id_empresa=e.id_empresa INNER JOIN pais c ON a.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE p.parent IS NULL GROUP BY p.id HAVING quantidade<>0 ORDER BY e.id_empresa ASC, a.id ASC");
    $query_acoes->execute();
    $num_acoes = $query_acoes->rowCount();

    if ($num_acoes == 0) {
        $arr = array('sucesso' => false, 'mensagem' => "Não existem ações para o grupo selecionado");
    } else {
        while ($linha_dados = $query_acoes->fetch(PDO::FETCH_ASSOC)) {
            if ($linha_dados['quantidade'] != 0) {
                $arr[] = array('nome_empresa' => $linha_dados['nome_empresa'], 'nome' => $linha_dados['nome'], 'preco' => $linha_dados['preco'], 'moeda_acao' => $linha_dados['ISO4217'], 'quantidade' => $linha_dados['quantidade'], 'moeda_emp' => $linha_moeda['ISO4217'], 'simbolo_moeda' => $linha_moeda['simbolo']);
            }
        }
    }
    logClicks($connection, "65");
} elseif ($_POST['tipo'] == "comparar_esp") {
    if ($_POST['id_grupo'] > 0) {
        $query_dados = $connection->prepare("SELECT emp.nome, compras.qtd_compras, compras.compras, vendas.qtd_vendas, IF(vendas.vendas IS NOT NULL, vendas.vendas, 0) AS vendas, IF(vendas-compras_vendidas IS NOT NULL, vendas-compras_vendidas, 0) AS lucro_real, compras.qtd_compras-vendas.qtd_vendas AS qtd_sobrante FROM (SELECT emp.id_empresa, SUM(IF (p.quantidade IS NOT NULL, p.quantidade, f.quantidade)) AS qtd_compras, SUM(p.preco*IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS compras_vendidas, SUM(p.preco*IF (p.quantidade IS NOT NULL, p.quantidade, f.quantidade)) AS compras FROM acao_trans p LEFT JOIN acao_trans f ON p.id = f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=p.id_empresa OR emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL GROUP BY emp.nome ORDER BY emp.nome) AS compras LEFT JOIN (SELECT emp.id_empresa, SUM(IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS qtd_vendas, SUM(f.preco*IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS vendas FROM acao_trans p LEFT JOIN acao_trans f ON p.id = f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=p.id_empresa OR emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL GROUP BY emp.nome) AS vendas ON compras.id_empresa=vendas.id_empresa INNER JOIN empresa emp ON emp.id_empresa=compras.id_empresa OR emp.id_empresa=vendas.id_empresa INNER JOIN grupo g ON g.id=emp.id_grupo AND g.id=:id WHERE emp.ativo='1'");
        $query_dados->execute(array(':id' => $_POST['id_grupo']));
        $num_dados = $query_dados->rowCount();

        if ($num_dados == 0) {
            $arr = array('sucesso' => true, 'vazio' => true);
        } else {
            while ($linha_dados = $query_dados->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('nome_empresa' => $linha_dados['nome'], 'qtd_compras' => $linha_dados['qtd_compras'], 'compras' => $linha_dados['compras'], 'qtd_vendas' => $linha_dados['qtd_vendas'], 'vendas' => $linha_dados['vendas'], 'lucro_real' => $linha_dados['lucro_real'], 'qtd_sobrante' => $linha_dados['qtd_sobrante']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
        }
    } else {
        $query_dados = $connection->prepare("SELECT emp.nome, compras.qtd_compras, compras.compras, vendas.qtd_vendas, IF(vendas.vendas IS NOT NULL, vendas.vendas, 0) AS vendas, IF(vendas-compras_vendidas IS NOT NULL, vendas-compras_vendidas, 0) AS lucro_real, compras.qtd_compras-vendas.qtd_vendas AS qtd_sobrante FROM (SELECT emp.id_empresa, SUM(IF (p.quantidade IS NOT NULL, p.quantidade, f.quantidade)) AS qtd_compras, SUM(p.preco*IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS compras_vendidas, SUM(p.preco*IF (p.quantidade IS NOT NULL, p.quantidade, f.quantidade)) AS compras FROM acao_trans p LEFT JOIN acao_trans f ON p.id = f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=p.id_empresa OR emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL GROUP BY emp.nome ORDER BY emp.nome) AS compras LEFT JOIN (SELECT emp.id_empresa, SUM(IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS qtd_vendas, SUM(f.preco*IF (f.quantidade IS NOT NULL, f.quantidade, 0)) AS vendas FROM acao_trans p LEFT JOIN acao_trans f ON p.id = f.parent INNER JOIN acao a ON p.id_acao=a.id OR f.id_acao=a.id INNER JOIN empresa emp ON emp.id_empresa=p.id_empresa OR emp.id_empresa=f.id_empresa WHERE emp.ativo='1' AND p.parent IS NULL GROUP BY emp.nome) AS vendas ON compras.id_empresa=vendas.id_empresa INNER JOIN empresa emp ON emp.id_empresa=compras.id_empresa OR emp.id_empresa=vendas.id_empresa INNER JOIN grupo g ON g.id=emp.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.id=:id_utilizador");
        $query_dados->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
        $num_dados = $query_dados->rowCount();

        if ($num_dados == 0) {
            $arr = array('sucesso' => true, 'vazio' => true);
        } else {
            while ($linha_dados = $query_dados->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('nome_empresa' => $linha_dados['nome'], 'qtd_compras' => $linha_dados['qtd_compras'], 'compras' => $linha_dados['compras'], 'qtd_vendas' => $linha_dados['qtd_vendas'], 'vendas' => $linha_dados['vendas'], 'lucro_real' => $linha_dados['lucro_real'], 'qtd_sobrante' => $linha_dados['qtd_sobrante']);
            }
            $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
        }
    }
    logClicks($connection, "64");
} elseif ($_POST['tipo'] == "dados_grupo") {
//    $query_dados_grupo = $connection->prepare("SELECT * FROM (SELECT g.id AS id_grupo, g.nome, eg.estado, tg.designacao AS tipo FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN utilizador u ON eg.id_user=u.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE u.id=:id_utilizador ORDER BY date(eg.`data`) DESC, time(eg.`data`) DESC) AS t1 GROUP BY id_grupo"); //esta esta
    $query_dados_grupo = $connection->prepare("SELECT g.id AS id_grupo, g.nome, eg.estado, tg.designacao AS tipo FROM estado_grupo eg JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo ) t1 ON t1.id_grupo=eg.id_grupo AND t1.max_date=eg.date_reg INNER JOIN grupo g ON eg.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE ug.id_user=:id_utilizador ORDER BY id_grupo");//meti esta
    $query_dados_grupo->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    while ($linha_dados_grupo = $query_dados_grupo->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id_grupo' => $linha_dados_grupo['id_grupo'], 'nome' => $linha_dados_grupo['nome'], 'estado' => $linha_dados_grupo['estado'], 'tipo' => $linha_dados_grupo['tipo']);
    }
    $arr = array('dados_in' => $arr_dados);
} elseif ($_POST['tipo'] == "dados_afet") {
    $query_dados_afet = $connection->prepare("SELECT emp.id_empresa, g.id AS id_grupo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");
    $query_dados_afet->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    while ($linha = $query_dados_afet->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id_empresa' => $linha['id_empresa'], 'id_grupo' => $linha['id_grupo']);
    }
    $arr = array('dados_in' => $arr_dados);
} elseif ($_POST['tipo'] == "dados_afet_user") {
    $query_dados_afet_users = $connection->prepare("SELECT users.id, users.nome_user AS nome_user, users.id_empresa, users.nome, users.u_ldap FROM (SELECT DISTINCT u.id, u.tipo, g.id AS id_grupo FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON u.id=ug.id_user) AS adm INNER JOIN (SELECT u.id, u.login AS nome_user, u.u_ldap, emp.id_empresa, emp.nome, g.id AS id_grupo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1') AS users ON adm.id_grupo=users.id_grupo WHERE adm.tipo=:tipo AND adm.id=:id_utilizador ORDER BY users.nome_user");
    $query_dados_afet_users->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    while ($linha = $query_dados_afet_users->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id_user' => $linha['id'], 'nome_user' => $linha['nome_user'], 'id_empresa' => $linha['id_empresa'], 'nome_user' => $linha['nome'], 'u_ldap' => $linha['u_ldap']);
    }
    $arr = array('dados_in' => $arr_dados);
} elseif ($_POST['tipo'] == "novo_admin") {
    $valido = true;
    $dados = json_decode($_POST['dados'], true);
    $username = strtolower($dados[0]["username"]);
    $query_utilizador = $connection->prepare("SELECT login FROM utilizador WHERE login=:username LIMIT 1");
    $query_utilizador->execute(array(':username' => $username));
    $num_utilizadores = $query_utilizador->rowCount();
    if ($num_utilizadores != 1) {
        if (!isset($_SESSION['id_entidade'])) {
            $query_entidade = $connection->prepare("SELECT ent.id FROM entidade ent INNER JOIN utilizador u ON u.id_entidade=ent.id WHERE u.id=:id");
            $query_entidade->execute(array(':id' => $_SESSION["id_utilizador"]));
            $linha_entidade = $query_entidade->fetch(PDO::FETCH_ASSOC);
            $id_entidade = $linha_entidade['id'];
        } else {
            $id_entidade = $_SESSION['id_entidade'];
        }
        $ldap = $dados[0]["ldap"];
        $date = new DateTime();
        $data = $date->format('Y-m-d H:i:s');
        if ($ldap == true) {
            $u_ldap = "1";
            $query_novo_admin = $connection->prepare("INSERT INTO utilizador (login, tipo, id_entidade, admin, u_ldap, parent, date) VALUES (:username, 'admin', :id_entidade, '0',:u_ldap, :parent, :data)");
            $query_novo_admin->execute(array(':username' => $username, ':id_entidade' => $id_entidade, ':u_ldap' => $u_ldap, ':parent' => $_SESSION["id_utilizador"], ':data' => $data));
        } elseif ($ldap == false) {
            $palavras = explode(" ", $dados[0]["nome"]);
            $primeiro_nome = $palavras[0];
            $nome_completo = $dados[0]["nome"];
            $password = $dados[0]["pass"];
            $email = $dados[0]["email"];
            $u_ldap = "0";
            $query_geraSalt = $connection->prepare("SET @salt = UNHEX(SHA2(UUID(), 256))");
            $query_geraSalt->execute();
            $query_novo_admin = $connection->prepare("INSERT INTO utilizador (login, p_nome, nome, pass, salt, email, tipo, id_entidade, admin, u_ldap, parent, date) VALUES (:username, :p_nome, :nome, UNHEX(SHA2(CONCAT(:password, HEX(@salt)), 256)), @salt, :email, 'admin', :id_entidade, '0',:u_ldap, :parent, :data)");
            $query_novo_admin->execute(array(':username' => $username, ':p_nome' => $primeiro_nome, ':nome' => $nome_completo, ':password' => $password, ':email' => $email, ':id_entidade' => $id_entidade, ':u_ldap' => $u_ldap, ':parent' => $_SESSION["id_utilizador"], ':data' => $data));
        }
        $query_admin = $connection->prepare("SELECT u.login, u.nome, date_format(u.date,'%d-%m-%Y') AS `data` FROM utilizador u WHERE parent=:id");
        $query_admin->execute(array(':id' => $_SESSION["id_utilizador"]));

        while ($linha = $query_admin->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('login' => $linha['login'], 'nome_user' => $linha['nome'], 'date' => $linha['data']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "O nome de utilizador já existe");
    }
    logClicks($connection, "31");
} elseif ($_POST['tipo'] == "ver_fornecedor_pais") {
    $id_pais = $_POST["id_pais"];
    $arr_dados = array();
    $simbolo = "";
    $vazio = true;

    $query_fornecedor_pais = $connection->prepare("SELECT f.id, f.nome_abrev, m.simbolo FROM fornecedor f INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE p.id_pais=:id_pais");
    $query_fornecedor_pais->execute(array(':id_pais' => $id_pais));
    $num_rows = $query_fornecedor_pais->rowCount();

    if ($num_rows > 0) {
        while ($linha = $query_fornecedor_pais->fetch(PDO::FETCH_ASSOC)) {
            $vazio = false;
            $arr_dados[] = array('id_fornecedor' => $linha['id'], 'nome_fornecedor' => $linha['nome_abrev']);
            $simbolo = $linha['simbolo'];
        }
    }
    $arr = array('sucesso' => true, 'vazio' => $vazio, 'dados_in' => $arr_dados, 'moeda' => $simbolo);
    
} elseif ($_POST['tipo'] == "dados_grafico") { /* Gráfico Utilizadores */
    if ($_SESSION['admin'] == "0") {
        $query_users_online = $connection->prepare("SELECT u.nome, SUM(TIMESTAMPDIFF(MINUTE,data_login,data_logout)) AS tempo_online FROM utilizador u INNER JOIN sessao s ON u.id=s.`user` INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u1 ON ug.id_user=u1.id WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador GROUP BY u.id ORDER BY tempo_online DESC");
    } else {
        $query_users_online = $connection->prepare("SELECT u.nome, SUM(TIMESTAMPDIFF(MINUTE,data_login,data_logout)) AS tempo_online FROM utilizador u1 INNER JOIN utilizador u2 ON u1.id=u2.parent INNER JOIN user_grupo ug ON ug.id_user=u2.id INNER JOIN grupo g ON g.id=ug.id_grupo INNER JOIN empresa emp ON emp.id_grupo=g.id INNER JOIN utilizador u ON u.id_empresa=emp.id_empresa INNER JOIN sessao s ON u.id=s.`user` WHERE emp.ativo='1' AND u1.tipo=:tipo AND u1.id=:id_utilizador GROUP BY u.id ORDER BY tempo_online DESC");
    }
    $query_users_online->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    $num_linhas = $query_users_online->rowCount();
    if ($num_linhas > 0) {
        while ($linha = $query_users_online->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('nome' => $linha['nome'], 'tempo' => $linha['tempo_online']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'dados_in' => "Ainda ninguém usou a aplicação");
    }
}
/* elseif($_POST['tipo'] == "info_acoes") {
  $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
  $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  try {
  $query_cotacao = $connection_bd_acao->prepare("SELECT DISTINCT a.id_acao, a.nome_acao, c.last_trade_price, c.`change`, c.`open`, c.days_high, c.days_low FROM cotacao c INNER JOIN acao a ON c.id_acao=a.id_acao INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa INNER JOIN pais p ON b.id_pais=p.id_pais ORDER BY a.nome_acao ASC, c.date_reg DESC");
  $query_cotacao->execute(array());
  $num_linhas = $query_cotacao->rowCount();

  if ($num_linhas > 0) {
  $id_acao = "";
  while ($linha_cotacao = $query_cotacao->fetch(PDO::FETCH_ASSOC)) {
  if ($id_acao != $linha_cotacao['id_acao']) {
  $arr_dados[] = array('nome_acao' => $linha_cotacao['nome_acao'], 'LastTradePriceOnly' => $linha_cotacao['last_trade_price']);
  $id_acao = $linha_cotacao['id_acao'];
  }
  }
  $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
  }
  else {
  $arr = array('sucesso' => true, 'vazio' => true);
  }

  } catch (PDOException $e) {
  $connection_bd_acao->rollBack();
  echo $e->getMessage();
  }

  $connection_bd_acao=null;
  } */

elseif ($_POST['tipo'] == "info_prod_afet") {
    $query_produto_afet = $connection->prepare("SELECT p.id, p.nome, p.descricao, cat.designacao AS cat, subcat.designacao AS subcat, f.designacao AS fam, CONCAT(rp.valor, ' ', rp.simbolo) AS iva FROM produto p INNER JOIN familia f ON p.familia=f.id INNER JOIN familia subcat ON f.parent=subcat.id INNER JOIN familia cat ON subcat.parent=cat.id INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=:id_prod AND r.nome_regra LIKE ('Taxa de IVA %') ORDER BY rp.date_reg DESC LIMIT 1");
    $query_produto_afet->execute(array(':id_prod' => $_POST['id']));
    $linha_produto_afet = $query_produto_afet->fetch(PDO::FETCH_ASSOC);
    $arr = array('sucesso' => true, 'descricao' => $linha_produto_afet['descricao'], 'cat' => $linha_produto_afet['cat'], 'subcat' => $linha_produto_afet['subcat'], 'fam' => $linha_produto_afet['fam'], 'iva' => $linha_produto_afet['iva']);
    logClicks($connection, "183");
    
} elseif ($_POST['tipo'] == "afet_prod_fornec") {
    $id_prod = $_POST['id_prod'];
    $id_fornec = $_POST['id_fornec'];
    $preco = $_POST['preco'];

    $query_check_fp = $connection->prepare("SELECT * FROM fp_stock fp WHERE fp.id_fornecedor=:id_fornec AND fp.id_produto=:id_produto");
    $query_check_fp->execute(array(':id_fornec' => $id_fornec, ':id_produto' => $id_prod));
    $check = $query_check_fp->rowCount();

    if ($check > 0) {
        $arr = array('sucesso' => false, 'mensagem' => 'Este fornecedor já tem o produto indicado');
    } else {
        $query_afet_prod = $connection->prepare("INSERT INTO fp_stock (id_fornecedor, id_produto, preco) VALUES (:id_fornec, :id_prod, :preco)");
        $query_afet_prod->execute(array(':id_fornec' => $id_fornec, ':id_prod' => $id_prod, ':preco' => $preco));
        $arr = array('sucesso' => true);
    }
    logClicks($connection, "184");
    
} elseif ($_POST['tipo'] == "ver_produto_fornecedor") {
    $id_fornec = $_POST['id_fornec'];

    $query_fp_stock = $connection->prepare("SELECT p.id, p.nome FROM fp_stock fp INNER JOIN produto p ON fp.id_produto=p.id WHERE fp.id_fornecedor=:id_fornec ORDER BY p.nome ASC");
    $query_fp_stock->execute(array(':id_fornec' => $id_fornec));
    $check = $query_fp_stock->rowCount();

    if ($check == 0) {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => 'Este fornecedor ainda não possui produtos');
    } else {
        while ($linha_fp_stock = $query_fp_stock->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_prod' => $linha_fp_stock['id'], 'nome_prod' => $linha_fp_stock['nome']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "187");
    
} elseif ($_POST['tipo'] == "add_desc_prod") {
    $tipo_desc = $_POST['tipo_desc'];
    $id_admin = $_SESSION['id_utilizador'];
    $id_fornec = $_POST['id_fornec'];
    $desc = $_POST['desc'];

    if ($tipo_desc == "financ") {
        $id_prod = $_POST['id_prod'];
        $prz_pag = $_POST['prz_pag'];

        $query_check_fp = $connection->prepare("SELECT * FROM fp_desconto fp WHERE fp.id_fornecedor=:id_fornec AND fp.id_produto=:id_produto AND id_utilizador=:id_user AND fp.active=:active");
        $query_check_fp->execute(array(':id_fornec' => $id_fornec, ':id_produto' => $id_prod, ':id_user' => $id_admin, ':active' => '1'));
        $check = $query_check_fp->rowCount();

        if ($check > 0) {
            $arr = array('sucesso' => false, 'mensagem' => 'Este fornecedor já possui um desconto para esse produto');
        } else {
            $query_afet_prod = $connection->prepare("INSERT INTO fp_desconto (id_fornecedor, id_produto, id_utilizador, desconto, prazo_pag, active) VALUES (:id_fornec, :id_prod, :id_user, :desconto, :prz_pag, :active)");
            $query_afet_prod->execute(array(':id_fornec' => $id_fornec, ':id_prod' => $id_prod, ':id_user' => $id_admin, ':desconto' => $desc, ':prz_pag' => $prz_pag, ':active' => '1'));
            $arr = array('sucesso' => true);
        }
    } else if ($tipo_desc == "comerc") {
        $query_check_fp = $connection->prepare("SELECT * FROM fp_desconto fp WHERE fp.id_fornecedor=:id_fornec AND fp.id_utilizador=:id_user AND fp.active=:active");
        $query_check_fp->execute(array(':id_fornec' => $id_fornec, ':id_user' => $id_admin, ':active' => '1'));
        $check = $query_check_fp->rowCount();

        if ($check > 0) {
            $arr = array('sucesso' => false, 'mensagem' => 'Este fornecedor já está possui um desconto deste tipo');
        } else {
            $query_afet_prod = $connection->prepare("INSERT INTO fp_desconto (id_fornecedor, id_utilizador, desconto, active) VALUES (:id_fornec, :id_user, :desconto, :active)");
            $query_afet_prod->execute(array(':id_fornec' => $id_fornec, ':id_user' => $id_admin, ':desconto' => $desc, ':active' => '1'));
            $arr = array('sucesso' => true);
        }
    }
    logClicks($connection, "189");
    
} elseif ($_POST['tipo'] == "edit_desc") {
    $id_admin = $_SESSION['id_utilizador'];
    $dados = json_decode($_POST['dados'], true);
    $changes = 0;

    foreach ($dados as $key => $value) {
        $id_desc = $dados[$key]['id'];
        $estado = $dados[$key]['estado'];

        $query_check_st = $connection->prepare("SELECT * FROM fp_desconto fp WHERE fp.id_desconto=:id_desc AND fp.id_utilizador=:id_user AND fp.active=:state");
        $query_check_st->execute(array(':id_desc' => $id_desc, ':id_user' => $id_admin, ':state' => $estado));
        $check = $query_check_st->rowCount();

        if ($check == 0) {
            $query_chg_st = $connection->prepare("UPDATE fp_desconto SET active=:state WHERE id_desconto=:id_desc AND id_utilizador=:id_user");
            $query_chg_st->execute(array(':state' => $estado, ':id_desc' => $id_desc, ':id_user' => $id_admin));
            $changes++;
        }
    }

    if ($changes > 0) {
        $query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.id_desconto, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', CONCAT(descontos.prazo_pag, ' dias')) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia, descontos.active FROM (SELECT u.id_entidade, fpd.id_desconto, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag, fpd.active FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC, info_desc.produto ASC");
        $query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
        while ($linha_fp_desc = $query_fp_desc->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_desc' => $linha_fp_desc['id_desconto'], 'fornec' => $linha_fp_desc['fornecedor'], 'desc' => $linha_fp_desc['desconto'], 'prz_pag' => $linha_fp_desc['prazo_pag'], 'nome_prod' => $linha_fp_desc['produto'], 'state' => $linha_fp_desc['active']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => 'Não foram efetuadas alterações');
    }
    logClicks($connection, "191");
    
} elseif ($_POST['tipo'] == "del_desc") {
    $id_admin = $_SESSION['id_utilizador'];
    $id_desc = $_POST['id_desc'];

    $query_chg_st = $connection->prepare("DELETE FROM fp_desconto WHERE id_desconto=:id_desc AND id_utilizador=:id_user");
    $query_chg_st->execute(array(':id_desc' => $id_desc, ':id_user' => $id_admin));

    $query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.id_desconto, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', CONCAT(descontos.prazo_pag, ' dias')) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia, descontos.active FROM (SELECT u.id_entidade, fpd.id_desconto, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag, fpd.active FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC, info_desc.produto ASC");
    $query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    $check = $query_fp_desc->rowCount();

    if ($check > 0) {
        while ($linha_fp_desc = $query_fp_desc->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_desc' => $linha_fp_desc['id_desconto'], 'fornec' => $linha_fp_desc['fornecedor'], 'desc' => $linha_fp_desc['desconto'], 'prz_pag' => $linha_fp_desc['prazo_pag'], 'nome_prod' => $linha_fp_desc['produto'], 'state' => $linha_fp_desc['active']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "192");
    
} elseif ($_POST['tipo'] == "filtrar_desc_fornec") {
    $id_admin = $_SESSION['id_utilizador'];
    $id_fornec = $_POST['id_fornec'];

    if ($id_fornec > 0) {
        $query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.id_desconto, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', CONCAT(descontos.prazo_pag, ' dias')) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia, descontos.active FROM (SELECT u.id_entidade, fpd.id_desconto, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag, fpd.active FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id WHERE fpd.id_fornecedor=:id_fornec) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC, info_desc.produto ASC");
        $query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador'], ':id_fornec' => $id_fornec));
    } else {
        $query_fp_desc = $connection->prepare("SELECT * FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT descontos.id_entidade, descontos.id_desconto, descontos.fornecedor, CONCAT(descontos.desconto, ' %') AS desconto, IF(descontos.prazo_pag IS NULL, '-', CONCAT(descontos.prazo_pag, ' dias')) AS prazo_pag, IF(info_prod.produto IS NULL, '-', info_prod.produto) AS produto, IF(info_prod.descricao IS NULL, '-', info_prod.descricao) AS descricao, IF(info_prod.familia IS NULL, '-', info_prod.familia) AS familia, descontos.active FROM (SELECT u.id_entidade, fpd.id_desconto, fpd.id_produto, f.nome_abrev AS fornecedor, fpd.desconto, fpd.prazo_pag, fpd.active FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id) AS descontos LEFT JOIN (SELECT p.id AS produto_id, p.nome AS produto, p.descricao, fam.designacao AS familia FROM produto p INNER JOIN familia fam ON p.familia=fam.id) AS info_prod ON descontos.id_produto=info_prod.produto_id) AS info_desc ON atualuser.id_entidade=info_desc.id_entidade WHERE info_desc.id_entidade IS NOT NULL ORDER BY info_desc.fornecedor ASC, info_desc.produto ASC");
        $query_fp_desc->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    }

    $check = $query_fp_desc->rowCount();
    if ($check > 0) {
        while ($linha_fp_desc = $query_fp_desc->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_desc' => $linha_fp_desc['id_desconto'], 'fornec' => $linha_fp_desc['fornecedor'], 'desc' => $linha_fp_desc['desconto'], 'prz_pag' => $linha_fp_desc['prazo_pag'], 'nome_prod' => $linha_fp_desc['produto'], 'state' => $linha_fp_desc['active']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    logClicks($connection, "193");
    
} elseif ($_POST['tipo'] == "alterar_extrato") {
    $id_mov = $_POST['id_mov'];
    
    $query_conta = $connection->prepare("SELECT c.id, c.id_empresa, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM movimento m INNER JOIN conta c ON m.id_conta=c.id WHERE m.id=:id_mov LIMIT 1");
    $query_conta->execute(array(':id_mov' => $id_mov));
    $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);
    
    $id_conta = $linha_conta['id'];
    $saldo_controlo_correto = $linha_conta['saldo_controlo'];
    $saldo_contab_correto = $linha_conta['saldo_contab'];
    $saldo_disp_correto = $linha_conta['saldo_disp'];
    
    $query_mov_errado = $connection->prepare("SELECT m.id, m.debito, m.credito, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM movimento m WHERE m.id_conta=:id_conta AND m.id>:id_mov");
    $query_mov_errado->execute(array(':id_conta' => $id_conta, 'id_mov' => $id_mov));
    
    $changes = 0;
    while ($linha_mov_errado = $query_mov_errado->fetch(PDO::FETCH_ASSOC)) {
        $linha_mov_errado['debito'] > 0 ? $valor_mov = - $linha_mov_errado['debito'] : $valor_mov = $linha_mov_errado['credito'];
        
        $new_saldo_controlo = $saldo_controlo_correto + $valor_mov;
        $new_saldo_contab = $saldo_contab_correto + $valor_mov;
        $new_saldo_disp = $saldo_disp_correto + $valor_mov;
        
        if ($linha_mov_errado['saldo_controlo'] != $new_saldo_controlo && $linha_mov_errado['saldo_contab'] != $new_saldo_contab && $linha_mov_errado['saldo_disp'] != $new_saldo_disp) {
            $query_upd_mov = $connection->prepare("UPDATE movimento SET date_reg=date_reg, saldo_controlo=:saldo_controlo, saldo_contab=:saldo_contab, saldo_disp=:saldo_disp WHERE id=:id_mov");
            $query_upd_mov->execute(array(':saldo_controlo' => $new_saldo_controlo, ':saldo_contab' => $new_saldo_contab, ':saldo_disp' => $new_saldo_disp, ':id_mov' => $linha_mov_errado['id']));
            $changes++;
        }
        
        $saldo_controlo_correto = $new_saldo_controlo;
        $saldo_contab_correto = $new_saldo_contab;
        $saldo_disp_correto = $new_saldo_disp;
    }
    $changes > 0 ? $arr = array('sucesso' => true, 'changes' => true, 'id_empresa' => $linha_conta['id_empresa'], 'saldo' => number_format($saldo_disp_correto, 2, ',', '.')) : $arr = array('sucesso' => true, 'changes' => false, 'mensagem' => 'Não foram efetuadas alterações');
    logClicks($connection, "194");
    
} elseif ($_POST['tipo'] == "get_rank_data") { //-- Atualização de processo de cálculo de resultados de ações
    //-- Obter taxas de câmbio
    $USDtoEUR_rate = '';
    $GBPtoEUR_rate = '';
    $rates = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
    foreach ($rates->Cube->Cube->Cube as $rate) {
        if ($rate['currency'] == 'USD') {
            $USDtoEUR_rate = 1 / floatval($rate['rate']);
            break;
        }
    }
    foreach ($rates->Cube->Cube->Cube as $rate) {
        if ($rate['currency'] == 'GBP') {
            $GBPtoEUR_rate = 1 / floatval($rate['rate']);
            break;
        }
    }
    
    //-- Obter dados cotações
    $cache = CacheManager::getInstance('files');
    $key = 'cotacao_acoes';
    $CachedString = $cache->getItem($key);
    $dados_cache = $CachedString->get();

    $arr_dados = [];
    if ($dados_cache && $dados_cache !== null) {
        foreach ($dados_cache as $value) {
            $arr_dados[] = array('nome_acao' => $value['nome_acao'], 'last_trade_price' => $value['last_trade_price']);
        }
        CacheManager::clearInstances();
    
    } else {
        $connection_bd_acao = new PDO("mysql:host=localhost;dbname=simemp_acoes;charset=utf8", 'root', $pwd_se_acoes);
        $connection_bd_acao->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection_bd_acao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//        $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT c.id_acao, c.last_trade_price, c.`change`, c.`open`, c.days_high, c.days_low, c.last_trade_date FROM cotacao c ORDER BY c.id_acao ASC, c.date_reg DESC) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao ASC ORDER BY acoes.nome_acao ASC");//estava esta
        $query_cotacao = $connection_bd_acao->prepare("SELECT * FROM (SELECT DISTINCT a.id_acao, a.nome_acao, a.nome_empresa FROM acao a INNER JOIN bolsa b ON a.id_bolsa=b.id_bolsa WHERE a.active='1' ORDER BY a.id_acao ASC) AS acoes INNER JOIN (SELECT last_cotacao.id_acao, last_cotacao.last_trade_price, last_cotacao.`change`, last_cotacao.`open`, last_cotacao.days_high, last_cotacao.days_low,last_cotacao.last_trade_date FROM cotacao last_cotacao JOIN  (SELECT id_acao, MAX(date_reg) AS max_date FROM cotacao GROUP BY id_acao )  c1 ON c1.id_acao=last_cotacao.id_acao AND c1.max_date=last_cotacao.date_reg) AS cotacoes ON acoes.id_acao=cotacoes.id_acao GROUP BY cotacoes.id_acao /*ASC*/ ORDER BY acoes.nome_acao ASC");// meti esta
        $query_cotacao->execute();
        while ($linha_cotacao = $query_cotacao->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('nome_acao' => $linha_cotacao['nome_acao'], 'last_trade_price' => $linha_cotacao['last_trade_price']);
        }
    }
    
    $arr = array('dados_in' => $arr_dados, 'USDtoEUR_rate' => $USDtoEUR_rate, 'GBPtoEUR_rate' => $GBPtoEUR_rate);
}

elseif ($_POST['tipo'] == "guardar_task") { // Adiçao de nova tarefa à BD
    $query_ins_data = $connection->prepare("INSERT INTO calendario_tasks (id_grupo, descricao, dia_v_ini, mes_v_ini, dia_v_fim, mes_v_fim) VALUES (:id_grupo, :desc, :dia_i, :mes_i, :dia_f, :mes_f)");
    $query_ins_data->execute(array(':id_grupo' => $_POST['id_grupo'], ':desc' => $_POST['desc'], ':dia_i' => $_POST['dia_i'], ':mes_i' => $_POST['mes_i'], ':dia_f' => $_POST['dia_f'], ':mes_f' => $_POST['mes_f']));
    $arr = array('sucesso' => true);
    
    // logClicks($connection, "34");
}

elseif ($_POST['tipo'] == "dados_calendario_task") {
    $query_get_task = $connection->prepare("SELECT * FROM calendario_tasks WHERE id=:id_task");
    $query_get_task->execute(array(':id_task' => $_POST['id_cal']));
    
    $linha_task = $query_get_task->fetch(PDO::FETCH_ASSOC);
    $arr = array('sucesso' => true, 'id_grupo' => $linha_task['id_grupo'], 'desc' => $linha_task['descricao'], 'mes_v_ini' => $linha_task['mes_v_ini'], 'dia_v_ini' => $linha_task['dia_v_ini'], 'mes_v_fim' => $linha_task['mes_v_fim'], 'dia_v_fim' => $linha_task['dia_v_fim']);
    
    // logClicks($connection, "34");
}

 elseif ($_POST['tipo'] == "update_task") {
    $query_ins_data = $connection->prepare("UPDATE calendario_tasks SET id_grupo=:id_grupo, descricao=:desc, dia_v_ini=:dia_i, mes_v_ini=:mes_i, dia_v_fim=:dia_f, mes_v_fim=:mes_f WHERE id=:id_task");
    $query_ins_data->execute(array(':id_grupo' => $_POST['id_grupo'], ':desc' => $_POST['desc'], ':dia_i' => $_POST['dia_v_ini'], ':mes_i' => $_POST['mes_v_ini'], ':dia_f' => $_POST['dia_v_fim'], ':mes_f' => $_POST['mes_v_fim'], ':id_task' => $_POST['id_cal']));
    $arr = array('sucesso' => true);
    
    // logClicks($connection, "37");
}

elseif ($_POST['tipo'] == "apagar_task") {
    $query_apagar_task = $connection->prepare("DELETE FROM calendario_tasks WHERE id=:id_cal");
    $query_apagar_task->execute(array(':id_cal' => $_POST['id_cal']));

//    $query_calend_tasks = $connection->prepare("SELECT g.nome, ct.* FROM (SELECT * FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo ASC) AS last_estado_grupo WHERE last_estado_grupo.estado='1') AS grupos_active INNER JOIN grupo g ON grupos_active.id_grupo=g.id INNER JOIN calendario_tasks ct ON grupos_active.id_grupo=ct.id_grupo INNER JOIN user_grupo ug ON grupos_active.id_grupo=ug.id_grupo INNER JOIN utilizador admin ON ug.id_user=admin.id INNER JOIN entidade e ON admin.id_entidade=e.id INNER JOIN utilizador me ON e.id=me.id_entidade WHERE me.id=:id_utilizador ORDER BY mes_v_ini ASC, dia_v_ini ASC");//estava esta
    $query_calend_tasks = $connection->prepare("SELECT g.nome,ct.id,ct.id_grupo,ct.descricao,ct.dia_v_ini,ct.mes_v_ini,ct.dia_v_fim,ct.mes_v_fim,ct.date_reg FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON last_estado_grupos.id_grupo=ug.id_grupo INNER JOIN calendario_tasks ct ON last_estado_grupos.id_grupo=ct.id_grupo INNER JOIN utilizador admin ON ug.id_user=admin.id INNER JOIN entidade e ON admin.id_entidade=e.id INNER JOIN utilizador me ON e.id=me.id_entidade WHERE last_estado_grupos.estado='1' AND me.id=:id_utilizador ORDER BY mes_v_ini ASC, dia_v_ini ASC");// meti esta
    $query_calend_tasks->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
    $num_linhas = $query_calend_tasks->rowCount();

    if ($num_linhas != 0) {
        while ($linha = $query_calend_tasks->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'], 'id_grupo' => $linha['id_grupo'], 'nome_grupo' => $linha['nome'], 'mes_v_ini' => conv_mes($linha['mes_v_ini']), 'dia_v_ini' => $linha['dia_v_ini'], 'mes_v_fim' => conv_mes($linha['mes_v_fim']), 'dia_v_fim' => $linha['dia_v_fim']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    
    // logClicks($connection, "38");
}

elseif ($_POST['tipo'] == "apagar_alerta") {
    
    $query_apagar_alerta = $connection->prepare("DELETE FROM alerta WHERE id=:id_alerta");
    $query_apagar_alerta->execute(array(':id_alerta' => $_POST['id_alerta']));
//
$query_alertas = $connection->prepare("SELECT a.id,a.id_utilizador,a.id_acao_trans,u.login,u.nome,a.nome AS simbolo,a.preco_compra,a.quantidade,a.preco_atual,a.date_reg FROM alerta a INNER JOIN utilizador u ON a.id_utilizador=u.id ORDER BY date_reg desc;");
$query_alertas->execute();
$num_linhas = $query_alertas->rowCount();
//
    if ($num_linhas != 0) {
        while ($linha = $query_alertas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha['id'],'id_utilizador'=> $linha['id_utilizador'],'id_acao_trans'=> $linha['id_acao_trans'],'login' => $linha['login'], 'nome' => $linha['nome'], 'simbolo' => $linha['simbolo'], 'preco_compra' => $linha['preco_compra'], 'quantidade' => $linha['quantidade'], 'preco_atual' => $linha['preco_atual'], 'date_reg'=>$linha['date_reg']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }

    // logClicks($connection, "38");
}

$connection = null;
echo json_encode($arr);
