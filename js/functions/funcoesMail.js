/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-16 16:16:31
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-24 14:29:03
 */

function constTblEmail(flag, dados, qs) {
    if (dados.sucesso === true) {
        if (flag === 1) {
            if (dados.vazio === false) {
                preencherTblEmailsRecebidos(dados, flag);
                qs.cache();
                $('#tblEmailsVazio').hide();
                $('#tblEmailsRecebidos').show();
            } else {
                $('#tblEmailsRecebidos').hide();
                $('#tblEmailsVazio').show();
            }
        } else if (flag === 2) {
            if (dados.vazio === false) {
                preencherTblEmailsEnviados(dados, flag);
                qs.cache();
                $('#tblEmailsVazio').hide();
                $('#tblEmailsEnviados').show();
            } else {
                $('#tblEmailsEnviados').hide();
                $('#tblEmailsVazio').show();
            }
        } else if (flag === 3) {
            if (dados.vazio === false) {
                preencherTblEmailsEliminados(dados, flag);
                qs.cache();
                $('#tblEmailsVazio').hide();
                $('#tblEmailsEliminados').show();
            } else {
                $('#tblEmailsEliminados').hide();
                $('#tblEmailsVazio').show();
            }
        }
    }
}

function preencherTblEmailsRecebidos(dados, flag) {
    var anexo;
    var lido;
    $('#tblEmailsRecebidos tbody').empty();
    $.each(dados.dados_in, function(i, item) {
        if (dados.dados_in[i].lido === "0") {
            lido = "nova";
        } else {
            lido = "lida";
        }
        if (dados.dados_in[i].anexo === null) {
            anexo = "";
        } else {
            anexo = "botaoAnexo icon-anexo";
        }
        $('#tblEmailsRecebidos tbody').append('<tr>' +
            '<td class="width5 ' + lido + '"><div class="checkbox"><input id="chk_' + dados.dados_in[i].id + '" type="checkbox" name="chk" class="chk" value="' + dados.dados_in[i].id + '"><label for="chk" class="label_chk">&nbsp;</label></div></td>' +
            '<td class="width25 ' + lido + ' ler"><input name="flag" type="hidden" value="' + flag + '">' + dados.dados_in[i].remetente + '</td>' +
            '<td class="width40 ' + lido + '">' + dados.dados_in[i].assunto + '</td>' +
            '<td class="width5 ' + lido + ' ' + anexo + '"></td>' +
            '<td class="width25 ' + lido + '">' + dados.dados_in[i].data + '</td>' +
            '</tr>');
    });
}

function preencherTblEmailsEnviados(dados, flag) {
    var anexo;
    var lido;
    $('#tblEmailsEnviados tbody').empty();
    $.each(dados.dados_in, function(i, item) {
        if (dados.dados_in[i].lido === "0") {
            lido = "nova";
        } else {
            lido = "lida";
        }
        if (dados.dados_in[i].anexo === null) {
            anexo = "";
        } else {
            anexo = "botaoAnexo icon-anexo";
        }
        $('#tblEmailsEnviados tbody').append('<tr>' +
            '<td class="width5 ' + lido + '"><div class="checkbox"><input id="chk_' + dados.dados_in[i].id + '" type="checkbox" name="chk" class="chk" value="' + dados.dados_in[i].id + '"><label for="chk" class="label_chk">&nbsp;</label></div></td>' +
            '<td class="width25 ' + lido + ' ler"><input name="flag" type="hidden" value="' + flag + '">' + dados.dados_in[i].destinatario + '</td>' +
            '<td class="width40 ' + lido + '">' + dados.dados_in[i].assunto + '</td>' +
            '<td class="width5 ' + lido + ' ' + anexo + '"></td>' +
            '<td class="width25 ' + lido + '">' + dados.dados_in[i].data + '</td>' +
            '</tr>');
    });
}

function preencherTblEmailsEliminados(dados, flag) {
    var anexo;
    var lido;
    $('#tblEmailsEliminados tbody').empty();
    $.each(dados.dados_in, function(i, item) {
        if (dados.dados_in[i].lido === "0") {
            lido = "nova";
        } else {
            lido = "lida";
        }
        if (dados.dados_in[i].anexo === null) {
            anexo = "";
        } else {
            anexo = "botaoAnexo icon-anexo";
        }
        $('#tblEmailsEliminados tbody').append('<tr>' +
            '<td class="width5 ' + lido + '"><div class="checkbox"><input id="chk_' + dados.dados_in[i].id + '" type="checkbox" name="chk" class="chk" value="' + dados.dados_in[i].id + '"><label for="chk" class="label_chk">&nbsp;</label></div></td>' +
            '<td class="width20 ' + lido + ' ler"><input name="flag" type="hidden" value="' + flag + '">' + dados.dados_in[i].remetente + '</td>' +
            '<td class="width20 ' + lido + '">' + dados.dados_in[i].destinatario + '</td>' +
            '<td class="width35 ' + lido + '">' + dados.dados_in[i].assunto + '</td>' +
            '<td class="width20 ' + lido + '">' + dados.dados_in[i].data + '</td>' +
            '</tr>');
    });
}

function fNovoEmail(event) {
    if (event.handler !== true) {
        $('[id^="tblEmails"]').hide();
        $('#divCabecalho').hide();
        $('#divTitulo').hide();
        if ($(this).data("valor") === 1) {
            $('.voltarDir').val(1);
        } else if ($(this).data("valor") === 2) {
            $('.voltarDir').val(2);
        } else if ($(this).data("valor") === 3) {
            $('.voltarDir').val(3);
        }
        var control = $('input[name="fileAnexar"]');
        $('#txtDestinatario').val('');
        $('#txtAssuntoEnviar').val('');
        $('#txtaEditor').val('');
        $('#txtPath').val('');
        control.replaceWith(control = control.clone(true));
        $('#txtDestinatario').attr('readonly', false);
        $('#txtAssuntoEnviar').attr('readonly', false);
        $('#divNovaMensagem').show();
        event.handler = true;
    }
    return false;
}

function fVoltarEmail(event) {
    if (event.handler !== true) {
        var flag = $(this).val();
        $('#divLerMail').hide();
        $('#divNovaMensagem').hide();
        $('[id^="tblEmails"]').hide();
        if (flag == 1) {
            $('#divCabecalho').show();
            $('#divTitulo').show();
            if ($('#tblEmailsRecebidos tr').length > 0) {
                $('#tblEmailsRecebidos').show();
            } else {
                $('#tblEmailsVazio').show();
            }
        } else if (flag == 2) {
            $('#divCabecalho').show();
            $('#divTitulo').show();
            if ($('#tblEmailsEnviados tr').length > 0) {
                $('#tblEmailsEnviados').show();
            } else {
                $('#tblEmailsVazio').show();
            }
        } else if (flag == 3) {
            $('#divCabecalho').show();
            $('#divTitulo').show();
            if ($('#tblEmailsEliminados tr').length > 0) {
                $('#tblEmailsEliminados').show();
            } else {
                $('#tblEmailsVazio').show();
            }
        } else if (flag == 4 || flag == 5) {
            $('#divTitulo').hide();
            $('#divCabecalho').hide();
            $('#divNovaMensagem').hide();
            $('.voltarDir').val($('#hddHistorical_flag').val());
            $('#divLerMail').show();
        }
        event.handler = true;
    }
    return false;
}

function fBtnMenuEmail(event) {
    if (event.handler !== true) {
        var nome_arr = $(this).attr('name').split('_');
        var flag;
        if (nome_arr[1] === "CaixaEntrada") {
            $('#slcFiltrarEmails').empty().append('<option selected="selected" value="0"></option>' +
                '<option value="1">De</option>' +
                '<option value="2">Data</option>');
            $("#slcFiltrarEmails").trigger("chosen:updated");
            flag = 1;
        } else if (nome_arr[1] === "Enviados") {
            $('#slcFiltrarEmails').empty().append('<option selected="selected" value="0"></option>' +
                '<option value="1">Para</option>' +
                '<option value="2">Data</option>');
            $("#slcFiltrarEmails").trigger("chosen:updated");
            flag = 2;
        } else if (nome_arr[1] === "Eliminados") {
            $('#slcFiltrarEmails').empty().append('<option selected="selected" value="0"></option>' +
                '<option value="1">De</option>' +
                '<option value="2">Para</option>' +
                '<option value="3">Data</option>');
            $("#slcFiltrarEmails").trigger("chosen:updated");
            flag = 3;
        }
        $('.txtProcEmails').val('');
        $('[id^="tblEmails"]').hide();
        $('#divLerMail').hide();
        $('#divNovaMensagem').hide();
        $('button[name^="btn_"]').removeClass('btnNoIcoActive').addClass('btnNoIco');
        $(this).removeClass('btnNoIco').addClass('btnNoIcoActive');
        $('#div' + nome_arr[1]).show();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_correio.php",
            data: "flag=" + flag + "&tipo=mensagens",
            dataType: "json",
            beforeSend: function() {
                $('#divTitulo').hide();
                $('#divCabecalho').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#divTitulo').show();
                $('#divCabecalho').show();
                $('#btnDelEmail').data("valor", flag);
                $('#slcFiltrarEmails').data("flag", flag);
                $('#btnNovaMsg').data("valor", flag);
                constTblEmail(flag, dados, event.data.qs);
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnReencEmail(event) {
    if (event.handler !== true) {
        var old_flag = $('.voltarDir').val();
        var new_flag = 5;
        var anexo_tmp = $('#aAnexoEmail').attr('href');
        var anexo_split = anexo_tmp.split('/');
        var anexo = anexo_split[1];
        var assunto = "FW: " + $('#txtLerAssunto').val();
        var mensagem = $('#txtaMensagem').val();
        $('.voltarDir').val(new_flag);
        $('#divLerMail').hide();
        $('#divNovaMensagem').show();
        $('#hddHistorical_flag').val(old_flag);
        $('#txtaEditor').val(mensagem);
        $('#txtAssuntoEnviar').val(assunto);
        $('#txtDestinatario').attr('readonly', false);
        $('#txtPath').val(anexo);
        resizeInput(event.data.BrowserDetect);
        event.handler = true;
    }
    return false;
}

function fBtnRespEmail(event) {
    if (event.handler !== true) {
        var old_flag = $('.voltarDir').val();
        var new_flag = 4;
        var id_remetente = $('#hddRemetente').val();
        var remetente = $('#txtRemetente').val();
        var assunto = $('#txtLerAssunto').val();
        $('.voltarDir').val(new_flag);
        $('#divLerMail').hide();
        $('#divNovaMensagem').show();
        $('#hddDestinatario').val(id_remetente);
        $('#hddHistorical_flag').val(old_flag);
        $('#txtDestinatario').val(remetente).attr('readonly', true).css('cursor', 'default');
        $('#txtAssuntoEnviar').val(assunto).attr('readonly', true);
        resizeInput(event.data.BrowserDetect);
        event.handler = true;
    }
    return false;
}

function fSlcFiltrarEmails(event) {
    if (event.handler !== true) {
        var id_filtro = $(this).val();
        var flag = $(this).data("flag");
        $.ajax({
            type: "POST",
            url: "functions/funcoes_correio.php",
            dataType: "json",
            data: "id_filtro=" + id_filtro + "&flag=" + flag + "&tipo=filtrar_emails",
            beforeSend: function() {
                $('#tblEmailsRecebidos').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                constTblEmail(flag, dados, event.data.qs);
            }
        });
        event.handler = true;
    }
    return false;
}

function fLerEmail(event) {
    if (event.handler !== true) {
        var td = $(this).closest('tr').children('td');
        $.each(td, function() {
            $(this).removeClass('nova');
            $(this).addClass('lida');
        });
        var flag = $(this).children('input[name="flag"]').val();
        var id = $(this).closest('tr').children('td').eq(0).find('input').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_correio.php",
            data: "id=" + id + "&tipo=ler_mensagem",
            dataType: "json",
            beforeSend: function() {
                $('[id^="tblEmails"]').hide();
                $('#divCabecalho').hide();
                $('#divTitulo').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    $('#hddRemetente').val(dados.id_remetente);
                    $('#txtRemetente').val(dados.remetente);
                    $('#txtLerAssunto').val(dados.assunto);
                    if (dados.anexo !== null) {
                        $('#aAnexoEmail').attr('href', dados.anexo); //estava esta
//                        var hrefDecoded=encodeURI(dados.anexo); //meti estas (o encodeURI não funciona) (nao funciona em localhost)
//                         var hrefDecoded=escape(dados.anexo); //meti estas (nao funciona em localhost comentado em localhost)
//                        $('#aAnexoEmail').attr('href', hrefDecoded); // meti estas (nao funciona em localhost comentado em localhost)
                        $('#aAnexoEmail').empty();
                        $('#aAnexoEmail').append('<div class="botao iconwrapper btnNoIco left">' +
                            '<div class="novolabelicon icon-anexo"></div>' +
                            '</div>');
                    } else {
                        $('#aAnexoEmail').empty();
                    }
                    CKEDITOR.instances.txtaMensagem.setReadOnly(false);
                    CKEDITOR.instances.txtaMensagem.setData(dados.mensagem);
                    $('.voltarDir').val(flag);
                    $('#hddMail').val(id);
                    // resizeInput(event.data.BrowserDetect);
                    $('#divLerMail').show();
                    CKEDITOR.instances.txtaMensagem.setReadOnly(true);
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnDelEmail(event) {
    if (event.handler !== true) {
        var count = 0;
        var tabela;
        if ($(this).data("valor") === 1) {
            tabela = "tblEmailsRecebidos";
        } else if ($(this).data("valor") === 2) {
            tabela = "tblEmailsEnviados";
        } else if ($(this).data("valor") === 3) {
            tabela = "tblEmailsEliminados";
        }
        $.each($('#' + tabela + ' tr'), function() {
            if ($(this).children('td').eq(0).find('input').prop('checked') === true) {
                count = count + 1;
            }
        });
        if (count > 0) {
            if (confirm("Deseja mesmo eliminar as mensagens?")) {
                $.each($('#' + tabela + ' tr'), function() {
                    if ($(this).children('td').eq(0).find('input').prop('checked') === true) {
                        var flag = $(this).find('.ler').children('input').val();
                        var id = $(this).children('td').eq(0).find('input').val();
                        $.ajax({
                            type: "POST",
                            url: "functions/funcoes_correio.php",
                            data: "id=" + id + "&flag=" + flag + "&tipo=apagar_mensagem",
                            dataType: "json",
                            beforeSend: function() {
                                $('#' + tabela).hide();
                                $('#divTitulo').hide();
                                $('#divCabecalho').hide();
                                showLoading();
                            },
                            success: function(dados) {
                                hideLoading();
                                $('#divTitulo').show();
                                $('#divCabecalho').show();
                                if (dados.sucesso === true) {
                                    if (dados.vazio === false) {
                                        if (flag === 1) {
                                            preencherTblEmailsRecebidos(dados, flag);
                                        } else if (flag === 2) {
                                            preencherTblEmailsEnviados(dados, flag);
                                        } else if (flag === 3) {
                                            preencherTblEmailsEliminados(dados, flag);
                                        }
                                        event.data.qs.cache();
                                        $('#' + tabela).show();
                                    } else {
                                        $('#' + tabela).hide();
                                        $('#tblEmailsVazio').show();
                                    }
                                }
                            }
                        });
                    }
                });
            }
        } else {
            $('.error').show().html('<span id="error">Não selecionou nenhuma mensagem</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnElimEmail(event) {
    if (event.handler !== true) {
        if (confirm("Deseja mesmo eliminar a mensagem?")) {
            var flag = $('#btnVoltarR').val();
            var id = $('#hddMail').val();
            $.ajax({
                type: "POST",
                url: "functions/funcoes_correio.php",
                data: "id=" + id + "&flag=" + flag + "&tipo=apagar_mensagem",
                dataType: "json",
                beforeSend: function() {
                    $('#tblEmailsRecebidos').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#divLerMail').hide();
                    constTblEmail(flag, dados, event.data.qs);
                }
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnLimparEmail(event) {
    if (event.handler !== true) {
        var control = $('input[name="fileAnexar"]');
        $('#txtDestinatario').val('');
        $('#txtAssuntoEnviar').val('');
        $('#txtaEditor').val('');
        $('#txtPath').val('');
        control.replaceWith(control = control.clone(true));
        $('#txtDestinatario').attr('readonly', false);
        $('#txtAssuntoEnviar').attr('readonly', false);
        event.handler = true;
    }
    return false;
}

function fBtnEnviarEmail(event) {
    if (event.handler !== true) {
        var data_v;
        var dataString;
        var flag = $('.voltarDir').val();
        var assunto = $('#txtAssuntoEnviar').val();
        var mensagem = $('#txtaEditor').val();
        var caminho = $('#txtPath').val();
        var tipo_user = $('#hddTipoUser').val();
        if (tipo_user === "admin") {
            var remetente = $('#slcRemetente').val();
        }
        $.each($('#divDadosDest').children('input[name="hddDestinatario"]'), function() {
            var destinatario = $(this).val();
            if (flag === 5) {
                if (destinatario !== undefined && destinatario.length !== 0) {
                    var id_mail = $('#hddMail').val();
                    if (tipo_user === "admin") {
                        data_v = getVirtualDateAdmin(destinatario);
                        dataString = "remetente=" + remetente + "&destinatario=" + destinatario + "&assunto=" + assunto + "&mensagem=" + mensagem + "&data_virtual=" + data_v + "&id_mail=" + id_mail + "&tipo=reencaminhar";
                    } else {
                        data_v = getVirtualDate();
                        dataString = "destinatario=" + destinatario + "&assunto=" + assunto + "&mensagem=" + mensagem + "&data_virtual=" + data_v + "&id_mail=" + id_mail + "&tipo=reencaminhar";
                    }
                    $.ajax({
                        type: "POST",
                        url: "functions/funcoes_correio.php",
                        data: dataString,
                        dataType: "json",
                        beforeSend: function() {
                            showLoading();
                        },
                        success: function(dados) {
                            hideLoading();
                            if (dados.sucesso === true) {
                                $('#txtDestinatario').attr('readonly', false);
                                $('#txtAssuntoEnviar').attr('readonly', false);
                                $('#divDadosDest').empty().append('<textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq width100 widthMax190"></textarea>' +
                                    '<input id="hddDestinatario" name="hddDestinatario" type="hidden">');
                            } else {
                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                $("body").animate({
                                    scrollTop: 0
                                });
                            }
                        }
                    });
                } else {
                    $('.error').show().html('<span id="error">Insira um destinatário</span>');
                    $("body").animate({
                        scrollTop: 0
                    });
                }
            } else {
                if (destinatario !== undefined && destinatario.length !== 0) {
                    var enviar = 1;
                    if (assunto === "") {
                        if (confirm("Deseja enviar a mensagem sem assunto?")) {
                            enviar = 1;
                        } else {
                            enviar = 0;
                        }
                    }
                    if (enviar === 1) {
                        var ficheiro = $('input[name="fileAnexar"]').prop("files")[0];
                        var fd = new FormData();
                        if (tipo_user === "admin") {
                            data_v = getVirtualDateAdmin(destinatario);
                            fd.append('remetente', remetente);
                        } else {
                            data_v = getVirtualDate();
                        }
                        fd.append('destinatario', destinatario);
                        fd.append('assunto', assunto);
                        fd.append('mensagem', mensagem);
                        fd.append('txtPath', caminho);
                        fd.append('fileAnexar', ficheiro);
                        fd.append('data_virtual', data_v);
                        fd.append('tipo', "enviar_mensagem");
                        $.ajax({
                            url: "functions/funcoes_correio.php",
                            type: 'POST',
                            data: fd,
                            contentType: false,
                            cache: false,
                            processData: false,
                            dataType: "json",
                            success: function(dados) {
                                if (dados.sucesso === true) {
                                    var control = $('input[name="fileAnexar"]');
                                    $('#txtDestinatario').val('');
                                    $('#txtAssuntoEnviar').val('');
                                    $('#txtaEditor').val('');
                                    $('#txtPath').val('');
                                    control.replaceWith(control = control.clone(true));
                                    $('#txtDestinatario').attr('readonly', false);
                                    $('#txtAssuntoEnviar').attr('readonly', false);
                                    $('#divDadosDest').empty().append('<textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq width100 widthMax190"></textarea>' +
                                        '<input id="hddDestinatario" name="hddDestinatario" type="hidden">');
                                } else {
                                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                            }
                        });
                    }
                } else {
                    $('.error').show().html('<span id="error">Insira um destinatário</span>');
                    $("body").animate({
                        scrollTop: 0
                    });
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fDestinatarioEmail(event) {
    if (event.handler !== true) {
        var user = $('#hddTipoUser').val();
        if ($(this).attr("readonly") === false || $(this).attr("readonly") === undefined) {
            $('#divDadosDest').empty().append('<textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq width100 widthMax190"></textarea>' +
                '<input id="hddDestinatario" name="hddDestinatario" type="hidden">');
            var left = (screen.width / 2) - (550 / 2);
            var top = (screen.height / 2) - (360 / 2);
            event.data.childWin = window.open('pag_destinatarios.php?tipo=' + user, 'Destinatários', 'resizable=no,width=550,height=360,scrollbars=yes,top=' + top + ', left=' + left);
            if (window.focus) {
                event.data.childWin.focus();
            }
            CKEDITOR.instances.txtaEditor.setReadOnly(false);
            event.handler = true;
        }
    }
}

function fFileAnexar(event) {
    if (event.handler !== true) {
        var nome_ficheiro = $(this).val().replace(/C:\\fakepath\\/i, '');
        $('#txtPath').val(nome_ficheiro);
        event.handler = true;
    }
}

function fBtnAnexar(event) {
    if (event.handler !== true) {
        $('#fileAnexar').click();
        event.handler = true;
    }
    return false;
}