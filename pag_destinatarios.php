<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-26 18:15:45
*/

if ($_GET['tipo'] == "admin") {
    include('./conf/check_admin.php');
//    $query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN grupo g ON g.id=emp.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND emp.id_empresa<>:cc AND emp.id_empresa<>:cf AND emp.id_empresa<>:cp AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");//estava esta
    $query_empresas = $connection->prepare("SELECT g.id AS id_grupo,g.nome AS nome_grupo,e.nome AS entidade_grupo, emp.id_empresa, emp.nome,atv.designacao AS nome_atv FROM empresa emp INNER JOIN grupo g ON g.id=emp.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN atividade atv ON emp.atividade=atv.id INNER JOIN entidade e ON u.id_entidade=e.id  WHERE emp.ativo='1' AND emp.id_empresa<>:cc AND emp.id_empresa<>:cf AND emp.id_empresa<>:cp AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY emp.nome");//meti esta
    $query_empresas->execute(array(':cc' => 1, ':cf' => 2, ':cp' => 3, ':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
    $row_count = $query_empresas->rowCount();

    $query_grupos = $connection->prepare("SELECT g.id, g.nome FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE u.tipo=:tipo AND u.id=:id_utilizador");
    $query_grupos->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
} else {
    include('./conf/check.php');
    if ($_SESSION['tipo_grupo'] == "Bolsa") {
        $query_grupos = $connection->prepare("SELECT g.id, g.nome FROM grupo g INNER JOIN empresa emp ON g.id=emp.id_grupo INNER JOIN utilizador u ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND u.id=:id_utilizador");
        $query_grupos->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
		
		$query_empresas = $connection->prepare("SELECT emp.id_empresa, emp.nome FROM empresa emp LEFT JOIN grupo g ON emp.id_grupo=g.id LEFT JOIN utilizador u ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND emp.id_empresa<>:id_empresa1 AND emp.id_empresa<>:cc AND emp.id_empresa<>:cp AND (g.id=(SELECT emp.id_grupo FROM empresa emp WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa2) OR g.id IS NULL) ORDER BY emp.nome");
        $query_empresas->execute(array(':id_empresa1' => $_SESSION['id_empresa'], ':cc' => "1", ':cp' => "3", ':id_empresa2' => $_SESSION['id_empresa']));
        $row_count = $query_empresas->rowCount();
		
    } else {
        /* OLD version * /
		$query_grupos = $connection->prepare("SELECT g.id, g.nome FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao=:tipo_grupo");
        $query_grupos->execute(array(':tipo_grupo' => "Normal"));
		
        $query_empresa = $connection->prepare("SELECT emp.id_empresa, emp.nome FROM empresa emp LEFT JOIN grupo g ON emp.id_grupo=g.id LEFT JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE emp.ativo='1' AND (emp.id_empresa<>:id_empresa OR g.id IS NULL) AND (tg.designacao=:tipo_grupo OR tg.designacao IS NULL) ORDER BY nome");
        $query_empresa->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':tipo_grupo' => "Normal"));
        $row_count = $query_empresa->rowCount();
		/* */
        
        //-- Todos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
        //$query_grupos = $connection->prepare("SELECT grupos.id, grupos.nome FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos WHERE grupos.estado='1' ORDER BY grupos.nome ASC");//
        $query_grupos = $connection->prepare("SELECT g.id, g.nome FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id WHERE last_estado_grupos.estado='1'   AND tp.designacao='Normal'  ORDER BY g.nome ASC");//meti esta
        $query_grupos->execute();
        
        //-- Todas as empresas, dos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
        //$query_empresas = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, grupos.entidade_grupo, em.id_empresa, em.nome, atv.designacao AS nome_atv FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado, e.nome AS entidade_grupo FROM estado_grupo eg INNER JOIN utilizador admin ON eg.id_user=admin.id INNER JOIN entidade e ON admin.id_entidade=e.id ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN atividade atv ON em.atividade=atv.id WHERE grupos.estado='1' AND em.ativo='1' AND em.id_empresa<>:id_empresa ORDER BY em.nome ASC"); //estava esta //pedente
        $query_empresas = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, grupos.entidade_grupo, em.id_empresa, em.nome, atv.designacao AS nome_atv FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado, e.nome AS entidade_grupo FROM estado_grupo eg INNER JOIN utilizador admin ON eg.id_user=admin.id INNER JOIN entidade e ON admin.id_entidade=e.id ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN atividade atv ON em.atividade=atv.id WHERE /*grupos.estado='1' AND*/ em.ativo='1' AND em.id_empresa<>:id_empresa ORDER BY em.nome ASC"); // meti esta pedente(esta devolver das empresas de todos os grupos tanto ativos como inativos)
//        $query_empresas->execute(array(':id_empresa' => 2));
        $query_empresas->execute(array(':id_empresa' => $_SESSION['id_empresa']));
		$row_count = $query_empresas->rowCount();
    }
}
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Destinatário</title>
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/chosen.css">
        <link rel="stylesheet" href="css/base.css">
        <link rel="stylesheet" href="css/destinatarios.css">
        <link rel="icon" href="favicon.ico">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <script src="js/functions.js"></script>
        <script src="js/jquery.quicksearch.js"></script>
        <script src="js/jquery.windowmsg-1.0.js"></script>
        <script>
            $(document).ready(function() {
                hideError();
                $('#tblVazia').hide();
                $(document).on('mousedown', fEsconderErro);
                $.initWindowMsg();
                $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});

                var qs = $('input#txtProcEmpresas').quicksearch('#tblEmpresas .tbody', {
                    noResults: '#tblVazia',
                    stripeRows: ['odd', 'even'],
                    loader: 'span.loading',
                    show: function() {
                        this.style.display = "";
                        $('#tblEmpresas tr').eq(0).show();
                        $('#btnInserirDestinatarios').show();
                        var tam_chk = $(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').length;
                        var chk_chc = $(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').filter(':checked').length;
                        if (tam_chk != chk_chc) {
                            $(this).closest('#tblEmpresas').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                        }
                    },
                    hide: function() {
                        this.style.display = "none";
                        if ($(this).closest('#tblEmpresas').find('.tbody').filter(':visible').length == "0") {
                            $('#tblEmpresas tr').eq(0).hide();
                            $('#btnInserirDestinatarios').hide();
                        }
                        $(this).find('.chk').prop('checked', false);
                        var tam_chk = $(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').length;
                        var chk_chc = $(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').filter(':checked').length;
                        if (tam_chk != chk_chc) {
                            $(this).closest('#tblEmpresas').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                        } else {
                            if ((tam_chk == "0" && chk_chc == "0")) {
                                $(this).closest('#tblEmpresas').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                            } else {
                                $(this).closest('#tblEmpresas').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', true);
                            }
                        }
                    }
                });
                if ($('#tblEmpresas').length != 1) {
                    $('#tblVazia').show();
                }
                $(document).on('click', '#btnInserirDestinatarios', function(event) {
                    if (event.handler !== true) {
                        var controlo = true;
                        $('input:checkbox').each(function() {
                            if ($(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').filter(':checked').length < 1) {
                                controlo = false;
                            }
                        });
                        if (controlo === true) {
                            $.each($('#tblEmpresas').find('.checkbox'), function() {
                                if ($(this).find('.chk').prop('checked')) {
                                    var id_empresa = $(this).find('#hddIdEmpresa').val();
                                    var nome_empresa = $(this).find('#hddEmpresa').val();
                                    if (id_empresa !== undefined && nome_empresa !== undefined) {
                                        $.triggerParentEvent("idEmpresa", id_empresa);
                                        $.triggerParentEvent("nomeEmpresa", nome_empresa);
                                    }
                                }
                            });
                            window.close();
                        } else {
                            $('.error').show().html('<span id="error">Escolha uma empresa</span>');
                            $("body").animate({scrollTop: 0});
                        }
                        event.handler = true;
                    }
                    return false;
                });

                $(document).on('change', '#slcGrupo', function(event) {
                    if (event.handler !== true) {
                        var id = $(this).val();
                        $.ajax({
                            type: "POST",
                            url: "functions/funcoes_correio.php",
                            data: 'id=' + id + '&tipo=empresas_grupo',
                            dataType: "json",
                            success: function(dados) {
                                if (dados.sucesso === true) {
                                    if (dados.vazio === false) {
                                        $('#tblVazia').hide();
                                        emptyTable('#tblEmpresas');
                                        $.each(dados.dados_in, function(i, item) {
                                            $('#tblEmpresas').append('<tr class="tbody">' +
                                                    '<td>' +
                                                    '<div class="checkbox">' +
                                                    '<input id="chkEmpresa_' + dados.dados_in[i].id_empresa + '" name="chkEmpresa" type="checkbox" class="chk" value="' + dados.dados_in[i].id_empresa + '">' +
                                                    '<label for="chkEmpresa" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                    '<input id="hddEmpresa" name="hddEmpresa" type="hidden" value="' + dados.dados_in[i].nome + '">' +
                                                    '<input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + dados.dados_in[i].id_empresa + '">' +
                                                    '</div>' +
                                                    '</td>' +
                                                    '<td>' + dados.dados_in[i].entidade_grupo + '</td>' +
                                                    '<td>' + dados.dados_in[i].nome + '</td>' +
                                                    '<td>' + dados.dados_in[i].nome_atv + '</td>' +
                                                    '</tr>');
                                        });
                                        $('#tblEmpresas').closest('.linha').show();
                                        qs.cache();
                                    } else {
                                        $('#tblVazia').empty().append('<tr>' +
                                                '<td>' + dados.mensagem + '</td>' +
                                                '</tr>').show();
                                        $('#tblEmpresas').closest('.linha').hide();
                                    }
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

                $(document).on('click', '#chkAllEmpresas', function(event) {
                    if (event.handler !== true) {
                        var estado = this.checked;
                        $(".tbody:visible").each(function() {
                            $(this).find('input[class="chk"]').prop('checked', estado);
                        });
                        event.handler = true;
                    }
                });

                $(document).on('click', 'label[for="chkEmpresa"]', function(event) {
                    if (event.handler !== true) {
                        if ($(this).closest('.checkbox').find('.chk').prop('checked') === true) {
                            $(this).closest('#tblEmpresas').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                            $(this).closest('.checkbox').find('.chk').prop('checked', false);
                        } else {
                            var tam_chk = $(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').length;
                            var chk_chc = $(this).closest('#tblEmpresas').find('.tbody').filter(':visible').find('input[type=checkbox].chk').filter(':checked').length + 1;
                            if (tam_chk == chk_chc) {
                                $(this).closest('#tblEmpresas').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', true);
                            }
                            $(this).closest('.checkbox').find('.chk').prop('checked', true);
                        }
                        event.handler = true;
                    }
                });
            });
        </script>
    </head>
    <body>
        <div class="var_content">
            <div class="linha left">
                <h3 class="left">Destinatários</h3>
                <div class="error"></div>
            </div>
            <?php if ($row_count > 0) { ?>
                <div class="linha left">
                    <div id="divProcEmpresas" name="divProcEmpresas" class="inputarea left">
                        <input id="txtProcEmpresas" name="txtProcEmpresas" type="text" class="procura left editableText" placeholder="Pesquise aqui">
                        <div class="iconwrapper left">
                            <div class="novolabelicon icon-lupa"></div>
                        </div>
                    </div>
                    <button id="btnInserirDestinatarios" name="btnInserirDestinatarios" class="botao">Inserir</button>
                    <select id="slcGrupo" name="slcGrupo" size="1" class="chosenSelect" data-placeholder="Grupo">
                        <option value="0" selected="selected"></option>
                        <?php while ($linha_grupos = $query_grupos->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?php echo $linha_grupos['id']; ?>"><?php echo $linha_grupos['nome']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <table id="tblEmpresas" name="tblEmpresas" class="tabela">
                    <tr class="th">
                        <td>
                            <div class="checkbox">
                                <input id="chkAllEmpresas" name="chkAllEmpresas" type="checkbox" class="chk">
                                <label for="chkAllEmpresas" style="padding-left: 0;">&nbsp;</label>
                            </div>
                        </td>
                        <td>Entidade</td>
                        <td>Nome empresa</td>
                        <td>Atividade</td>
                    </tr>
                    <?php
                    while ($linha_empresa = $query_empresas->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr class="tbody">
                            <td>
                                <div class="checkbox">
                                    <input id="chkEmpresa_<?php echo $linha_empresa['id_empresa']; ?>" name="chkEmpresa" type="checkbox" class="chk" value="<?php echo $linha_empresa['id_empresa']; ?>">
                                    <label for="chkEmpresa" class="label_chk" style="padding-left: 0;">&nbsp;</label>
                                    <input id="hddEmpresa" name="hddEmpresa" type="hidden" value="<?php echo $linha_empresa['nome']; ?>">
                                    <input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="<?php echo $linha_empresa['id_empresa']; ?>">
                                </div>
                            </td>
                            <td><?php echo $linha_empresa['entidade_grupo']; ?></td>
                            <td><?php echo $linha_empresa['nome']; ?></td>
                            <td><?php echo $linha_empresa['nome_atv']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
            <table id="tblVazia" name="tblVazia" class="tabela">
                <tr>
                    <td colspan="5" style="background-color: #2b6db9; color: #fff;">Não existem resultados</td>
                </tr>
            </table>
        </div>
    </body>
</html>