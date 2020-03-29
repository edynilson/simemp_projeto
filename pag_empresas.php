<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-19 19:48:26
*/

include('./conf/check.php');

//-- Todos os grupos, incluindo INATIVOS
// $query_grupos = $connection->prepare("SELECT id, nome FROM grupo");
// $query_grupos->execute();

//-- Todos grupos ATIVOS, da MESMA entidade do user
// $query_grupos = $connection->prepare("SELECT * FROM (SELECT * FROM (SELECT u.id_entidade AS id_ent FROM utilizador u WHERE u.id=:id_user) AS ent LEFT JOIN (SELECT u.id_entidade, g.id, g.nome FROM user_grupo ug INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN grupo g ON ug.id_grupo=g.id) AS ent_grupos ON ent.id_ent=ent_grupos.id_entidade LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos WHERE grupos.estado='1'");
// $query_grupos->execute(array(':id_user' => $_SESSION['id_utilizador']));

//-- Todos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
//$query_grupos = $connection->prepare("SELECT grupos.id, grupos.nome FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos WHERE grupos.estado='1' ORDER BY grupos.nome ASC");//estava esta
$query_grupos = $connection->prepare("SELECT g.id, g.nome FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id WHERE last_estado_grupos.estado='1'   AND tp.designacao='Normal'  ORDER BY g.nome ASC");//meti esta
$query_grupos->execute();

//-- Todas as empresas dos grupos ATIVOS, da MESMA entidade do user
// $query_empresas = $connection->prepare("SELECT em.id_empresa, em.nome, c.num_conta FROM (SELECT * FROM (SELECT u.id_entidade AS id_ent FROM utilizador u WHERE u.id=:id_user) AS ent LEFT JOIN (SELECT u.id_entidade, g.id, g.nome FROM user_grupo ug INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN grupo g ON ug.id_grupo=g.id) AS ent_grupos ON ent.id_ent=ent_grupos.id_entidade LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON  grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
// $query_empresas->execute(array(':id_user' => $_SESSION['id_utilizador'], ':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));

//-- Todas as empresas dos grupos ATIVOS, de QUALQUER entidade
// $query_empresas = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, em.id_empresa, em.nome, c.num_conta FROM (SELECT * FROM (SELECT * FROM grupo) AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
// $query_empresas->execute(array(':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));

/*//-- Obter "atividade" da empresa
$query_empresa = $connection->prepare("SELECT atividade FROM empresa WHERE id_empresa=:id_empresa");
$query_empresa->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$my_empresa = $query_empresa->fetch(PDO::FETCH_ASSOC);
$atividade_id = $my_empresa['atividade'];
*/

//-- Todas as empresas, da MESMA atividade, dos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
// $query_empresas = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, em.id_empresa, em.nome, c.num_conta FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND em.atividade=:atividade_id AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");
// $query_empresas->execute(array(':atividade_id' => $atividade_id, ':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));

//-- Todas as empresas, dos grupos do tipo NORMAL, ATIVOS, de QUALQUER entidade
$query_empresas = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, em.id_empresa, em.nome, c.num_conta FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN conta c ON em.id_empresa=c.id_empresa WHERE grupos.estado='1' AND em.ativo='1' AND (em.id_empresa<>:id_empres AND c.tipo_conta='ordem') OR (em.id_empresa=:id_empresa AND c.tipo_conta='prazo' AND c.data_lim_r>NOW()) ORDER BY em.nome ASC");// pedente
$query_empresas->execute(array(':id_empres' => $_SESSION['id_empresa'], ':id_empresa' => $_SESSION['id_empresa']));

?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Beneficiários</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="icon" href="favicon.ico">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="./js/functions.js"></script>
        <script src="js/jquery.quicksearch.js"></script>
        <script src="./js/jquery.windowmsg-1.0.js"></script>
        <script>
            $(document).ready(function() {
                hideError();
                $(document).on('mousedown', fEsconderErro);
                $.initWindowMsg();
                $(document).on('click', '#btnInserirEmpresa', function(event) {
                    if (event.handler !== true) {
                        var controlo = true;
                        $('input:radio').each(function() {
                            var name = $(this).attr("name");
                            if ($("input:radio[name=" + name + "]:checked").length == 0) {
                                controlo = false;
                            }
                        });
                        if (controlo == true) {
                            $.each($('#divEmpresas').find('.radio'), function() {
                                if ($(this).find('input[name="rdEmpresa"]').prop('checked')) {
                                    var cd1 = $(this).find('#hddCD1').val();
                                    var cd2 = $(this).find('#hddCD2').val();
                                    var cd3 = $(this).find('#hddCD3').val();
                                    var id_empresa = $(this).find('#hddIdEmpresa').val();
                                    var nome_empresa = $(this).find('#hddEmpresa').val();
                                    $.triggerParentEvent("txtContaDestino1", cd1);
                                    $.triggerParentEvent("txtContaDestino2", cd2);
                                    $.triggerParentEvent("txtContaDestino3", cd3);
                                    $.triggerParentEvent("idEmpresa", id_empresa);
                                    $.triggerParentEvent("nomeEmpresa", nome_empresa);
                                    window.close();
                                }
                            });
                        } else {
                            $('.error').show().html('<span id="error">Escolha um destinatário</span>');
                            $("body").animate({scrollTop: 0});
                        }
                        event.handler = true;
                    }
                    return false;
                });

                var qs = $('input#txtProcEmpresas').quicksearch('#divEmpresas .radio', {
                    noResults: '#tblNoResults',
                    stripeRows: ['odd', 'even'],
                    loader: 'span.loading',
                    show: function() {
                        this.style.display = "";
                        $('#btnInserirEmpresa').show();
                        $('#tblNoResults').hide();
                    },
                    hide: function() {
                        this.style.display = "none";
                        if ($(this).closest('#divEmpresas').find('.radio').filter(':visible').length == 0) {
                            $('#btnInserirEmpresa').hide();
                            $('#tblNoResults').show();
                        }
                    }
                });
                $(document).on('change', '#slcGrupo', function(event) {
                    if (event.handler !== true) {
                        var id = $(this).val();
                        var dataString = "id=" + id + "&tipo=emp_grupos";
                        var noRemove = $('#divEmpresas').find('.linha');
                        $.ajax({
                            type: "POST",
                            url: "functions/funcoes_geral.php",
                            data: dataString,
                            dataType: "json",
                            success: function(dados) {
                                $('#txtProcEmpresas').val('');
                                $('#divEmpresas').empty();
                                if (dados.sucesso == true) {
                                    //-- Adicionar, manualmente, radio de Centrais
                                    $('#divEmpresas').append('<div class="radio">' + 
                                        '<input id="rdEmpresa_2" name="rdEmpresa" type="radio" value="2">' + 
                                        '<label for="rdEmpresa" class="btnRadio">Centrais</label>' + 
                                        '<input id="hddCD1" name="hddCD1" type="hidden" value="0337">' + 
                                        '<input id="hddCD2" name="hddCD2" type="hidden" value="2347">' + 
                                        '<input id="hddCD3" name="hddCD3" type="hidden" value="4746">' + 
                                        '<input id="hddEmpresa" name="hddEmpresa" type="hidden" value="Centrais">' + 
                                        '<input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="2">' + 
                                    '</div>');
                                    //
                                    
									if (dados.dados_in != null) {
										$.each(dados.dados_in, function(i, item) {
											var num_conta_dividida = dados.dados_in[i].num_conta.match(/.{4}/g);
											$('#divEmpresas').append('<div class="radio">' +
												'<input id="rdEmpresa_' + dados.dados_in[i].id_empresa + '" name="rdEmpresa" type="radio" value="' + dados.dados_in[i].id_empresa + '">' +
												'<label for="rdEmpresa" class="btnRadio">' + dados.dados_in[i].nome + '</label>' +
												'<input id="hddCD1" name="hddCD1" type="hidden" value="' + num_conta_dividida[0] + '">' +
												'<input id="hddCD2" name="hddCD2" type="hidden" value="' + num_conta_dividida[1] + '">' +
												'<input id="hddCD3" name="hddCD3" type="hidden" value="' + num_conta_dividida[2] + '">' +
												'<input id="hddEmpresa" name="hddEmpresa" type="hidden" value="' + dados.dados_in[i].nome + '">' +
												'<input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + dados.dados_in[i].id_empresa + '">' +
												'</div>');
										});
									}
                                    $('#divEmpresas').append(noRemove);
                                    qs.cache();
                                } else {
                                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                    $("body").animate({scrollTop: 0});
                                }
                            }
                        });
                        event.handler = true;
                    }
                    return false;
                });
                $(document).on('click', '.btnRadio', function(event) {
                    if (event.handler !== true) {
                        var index = $(this).index() + 1;
                        var index = (index / 2) - 1;
                        if ($(this).closest('.radio').find('input').eq(index).prop('checked') != true) {
                            $(this).closest('.radio').find('input').eq(index).prop('checked', true);
                        }
                        event.handler = true;
                    }
                    return false;
                });
            });
        </script>
    </head>
    <body id="normal">
        <div id="var_content">
            <article>
                <div class="linha">
                    <div class="left-column">
                        <h3>Beneficiários</h3>
                    </div>
                    <div class="center-column">
                        <div class="error"></div>
                    </div>
                    <div class="right-column">&nbsp;</div>
                </div>
                <div class="linha">
                    <div id="divPesqEmpresas" name="divPesqEmpresas" style="float: left; background-color: #fff; height: 30px; width: 165px;">
                        <input id="txtProcEmpresas" name="txtProcEmpresas" class="txtProcEmails" type="text" placeholder="Pesquise aqui" style="float: left; height: 92%; margin: 0; padding: 0 0 0 5px; font-size: 12px; border: 1px #77a4d7 solid;">
                        <label class="icon-lupa" style="float: right; font-size: 20px; border: none; position: absolute; line-height: 1em; color: #eaedf1; background: #77a4d7; padding: 5px;"></label>
                    </div>
                    <div class="inputarea_col1" style="float: right; width: 140px; margin-right: 1%;">
                        <div class="styled-select">
                            <select id="slcGrupo" name="slcGrupo" size="1" class="select">
                                <option value="0" selected="selected">-- Grupo --</option>
                                <?php /* while ($linha_grupos = $query_grupos->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $linha_grupos['id']; ?>"><?php echo $linha_grupos['nome']; ?></option>
                                <?php } */ ?>
								<?php while ($linha_grupos = $query_grupos->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $linha_grupos['id']; ?>"><?php echo $linha_grupos['nome']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="linha">
                    <div id="divEmpresas" name="divEmpresas">
                        <!-- -->
						<div class="radio">
							<input id="rdEmpresa_2" name="rdEmpresa" type="radio" value="2">
							<label for="rdEmpresa" class="btnRadio">Centrais</label>
							<input id="hddCD1" name="hddCD1" type="hidden" value="0337">
							<input id="hddCD2" name="hddCD2" type="hidden" value="2347">
							<input id="hddCD3" name="hddCD3" type="hidden" value="4746">
							<input id="hddEmpresa" name="hddEmpresa" type="hidden" value="Centrais">
							<input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="2">
						</div>
						<!-- -->
						<?php
                        while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
                            $num_conta_dividida = str_split($linha_empresa['num_conta'], 4);
                            ?>
                            <div class="radio">
                                <input id="rdEmpresa_<?php echo $linha_empresa['id_empresa']; ?>" name="rdEmpresa" type="radio" value="<?php echo $linha_empresa['id_empresa']; ?>">
                                <label for="rdEmpresa" class="btnRadio"><?php
                                    /* if ($linha_empresa['nome'] == "Central Financeira") { // Centrais não estão associados a grupos, logo não aparecem no resultado da query
                                        echo "Centrais";
                                    } else */ if ($linha_empresa['id_empresa'] == $_SESSION['id_empresa']) {
                                        echo $linha_empresa['nome'].' (Conta a Prazo)';
                                    } else {
                                        echo $linha_empresa['nome'];
                                    }
                                    ?></label>
                                <input id="hddCD1" name="hddCD1" type="hidden" value="<?php echo $num_conta_dividida[0]; ?>">
                                <input id="hddCD2" name="hddCD2" type="hidden" value="<?php echo $num_conta_dividida[1]; ?>">
                                <input id="hddCD3" name="hddCD3" type="hidden" value="<?php echo $num_conta_dividida[2]; ?>">
                                <input id="hddEmpresa" name="hddEmpresa" type="hidden" value="<?php
                                    /* if ($linha_empresa['nome'] == "Central Financeira") {
                                        echo "Centrais";
                                    } else */ if ($linha_empresa['id_empresa'] == $_SESSION['id_empresa']) {
                                        echo $linha_empresa['nome']; echo " (Conta a Prazo)";
                                    } else {
                                        echo $linha_empresa['nome'];
                                    }
                                ?>">
                                <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_empresa['id_empresa']; ?>">
                            </div>
                        <?php } ?>
                        <div class="linha" style="text-align: center;">
                            <button id="btnInserirEmpresa" name="btnInserirEmpresa" class="btnNoIco" style="float: none; margin-top: 10px;">Inserir</button>
                        </div>
                    </div>
                    <table id="tblNoResults" name="tblNoResults" class="tabela">
                        <tr>
                            <td>Não existem resultados</td>
                        </tr>
                    </table>
                </div>
            </article>
        </div>
    </body>
</html>