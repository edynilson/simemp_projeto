<?php

/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-01 18:12:35
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-08-27 19:20:10
 */

include('../conf/check_pastas.php');
include_once('functions.php');

$query_moeda = $connection->prepare("SELECT mo.simbolo, mo.ISO4217 FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$id_empresa = $_SESSION['id_empresa'];
$id_utilizador = $_SESSION['id_utilizador'];

if ($_POST['tipo'] == "get_rubrica") {
    $query_rubrica = $connection->prepare("SELECT cod.designacao FROM codigo cod WHERE cod.id=:cod_rubrica");
    $query_rubrica->execute(array(':cod_rubrica' => $_POST['cod']));
    $linha_rubrica = $query_rubrica->fetch(PDO::FETCH_ASSOC);

    $arr = array('sucesso' => true, 'desig' => $linha_rubrica['designacao']);
    logClicks($connection, "136");
} elseif ($_POST['tipo'] == "arr_rubrica") {
    $query_rubrica = $connection->prepare("SELECT cod.id, cod.rubrica FROM codigo cod");
    $query_rubrica->execute();

    while ($linha_rubrica = $query_rubrica->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id' => $linha_rubrica['id'], 'rubrica' => $linha_rubrica['rubrica']);
    }
    $arr = array('dados_in' => $arr_dados, 'moeda' => $linha_moeda['simbolo']);
} elseif ($_POST['tipo'] == "guardar_decRet") {
    $dados = json_decode($_POST['dados'], true);
    $data_entrega = date("Y-m-d H:i:s", strtotime($dados["data_entrega"]));
    $data_limit = date("Y-m-d H:i:s", strtotime($dados["data_limit"]));
    $total = $dados["total"];
    if ($dados["n_res"] == true) {
        $n_res = "0";
    } else {
        $n_res = "1";
    }
    $query_ins_dec_retencao = $connection->prepare("INSERT INTO dec_retencao (data, data_lim_pag, residentes, total, id_empresa, pago) VALUES (:data_ent, :data_lim, :n_res, :total, :id_empresa, :pago)");
    $query_ins_dec_retencao->execute(array(':data_ent' => $data_entrega, ':data_lim' => $data_limit, ':n_res' => $n_res, ':total' => $total, ':id_empresa' => $_SESSION['id_empresa'], ':pago' => '0'));

    $query_select = $connection->prepare("SELECT dr.id FROM dec_retencao dr INNER JOIN empresa emp ON dr.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY dr.id DESC LIMIT 1");
    $query_select->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $linha_select = $query_select->fetch(PDO::FETCH_ASSOC);

    $mes = date('m', strtotime($data_limit));
    $ano = date('Y', strtotime($data_limit));
    $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
    $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
    $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
    $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
    $rowCount = $query->rowCount();

    if ($rowCount > 0) {
        $linha = $query->fetch(PDO::FETCH_ASSOC);
        $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
        $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
        $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_limit);
    } else {
        $date = new DateTime();
        $intervalo = new DateInterval('P1M');
        $date->add($intervalo);
        $data_lim = $date->format('Y-m' . '-20 23:59:59');
        $data_lim_r = date("Y-m-d H:i:s", strtotime($data_lim));
    }

    $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_dec_retencao, tipo, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_dr, :tipo, :data_lim, :data_lim_r, :val, :pago)");
    $query_pagamento->execute(array(':id_dr' => $linha_select['id'], ':tipo' => "D", ':data_lim' => $data_limit, ':data_lim_r' => $data_lim_r, ':val' => $total, ':pago' => '0'));

    foreach ($dados["linha"] as $key => $value) {
        $zona = $dados["linha"][$key]["zona"];
        $rubrica = $dados["linha"][$key]["rubrica"];
        $valor = $dados["linha"][$key]["valor"];

        $query_inserir = $connection->prepare("INSERT INTO dec_retencao_empresa (id_dec_retencao, rubrica, zona, valor) VALUES (:id_dr, :rubrica, :zona, :valor)");
        $query_inserir->execute(array(':id_dr' => $linha_select['id'], ':rubrica' => $rubrica, ':zona' => $zona, ':valor' => $valor));
    }

    $arr = array('sucesso' => true, 'id' => $linha_select['id']);
    logClicks($connection, "137");
} elseif ($_POST['tipo'] == "ver_entregas") {
    $query_entregas = $connection->prepare("SELECT en.id, date_format(en.data,'%d-%m-%Y') AS data, en.valor, IF(en.f_prazo='N', 'Não', 'Sim') AS prazo, IF(en.pago=0, 'Não', 'Sim') AS pago, te.designacao, en.mes, en.ano, en.ficheiro FROM entrega en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN tipo_entrega te ON en.id_tipo_entrega=te.id WHERE emp.ativo='1' AND emp.id_empresa=:id");
    $query_entregas->execute(array(':id' => $_SESSION['id_empresa']));
    $rowCount = $query_entregas->rowCount();

    while ($linha_entregas = $query_entregas->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('id' => $linha_entregas['id'], 'data' => $linha_entregas['data'], 'valor' => $linha_entregas['valor'], 'prazo' => $linha_entregas['prazo'], 'pago' => $linha_entregas['pago'], 'designacao' => $linha_entregas['designacao'], 'mes' => $linha_entregas['mes'], 'ano' => $linha_entregas['ano'], 'ficheiro' => $linha_entregas['ficheiro']);
    }

    if ($rowCount > 0) {
        $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem entregas");
    }
    logClicks($connection, "134");
} elseif ($_POST['tipo'] == "ver_dec_ret") {
    $query_dec_ret = $connection->prepare("SELECT dr.id, date_format(dr.data_lim_pag,'%d-%m-%Y') AS data, dr.residentes, dr.total, dr.pago FROM dec_retencao dr INNER JOIN empresa emp ON emp.id_empresa=dr.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
    $query_dec_ret->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $rowCount = $query_dec_ret->rowCount();

    while ($linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC)) {
        if ($linha_dec_ret['residentes'] == "0") {
            $n_res = "Não";
        } else {
            $n_res = "Sim";
        }
        if ($linha_dec_ret['pago'] == "0") {
            $pago = "Não";
        } else {
            $pago = "Sim";
        }
        $arr_dados[] = array('id' => $linha_dec_ret['id'], 'data' => $linha_dec_ret['data'], 'n_res' => $n_res, 'total' => $linha_dec_ret['total'], 'pago' => $pago);
    }

    if ($rowCount > 0) {
        $arr = array('sucesso' => true, 'vazio' => false, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem declarações de retenções");
    }
    logClicks($connection, "132");
} elseif ($_POST['tipo'] == "dec_ret_detalhes") {
    $query_dec_ret_detalhes = $connection->prepare("SELECT cod.rubrica, dre.zona, dre.valor FROM dec_retencao dr INNER JOIN dec_retencao_empresa dre ON dr.id=dre.id_dec_retencao INNER JOIN codigo cod ON dre.rubrica=cod.id INNER JOIN empresa emp ON emp.id_empresa=dr.id_empresa WHERE emp.ativo='1' AND dr.id=:id_dec_ret");
    $query_dec_ret_detalhes->execute(array(':id_dec_ret' => $_POST['id_dec_ret']));

    while ($linha_dec_ret = $query_dec_ret_detalhes->fetch(PDO::FETCH_ASSOC)) {
        $arr_dados[] = array('rubrica' => $linha_dec_ret['rubrica'], 'zona' => $linha_dec_ret['zona'], 'valor' => $linha_dec_ret['valor']);
    }

    $query_dec_ret = $connection->prepare("SELECT dr.data_lim_pag, dr.residentes, dr.pago, dr.total FROM dec_retencao dr INNER JOIN dec_retencao_empresa dre ON dr.id=dre.id_dec_retencao INNER JOIN codigo cod ON dre.rubrica=cod.id INNER JOIN empresa emp ON emp.id_empresa=dr.id_empresa WHERE emp.ativo='1' AND dr.id=:id_dec_ret");
    $query_dec_ret->execute(array(':id_dec_ret' => $_POST['id_dec_ret']));
    $linha_dec_ret = $query_dec_ret->fetch(PDO::FETCH_ASSOC);

    if ($linha_dec_ret['residentes'] == "0") {
        $n_res = "Não";
    } else {
        $n_res = "Sim";
    }
    if ($linha_dec_ret['pago'] == "0") {
        $pago = "Não";
    } else {
        $pago = "Sim";
    }
    $arr = array('sucesso' => true, 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados, 'data_completa' => $linha_dec_ret['data_lim_pag'], 'res' => $n_res, 'pago' => $pago, 'total' => $linha_dec_ret['total']);
    logClicks($connection, "133");
} elseif ($_POST['tipo'] == "fatura_detalhes") {
    $query_fatura = $connection->prepare("SELECT f.num_fatura, f.cliente, f.valor, date_format(f.data_virtual, '%d-%m-%Y %H:%i:%s') AS data_virtual, date_format(f.data_lim_v, '%d-%m-%Y %H:%i:%s') AS data_lim, f.pago FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND f.id_fatura=:id_fatura");
    $query_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fatura' => $_POST['id_fatura']));
    $linha_fatura = $query_fatura->fetch(PDO::FETCH_ASSOC);
    $arr = array('sucesso' => true, 'num_fatura' => $linha_fatura['num_fatura'], 'cliente' => $linha_fatura['cliente'], 'valor' => $linha_fatura['valor'], 'data_virtual' => $linha_fatura['data_virtual'], 'data_lim' => $linha_fatura['data_lim'], 'pago' => $linha_fatura['pago']);
    logClicks($connection, "128");
} elseif ($_POST['tipo'] == "up_entrega") {
    $allowedExts = array("pdf");
    $extension = end(explode(".", $_FILES["fileAnexar"]["name"]));
    $filename = str_replace(' ', '_', retiraEsp($_POST['nome_tipo']));
    $data = gmdate('d/m/o H:i:s');
    $condicoes = array("/", ":", " ");
    $data_final = str_replace($condicoes, "", $data);
    $nome_ficheiro = $filename . "_" . $data_final . "_" . $_SESSION['id_empresa'];
    $nome_disco = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', str_replace(' ', '_', retiraEsp($_POST['nome_tipo']))) . "_" . $data_final . "_" . $_SESSION['id_empresa'];
    $path = "../central_publica/";
    if (isset($_FILES["fileAnexar"])) {
        if ((($_FILES["fileAnexar"]["type"] == "application/pdf")) && in_array($extension, $allowedExts)) {
            if (($_FILES["fileAnexar"]["size"] < 1048576)) {
                if ($_FILES["fileAnexar"]["error"] <= 0) {
                    if (file_exists($path . $nome_ficheiro . "." . $extension)) {
                        $arr = array('sucesso' => false, 'mensagem' => "O ficheiro que anexou já existe, tente novamente");
                    } else {
                        // move_uploaded_file($_FILES["fileAnexar"]["tmp_name"], $path . $nome_disco . "." . $extension);
                        if (move_uploaded_file($_FILES["fileAnexar"]["tmp_name"], $path . $nome_disco . "." . $extension)) {
                            $query_entrega = $connection->prepare("INSERT INTO entrega (ficheiro, data, valor, f_prazo, pago, mes, ano, id_tipo_entrega, id_empresa) VALUES (:ficheiro, :data, :valor, :f_prazo, :pago, :mes, :ano, :tipo_ent, :id_empresa)");
                            $query_entrega->execute(array(':ficheiro' => substr($path, 3) . $nome_ficheiro . "." . $extension, ':data' => gmdate('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':valor' => $_POST['valor'], ':f_prazo' => $_POST['f_prazo'], ':pago' => '0', ':mes' => $_POST['mes'], ':ano' => $_POST['ano'], ':tipo_ent' => $_POST['tipo_entrega'], ':id_empresa' => $_SESSION['id_empresa']));

                            $query_select_ent = $connection->prepare("SELECT en.id FROM entrega en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY en.id DESC LIMIT 1");
                            $query_select_ent->execute(array(':id_empresa' => $_SESSION['id_empresa']));
                            $linha_dados = $query_select_ent->fetch(PDO::FETCH_ASSOC);

                            $data_tmp = date('Y-m-d H:i:s', strtotime($_POST["data_virtual"]));
                            $date = new DateTime($data_tmp);
                            $intervalo = new DateInterval('P2M');
                            $date->add($intervalo);
                            $data_lim = $date->format('Y-m-d H:i:s');

                            $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim));
                            $mes = date('m', strtotime($data_recebida));
                            $ano = date('Y', strtotime($data_recebida));
                            $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
                            $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
                            $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
                            $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
                            $rowCount = $query->rowCount();

                            if ($rowCount > 0) {
                                $linha = $query->fetch(PDO::FETCH_ASSOC);
                                $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
                                $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];

                                $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim);
                            } else {
                                $date = new DateTime();
                                $intervalo = new DateInterval('P1M');
                                $date->add($intervalo);
                                $data_lim = $date->format('Y-m-d H:i:s');

                                $data_lim_r = date("Y-m-d H:i:s", strtotime($data_lim));
                            }

                            $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_entrega, tipo, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_ent, :tipo, :data_limit, :data_lim_r, :valor, :pago)");
                            $query_pagamento->execute(array(':id_ent' => $linha_dados['id'], ':tipo' => "D", ':data_limit' => $data_lim, ':data_lim_r' => $data_lim_r, ':valor' => $_POST['valor'], ':pago' => '0'));

                            $arr = array('sucesso' => true);
                        } else {
                            $arr = array('sucesso' => false, 'mensagem' => "Ocorreu um erro no carregamento do ficheiro");
                        }
                    }
                } else {
                    $arr = array('sucesso' => false, 'mensagem' => "Ocorreu um erro no carregamento do ficheiro");
                }
            } else {
                $arr = array('sucesso' => false, 'mensagem' => "O ficheiro ultrapassa o tamanho máximo permitido: 2MB");
            }
        } else {
            // $arr = array('sucesso' => false, 'mensagem' => "Por favor, anexe um ficheiro com a extensão .pdf");
            $arr = array('sucesso' => false, 'mensagem' => "O formato do ficheiro deve ser .pdf");
        }
    }
    logClicks($connection, "138");
}
/* elseif ($_POST['tipo'] == "reg_faturas") {
  $num_fatura = $_POST['num_fatura'];
  $query_fatura_s = $connection->prepare("SELECT f.num_fatura FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND f.id_factoring IS NULL AND f.num_fatura=:num_fatura AND emp.id_empresa=:id_empresa LIMIT 1");
  $query_fatura_s->execute(array(':id_empresa' => $id_empresa, ':num_fatura' => $num_fatura));
  $count_query_fatura_s = $query_fatura_s->rowCount();
  if ($count_query_fatura_s == 1) {
  $arr = array('sucesso' => false, 'mensagem' => "O número da fatura já existe");
  }
  else {
  $query_plafond_fatura = $connection->prepare("SELECT re.id_regra, re.id_empresa, re.valor, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
  $query_plafond_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (faturas)"));
  $linha_plafond_fatura = $query_plafond_fatura->fetch(PDO::FETCH_ASSOC);
  if ($_POST['valor_fatura'] > 0) $valor = $_POST['valor_fatura'];
  else {
  $diferenca_adiant = -($_POST['valor_fatura']);
  $valor = 0;
  }

  if ($valor <= $linha_plafond_fatura['valor']) {
  $cliente = $_POST['cliente'];
  $data_v = date("Y-m-d H:i:s", strtotime($_POST['data_virtual_fatura']));
  $date = new DateTime($data_v);
  $intervalo = new DateInterval("P1M");
  $date->add($intervalo);
  $data_lim_v = $date->format("Y-m-d H:i:s");
  $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim_v));
  $mes = date('m', strtotime($data_recebida));
  $ano = date('Y', strtotime($data_recebida));
  $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
  $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
  $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
  $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
  $linha = $query->fetch(PDO::FETCH_ASSOC);
  $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
  $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
  $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim_v);

  $id_adiantamento = $_POST['id_adiantamento'];
  if ($valor == 0) {
  $query_update_adiant = $connection->prepare("UPDATE adiantamento SET valor=:adiantamento WHERE id_adiantamento=:id_adiantamento");
  $query_update_adiant->execute(array(':adiantamento' => $diferenca_adiant, ':id_adiantamento' => $id_adiantamento));
  } elseif ($_POST['adiantamento'] == "true" && $valor > 0) {
  $query_update_adiant = $connection->prepare("UPDATE adiantamento SET pago=1 WHERE id_adiantamento=:id_adiantamento");
  $query_update_adiant->execute(array(':id_adiantamento' => $id_adiantamento));
  }

  $query_fatura_i = $connection->prepare("INSERT INTO fatura (num_fatura, cliente, valor, data_virtual, data_lim_v, data_lim_r, pago, id_empresa) VALUES (:num_fatura, :cliente, :valor, :data_v, :data_lim_v, :data_lim_r, :pago, :id_empresa)");
  $query_fatura_i->execute(array(':num_fatura' => $num_fatura, ':cliente' => $cliente, ':valor' => $valor, ':data_v' => $data_v, ':data_lim_v' => $data_lim_v, ':data_lim_r' => $data_lim_r, ':pago' => 0, ':id_empresa' => $id_empresa));
  $query_re_plafond = $connection->prepare("INSERT INTO regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:id_regra, :id_empresa, :data, :valor, :simbolo, :id_banco)");
  $query_re_plafond->execute(array(':id_regra' => $linha_plafond_fatura['id_regra'], ':id_empresa' => $linha_plafond_fatura['id_empresa'], ':data' => date("Y-m-d H:i:s"), ':valor' => $linha_plafond_fatura['valor'] - $valor, ':simbolo' => $linha_plafond_fatura['simbolo'], ':id_banco' => $linha_plafond_fatura['id_banco']));
  $query_plafond_fatura = $connection->prepare("SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
  $query_plafond_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (faturas)"));
  $linha_plafond_fatura = $query_plafond_fatura->fetch(PDO::FETCH_ASSOC);
  $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
  $query_faturas->execute(array(':id_empresa' => $_SESSION['id_empresa']));

  $query_adiant_fat = $connection->prepare("SELECT a.id_adiantamento, a.nome_cliente FROM adiantamento a WHERE a.id_empresa=:id_empresa AND a.pago='0' AND a.id_fornecedor IS NULL");
  $query_adiant_fat->execute(array(':id_empresa' => $_SESSION['id_empresa']));

  while ($linha_fatura = $query_faturas->fetch(PDO::FETCH_ASSOC)) {
  $arr_dados[] = array('id_fatura' => $linha_fatura['id_fatura'], 'num_fatura' => $linha_fatura['num_fatura'], 'cliente' => $linha_fatura['cliente'], 'valor' => $linha_fatura['valor']);
  }
  while ($linha_adiant_fat = $query_adiant_fat->fetch(PDO::FETCH_ASSOC)) {
  $arr_adiantamento[] = array('id_adiantamento' => $linha_adiant_fat['id_adiantamento'], 'nome_cliente' => $linha_adiant_fat['nome_cliente']);
  }
  $arr = array('sucesso' => true, 'valor' => $linha_plafond_fatura['valor'], 'dados_in' => $arr_dados, 'dados_adiant' => $arr_adiantamento);
  } else {
  $arr = array('sucesso' => false, 'mensagem' => "Não tem plafond suficiente para registar a fatura");
  }
  }
  logClicks($connection, "129");
  } */ elseif ($_POST['tipo'] == "reg_faturas") {
    $num_fatura = $_POST['num_fatura'];
    /* Added */
    $data_v = date("Y-m-d H:i:s", strtotime($_POST['data_virtual_fatura']));
    // $data_recebida = date("Y-m-d H:i:s", strtotime($_POST['data_virtual_limite_fatura'].' 23:59:59')); // Added time so Payment date will be greater
    $data_recebida = date("Y-m-d H:i:s", strtotime($_POST['data_virtual_limite_fatura']));

    $query_fatura_s = $connection->prepare("SELECT f.num_fatura FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND f.id_factoring IS NULL AND f.num_fatura=:num_fatura AND emp.id_empresa=:id_empresa LIMIT 1");
    $query_fatura_s->execute(array(':id_empresa' => $id_empresa, ':num_fatura' => $num_fatura));
    $count_query_fatura_s = $query_fatura_s->rowCount();

    $query_chk_date = $connection->prepare("SELECT f.num_fatura FROM fatura f WHERE f.id_empresa=:id_empresa AND f.data_lim_v=:data_pag LIMIT 1");
    $query_chk_date->execute(array(':id_empresa' => $id_empresa, ':data_pag' => $data_recebida));
    $count_chk_date = $query_chk_date->rowCount();
    
    if ($data_recebida == "1970-01-01 01:00:00" || $data_v == "1970-01-01 01:00:00") { // Default value of "date" if not set. When user doesn't indicate dates
        $arr = array('sucesso' => false, 'mensagem' => "Tem de escolher as datas");
    } elseif ($data_recebida < $data_v) {
        $arr = array('sucesso' => false, 'mensagem' => "A data de vencimento deve ser maior que a data da fatura");
    } elseif ($count_query_fatura_s == 1) {
        $arr = array('sucesso' => false, 'mensagem' => "O número da fatura já existe");
    } elseif ($count_chk_date == 1) {
        $arr = array('sucesso' => false, 'mensagem' => "Já possui uma fatura com essa data de pagamento");
    } else {
        $query_plafond_fatura = $connection->prepare("SELECT re.id_regra, re.id_empresa, re.valor, re.simbolo, re.id_banco FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
        $query_plafond_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (faturas)"));
        $linha_plafond_fatura = $query_plafond_fatura->fetch(PDO::FETCH_ASSOC);
        if ($_POST['valor_fatura'] > 0)
            $valor = $_POST['valor_fatura'];
        else {
            $diferenca_adiant = -($_POST['valor_fatura']);
            $valor = 0;
        }

        if ($valor <= $linha_plafond_fatura['valor']) {
            $cliente = $_POST['cliente'];
            $mes = date('m', strtotime($data_recebida));
            $ano = date('Y', strtotime($data_recebida));
            $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
            $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
            $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
            $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
            $linha = $query->fetch(PDO::FETCH_ASSOC);
            $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
            $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
            $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_recebida);

            $id_adiantamento = $_POST['id_adiantamento'];
            if ($valor == 0) {
                $query_update_adiant = $connection->prepare("UPDATE adiantamento SET valor=:adiantamento WHERE id_adiantamento=:id_adiantamento");
                $query_update_adiant->execute(array(':adiantamento' => $diferenca_adiant, ':id_adiantamento' => $id_adiantamento));
            } elseif ($_POST['adiantamento'] == "true" && $valor > 0) {
                $query_update_adiant = $connection->prepare("UPDATE adiantamento SET pago=1 WHERE id_adiantamento=:id_adiantamento");
                $query_update_adiant->execute(array(':id_adiantamento' => $id_adiantamento));
            }

            $query_fatura_i = $connection->prepare("INSERT INTO fatura (num_fatura, cliente, valor, data_virtual, data_lim_v, data_lim_r, pago, id_empresa) VALUES (:num_fatura, :cliente, :valor, :data_v, :data_lim_v, :data_lim_r, :pago, :id_empresa)");
            $query_fatura_i->execute(array(':num_fatura' => $num_fatura, ':cliente' => $cliente, ':valor' => $valor, ':data_v' => $data_v, ':data_lim_v' => $data_recebida, ':data_lim_r' => $data_lim_r, ':pago' => 0, ':id_empresa' => $id_empresa));
            $query_re_plafond = $connection->prepare("INSERT INTO regra_empresa (id_regra, id_empresa, data, valor, simbolo, id_banco) VALUES (:id_regra, :id_empresa, :data, :valor, :simbolo, :id_banco)");
            $query_re_plafond->execute(array(':id_regra' => $linha_plafond_fatura['id_regra'], ':id_empresa' => $linha_plafond_fatura['id_empresa'], ':data' => date("Y-m-d H:i:s"), ':valor' => $linha_plafond_fatura['valor'] - $valor, ':simbolo' => $linha_plafond_fatura['simbolo'], ':id_banco' => $linha_plafond_fatura['id_banco']));
            $query_plafond_fatura = $connection->prepare("SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_plafond_fatura->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Plafond (faturas)"));
            $linha_plafond_fatura = $query_plafond_fatura->fetch(PDO::FETCH_ASSOC);
            $query_faturas = $connection->prepare("SELECT f.id_fatura, f.num_fatura, f.cliente, f.valor FROM fatura f INNER JOIN empresa emp ON f.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
            $query_faturas->execute(array(':id_empresa' => $_SESSION['id_empresa']));

            $query_adiant_fat = $connection->prepare("SELECT a.id_adiantamento, a.nome_cliente FROM adiantamento a WHERE a.id_empresa=:id_empresa AND a.pago='0' AND a.id_fornecedor IS NULL");
            $query_adiant_fat->execute(array(':id_empresa' => $_SESSION['id_empresa']));
            $count_adiant = $query_adiant_fat->rowCount();

            while ($linha_fatura = $query_faturas->fetch(PDO::FETCH_ASSOC)) {
                $arr_dados[] = array('id_fatura' => $linha_fatura['id_fatura'], 'num_fatura' => $linha_fatura['num_fatura'], 'cliente' => $linha_fatura['cliente'], 'valor' => $linha_fatura['valor']);
            }
            $arr_adiantamento = "";
            if ($count_adiant > 0) {
                while ($linha_adiant_fat = $query_adiant_fat->fetch(PDO::FETCH_ASSOC)) {
                    $arr_adiantamento[] = array('id_adiantamento' => $linha_adiant_fat['id_adiantamento'], 'nome_cliente' => $linha_adiant_fat['nome_cliente']);
                }
            }
            $arr = array('sucesso' => true, 'valor' => $linha_plafond_fatura['valor'], 'dados_in' => $arr_dados, 'dados_adiant' => $arr_adiantamento);
        } else {
            $arr = array('sucesso' => false, 'mensagem' => "Não tem plafond suficiente para registar a fatura");
        }
    }
    logClicks($connection, "129");
} elseif ($_POST['tipo'] == "produtos_dados") {
    if ($_POST['id'] == 0 && $_POST['nivel'] == 0) {
        // $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY s.nome");
        $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY s.nome");
        $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $num_linhas = $query_produtos->rowCount();
    } elseif ($_POST['id'] == 0 && $_POST['nivel'] == 1) {
        // $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa1.id=:id ORDER BY s.nome");
        $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa1.id=:id ORDER BY s.nome");
        $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id' => $_POST['cat']));
        $num_linhas = $query_produtos->rowCount();
    } elseif ($_POST['id'] == 0 && $_POST['nivel'] == 2) {
        // $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa2.id=:id ORDER BY s.nome");
        $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa2.id=:id ORDER BY s.nome");
        $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id' => $_POST['cat']));
        $num_linhas = $query_produtos->rowCount();
    } elseif ($_POST['id'] != 0 && $_POST['nivel'] == 0) {
        // $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa1.id=:id ORDER BY s.nome");
        $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa1.id=:id ORDER BY s.nome");
        $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id' => $_POST['id']));
        $num_linhas = $query_produtos->rowCount();
    } elseif ($_POST['id'] != 0 && $_POST['nivel'] == 1) {
        // $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa2.id=:id ORDER BY s.nome");
        $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa2.id=:id ORDER BY s.nome");
        $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id' => $_POST['id']));
        $num_linhas = $query_produtos->rowCount();
    } elseif ($_POST['id'] != 0 && $_POST['nivel'] == 2) {
        // $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa3.id=:id ORDER BY s.nome");
        $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND fa3.id=:id ORDER BY s.nome");
        $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id' => $_POST['id']));
        $num_linhas = $query_produtos->rowCount();
    }

    $query_categoria = $connection->prepare("SELECT id, designacao FROM familia WHERE parent=:parent ORDER BY designacao");
    $query_categoria->execute(array(':parent' => $_POST['id']));
    $rows = $query_categoria->rowCount();

    if ($rows > 0) {
        for ($i = 0; $i < $rows; $i++) {
            $linha_dados = $query_categoria->fetch(PDO::FETCH_ASSOC);
            $arr_cat[] = array('id' => $linha_dados['id'], 'desig' => $linha_dados['designacao']);
        }
    } else {
        $arr_cat[] = array('cat_vazia' => true, 'id' => 0, 'desig' => "- Não existe -");
    }

    if ($num_linhas > 0) {
        for ($i = 0; $i < $num_linhas; $i++) {
            $linha_dados = $query_produtos->fetch(PDO::FETCH_ASSOC);
            $arr_dados[] = array('id_produto' => $linha_dados['id_produto'], 'id_fornecedor' => $linha_dados['id_fornecedor'], 'nome_fornecedor' => $linha_dados['nome_abrev'], 'nome' => $linha_dados['nome'], 'preco_un' => $linha_dados['preco_un'], 'taxa' => $linha_dados['taxa'], 'preco' => $linha_dados['total'], 'descricao' => $linha_dados['descricao'], 'moeda' => $linha_dados['simbolo_moeda']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados, 'dados_cat' => $arr_cat);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'dados_in' => $arr_dados, 'dados_cat' => $arr_cat);
    }
    logClicks($connection, "118");
    
} elseif ($_POST['tipo'] == "add") {
    $id_produto = $_POST['id_produto'];
    $preco = $_POST['preco'];
    $id_fornecedor = $_POST['id_fornecedor'];
    
    //-- Identificar taxa associada a produto
    $taxa_iva = 0;
    $taxa_irc = 0;
    if ($_POST['taxa_iva'] > 0) $taxa_iva = $_POST['taxa_iva']; else $taxa_irc = abs($_POST['taxa_iva']);
    
    $valido = true;
    $query_pais_fornecedor_carrinho = $connection->prepare("SELECT pp.id_pais FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais WHERE c.id_empresa=:id_empresa ORDER BY c.date_reg DESC LIMIT 1");
    $query_pais_fornecedor_carrinho->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $item_carrinho = $query_pais_fornecedor_carrinho->rowCount();

    if ($item_carrinho > 0) {
        $query_pais_fornecedor = $connection->prepare("SELECT pp.id_pais FROM fornecedor f INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais WHERE f.id=:id_fornecedor LIMIT 1");
        $query_pais_fornecedor->execute(array(':id_fornecedor' => $id_fornecedor));
        $dados_fornecedor = $query_pais_fornecedor->fetch(PDO::FETCH_ASSOC);
        $dados_fornecedor_carrinho = $query_pais_fornecedor_carrinho->fetch(PDO::FETCH_ASSOC);

        if ($dados_fornecedor['id_pais'] != $dados_fornecedor_carrinho['id_pais']) {
            $valido = false;
            $arr = array('sucesso' => false, 'mensagem' => 'Só pode encomendar produtos do mesmo país de cada vez. Por favor encerre a encomenda pendente primeiro');
        }
    }

    $query_consulta = $connection->prepare("SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, p.nome, c.preco, c.quantidade, c.iva, c.valor FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor WHERE c.id_empresa=:id_empresa AND p.id=:id_produto AND f.id=:id_fornecedor");
    $query_consulta->execute(array(':id_produto' => $id_produto, ':id_empresa' => $id_empresa, ':id_fornecedor' => $id_fornecedor));
    $num_check_itens = $query_consulta->rowCount();
    
    if ($num_check_itens > 0 && $valido == true) {
        $linha_query = $query_consulta->fetch(PDO::FETCH_ASSOC);

        $quantidade = $linha_query['quantidade'] + 1;
        $total_linha = $quantidade * $linha_query['preco'];

        $query_update = $connection->prepare("UPDATE carrinho SET quantidade=:quantidade, valor=:valor WHERE id_empresa=:id_empresa AND item_adicionado=:id_produto AND id_fornecedor=:id_fornecedor");
        $query_update->execute(array(':quantidade' => $quantidade, ':valor' => $total_linha, ':id_empresa' => $id_empresa, ':id_produto' => $id_produto, ':id_fornecedor' => $id_fornecedor));

        $query_total_produtos = $connection->prepare("SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, c.iva AS taxa, c.valor, m.simbolo AS simbolo_moeda FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id");
        $query_total_produtos->execute(array(':id_empresa' => $id_empresa));
        $num_total_produtos = $query_total_produtos->rowCount();

        for ($i = 0; $i <= $num_total_produtos - 1; $i++) {
            $itens = $query_total_produtos->fetch(PDO::FETCH_ASSOC);
            $query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_desconto->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
            $linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);
            $moeda = $itens['simbolo_moeda'];
            $arr_dados[] = array('id_item_adicionado' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['preco'] * $itens['quantidade'] + ($itens['preco'] * $itens['quantidade'] * $itens['taxa'] / 100), 'taxa_desc' => $linha_desconto['valor']);
        }

        $connection->beginTransaction();
        $set_desconto_query = $connection->prepare("SET @desconto:=ROUND((SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1), 2);");
        $set_desconto_query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
        $set_desconto_valor_query = $connection->prepare("SET @desconto_valor:=ROUND((SELECT SUM(c.valor)-(SUM(c.valor)-((@desconto/100)*SUM(c.valor))) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_desconto_valor_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_tot_s_desc_query = $connection->prepare("SET @total_s_desc:=ROUND((SELECT SUM(c.valor) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_tot_s_desc_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_iva = $connection->prepare("SET @valor_iva:=ROUND((SELECT SUM(c.ponderacao*c.preco*c.quantidade*(c.iva/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_val_iva->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_irc = $connection->prepare("SET @valor_irc:=ROUND((SELECT SUM(c.preco*c.quantidade*(c.irc/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_val_irc->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        
        $total_query = $connection->prepare("SELECT @desconto AS desconto, @total_s_desc AS total_s_desc, @desconto_valor AS desconto_valor, @valor_iva AS valor_iva, @valor_irc AS valor_irc, @total_s_desc-@desconto_valor+@valor_iva-@valor_irc AS total_itens FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $total_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $total_itens = $total_query->fetch(PDO::FETCH_ASSOC);
        $connection->commit();
        $arr = array('sucesso' => true, 'total' => $total_itens['total_itens'], 'valor_iva' => $total_itens['valor_iva'], 'valor_irc' => $total_itens['valor_irc'], 'total_s_desc' => $total_itens['total_s_desc'], 'desconto_valor' => $total_itens['desconto_valor'], 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
        
    } elseif ($num_check_itens == 0 && $valido == true) {
        $quantidade = 1;
        $valor = $preco * $quantidade;
        $ponderacao=1;
        $query_insert = $connection->prepare("INSERT INTO carrinho (id_empresa, item_adicionado, id_fornecedor, preco, quantidade, iva, irc, valor, ponderacao) VALUES (:id_empresa, :item_adicionado, :id_fornecedor, :preco, :quantidade, :iva, :irc, :valor ,:ponderacao)");
        $query_insert->execute(array(':id_empresa' => $id_empresa, ':item_adicionado' => $id_produto, ':id_fornecedor' => $id_fornecedor, ':preco' => $preco, ':quantidade' => $quantidade, ':iva' => $taxa_iva, ':irc' => $taxa_irc, ':valor' => $valor, ':ponderacao'=>$ponderacao));
        $query_total_produtos = $connection->prepare("SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, c.iva AS taxa, c.valor, m.simbolo AS simbolo_moeda FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id");
        $query_total_produtos->execute(array(':id_empresa' => $id_empresa));
        $num_total_produtos = $query_total_produtos->rowCount();
        for ($i = 0; $i <= $num_total_produtos - 1; $i++) {
            $itens = $query_total_produtos->fetch(PDO::FETCH_ASSOC);
            $query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_desconto->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
            $linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);
            $arr_dados[] = array('id_item_adicionado' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['preco'] * $itens['quantidade'] + ($itens['preco'] * $itens['quantidade'] * $itens['taxa'] / 100), 'taxa_desc' => $linha_desconto['valor'],$itens['id_item_add']);
            $moeda = $itens['simbolo_moeda'];
        }
        $connection->beginTransaction();
        $set_desconto_query = $connection->prepare("SET @desconto:=ROUND((SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1), 2)");
        $set_desconto_query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
        $set_desconto_valor_query = $connection->prepare("SET @desconto_valor:=ROUND((SELECT SUM(c.valor)-(SUM(c.valor)-((@desconto/100)*SUM(c.valor))) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_desconto_valor_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_tot_s_desc_query = $connection->prepare("SET @total_s_desc:=ROUND((SELECT SUM(c.valor) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_tot_s_desc_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_iva = $connection->prepare("SET @valor_iva:=ROUND((SELECT SUM(c.ponderacao*c.preco*c.quantidade*(c.iva/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_val_iva->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_irc = $connection->prepare("SET @valor_irc:=ROUND((SELECT SUM(c.preco*c.quantidade*(c.irc/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_val_irc->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        
        $total_query = $connection->prepare("SELECT @desconto AS desconto, @total_s_desc AS total_s_desc, @desconto_valor AS desconto_valor, @valor_iva AS valor_iva, @valor_irc AS valor_irc, @total_s_desc-@desconto_valor+@valor_iva-@valor_irc AS total_itens FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $total_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $total_itens = $total_query->fetch(PDO::FETCH_ASSOC);
        $connection->commit();
        $arr = array('sucesso' => true, 'total' => $total_itens['total_itens'], 'valor_iva' => $total_itens['valor_iva'], 'valor_irc' => $total_itens['valor_irc'], 'total_s_desc' => $total_itens['total_s_desc'], 'desconto_valor' => $total_itens['desconto_valor'], 'moeda' => $moeda, 'dados_in' => $arr_dados);
    }

    logClicks($connection, "119");
}
/* elseif ($_POST['tipo'] == "get_carrinho") {
  $query_total_produtos = $connection->prepare("SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, c.iva AS taxa, c.valor, m.simbolo FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id");
  $query_total_produtos->execute(array(':id_empresa' => $id_empresa));
  $num_total_produtos = $query_total_produtos->rowCount();
  if ($num_total_produtos == 0) {
  $arr = array('sucesso' => true, 'vazio' => true);
  } else {
  while ($itens = $query_total_produtos->fetch(PDO::FETCH_ASSOC)) {
  $query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
  $query_desconto->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
  $linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);
  $arr_dados[] = array('id_item_add' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['valor'], 'taxa_desc' => $linha_desconto['valor']);
  $moeda = $itens['simbolo'];
  }
  $connection->beginTransaction();
  $set_desconto_query = $connection->prepare("SET @desconto:=ROUND((SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1), 2);");
  $set_desconto_query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
  $set_desconto_valor_query = $connection->prepare("SET @desconto_valor:=ROUND((SELECT SUM(c.valor)-(SUM(c.valor)-((@desconto/100)*SUM(c.valor))) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
  $set_desconto_valor_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
  $set_tot_s_desc_query = $connection->prepare("SET @total_s_desc:=ROUND((SELECT SUM(c.valor) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
  $set_tot_s_desc_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
  $set_val_iva = $connection->prepare("SET @valor_iva:=ROUND((SELECT SUM(c.preco*c.quantidade*(c.iva/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
  $set_val_iva->execute(array(':id_empresa' => $_SESSION['id_empresa']));
  $total_query = $connection->prepare("SELECT @desconto AS desconto, @total_s_desc AS total_s_desc, @desconto_valor AS desconto_valor, @valor_iva AS valor_iva, @total_s_desc-@desconto_valor+@valor_iva AS total_itens FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
  $total_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
  $total_itens = $total_query->fetch(PDO::FETCH_ASSOC);
  $connection->commit();
  $arr = array('sucesso' => true, 'vazio' => false, 'total' => $total_itens['total_itens'], 'valor_iva' => $total_itens['valor_iva'], 'total_s_desc' => $total_itens['total_s_desc'], 'desconto_valor' => $total_itens['desconto_valor'], 'moeda' => $moeda, 'dados_in' => $arr_dados);
  }
  logClicks($connection, "120");
  } */ 

elseif ($_POST['tipo'] == "get_carrinho") {
    // $query_total_produtos = $connection->prepare("SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, c.iva AS taxa, c.valor, m.simbolo FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id");
    // $query_total_produtos->execute(array(':id_empresa' => $id_empresa));
    $query_total_produtos = $connection->prepare("SELECT * FROM (SELECT c.id AS id_item_add,c.ponderacao AS ponderacao_carrinho, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, IF(c.iva > 0, c.iva, -c.irc) AS taxa, c.valor, m.simbolo FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id) AS dados_carrinho LEFT JOIN (SELECT descontos.id_fornec_desc, descontos.desconto FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT u.id_entidade, fpd.id_fornecedor AS id_fornec_desc, fpd.desconto, fpd.prazo_pag FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id WHERE fpd.id_produto IS NULL AND fpd.active=:active) AS descontos ON atualuser.id_entidade=descontos.id_entidade WHERE descontos.id_entidade IS NOT NULL) AS dados_desc ON dados_carrinho.id_fornecedor=dados_desc.id_fornec_desc");
    $query_total_produtos->execute(array(':id_empresa' => $id_empresa, ':id_utilizador' => $id_utilizador, ':active' => '1'));

    $num_total_produtos = $query_total_produtos->rowCount();
    if ($num_total_produtos == 0) {
        $arr = array('sucesso' => true, 'vazio' => true);
    } else {
        while ($itens = $query_total_produtos->fetch(PDO::FETCH_ASSOC)) {
            $query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_desconto->execute(array(':id_empresa' => $id_empresa, ':nome_regra' => "Taxa de desconto por defeito"));
            $linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);
            // $arr_dados[] = array('id_item_add' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['valor'], 'taxa_desc' => $linha_desconto['valor']);

            $taxa_desc_tot = $itens['desconto'] + $linha_desconto['valor'];
            $arr_dados[] = array('id_item_add' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['valor'], 'taxa_desc' => $taxa_desc_tot,'ponderacao'=>$itens['ponderacao_carrinho']);
            $moeda = $itens['simbolo'];
        }
        $connection->beginTransaction();
        $set_desconto_query = $connection->prepare("SET @desconto:=ROUND((SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1), 2)");
        $set_desconto_query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
        /* Desconto por fornecedor */
        $add_desconto_query = $connection->prepare("SET @desconto:=@desconto + ROUND((SELECT SUM(dados_desc.desconto) AS total_desc_fornec FROM (SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, c.iva AS taxa, c.valor, m.simbolo FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id) AS dados_carrinho LEFT JOIN (SELECT descontos.id_fornec_desc, descontos.desconto FROM (SELECT id_entidade FROM utilizador ut WHERE ut.id=:id_utilizador) AS atualuser LEFT JOIN (SELECT u.id_entidade, fpd.id_fornecedor AS id_fornec_desc, fpd.desconto, fpd.prazo_pag FROM fp_desconto fpd INNER JOIN fornecedor f ON fpd.id_fornecedor=f.id INNER JOIN utilizador u ON fpd.id_utilizador=u.id WHERE fpd.id_produto IS NULL AND fpd.active=:active) AS descontos ON atualuser.id_entidade=descontos.id_entidade WHERE descontos.id_entidade IS NOT NULL) AS dados_desc ON dados_carrinho.id_fornecedor=dados_desc.id_fornec_desc), 2)");
        $add_desconto_query->execute(array(':id_empresa' => $id_empresa, 'id_utilizador' => $id_utilizador, ':active' => '1'));
        /* */
        $set_desconto_valor_query = $connection->prepare("SET @desconto_valor:=IF(@desconto IS NULL, 0, ROUND((SELECT SUM(c.valor)-(SUM(c.valor)-((@desconto/100)*SUM(c.valor))) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2))");
        $set_desconto_valor_query->execute(array(':id_empresa' => $id_empresa));
        $set_tot_s_desc_query = $connection->prepare("SET @total_s_desc:=ROUND((SELECT SUM(c.valor) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_tot_s_desc_query->execute(array(':id_empresa' => $id_empresa));
        $set_val_iva = $connection->prepare("SET @valor_iva:=ROUND((SELECT SUM(c.ponderacao*c.preco*c.quantidade*(c.iva/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_val_iva->execute(array(':id_empresa' => $id_empresa));
        $set_val_irc = $connection->prepare("SET @valor_irc:=ROUND((SELECT SUM(c.preco*c.quantidade*(c.irc/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_val_irc->execute(array(':id_empresa' => $id_empresa));
        
        $total_query = $connection->prepare("SELECT @desconto AS desconto, @total_s_desc AS total_s_desc, @desconto_valor AS desconto_valor, @valor_iva AS valor_iva, @valor_irc AS valor_irc, @total_s_desc-@desconto_valor+@valor_iva-@valor_irc AS total_itens FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $total_query->execute(array(':id_empresa' => $id_empresa));
        $total_itens = $total_query->fetch(PDO::FETCH_ASSOC);
        $connection->commit();
        $arr = array('sucesso' => true, 'vazio' => false, 'total' => $total_itens['total_itens'], 'valor_iva' => $total_itens['valor_iva'], 'valor_irc' => $total_itens['valor_irc'], 'total_s_desc' => $total_itens['total_s_desc'], 'desconto_valor' => $total_itens['desconto_valor'], 'moeda' => $moeda, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "120");
    
} elseif ($_POST['tipo'] == "rem_linha") {
    $query_rem = $connection->prepare("DELETE FROM carrinho WHERE id=:id");
    $query_rem->execute(array(':id' => $_POST['id_produto']));
    $query_total_produtos = $connection->prepare("SELECT c.id AS id_item_add,c.ponderacao AS carrinho_ponderacao, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, IF(c.iva > 0, c.iva, -c.irc) AS taxa, c.valor FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor WHERE id_empresa=:id_empresa ORDER BY c.id");
    $query_total_produtos->execute(array(':id_empresa' => $id_empresa));
    $num_total_produtos = $query_total_produtos->rowCount();
    if ($num_total_produtos == 0) {
        $arr = array('sucesso' => true, 'vazio' => true);
    } else {
        while ($itens = $query_total_produtos->fetch(PDO::FETCH_ASSOC)) {
            $query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_desconto->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
            $linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);
            $arr_dados[] = array('id_item_add' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['valor'], 'taxa_desc' => $linha_desconto['valor'], 'ponderacao' => $itens['carrinho_ponderacao']);
        }
        $connection->beginTransaction();
        $set_desconto_query = $connection->prepare("SET @desconto:=ROUND((SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1), 2);");
        $set_desconto_query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
        $set_desconto_valor_query = $connection->prepare("SET @desconto_valor:=ROUND((SELECT SUM(c.valor)-(SUM(c.valor)-((@desconto/100)*SUM(c.valor))) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_desconto_valor_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_tot_s_desc_query = $connection->prepare("SET @total_s_desc:=ROUND((SELECT SUM(c.valor) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_tot_s_desc_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_iva = $connection->prepare("SET @valor_iva:=ROUND((SELECT SUM(c.ponderacao*c.preco*c.quantidade*(c.iva/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_val_iva->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_irc = $connection->prepare("SET @valor_irc:=ROUND((SELECT SUM(c.preco*c.quantidade*(c.irc/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_val_irc->execute(array(':id_empresa' => $id_empresa));
        
        $total_query = $connection->prepare("SELECT @desconto AS desconto, @total_s_desc AS total_s_desc, @desconto_valor AS desconto_valor, @valor_iva AS valor_iva, @valor_irc AS valor_irc, @total_s_desc-@desconto_valor+@valor_iva-@valor_irc AS total_itens FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $total_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $total_itens = $total_query->fetch(PDO::FETCH_ASSOC);
        $connection->commit();
        $arr = array('sucesso' => true, 'vazio' => false, 'total' => $total_itens['total_itens'], 'valor_iva' => $total_itens['valor_iva'], 'valor_irc' => $total_itens['valor_irc'], 'total_s_desc' => $total_itens['total_s_desc'], 'desconto_valor' => $total_itens['desconto_valor'], 'moeda' => $linha_moeda['simbolo'], 'dados_in' => $arr_dados);
    }
    logClicks($connection, "122");
    
} elseif ($_POST['tipo'] == "esvaziar_carrinho") {
    $query_delete = $connection->prepare("DELETE FROM carrinho WHERE id_empresa=:id_empresa");
    $query_delete->execute(array(':id_empresa' => $id_empresa));
    $arr = array('sucesso' => true);
    logClicks($connection, "123");
    
} elseif ($_POST['tipo'] == "act_qtd") {
    $id_produto = $_POST['id_produto'];
    $query_carrinho = $connection->prepare("SELECT c.preco, c.iva AS taxa FROM carrinho c WHERE c.id=:id");
    $query_carrinho->execute(array(':id' => $id_produto));
    $linha_carrinho = $query_carrinho->fetch(PDO::FETCH_ASSOC);
    $quantidade = $_POST['quantidade'];
    $ponderacao = $_POST['ponderacao'];
    //var_dump($_POST['quantidade']);
    $total_linha = $quantidade * $linha_carrinho['preco'];
    $total=$_POST['ponderacao']*($quantidade * $linha_carrinho['preco']);
    if ($quantidade > 0) {
        $query_update = $connection->prepare("UPDATE carrinho SET ponderacao=:ponderacao, quantidade=:quantidade, valor=:valor WHERE id_empresa=:id_empresa AND id=:id_produto");
        $query_update->execute(array(':ponderacao' => $ponderacao,':quantidade' => $quantidade, ':valor' => $total, ':id_empresa' => $id_empresa, ':id_produto' => $id_produto));
    } else {
        $query_rem = $connection->prepare("DELETE FROM carrinho WHERE id=:id");
        $query_rem->execute(array('id' => $id_produto));
    }
    $query_total_produtos = $connection->prepare("SELECT c.id AS id_item_add,c.ponderacao AS ponderacao_carrinho, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, m.simbolo, c.quantidade, IF(c.iva > 0, c.iva, -c.irc) AS taxa, c.valor FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id");
    $query_total_produtos->execute(array(':id_empresa' => $id_empresa));
    $num_total_produtos = $query_total_produtos->rowCount();
    if ($num_total_produtos == 0) {
        $arr = array('sucesso' => true, 'vazio' => true);
    } else {
        while ($itens = $query_total_produtos->fetch(PDO::FETCH_ASSOC)) {
            $query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
            $query_desconto->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
            $linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);
            $simbolo_moeda = $itens['simbolo'];
            $arr_dados[] = array('id_item_add' => $itens['id_item_add'], 'id_produto' => $itens['id_produto'], 'id_fornecedor' => $itens['id_fornecedor'], 'nome_fornecedor' => $itens['nome_fornecedor'], 'nome' => $itens['nome'], 'preco' => $itens['preco'], 'quantidade' => $itens['quantidade'], 'taxa' => $itens['taxa'], 'valor' => $itens['valor'], 'taxa_desc' => $linha_desconto['valor'],'ponderacao' => $itens['ponderacao_carrinho']);
        }
        $connection->beginTransaction();
        $set_desconto_query = $connection->prepare("SET @desconto:=ROUND((SELECT re.valor FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1), 2);");
        $set_desconto_query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
        $set_desconto_valor_query = $connection->prepare("SET @desconto_valor:=ROUND((SELECT SUM(c.valor)-(SUM(c.valor)-((@desconto/100)*SUM(c.valor))) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_desconto_valor_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_tot_s_desc_query = $connection->prepare("SET @total_s_desc:=ROUND((SELECT SUM(c.valor) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_tot_s_desc_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_iva = $connection->prepare("SET @valor_iva:=ROUND((SELECT SUM(c.ponderacao*c.preco*c.quantidade*(c.iva/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2);");
        $set_val_iva->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $set_val_irc = $connection->prepare("SET @valor_irc:=ROUND((SELECT SUM(c.preco*c.quantidade*(c.irc/100)) FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa), 2)");
        $set_val_irc->execute(array(':id_empresa' => $id_empresa));
        
        $total_query = $connection->prepare("SELECT @desconto AS desconto, @total_s_desc AS total_s_desc, @desconto_valor AS desconto_valor, @valor_iva AS valor_iva, @valor_irc AS valor_irc, @total_s_desc-@desconto_valor+@valor_iva-@valor_irc AS total_itens FROM carrinho c INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $total_query->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $total_itens = $total_query->fetch(PDO::FETCH_ASSOC);
        $connection->commit();
        $arr = array('sucesso' => true, 'vazio' => false, 'total' => $total_itens['total_itens'], 'valor_iva' => $total_itens['valor_iva'], 'valor_irc' => $total_itens['valor_irc'], 'total_s_desc' => $total_itens['total_s_desc'], 'desconto_valor' => $total_itens['desconto_valor'], 'moeda' => $simbolo_moeda, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "121");
    
} elseif ($_POST['tipo'] == "encomendar") {
    $dados = json_decode($_POST['dados'], true);
    $data = date('Y-m-d H:i:s', strtotime($dados["data"]));
    $transporte = 0;
    $date = new DateTime($data);
    $intervalo = new DateInterval('P1M');
    $date->add($intervalo);
    $data_lim = $date->format('Y-m-d H:i:s');
    $data_recebida = date("Y-m-d H:i:s", strtotime($data_lim));
    $mes = date('m', strtotime($data_recebida));
    $ano = date('Y', strtotime($data_recebida));
    $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
    $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
    $query = $connection->prepare("SELECT cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND cal.mes=:mes AND cal.ano=:ano");
    $query->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':mes' => $mes, ':ano' => $ano));
    $rowCount = $query->rowCount();
    if ($rowCount > 0) {
        $linha = $query->fetch(PDO::FETCH_ASSOC);
        $data_inicio = $linha['data_inicio'] . " " . $linha['hora_inicio'];
        $data_fim = $linha['data_fim'] . " " . $linha['hora_fim'];
        $data_lim_r = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_lim);
    } else {
        $date = new DateTime();
        $intervalo = new DateInterval('P1M');
        $date->add($intervalo);
        $data_lim = $date->format('Y-m-d H:i:s');
        $data_lim_r = date("Y-m-d H:i:s", strtotime($data_lim));
    }
    $query_nome_empresa = $connection->prepare("SELECT emp.nome FROM empresa emp WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
    $query_nome_empresa->execute(array(':id_empresa' => $_SESSION['id_empresa']));
    $linha_nome_empresa = $query_nome_empresa->fetch(PDO::FETCH_ASSOC);
    foreach ($dados["fornecedor"] as $key => $value) {
        $total_desconto = $dados["fornecedor"][$key]["total_desconto"];
        $total = $dados["fornecedor"][$key]["total"];
        $total_iva = $dados["fornecedor"][$key]["total_iva"];
        $total_irc = $dados["fornecedor"][$key]["total_irc"];
        $id_fornecedor = $dados["fornecedor"][$key]["id_fornecedor"];
        $total_desconto_TEST = 0;
        $total_TEST=1;

//        $query_ref = $connection->prepare("SELECT @row_num:=@row_num+1 AS rank, T1.* FROM (SELECT enc.id FROM encomenda enc INNER JOIN fornecedor f ON enc.id_fornecedor=f.id WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r ORDER BY rank DESC LIMIT 1");//estava esta (problemas mariadb para musql)
        $query_ref = $connection->prepare("SELECT @row_num:=@row_num+1 AS 'rank', T1.* FROM (SELECT enc.id FROM encomenda enc INNER JOIN fornecedor f ON enc.id_fornecedor=f.id WHERE f.id=:fornecedor) AS T1, (SELECT @row_num:=0) r ORDER BY 'rank' DESC LIMIT 1");//meti esta
        $query_ref->execute(array(':fornecedor' => $id_fornecedor));
        $linha_ref = $query_ref->fetch(PDO::FETCH_ASSOC);
        if ($linha_ref['rank'] == "") {
            $linha_ref['rank'] = 0;
        }
        $ref = geraRef($date, $linha_nome_empresa['nome'], $linha_ref['rank'] + 1);
        $query_encomenda = $connection->prepare("INSERT INTO encomenda (id_empresa, id_fornecedor, ref, data, transporte, desconto, iva, irc, total, pago) VALUES (:id_empresa, :id_fornecedor, :ref, :data, :transporte, :desconto, :iva, :irc, :total, :pago)");
        $query_encomenda->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fornecedor' => $id_fornecedor, ':ref' => $ref, ':data' => $data, ':transporte' => $transporte, ':desconto' => $total_desconto, ':iva' => $total_iva, ':irc' => $total_irc, ':total' => $total, ':pago' => '0'));
        
        // $query_select = $connection->prepare("SELECT e.id, f.nome_abrev FROM encomenda e INNER JOIN fornecedor f ON e.id_fornecedor=f.id WHERE id_empresa=:id_empresa ORDER BY e.id DESC LIMIT 1");
        $query_select = $connection->prepare("SELECT e.id, f.nome_abrev, p.nome_abrev AS abrev_pais FROM encomenda e INNER JOIN fornecedor f ON e.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais WHERE id_empresa=:id_empresa ORDER BY e.id DESC LIMIT 1");
        $query_select->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $linha_select = $query_select->fetch(PDO::FETCH_ASSOC);
        
        $query_pagamento = $connection->prepare("INSERT INTO pagamento (id_encomenda, tipo, data_limit_pag, data_lim_pag_r, valor, pago) VALUES (:id_enc, :tipo, :data_lim, :data_lim_r, :val, :pago)");
        $query_pagamento->execute(array(':id_enc' => $linha_select['id'], ':tipo' => "O", ':data_lim' => $data_lim, ':data_lim_r' => $data_lim_r, ':val' => $total, ':pago' => '0'));
        foreach ($dados["fornecedor"][$key]["produtos"] as $chave => $valor) {
            $id_produto = $dados["fornecedor"][$key]["produtos"][$chave]["id_produto"];
            $id_prod_carr = $dados["fornecedor"][$key]["produtos"][$chave]["id_prod_carr"];
            $preco = $dados["fornecedor"][$key]["produtos"][$chave]["preco"];
            $quantidade = $dados["fornecedor"][$key]["produtos"][$chave]["quantidade"];
            $valor = $dados["fornecedor"][$key]["produtos"][$chave]["valor"];
            $iva = $dados["fornecedor"][$key]["produtos"][$chave]["iva"];
            $irc = $dados["fornecedor"][$key]["produtos"][$chave]["irc"];
            $desconto = $dados["fornecedor"][$key]["produtos"][$chave]["desconto"];
            $query_inserir = $connection->prepare("INSERT INTO detalhes_encomenda (id_encomenda, id_produto, preco, quantidade, desconto, iva, irc, total_linha) VALUES (:id_enc, :id_prod, :preco, :qtd, :desc, :iva, :irc, :tot_linha)");
            $query_inserir->execute(array(':id_enc' => $linha_select['id'], ':id_prod' => $id_produto, ':preco' => $preco, ':qtd' => $quantidade, ':desc' => $desconto, ':iva' => $iva, ':irc' => $irc, ':tot_linha' => $valor));
            $query_delete = $connection->prepare("DELETE FROM carrinho WHERE id=:id_prod");
            $query_delete->execute(array(':id_prod' => $id_prod_carr));
        }
        $arr_dados[] = array('id' => $linha_select['id'], 'pais' => $linha_select['abrev_pais']);
    }
    $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    logClicks($connection, "124");
    
} elseif ($_POST['tipo'] == "filtrar_enc") {
    if ($_POST['id_fornecedor'] > 0) {
        $query_encomendas = $connection->prepare("SELECT en.id, en.ref, date_format(en.data,'%d-%m-%Y') AS data, IF(en.pago=0, 'Não', 'Sim') AS pago, f.nome_abrev, p.nome_pais, en.total, m.simbolo FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND f.id=:id_fornecedor");
        $query_encomendas->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fornecedor' => $_POST['id_fornecedor']));
        while ($linha_encomendas = $query_encomendas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_encomendas['id'], 'ref' => $linha_encomendas['ref'], 'data' => $linha_encomendas['data'], 'pago' => $linha_encomendas['pago'], 'nome_abrev' => $linha_encomendas['nome_abrev'], 'nome_pais' => $linha_encomendas['nome_pais'], 'total' => $linha_encomendas['total'], 'moeda' => $linha_encomendas['simbolo']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    } else {
        $query_encomendas = $connection->prepare("SELECT en.id, en.ref, date_format(en.data,'%d-%m-%Y') AS data, IF(en.pago=0, 'Não', 'Sim') AS pago, f.nome_abrev, p.nome_pais, en.total, m.simbolo FROM encomenda en INNER JOIN empresa emp ON en.id_empresa=emp.id_empresa INNER JOIN fornecedor f ON en.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
        $query_encomendas->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        while ($linha_encomendas = $query_encomendas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id' => $linha_encomendas['id'], 'ref' => $linha_encomendas['ref'], 'data' => $linha_encomendas['data'], 'pago' => $linha_encomendas['pago'], 'nome_abrev' => $linha_encomendas['nome_abrev'], 'nome_pais' => $linha_encomendas['nome_pais'], 'total' => $linha_encomendas['total'], 'moeda' => $linha_encomendas['simbolo']);
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados);
    }
    logClicks($connection, "126");
} elseif ($_POST['tipo'] == "dados_adiantamento") {
    $query_dados_adiant = $connection->prepare("SELECT a.nome_cliente, valor FROM adiantamento a WHERE a.id_adiantamento=:id_adiantamento LIMIT 1");
    $query_dados_adiant->execute(array(':id_adiantamento' => $_POST['id_adiantamento']));
    $rowCount = $query_dados_adiant->rowCount();
    if ($rowCount == 1) {
        $linha_dados_adiant = $query_dados_adiant->fetch(PDO::FETCH_ASSOC);
        $arr = array('sucesso' => true, 'cliente' => $linha_dados_adiant['nome_cliente'], 'valor' => $linha_dados_adiant['valor']);
        logClicks($connection, "198");
    } else
        $arr = array('sucesso' => false, 'mensagem' => 'Ocorreu um erro. Adiantamento não encontrado');
}
elseif ($_POST['tipo'] == "carregar_produto_pais") {
    $vazio = true;
    $arr_dados = array();

    $query_produto_inter = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais c ON pf.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.id_pais=:id_pais ORDER BY s.nome");
    $query_produto_inter->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_pais' => $_POST['pais']));
    $num_produto_inter = $query_produto_inter->rowCount();

    if ($num_produto_inter > 0) {
        $vazio = false;
        while ($linha_produtos_inter = $query_produto_inter->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_produto' => $linha_produtos_inter['id_produto'], 'id_fornecedor' => $linha_produtos_inter['id_fornecedor'], 'nome_fornecedor' => $linha_produtos_inter['nome_abrev'], 'nome_produto' => $linha_produtos_inter['nome'], 'preco' => $linha_produtos_inter['preco_un'], 'taxa_iva' => $linha_produtos_inter['taxa'], 'total' => $linha_produtos_inter['total'], 'simbolo_moeda' => $linha_produtos_inter['simbolo_moeda'], 'descricao' => $linha_produtos_inter['descricao']);
        }
    }
    $arr = array('sucesso' => true, 'vazio' => $vazio, 'dados_in' => $arr_dados);
    logClicks($connection, "118");
} elseif ($_POST['tipo'] == "dados_fatura") {
    $id_fatura = $_POST['id_fatura'];

    $query_enc_linhas = $connection->prepare("SELECT e.id AS id_enc, e.ref, rp.valor, rp.simbolo, f.id AS id_fornec, f.nome_abrev, p.id AS id_prod, p.nome AS p_nome, de.preco AS de_preco, de.quantidade AS de_qtd, de.iva AS de_iva, de.desconto AS de_desc, de.total_linha AS de_tot, m.simbolo AS simbolo_moeda FROM encomenda e INNER JOIN detalhes_encomenda de ON e.id=de.id_encomenda INNER JOIN produto p ON de.id_produto=p.id INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra INNER JOIN fornecedor f ON e.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais c ON pf.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE e.id=:id");
    $query_enc_linhas->execute(array(':id' => $id_fatura));
    $num_rows = $query_enc_linhas->rowCount();

    if ($num_rows > 0) {
        $total = 0;
        $total_fat = 0;
        $total_iva = 0;
        $total_desc = 0;
        $total_final = 0;
        while ($linha_enc_linhas = $query_enc_linhas->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados[] = array('id_enc' => $linha_enc_linhas['id_enc'], 'valor_regra' => $linha_enc_linhas['valor'], 'simbolo_regra' => $linha_enc_linhas['simbolo'], 'id_fornec' => $linha_enc_linhas['id_fornec'], 'nome_fornecedor' => $linha_enc_linhas['nome_abrev'], 'id_produto' => $linha_enc_linhas['id_prod'], 'nome_produto' => $linha_enc_linhas['p_nome'], 'preco' => $linha_enc_linhas['de_preco'], 'qtd' => $linha_enc_linhas['de_qtd'], 'valor_iva' => $linha_enc_linhas['de_iva'], 'valor_desc' => $linha_enc_linhas['de_desc'], 'total' => $linha_enc_linhas['de_tot']);
            $ref_enc = $linha_enc_linhas['ref'];
            $simbolo_moeda = $linha_enc_linhas['simbolo_moeda'];
            $total += $linha_enc_linhas['de_tot'];
            $total_iva += $linha_enc_linhas['de_iva'];
            $total_desc += $linha_enc_linhas['de_desc'];
            $total_fat = $total - $total_iva;
            $total_final = $total_fat + $total_iva - $total_desc;
        }
        $arr = array('sucesso' => true, 'dados_in' => $arr_dados, 'ref_enc' => $ref_enc, 'total_fat' => $total_fat, 'total_iva' => $total_iva, 'total_desc' => $total_desc, 'total_final' => $total_final, 'simbolo_moeda' => $simbolo_moeda);
    } else
        $arr = array('sucesso' => false, 'mensagem' => 'Ocorreu um erro. Não foi possivel obter dados da fatura');
}
elseif ($_POST['tipo'] == "nota_credito") {
    $dados = json_decode($_POST['det_nc'], true);
    $id_fat = $_POST['id_fat'];
    $id_fornecedor = $_POST['id_fornecedor'];
    $total_iva = $_POST['total_iva'];
    $total_final = floatval(round($_POST['total_final'], 2));
    $data = date('Y-m-d H:i:s', strtotime($_POST["data_virt"]));

    $query_dados_fat = $connection->prepare("SELECT enc.id, enc.ref, f.nome_abrev, enc.total, m.simbolo, m.ISO4217, enc.pago FROM encomenda enc INNER JOIN empresa e ON enc.id_empresa=e.id_empresa INNER JOIN fornecedor f ON enc.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais INNER JOIN moeda m ON p.id_moeda=m.id WHERE e.id_empresa=:id_empresa AND enc.id=:id_fat LIMIT 1");
    $query_dados_fat->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':id_fat' => $id_fat));
    $linha_dados_fat = $query_dados_fat->fetch(PDO::FETCH_ASSOC);

    $query_nota_credito = $connection->prepare("INSERT INTO nota_credito (id_encomenda, id_empresa, id_fornecedor, ref, data, iva, total, pago) VALUES (:id_encomenda, :id_empresa, :id_fornecedor, :ref, :data_virt, :iva_total, :total_final, :pago)");
    $query_nota_credito->execute(array(':id_encomenda' => $id_fat, ':id_empresa' => $_SESSION['id_empresa'], ':id_fornecedor' => $id_fornecedor, ':ref' => $linha_dados_fat['ref'], ':data_virt' => $data, ':iva_total' => $total_iva, ':total_final' => $total_final, ':pago' => $linha_dados_fat['pago']));
    $id_nota_cred = $connection->lastInsertId();

    foreach ($dados as $key => $value) {
        $id_produto = $dados[$key]['id_produto'];
        $preco = $dados[$key]['preco'];
        $qtd = $dados[$key]['qtd'];
        $iva = $dados[$key]['iva'];
        $total_linha = $dados[$key]['total_linha'];

        $query_inserir = $connection->prepare("INSERT INTO detalhes_nota_credito (id_nota_credito, id_produto, preco, quantidade, iva, total_linha) VALUES (:id_nota_cred, :id_produto, :preco, :qtd, :iva, :total_linha)");
        $query_inserir->execute(array(':id_nota_cred' => $id_nota_cred, ':id_produto' => $id_produto, ':preco' => $preco, ':qtd' => $qtd, ':iva' => $iva, ':total_linha' => $total_linha));
    }

    if ($linha_dados_fat['pago'] == '1') {
        $query_conta = $connection->prepare("SELECT m.id_conta, m.saldo_controlo, m.saldo_contab, m.saldo_disp FROM movimento m INNER JOIN conta c ON m.id_conta=c.id INNER JOIN empresa e ON c.id_empresa=e.id_empresa WHERE c.tipo_conta='ordem' AND e.id_empresa=:id_empresa ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
        $query_conta->execute(array(':id_empresa' => $_SESSION['id_empresa']));
        $linha_conta = $query_conta->fetch(PDO::FETCH_ASSOC);

        $xchange_rate = 1;
        if ($linha_dados_fat['ISO4217'] != $linha_moeda['ISO4217']) {
            $contents = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
            foreach ($contents->Cube->Cube->Cube as $rates) {
                if ($rates['currency'] == $linha_select_fatura['ISO4217']) {
                    $xchange_rate = floatval($rates['rate']);
                    break;
                }
            }
        }
        // $valor_convert = number_format($total_final * $xchange_rate, 2, '.', '');
        $valor_convert = round($total_final * $xchange_rate, 2);

        $saldo_controlo = $linha_conta['saldo_controlo'] + $valor_convert;
        $saldo_contab = $linha_conta['saldo_contab'] + $valor_convert;
        $saldo_disp = $linha_conta['saldo_disp'] + $valor_convert;

        $query_insert_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_virt, 'CRE', :descricao, :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
        $query_insert_mov->execute(array(':id_conta' => $linha_conta['id_conta'], ':data_virt' => $data, ':descricao' => "Pagamento de nota de crédito do fornecedor $linha_dados_fat[nome_abrev]", ':credito' => $total_final, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
    }
    /* * /
    elseif ($linha_dados_fat['pago'] == '0' && $total_final == round($linha_dados_fat['total'], 2)) {
        $query_upd_fat = $connection->prepare("UPDATE encomenda SET pago='1' WHERE id=:id_fat");
        $query_upd_fat->execute(array(':id_fat' => $id_fat));
    }
    /* */

    $arr = array('sucesso' => true);
    logClicks($connection, "192");
}

// Usado em "Adiantamentos"
elseif ($_POST['tipo'] == "filter_fornec_pais") {
    $id_pais = $_POST['id_pais'];
    
    $query_moeda_pais = $connection->prepare("SELECT m.simbolo, m.ISO4217 FROM pais p INNER JOIN moeda m ON p.id_moeda=m.id WHERE p.id_pais=:id_pais LIMIT 1");
    $query_moeda_pais->execute(array(':id_pais' => $id_pais));
    $linha_moeda = $query_moeda_pais->fetch(PDO::FETCH_ASSOC);
    $simbolo_moeda = $linha_moeda['simbolo'];
    $isoMoeda = $linha_moeda['ISO4217'];
    
    $query_fornec_pais = $connection->prepare("SELECT DISTINCT f.id, f.nome_abrev FROM fornecedor f INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais p ON pf.id_pais=p.id_pais WHERE p.id_pais=:id_pais ORDER BY f.nome_abrev ASC");
    $query_fornec_pais->execute(array(':id_pais' => $id_pais));
    $fornecedores_pais = $query_fornec_pais->fetchAll();
    
    $fornecs_pais = [];
    foreach ($fornecedores_pais AS $fornec_pais) {
        $fornecs_pais[] = ['id' => $fornec_pais['id'], 'nome' => $fornec_pais['nome_abrev']];
    }
    
    $arr = array('sucesso' => true, 'dados_in' => $fornecs_pais, 'moeda' => $simbolo_moeda, 'isomoeda' => $isoMoeda);
}

// Carrega datas com tarefas a serem cumpridas, para sinalizar no calendário
elseif ($_POST['tipo'] == "calendario_tasks") {
    $curr_year_v = $_POST['curr_year_v'];
    $id_empresa = $_SESSION['id_empresa'];
    
    // Carregar TAREFAS associadas ao Grupo da Empresa
    $query_tasks = $connection->prepare("SELECT ct.dia_v_ini, ct.mes_v_ini, ct.dia_v_fim, ct.mes_v_fim, DATEDIFF(CONCAT(:year_fim, '-', ct.mes_v_fim, '-', ct.dia_v_fim), CONCAT(:year_ini, '-', ct.mes_v_ini, '-', ct.dia_v_ini)) AS days_diff FROM empresa e INNER JOIN grupo g ON e.id_grupo=g.id INNER JOIN calendario_tasks ct ON g.id=ct.id_grupo WHERE e.id_empresa=:id_empresa ORDER BY ct.mes_v_ini ASC, ct.dia_v_ini ASC");
    $query_tasks->execute(array(':year_fim' => $curr_year_v, ':year_ini' => $curr_year_v, ':id_empresa' => $id_empresa));
    $intervals = $query_tasks->fetchAll();
    
    // Carregar CALENDÁRIO VIRTUAL associado ao Grupo da Empresa
    $query_calend = $connection->prepare("SELECT cal.ano, cal.mes, cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY cal.ano ASC, cal.mes ASC");
    $query_calend->execute(array(':id_empresa' => $id_empresa));
    $linhas_calend = $query_calend->fetchAll();
    $rowCount = count($linhas_calend);
    
    // Descarregar registos do CALENDÁRIO VIRTUAL num array, indexado pelo ano+mes
    $calend_regs = [];
    if ($rowCount > 0) {
        foreach ($linhas_calend AS $linha_calend) {
            $mes = $linha_calend['mes'] < 10 ? '0'.$linha_calend['mes'] : $linha_calend['mes'];
            $calend_regs[$linha_calend['ano'].$mes] = ['data_inicio' => $linha_calend['data_inicio'], 'hora_inicio' => $linha_calend['hora_inicio'], 'data_fim' => $linha_calend['data_fim'], 'hora_fim' => $linha_calend['hora_fim']];
        }
    }
    
    $empty = true;
    $days_w_tasks = [];
    // Percorrer registos de TAREFAS (definidas para data VIRTUAL), converter data em data REAL e adicionar DIA a array de dias com tarefas
    foreach($intervals AS $intrvl) {
        $data_ini = new DateTime($curr_year_v.'-'.$intrvl['mes_v_ini'].'-'.$intrvl['dia_v_ini']);
        $days_diff = $intrvl['days_diff'];
        
        $intervalo = new DateInterval('P1D');
        for ($i=0; $i<$days_diff; $i++) {
            $day = $data_ini->format('Y-m-d');
            $data_ini->add($intervalo);
            $data_ini_to_conv_ini = $data_ini;
            $data_ini_to_conv = $data_ini_to_conv_ini->format('Y-m-d H:i:s');
            
            $data_recebida = date("Y-m-d", strtotime($data_ini_to_conv));
            $mes = date('m', strtotime($data_recebida));
            $ano = date('Y', strtotime($data_recebida));
            $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
            $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
            
            if (array_key_exists($ano.$mes, $calend_regs)) {
                $data_inicio = $calend_regs[$ano.$mes]['data_inicio'] . " " . $calend_regs[$ano.$mes]['hora_inicio'];
                $data_fim = $calend_regs[$ano.$mes]['data_fim'] . " " . $calend_regs[$ano.$mes]['hora_fim'];
                $day_ini = dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_ini_to_conv);
                $day = date("Y-m-d", strtotime($day_ini));
                $days_w_tasks[$day] = "";
                if ($empty == true) $empty = false;
                
            } /* else {
                $date = new DateTime();
                $day = $date->format('Y-m-d');
                
            } */
        }
    }
    
    $arr = ['sucesso' => true, 'vazio' => $empty, 'dados_in' => $days_w_tasks];
}

// Carregar tarefas a serem efetuadas em determinada "data_virtual"
elseif ($_POST['tipo'] == "date_task") {
    $id_empresa = $_SESSION['id_empresa'];
    $dataReal = $_POST['data_v'];
    
    // Carregar CALENDÁRIO VIRTUAL associado ao Grupo da Empresa
    $query_calend = $connection->prepare("SELECT cal.ano, cal.mes, cal.data_inicio, cal.hora_inicio, cal.data_fim, cal.hora_fim FROM calendario cal INNER JOIN grupo g ON cal.id_grupo=g.id INNER JOIN empresa emp ON g.id=emp.id_grupo WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa ORDER BY cal.ano ASC, cal.mes ASC");
    $query_calend->execute(array(':id_empresa' => $id_empresa));
    $linhas_calend = $query_calend->fetchAll();
    $rowCount = count($linhas_calend);
    
    // Descarregar registos do CALENDÁRIO VIRTUAL num array, indexado pelo ano+mes
    $calend_regs = [];
    if ($rowCount > 0) {
        foreach ($linhas_calend AS $linha_calend) {
            $calend_regs[] = ['mes' => $linha_calend['mes'], 'ano' => $linha_calend['ano'], 'data_inicio' => $linha_calend['data_inicio'], 'hora_inicio' => $linha_calend['hora_inicio'], 'data_fim' => $linha_calend['data_fim'], 'hora_fim' => $linha_calend['hora_fim']];
        }
    }
    
    // Converter "data_real" recebida em data_virtual. Prq "tarefas" estão definidas por datas virtuais
    $dataVirtual = $dataReal;
    $dateTimeReal = new DateTime($dataReal);
    foreach ($calend_regs AS $reg) {
        $dateTimeIni = new DateTime($reg['data_inicio'] . " " . $reg['hora_inicio']);
        $dateTimeLim = new DateTime($reg['data_fim'] . " " . $reg['hora_fim']);
        
        if ($dateTimeReal >= $dateTimeIni && $dateTimeReal <= $dateTimeLim) {
            $mes = $reg['mes'] < 10 ? '0'.$reg['mes'] : $reg['mes'];
            $ano = $reg['ano'];
            $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
            $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
            $data_inicio = $reg['data_inicio'] . " " . $reg['hora_inicio'];
            $data_fim = $reg['data_fim'] . " " . $reg['hora_fim'];
            $dataVirtual_ini = dataVirtual($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $dataReal);
            $dataVirtual = date("Y-m-d", strtotime($dataVirtual_ini));
        }
    }
    

//    $dataVirtual = $dataReal; // meti esta para a dataVirtual voltar a assumir o valor da data real pois por causa do foreach anterior a datavirtual volta assumir os valores das datas virtuais, sem esta linha as tarefas que têm datas virtuais não são mostradas ao clickar no calendario
    // Procurar se há tarefas para a data_virtual calculada
    $query_day_tasks = $connection->prepare("SELECT ct.id, ct.descricao FROM empresa e INNER JOIN grupo g ON e.id_grupo=g.id INNER JOIN calendario_tasks ct ON g.id=ct.id_grupo WHERE e.id_empresa=:id_empresa AND (:data_v BETWEEN DATE(CONCAT(YEAR(CURDATE()), '-', ct.mes_v_ini, '-', ct.dia_v_ini)) AND DATE(CONCAT(YEAR(CURDATE()), '-', ct.mes_v_fim, '-', ct.dia_v_fim))) ORDER BY ct.mes_v_ini ASC, ct.dia_v_ini ASC");
    $query_day_tasks->execute(array(':id_empresa' => $id_empresa, ':data_v' => $dataVirtual));
    $results = $query_day_tasks->rowCount();
    
    if ($results == 0)
        $arr = array('sucesso' => true, 'vazio' => true);
        // $arr = array('sucesso' => true, 'vazio' => true, 'intervalo' => 'Mês '.$mes.'/'.$ano.' começa em '.$data_inicio.' e termina em '.$data_fim);
    
    else {
        $tasks = [];
        while ($linha_tasks = $query_day_tasks->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = ['id' => $linha_tasks['id'], 'descricao' => $linha_tasks['descricao']];
        }
        
        $arr = array('sucesso' => true, 'vazio' => false, 'data_virtual' => $dataVirtual, 'dados_in' => $tasks);
    }
}

// Pesquisar produtos pelo nome
elseif ($_POST['tipo'] == "produtos_search") {
    $prod_nome = $_POST['nome'];
    
    $query_produtos = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND s.nome LIKE CONCAT(:nome, '%') ORDER BY s.nome");
    $query_produtos->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome' => $prod_nome));
    $num_linhas = $query_produtos->rowCount();
    
    if ($num_linhas > 0) {
        for ($i = 0; $i < $num_linhas; $i++) {
            $linha_dados = $query_produtos->fetch(PDO::FETCH_ASSOC);
            $arr_dados[] = array('id_produto' => $linha_dados['id_produto'], 'id_fornecedor' => $linha_dados['id_fornecedor'], 'nome_fornecedor' => $linha_dados['nome_abrev'], 'nome' => $linha_dados['nome'], 'preco_un' => $linha_dados['preco_un'], 'taxa' => $linha_dados['taxa'], 'preco' => $linha_dados['total'], 'descricao' => $linha_dados['descricao'], 'moeda' => $linha_dados['simbolo_moeda']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true);
    }
    
    // logClicks($connection, "xxx");
}

echo json_encode($arr);
$connection = null;
