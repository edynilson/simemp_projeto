<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-25 18:18:17
*/

include('./conf/check_admin.php');
include_once('./conf/common.php');
include_once('./functions/functions.php');

$query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.para=:para1 OR co.para=:para2 OR co.para=:para3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
$query_email_recebido->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':para1' => 1, ':para2' => 2, ':para3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));

$datetime = new DateTime();
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0">
        <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/chosen.css">
        <link rel="stylesheet" href="css/base.css">
        <link rel="stylesheet" href="css/email.css">
        <link rel="icon" href="favicon.ico">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="js/functions.js"></script>
        <script src="js/jquery.windowmsg-1.0.js"></script>
        <script src="ckeditor/ckeditor.js"></script>
        <script src="js/jquery.quicksearch.js"></script>
        <script src="ckeditor/adapters/jquery.js"></script>
        <script src="js/functions/funcoesMail.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                if ($('input[name="windowCommEvent"]').length) {
                    $('form[name="windowComm"]').remove();
                }
                $('.chosenSelect').chosen({
                    allow_single_deselect: true,
                    no_results_text: 'Sem resultados!'
                });
                heartbeat();
                setInterval(heartbeat, 60000);
                var BrowserDetect = fDetectBrowser();
                BrowserDetect.init();
                $.initWindowMsg();
                var childWin;
                hideError();
                hideLoading();
                $('#divLerMail').hide();
                $('#divNovaMensagem').hide();
                $('#tblEmailsEliminados').hide();
                $('#tblEmailsEnviados').hide();
                $('#txtaEditor').ckeditor();
                $('#txtaMensagem').ckeditor();
                var qs = $('input#txtProcEmails').quicksearch('.tabela_proc tbody tr', {
                    noResults: '#tblEmailsVazio',
                    stripeRows: ['odd', 'even'],
                    loader: 'span.loading'
                });
                if ($('#tblEmailsRecebidos tr').length > 0) {
                    $('#tblEmailsVazio').hide();
                    $('#tblEmailsRecebidos').show();
                } else {
                    $('#tblEmailsRecebidos').hide();
                    $('#tblEmailsVazio').show();
                }
                $(document).on('mousedown', fEsconderErro);
                $(document).on('click', '.label_chk', fChkClick);
                $(document).on('click', '.ler', {BrowserDetect: BrowserDetect}, fLerEmail);
                $(document).on('click', '.voltarDir', fVoltarEmail);
                $(document).on('click', '#btnAnexar', fBtnAnexar);
                $(document).on('click', '#btnDelEmail', {qs: qs}, fBtnDelEmail);
                $(document).on('click', '#btnEliminar', {qs: qs}, fBtnElimEmail);
                $(document).on('click', '#btnEnviar', fBtnEnviarEmail);
                $(document).on('click', '#btnLimpar', fBtnLimparEmail);
                $(document).on('click', '#btnNovaMsg', fNovoEmail);
                $(document).on('click', '#btnReencaminhar', {BrowserDetect: BrowserDetect}, fBtnReencEmail);
                $(document).on('click', '#btnRsp', {BrowserDetect: BrowserDetect}, fBtnRespEmail);
                $(document).on('change', '#fileAnexar', fFileAnexar);
                $(document).on('change', '#slcFiltrarEmails', {qs: qs}, fSlcFiltrarEmails);
                $(document).on('click', '#txtDestinatario', {childWin: childWin}, fDestinatarioEmail);
                $(document).on('click', 'button[name^="btn_"]', {qs: qs}, fBtnMenuEmail);

                $.windowMsg("idEmpresa", function(message) {
                    if ($("#hddDestinatario").length == 1) {
                        if ($("#hddDestinatario").val() === "") {
                            $("#hddDestinatario").remove();
                        }
                    }
                    $('#txtDestinatario').after('<input id="hddDestinatario" name="hddDestinatario" type="hidden" value="' + message + '">');
                });
                $.windowMsg("nomeEmpresa", function(message) {
                    if ($('#txtDestinatario').val() === "") {
                        $('#txtDestinatario').val(message);
                    } else {
                        var content = $('#txtDestinatario').val();
                        $('#txtDestinatario').val(content + '; ' + message);
                    }
                });
            });
        </script>
    </head>
    <body>
        <header>
            <div class="panel" style="height: 40px; width: 12%; position: absolute">
                <a href="pag_admin.php" style="display: inline-block; margin-left: 2%; padding-top: 4%">
                    <span class="icon-home"></span>
                </a>
                <a href="?lingua=pt" id="pt" style="display: inline-block">
                    <img src="images/pt.gif" alt="" width="35" height="25" style="margin-left: 45%; padding-bottom: 40%">
                </a>
                <a href="?lingua=en" id="en" style="display: inline-block">
                    <img src="images/en.jpg" alt="" width="35" height="25" style="padding-bottom: 40%; margin-left: 55%">
                </a>
            </div>
            <div>
                <img id="logo_simemp" src="./images/logo_med.png">
            </div>
            <div class="panel">
                <a href='terminar_sessao.php'>
                    <span class="icon-off"></span>
                </a>
            </div>
        </header>
        <div class="var_content left">
            <div class="loading">
                <div class="linha10 left">
                    <img src="images/loading.gif" width="150" height="150" align="absmiddle" alt="A carregar...">
                </div>
                <div class="linha left textoCentro"><?php echo $lingua['LDNG']; ?></div>
            </div>
            <div id="email" data-value="1">
                <input id="hddHistorical_flag" name="hddHistorical_flag" type="hidden" value="1">
                <input id="hddTipoUser" name="hddTipoUser" type="hidden" value="<?php echo $_SESSION['tipo']; ?>">
                <div id="divCabecalho" name="divCabecalho">
                    <div class="linha left">
                        <h3 class="left"><?php echo $lingua['EMAIL']; ?></h3>
                        <div class="error"></div>
                    </div>
                    <div class="linha left">
                        <button id="btn_CaixaEntrada" name="btn_CaixaEntrada" class="botao left btnNoIcoActive"><?php echo $lingua['CX_E']; ?></button>
                        <button id="btn_Enviados" name="btn_Enviados" class="botao left btnNoIco"><?php echo $lingua['SNDD']; ?></button>
                        <button id="btn_Eliminados" name="btn_Eliminados" class="botao left btnNoIco"><?php echo $lingua['DLTD']; ?></button>
                    </div>
                    <div class="linha left">
                        <button id="btnNovaMsg" name="btnNovaMsg" class="botao left btnNoIco" data-valor="1"><?php echo $lingua['NEW']; ?></button>
                        <button id="btnDelEmail" name="btnDelEmail" class="botao iconwrapper btnNoIco left" data-valor="1">
                            <span class="novolabelicon icon-garbage"></span>
                        </button>
                        <select id="slcFiltrarEmails" name="slcFiltrarEmails" class="chosenSelect" data-flag="1" data-placeholder="<?php echo $lingua['ORD_B']; ?>">
                            <option selected="selected" value="0"></option>
                            <option value="1"><?php echo $lingua['FROM']; ?></option>
                            <option value="2"><?php echo $lingua['DATE']; ?></option>
                        </select>
                        <div id="divProcEmails" name="divProcEmails" class="inputarea left">
                            <input id="txtProcEmails" name="txtProcEmails" type="text" class="procura left editableText" placeholder="<?php echo $lingua['SRCH_TXT']; ?>">
                            <div class="iconwrapper left">
                                <div class="novolabelicon icon-lupa"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <table id="tblEmailsRecebidos" name="tblEmailsRecebidos" class="tabela tabela_proc left">
                    <tbody><?php while ($linha_email_recebido = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) { ?>
                            <?php
                            if ($linha_email_recebido['lido'] == 0) {
                                $lido = "nova";
                            } else {
                                $lido = "lida";
                            }
                            if ($linha_email_recebido['anexo'] == null) {
                                $anexo = "";
                            } else {
                                $anexo = "botaoAnexo icon-anexo";
                            }
                            ?>
                            <tr>
                                <td class="width5 <?php echo $lido; ?>"><div class="checkbox"><input id="chk_<?php echo $linha_email_recebido['id']; ?>" type="checkbox" name="chk" class="chk" value="<?php echo $linha_email_recebido['id']; ?>"><label for="chk" class="label_chk">&nbsp;</label></div></td>
                                <td class="width25 <?php echo $lido; ?> ler"><input name="flag" type="hidden" value="1"><?php echo $linha_email_recebido['de']; ?></td>
                                <td class="width40 <?php echo $lido; ?>"><?php echo $linha_email_recebido['assunto']; ?></td>
                                <td class="width5 <?php echo $lido; ?> <?php echo $anexo; ?>"></td>
                                <td class="width25 <?php echo $lido; ?>"><?php echo $linha_email_recebido['data']; ?></td>
                            </tr>
                        <?php } ?></tbody>
                </table>
                <table id="tblEmailsEnviados" name="tblEmailsEnviados" class="tabela tabela_proc left">
                    <tbody></tbody>
                </table>
                <table id="tblEmailsEliminados" name="tblEmailsEliminados" class="tabela tabela_proc left">
                    <tbody></tbody>
                </table>
                <table id="tblEmailsVazio" name="tblEmailsVazio" class="tabela left">
                    <tr>
                        <td colspan="5" style="background-color: #2b6db9; color: #fff; font-weight: normal"><?php echo $lingua['TBL_VAZIA']; ?></td>
                    </tr>
                </table>
                <div id="divLerMail">
                    <div class="linha left">
                        <h3 class="left"><?php echo $lingua['READ'].' '.$lingua['MSG']; ?></h3>
                        <div class="error"></div>
                    </div>
                    <div class="linha left">
                        <button id="btnVoltarR" name="btnVoltarR" class="botao right btnNoIco voltarDir" value="1"><?php echo $lingua['BCK']; ?></button>
                    </div>
                    <div class="linha10 left">
                        <div class="width5 left">
                            <label for="txtRef" class="labelEsp left"><?php echo $lingua['FROM']; ?>:</label>
                        </div>
                        <div class="width95 left">
                            <div class="left width100">
                                <input id="txtRemetente" name="txtRemetente" type="text" class="inputNoBackground" readonly="readonly">
                            </div>
                        </div>
                    </div>
                    <div class="linha10 left">
                        <div class="width10 left">
                            <label for="txtLerAssunto" class="labelEsp left"><?php echo $lingua['SUB']; ?>:</label>
                        </div>
                        <div class="width90 left">
                            <input id="txtLerAssunto" name="txtLerAssunto" type="text" class="inputNoBackground" readonly="readonly">
                        </div>
                    </div>
                    <div class="linha left">
                        <a id="aAnexoEmail" href="" target="_blank"></a>
                    </div>
                    <div class="linha left">
                        <textarea id="txtaMensagem" name="txtaMensagem"></textarea>
                    </div>
                    <div class="linha left">
                        <button id="btnEliminar" name="btnEliminar" class="botao right btnNoIco"><?php echo $lingua['DEL']; ?></button>
                        <button id="btnReencaminhar" name="btnReencaminhar" class="botao right btnNoIco"><?php echo $lingua['FWD']; ?></button>
                        <button id="btnRsp" name="btnRsp" class="botao right btnNoIco"><?php echo $lingua['ANS']; ?></button>
                    </div>
                </div>
                <div id="divNovaMensagem">
                    <div class="linha left">
                        <h3 class="left"><?php echo $lingua['NEW'].' '.$lingua['MSG']; ?></h3>
                        <div class="error"></div>
                    </div>
                    <div class="linha left">
                        <button id="btnVoltarA" name="btnVoltarA" class="botao right btnNoIco voltarDir"><?php echo $lingua['BCK']; ?></button>
                    </div>
                    <form id="frmEmail" name="frmEmail" enctype="multipart/form-data">
                        <div class="linha10 left">
                            <div class="width5 left">
                                <label for="slcRemetente" class="labelEsp"><?php echo $lingua['FROM']; ?></label>
                            </div>
                            <div class="width95 left">
                                <select id="slcRemetente" name="slcRemetente" class="chosenSelect">
                                    <option value="1"><?php echo $lingua['ORDER']; ?></option>
                                    <option value="2"><?php echo $lingua['FINANCIAL_CORE']; ?></option>
                                    <option value="3"><?php echo $lingua['PUBLIC_CENTRAL']; ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="linha10 left">
                            <div class="width5 left">
                                <label class="labelEsp left"><?php echo $lingua['TO']; ?></label>
                            </div>
                            <div class="width95 left">
                                <div id="divDadosDest">
                                    <textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq width100 widthMax190"></textarea>
                                    <input id="hddDestinatario" name="hddDestinatario" type="hidden">
                                </div>
                            </div>
                        </div>
                        <div class="linha left">
                            <div class="width10 left">
                                <label for="txtAssuntoEnviar" class="labelEsp left"><?php echo $lingua['SUB']; ?></label>
                            </div>
                            <div class="width90 left">
                                <div class="inputarea width100 widthMax380 left">
                                    <input id="txtAssuntoEnviar" name="txtAssuntoEnviar" type="text" class="editableText">
                                </div>
                            </div>
                        </div>
                        <div class="linha left">
                            <textarea id="txtaEditor" name="txtaEditor"></textarea>
                        </div>
                        <div class="linha left">
                            <button id="btnAnexar" name="btnAnexar" class="botao left btnNoIco"><?php echo $lingua['ATT']; ?></button>
                            <input id="fileAnexar" name="fileAnexar" type="file">
                            <div class="inputarea width100 widthMax380 left">
                                <input id="txtPath" name="txtPath" type="text" readonly="readonly" class="inputNoBackground">
                            </div>
                        </div>
                    </form>
                    <div class="linha left">
                        <button id="btnLimpar" name="btnLimpar" class="botao iconwrapper right">
                            <span class="novolabelicon icon-garbage"></span>
                        </button>
                        <button id="btnEnviar" name="btnEnviar" class="botao right btnNoIco"><?php echo $lingua['SND']; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>