<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-07-01 17:52:24
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 11:21:28
*/

include('../conf/checkregisto.php');
include_once('../functions/functions.php');

if ($_POST['tipo'] == "gera_nipc") {
    $query_nipc = $connection->prepare("SELECT nipc FROM empresa");
    $query_nipc->execute();
    $linha_nipc = $query_nipc->fetch(PDO::FETCH_ASSOC);
    $count = $query_nipc->rowCount();
    $nipc = gerarNIPC();
    for ($i = 0; $i < $count; $i++) {
        if ($nipc == $linha_nipc['nipc']) {
            $niss = gerarNIPC();
            $i = 0;
        }
    }
    $arr = array('sucesso' => true, 'nipc' => $nipc);
} elseif ($_POST['tipo'] == "gera_niss") {
    $query_niss = $connection->prepare("SELECT niss FROM empresa");
    $query_niss->execute();
    $linha_niss = $query_niss->fetch(PDO::FETCH_ASSOC);
    $count = $query_niss->rowCount();
    $niss = gerarNISS();
    for ($i = 0; $i < $count; $i++) {
        if ($niss == $linha_niss['niss']) {
            $niss = gerarNISS();
            $i = 0;
        }
    }
    $arr = array('sucesso' => true, 'niss' => $niss);
} elseif ($_POST['tipo'] == "reg_sair") {
    $id_utilizador = $_SESSION['id_utilizador'];
    $query_empresa = $connection->prepare("DELETE FROM utilizador WHERE id=:id_utilizador");
    $num_deletes = $query_empresa->execute(array(':id_utilizador' => $id_utilizador));

    if ($num_deletes == 1) {
        $arr = array('sucesso' => true);
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal ao apagar os utilizadores");
    }
} elseif ($_POST['tipo'] == "juntar_empresa") {
    if (!isset($_POST["nome"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal com o formulário");
    } elseif (empty($_POST["nome"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Escolha uma empresa");
    } else {
        $id_empresa = $_POST["nome"];
        $id_utilizador = $_SESSION['id_utilizador'];
        $sql_user = $connection->prepare("SELECT id_empresa FROM utilizador WHERE id=:utilizador AND id_empresa IS NULL");
        $sql_user->execute(array(':utilizador' => $id_utilizador));
        $num_user = $sql_user->rowCount();
        if ($num_user == 1) {
            $query_update = $connection->prepare("UPDATE utilizador SET id_empresa=:id_empresa WHERE id=:id_utilizador");
            $query_update->execute(array(':id_empresa' => $id_empresa, ':id_utilizador' => $id_utilizador));
            $linhas = $query_update->rowCount();
            if ($linhas == 1) {
                $arr = array('sucesso' => true);
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Não foi possível associar o utilizador à empresa");
            }
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Lamentamos, mas o utilizador já tem uma empresa associada");
        }
    }
} elseif ($_POST['tipo'] == "dados_atividade") {
    if (!isset($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal com o formulário");
    } elseif (empty($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Tem de escolher uma atividade");
    } else {
        $id = $_POST['id'];
        $query_dados_atividade = $connection->prepare("SELECT capital_social_monetario FROM atividade WHERE id=:id LIMIT 1");
        $query_dados_atividade->execute(array(':id' => $id));
        $num_dados = $query_dados_atividade->rowCount();

        if ($num_dados == 1) {
            $linha_dados = $query_dados_atividade->fetch(PDO::FETCH_ASSOC);
            $arr = array('sucesso' => true, 'cap_soc' => $linha_dados['capital_social_monetario']);
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Algo falhou. Contate o administrador");
        }
    }
} elseif ($_POST['tipo'] == "dados_empresa") {
    if (!isset($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal com o formulário");
    } elseif (empty($_POST['id'])) {
        $arr = array('sucesso' => false, 'mensagem' => "Tem de escolher uma empresa");
    } else {
        $id = $_POST['id'];
        $query_dados_empresa = $connection->prepare("SELECT emp.niss, emp.nipc, te.tipo, a.designacao, emp.morada, emp.cod_postal, emp.localidade, emp.pais, emp.email, cs.capital_social_monetario, cs.capital_social_especie, b.nome AS banco, g.nome FROM empresa emp INNER JOIN atividade a ON emp.`atividade`=a.id INNER JOIN tipo_empresa te ON emp.tipo=te.id INNER JOIN capital_social cs ON emp.tipo=te.id AND emp.id_empresa=cs.id_empresa INNER JOIN grupo g ON g.id=emp.id_grupo INNER JOIN conta c ON emp.id_empresa=c.id_empresa INNER JOIN banco b ON c.id_banco=b.id WHERE emp.ativo='1' AND emp.id_empresa=:id LIMIT 1");
        $query_dados_empresa->execute(array(':id' => $id));
        $num_dados = $query_dados_empresa->rowCount();

        if ($num_dados == 1) {
            $linha_dados = $query_dados_empresa->fetch(PDO::FETCH_ASSOC);
            $arr = array('sucesso' => true, 'niss' => $linha_dados['niss'], 'nipc' => $linha_dados['nipc'], 'tipo' => $linha_dados['tipo'], 'atividade' => $linha_dados['designacao'], 'morada' => $linha_dados['morada'], 'cod_postal' => $linha_dados['cod_postal'], 'localidade' => $linha_dados['localidade'], 'pais' => $linha_dados['pais'], 'email' => $linha_dados['email'], 'cap_soc_m' => $linha_dados['capital_social_monetario'], 'cap_soc_e' => $linha_dados['capital_social_especie'], 'banco' => $linha_dados['banco'], 'grupo' => $linha_dados['nome']);
        }
    }
} elseif ($_POST['tipo'] == "reg_empresa") {
    if (!isset($_POST["niss"], $_POST["nipc"], $_POST["nome"], $_POST["tipo_emp"], $_POST["atividade"], $_POST["cap_soc_m"], $_POST["cap_soc_e"], $_POST["morada"], $_POST["cod_postal"], $_POST["localidade"], $_POST["email"], $_POST["pais"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal, tente efetuar o registo de novo");
    } elseif (empty($_POST["niss"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o NISS da empresa");
    } elseif (strlen($_POST["niss"]) != 11) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira um NISS válido (11 números)");
    } elseif (!preg_match('/^[0-9]+$/', $_POST["niss"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira um NISS válido (apenas números)");
    } elseif (empty($_POST["nipc"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o NIPC da empresa");
    } elseif (strlen($_POST["nipc"]) != 9) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira um NIPC válido (9 números)");
    } elseif (!preg_match('/^[0-9]+$/', $_POST["nipc"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira um NIPC válido (apenas números)");
    } elseif (empty($_POST["nome"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o nome da empresa");
    } elseif (strlen($_POST["nome"]) > 50) {
        $arr = array('sucesso' => false, 'mensagem' => "O nome da empresa é demasiado longo");
    } elseif (empty($_POST["tipo_emp"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Escolha o tipo de empresa");
    } elseif (empty($_POST["atividade"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Escolha a atividade da empresa");
    } elseif (empty($_POST["morada"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira a morada da empresa");
    } elseif (strlen($_POST["morada"]) > 100) {
        $arr = array('sucesso' => false, 'mensagem' => "A morada da empresa é demasiado longa");
    } elseif (empty($_POST["cod_postal"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o código postal da empresa");
    } elseif (!preg_match('/^\d{4}\-\d{3}$/', $_POST["cod_postal"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira um código postal válido (ex: 5300-131)");
    } elseif (empty($_POST["localidade"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira a localidade da empresa");
    } elseif (!preg_match('/^[a-zA-ZéúíóáÉÚÍÓÁàÀõãÕÃêôâÊÔÂçÇ\s\-]+$/', $_POST["localidade"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira uma localidade válida");
    } elseif (empty($_POST["pais"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira o país onde se localiza a empresa");
    } elseif (!preg_match('/^[a-zA-ZéúíóáÉÚÍÓÁàÀõãÕÃêôâÊÔÂçÇ\s\-]+$/', $_POST["pais"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Insira um pais válido");
    } elseif (empty($_POST["cap_soc_m"])) {
        $arr = array('sucesso' => false, 'mensagem' => "Preencha o capital social monetário");
    } else {
        $niss = $_POST["niss"];
        $nipc = $_POST["nipc"];
        $designacao = ucwords_pt($_POST["nome"]);
        $tipo = $_POST["tipo_emp"];
        $atividade = $_POST["atividade"];
        $cap_soc_m = $_POST["cap_soc_m"];
        $cap_soc_e = $_POST["cap_soc_e"];
        $morada = ucwords_pt($_POST["morada"]);
        $cod_postal = $_POST["cod_postal"];
        $localidade = ucwords_pt($_POST["localidade"]);
        $pais_nome = ucwords_pt($_POST["pais"]);
        $email = $_POST["email"];
        $grupo = $_POST["grupo"];
        $query_empresa = $connection->prepare("SELECT nome FROM empresa WHERE ativo='1' AND nome=:designacao LIMIT 1");
        $query_empresa->execute(array(':designacao' => $designacao));
        $num_empresa = $query_empresa->rowCount();
        if ($num_empresa == 1) {
            $arr = array('sucesso' => false, 'mensagem' => "O nome para a empresa já existe. Escolha outro nome, por favor");
        } else {
            $id_entidade = $_SESSION['id_entidade'];
            $query_banco = $connection->prepare("SELECT eb.id_banco, b.cod_banco FROM banco b INNER JOIN entidade_banco eb ON b.id=eb.id_banco INNER JOIN entidade ent ON eb.id_entidade=ent.id WHERE ent.id=:id_entidade");
            $query_banco->execute(array(':id_entidade' => $id_entidade));
            $row_banco = $query_banco->fetch(PDO::FETCH_ASSOC);
            $id_banco = $row_banco['id_banco'];
            $pais = "PT50";
            $cod_banco = $row_banco['cod_banco'];
            $cod_balcao = "2040";
            $num = gerarIBAN($pais, $cod_banco, $cod_balcao);
            $num_array = explode("_", $num);
            $num_conta = $num_array[2];
            $query_num_conta = $connection->prepare("SELECT num_conta FROM conta");
            $query_num_conta->execute();
            $linha_num_conta = $query_num_conta->fetch(PDO::FETCH_ASSOC);
            $count = $query_num_conta->rowCount();
            for ($i = 0; $i < $count; $i++) {
                if ($num_conta == $linha_num_conta['num_conta']) {
                    $num = gerarIBAN($pais, $cod_banco, $cod_balcao);
                    $num_array = explode("_", $num);
                    $num_conta = $num_array[2];
                    $i = 0;
                }
            }
            $nib = $num_array[1] . $num_array[2] . $num_array[3];
            $iban = $num_array[0] . $num_array[1] . $num_array[2] . $num_array[3];
            $query_registo = $connection->prepare("INSERT INTO empresa (niss, nipc, nome, tipo, atividade, morada, cod_postal, localidade, pais, email, id_grupo, ativo) VALUES (:niss, :nipc, :designacao, :tipo, :atividade, :morada, :cod_postal, :localidade, :pais, :email, :grupo, :ativo)");
            $query_registo->execute(array(':niss' => $niss, ':nipc' => $nipc, ':designacao' => $designacao, ':tipo' => $tipo, ':atividade' => $atividade, ':morada' => $morada, ':cod_postal' => $cod_postal, ':localidade' => $localidade, ':pais' => $pais_nome, ':email' => $email, ':grupo' => $grupo, ':ativo' => "1"));
            $num_registo = $query_registo->rowCount();
            if ($num_registo == 1) {
                $query_empresa2 = $connection->prepare("SELECT id_empresa FROM empresa WHERE ativo='1' AND niss=:niss AND nipc=:nipc AND nome=:designacao AND tipo=:tipo AND atividade=:atividade AND morada=:morada AND cod_postal=:cod_postal AND localidade=:localidade AND pais=:pais LIMIT 1");
                $query_empresa2->execute(array(':niss' => $niss, ':nipc' => $nipc, ':designacao' => $designacao, ':tipo' => $tipo, 'atividade' => $atividade, ':morada' => $morada, ':cod_postal' => $cod_postal, ':localidade' => $localidade, ':pais' => $pais_nome));
                $count_empresa = $query_empresa2->rowCount();
                if ($count_empresa == 1) {
                    $row = $query_empresa2->fetch(PDO::FETCH_ASSOC);
                    $id_empresa = $row['id_empresa'];
                    $date = gmdate('Y-m-d H:i:s');
                    $query_capital = $connection->prepare("INSERT INTO capital_social (id_empresa, capital_social_monetario, capital_social_especie, date) VALUES (:id_empresa, :cap_soc_m, :cap_soc_e, :date)");
                    $query_capital->execute(array(':id_empresa' => $id_empresa, ':cap_soc_m' => $cap_soc_m, ':cap_soc_e' => $cap_soc_e, ':date' => $date));
                    $count_capital = $query_capital->rowCount();
                    if ($count_capital == 1) {
                        $id_utilizador = $_SESSION['id_utilizador'];
                        $query_update = $connection->prepare("UPDATE utilizador SET id_empresa=:id_empresa WHERE id=:id_utilizador LIMIT 1");
                        $query_update->execute(array(':id_empresa' => $id_empresa, ':id_utilizador' => $id_utilizador));
                        $count_update = $query_update->rowCount();
                        if ($count_update == 1) {
                            $num_conta12 = '0' . $num_conta;
                            $query_conta = $connection->prepare("INSERT INTO conta (num_conta, nib, iban, tipo_conta, id_banco, id_empresa, date) VALUES (:num_conta12, :nib, :iban, 'ordem', :id_banco, :id_empresa, :date)");
                            $query_conta->execute(array(':num_conta12' => $num_conta12, ':nib' => $nib, ':iban' => $iban, ':id_banco' => $id_banco, ':id_empresa' => $id_empresa, ':date' => $date));
                            $count_conta = $query_conta->rowCount();
                            if ($count_conta == 1) {
                                $query_conta2 = $connection->prepare("SELECT id FROM conta WHERE num_conta=:num_conta12 AND nib=:nib AND iban=:iban AND tipo_conta='ordem' AND id_banco=:id_banco AND id_empresa=:id_empresa AND date=:date LIMIT 1");
                                $query_conta2->execute(array(':num_conta12' => $num_conta12, ':nib' => $nib, ':iban' => $iban, ':id_banco' => $id_banco, ':id_empresa' => $id_empresa, ':date' => $date));
                                $count_conta2 = $query_conta2->rowCount();
                                if ($count_conta2 == 1) {
                                    $linha_conta2 = $query_conta2->fetch(PDO::FETCH_ASSOC);
                                    $id_conta = $linha_conta2['id'];
                                    $saldo_abertura = $cap_soc_m;

                                    $query = $connection->prepare("SELECT c.mes, c.ano, c.data_inicio, c.hora_inicio, c.data_fim, c.hora_fim FROM calendario c INNER JOIN grupo g ON c.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND NOW() BETWEEN CONCAT(data_inicio, ' ', hora_inicio) AND CONCAT(data_fim, ' ', hora_fim) LIMIT 1");
                                    $query->execute(array(':id_empresa' => $id_empresa));
                                    $rowCount = $query->rowCount();
                                    if ($rowCount > 0) {
                                        $linha = $query->fetch(PDO::FETCH_ASSOC);
                                        $mes = $linha['mes'];
                                        $ano = $linha['ano'];
                                        $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
                                        $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
                                        $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
                                        $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
                                        $diferenca_datas = time_diff($data_fim, $data_inicio);
                                        $diff_agr = time_diff($date, $data_inicio);
                                        $factor = $diff_agr / $diferenca_datas;
                                        $distancia = time_diff(date("Y-m-d H:i:s", strtotime("$ano-$mes-$ultimo_dia 23:59:59")), date("Y-m-d H:i:s", strtotime("$ano-$mes-$primeiro_dia 00:00:00")));
                                        $tempo_referencia = strtotime(date("$ano-$mes-01 00:00:00"));
                                        $data_virtual = ($factor * $distancia) + $tempo_referencia;
                                        $date = date("Y-m-d H:i:s", $data_virtual);
                                    }
                                    $query_movimento_insert = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, description, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :date, 'ABE', 'Depósito de abertura de conta', 'Account opening deposit', :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
                                    $query_movimento_insert->execute(array(':id_conta' => $id_conta, ':date' => $date, ':credito' => $saldo_abertura, ':saldo_controlo' => $saldo_abertura, ':saldo_contab' => $saldo_abertura, ':saldo_disp' => $saldo_abertura));
                                    $count_movimento = $query_movimento_insert->rowCount();
                                    if ($count_movimento == 1) {
                                        $query_regras = $connection->prepare("SELECT id_regra, valor, simbolo, id_banco FROM regra");
                                        $query_regras->execute();
                                        $num = $query_regras->rowCount();
                                        if ($num > 0) {
                                            $id_regra_array = array();
                                            $valor_array = array();
                                            $simbolo_array = array();
                                            $banco_array = array();
                                            while ($values = $query_regras->fetch(PDO::FETCH_ASSOC)) {
                                                $id_regra_array[] = $values['id_regra'];
                                                $valor_array[] = $values['valor'];
                                                $simbolo_array[] = $values['simbolo'];
                                                $banco_array[] = $values['id_banco'];
                                            }
                                            for ($i = 0; $i < $num; $i++) {
                                                $stmt = $connection->prepare("INSERT INTO regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:id_regra, :id_empresa, :date, :valor, :simbolo, :id_banco)");
                                                $stmt->execute(array(':id_regra' => $id_regra_array[$i], ':id_empresa' => $id_empresa, ':date' => $date, ':valor' => $valor_array[$i], ':simbolo' => $simbolo_array[$i], ':id_banco' => $banco_array[$i]));
                                            }
                                            $query_licenca = $connection->prepare("UPDATE entidade ent SET ent.licencas=ent.licencas-1 WHERE ent.id=:id_entidade AND ent.licencas>0 LIMIT 1");
                                            $query_licenca->execute(array(':id_entidade' => $id_entidade));
                                            $count_licenca = $query_licenca->rowCount();
                                            if ($count_licenca == 1) {
                                                unset($_SESSION['id_entidade']);
                                                $arr = array('sucesso' => true);
                                            } else {
                                                $arr = array('sucesso' => false, 'mensagem' => "Não existem licenças para a sua instituição");
                                            }
                                        } else {
                                            $arr = array('sucesso' => false, 'mensagem' => "Não foi possível encontrar as regras");
                                        }
                                    } else {
                                        $arr = array('sucesso' => false, 'mensagem' => "Não foi possível fazer o depósito inicial");
                                    }
                                } else {
                                    $arr = array('sucesso' => false, 'mensagem' => "A conta não foi encontrada");
                                }
                            } else {
                                $arr = array('sucesso' => false, 'mensagem' => "Não foi possível criar uma conta");
                            }
                        } else {
                            $arr = array('sucesso' => false, 'mensagem' => "Não foi possível associar o utilizador à empresa");
                        }
                    } else {
                        $arr = array('sucesso' => false, 'mensagem' => "Não foi possível inserir os capitais sociais");
                    }
                } else {
                    $arr = array('sucesso' => false, 'mensagem' => "Não foi possível encontrar a empresa");
                }
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "Não foi possível criar a empresa");
            }
        }
    }
} elseif ($_POST['tipo'] == "reg_user") {
    $valido = true;
    
    require_once "../recaptcha/recaptchalib.php";
//    $private_key = "6Leo8BATAAAAAPcdEakIZNeJgykVQBtb9GGhBQjy"; //estava esta(para funcionar no site)
      $private_key="6LcME8wUAAAAAJo4AXoWBiuN7EhprUGnzWSlyBP7";//meti esta (para funcionar no localhost)
    
    $resp = null;
    $reCaptcha = new ReCaptcha($private_key);
    
    if ($_POST["g-recaptcha-response"]) {
        $resp = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);
        if ($resp != null && $resp->success) {
            if ($_POST['modo'] == "ldap") {
                if (empty($_POST["username"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escreva o nome de utilizador");
                } elseif (empty($_POST["password"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escreva a palavra-passe");
                } elseif (empty($_POST["grupo"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escolha um grupo");
                }
            } elseif ($_POST['modo'] == "nldap") {
                if (!isset($_POST["name"], $_POST["username"], $_POST["password"], $_POST["pass_conf"], $_POST['email'])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal, tente efetuar o registo novamente");
                } elseif (empty($_POST["name"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escreva o seu nome");
                } elseif (empty($_POST["username"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escreva o nome de utilizador");
                } elseif (empty($_POST["password"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escreva a palavra-passe");
                } elseif (empty($_POST["pass_conf"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escreva a confirmação da palavra-passe");
                } elseif ($_POST["password"] != $_POST["pass_conf"]) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Palavras-passe não correspondem");
                } elseif (empty($_POST["grupo"])) {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Escolha um grupo");
                }
            }
            if ($valido == true) {
                $username = strtolower($_POST["username"]);
                $password = $_POST["password"];
                $grupo = $_POST["grupo"];

                //$query_permissoes_grupo = $connection->prepare("SELECT * FROM (SELECT g.id AS id_grupo, eg.estado FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN utilizador u ON eg.id_user=u.id WHERE g.id=:id_grupo ORDER BY date(eg.`data`) DESC, time(eg.`data`) DESC) AS t1 GROUP BY id_grupo LIMIT 1"); //estava esta
                $query_permissoes_grupo = $connection->prepare("SELECT * FROM (SELECT g.id AS id_grupo, eg.estado,eg.data FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN utilizador u ON eg.id_user=u.id WHERE g.id=:id_grupo ORDER BY eg.data ASC) AS t1 ORDER BY t1.data DESC LIMIT 1"); //meti esta
                $query_permissoes_grupo->execute(array(':id_grupo' => $grupo));
                $linha_permissoes_grupo = $query_permissoes_grupo->fetch(PDO::FETCH_ASSOC);
                if ($linha_permissoes_grupo['estado'] == "1") {
                    $query_utilizador = $connection->prepare("SELECT login FROM utilizador WHERE login=:username LIMIT 1");
                    $query_utilizador->execute(array(':username' => $username));
                    $num_utilizadores = $query_utilizador->rowCount();
                    if ($num_utilizadores == 1) {
                        $valido = false;
                        $arr = array('sucesso' => false, 'mensagem' => "Nome de utilizador já existe");
                    } else {
                        $id_entidade = $_SESSION['id_entidade'];
                        $date = date('Y-m-d H:i:s');
                        if ($_POST['modo'] == "nldap") {
                            $palavras = explode(" ", $_POST["name"]);
                            $primeiro_nome = $palavras[0];
                            $nome_completo = $_POST["name"];
                            $email = $_POST["email"];
                            $u_ldap = "0";
                        } elseif ($_POST['modo'] == "ldap") {
                            if (!isset($_SESSION['id_entidade'])) {
                                $query_entidade = $connection->prepare("SELECT ent.id FROM entidade ent INNER JOIN utilizador u ON u.id_entidade=ent.id WHERE u.login=:login");
                                $query_entidade->execute(array(':login' => $username));
                                $linha_entidade = $query_entidade->fetch(PDO::FETCH_ASSOC);
                                $id_entidade_rec = $linha_entidade['id'];
                            } else {
                                $id_entidade_rec = $_SESSION['id_entidade'];
                            }
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
                            //
                            $u_ldap = "1";
                            //
                            if ($ldapconn) {
                                $ldapbind = @ldap_bind($ldapconn, str_replace('$username', $username, $ldaprdns), $password);
                                if (!$ldapbind) {
                                    $ldapbind = @ldap_bind($ldapconn, str_replace('$username', $username, $ldaprdnp), $password);
                                    if (!$ldapbind) {
                                        $valido = false;
                                        $arr = array('sucesso' => false, 'mensagem' => "A sua palavra-passe ou nome de utilizador estão errados");
                                    } else {
                                        $sr_person = ldap_search($ldapconn, $ldaprdnsearchp, str_replace('$username', $username, $filter_person));
                                        $sr = ldap_get_entries($ldapconn, $sr_person);
                                        $primeiro_nome = $sr[0][$ldap_givenname][0];
                                        $nome_completo = $sr[0][$ldap_cn][0];
                                        $email = $sr[0][$ldap_mail][0];
                                        //$u_ldap = "1";
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
                        }
                    }
                } else {
                    $valido = false;
                    $arr = array('sucesso' => false, 'mensagem' => "Não tem permissões para se registar");
                }
                if ($valido == true) {
                    $connection->beginTransaction();
                    $query_geraSalt = $connection->prepare("SET @salt = UNHEX(SHA2(UUID(), 256))");
                    $query_geraSalt->execute();
                    $query_registo = $connection->prepare("INSERT INTO utilizador (login, p_nome, nome, pass, salt, email, tipo, id_entidade, u_ldap, date) VALUES (:username, :p_nome, :nome, UNHEX(SHA2(CONCAT(:password, HEX(@salt)), 256)), @salt, :email, 'user', :id_entidade, :u_ldap, :date)");
                    $query_registo->execute(array(':username' => $username, ':p_nome' => $primeiro_nome, ':nome' => $nome_completo, ':password' => $password, ':email' => $email, ':id_entidade' => $id_entidade, ':u_ldap' => $u_ldap, ':date' => $date));
                    $num_registos = $query_registo->rowCount();
                    $user_id = $connection->lastInsertId();

//                    if ($id_entidade == "1") { // estava este( comentado pois o guacamole deixo de funcionar á muito tempo segundo o Ricardo)
//                        $encrypted_data = mc_encrypt($password, ENCRYPTION_KEY);
//                        $query_conexoes = $connection->prepare("SELECT cg.id_conn FROM conexao_guacamole cg INNER JOIN entidade ent ON cg.id_entidade=ent.id AND ent.id=:id_entidade");
//                        $query_conexoes->execute(array(':id_entidade' => $id_entidade));
//                        $num = $query_conexoes->rowCount();
//                        if ($num > 0) {
//                            while ($values = $query_conexoes->fetch(PDO::FETCH_ASSOC)) {
//                                $id_conn[] = $values['id_conn'];
//                            }
//                            for ($i = 0; $i < $num; $i++) {
//                                $stmt = $connection->prepare("INSERT INTO guac_user (id_conexao, id_user, password) VALUES (:id_conexao, :id_user, :password)");
//                                $stmt->execute(array(':id_conexao' => $id_conn[$i], ':id_user' => $user_id, ':password' => $encrypted_data));
//                            }
//                        }
//                    }
                    $connection->commit();
                    if ($num_registos == 1) {
                        $_SESSION['id_utilizador'] = $user_id;
                        $_SESSION['id_grupo'] = $grupo;
                        $arr = array('sucesso' => true, 'pagina' => "pag_regemp.php");
                    } else {
                        $arr = array('sucesso' => false, 'mensagem' => "Algo correu mal na inserção");
                    }
                }
            }
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Não foi possivel validar Captcha");
        }
    } else {
        $arr = array('sucesso' => false, 'mensagem' => "Por favor, prove que não é um robô");
    }
} elseif ($_POST['tipo'] == "checkLDAP") {
    if (session_id() == '') {
        session_start();
    }
    if (!isset($_SESSION['ldap']) || $_SESSION['ldap'] == "0") {
        $arr = array('ldap' => false);
    } elseif (isset($_SESSION['ldap']) && $_SESSION['ldap'] == "1") {
        $arr = array('ldap' => true);
    }
}

$connection = null;
echo json_encode($arr);