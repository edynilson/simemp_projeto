/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-25 11:14:48
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-25 16:30:36
 */

function loadEntregas() {
    hideError();
    hideLoading();
    $('#btnDeleteDeclaracao').hide();
    $('#btnDeleteDecRet').hide();
    $('#btnDeleteDecRet').closest('.linha').hide();
    $('#divDecRetDetalhes').closest('.linha').hide();
    $('#divEntregasDetalhes').closest('.linha').hide();
    $('#hideSlcFiltrarDecRet').hide();
    $('#hideSlcFiltrarEntregas').hide();
    $('#hideSlcFiltrarEntregasTipo').hide();
    $('#tblDecRetGeral').hide();
    if ($('#tblEntregasGeral tr').length > 1) {
        $('#tblDecVazia').hide();
        $('#btnDeleteDeclaracao').closest('.linha').show();
        $('#tblEntregasGeral').show();
    } else {
        $('#tblEntregasGeral').hide();
        $('#btnDeleteDeclaracao').closest('.linha').hide();
        $('#tblDecVazia').show();
    }
    $('.chosenSelect').chosen({
        allow_single_deselect: true,
        no_results_text: 'Sem resultados!'
    });
    $(document).on('click', '#aVerCodigos', fAVerCodigos);
    $(document).on('click', '#btnDeleteDeclaracao', fBtnDeleteDeclaracao);
    $(document).on('click', '#btnDeleteDecRet', fBtnDeleteDecRet);
    $(document).on('click', '#btnGuardarTipo', fBtnGuardarTipo);
    $(document).on('click', 'div[name^="btnIdDecRet_"]', fBtnIdDecRet);
    $(document).on('click', 'div[name^="btnIdEntrega"]', fBtnIdEntrega);
    $(document).on('click', 'button[name="btnInserirTipo"]', fBtnInserirTipo);
    $(document).on('click', '#btnVoltarDecRet', fBtnVoltarDecRet);
    $(document).on('click', '#btnVoltarEntregas', fBtnVoltarEntregas);
    $(document).on('click', '#chkAllDecRet', fChkAllDecRet);
    $(document).on('click', '#chkAllEntregas', fChkAllEntregas);
    $(document).on('change', '#slcFiltrarDecRet', fSlcFiltrarDecRet);
    $(document).on('change', '#slcFiltrarEntregas', fSlcFiltrarEntregas);
    $(document).on('change', '#slcFiltrarEntregasTipo', fSlcFiltrarEntregasTipo);
    $(document).on('change', '#slcOrdenarDecRet', fSlcOrdenarDecRet);
    $(document).on('change', '#slcOrdenarEntregas', fSlcOrdenarEntregas);
}

function fAVerCodigos(event) {
    if (event.handler !== true) {
        var left = (screen.width / 2) - (550 / 2);
        var top = (screen.height / 2) - (360 / 2);
        var win = window.open('./pag_codigos.php?tipo=admin', 'Codigos', 'resizable=no,width=550,height=360,scrollbars=yes,top=' + top + ', left=' + left);
        win.focus();
        event.handler = true;
    }
    return false;
}

function fBtnDeleteDeclaracao(event) {
    if (event.handler !== true) {
        var dados = {};
        $.each($('#tblEntregasGeral tr'), function(key, value) {
            if (key != "0") {
                if ($(this).find('input[type=checkbox].chk').prop('checked') === true) {
                    dados[key] = {};
                    dados[key].id = $(this).find('input[name="chkDeclaracao"]').val();
                }
            }
        });
        if (Object.size(dados) > 0) {
            if (confirm('Deseja mesmo remover as linhas selecionadas?')) {
                var id_empresa = $('#slcFiltrarEntregas').val();
                var id_filtro = $('#slcOrdenarEntregas').val();
                var id_tipo = $('#slcFiltrarEntregasTipo').val();
                var dataString = "dados=" + JSON.stringify(dados) + "&id_filtro=" + id_filtro + "&id_empresa=" + id_empresa + "&id_tipo=" + id_tipo + "&tipo=del_entregas";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function() {
                        showLoading();
                        $('#tblEntregasGeral').hide();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                $('#tblDecVazia').hide();
                                emptyTable('#tblEntregasGeral');
                                $.each(dados.dados_in, function() {
                                    $('#tblEntregasGeral').append('<tr>' +
                                        '<td style="background-color: transparent; padding: 2px;">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkDeclaracao_' + this.id + '" name="chkDeclaracao" type="checkbox" class="chk" value="' + this.id + '">' +
                                        '<label for="chkDeclaracao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdDeclaracao" name="hddIdDeclaracao" type="hidden" value="' + this.id + '">' +
                                        '</div>' +
                                        '</td>' +
                                        '<td style="padding: 2px;">' + this.data + '</td>' +
                                        '<td style="padding: 2px;">' + this.tipo + '</td>' +
                                        '<td style="padding: 2px;">' + this.pago + '</td>' +
                                        '<td style="padding: 2px;">' + number_format(this.valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                        '<td style="padding: 2px;">' + this.f_prazo + '</td>' +
                                        '<td style="padding: 2px;">' + this.empresa + '</td>' +
                                        '<td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">' +
                                        '<input id="hddIdEntrega" name="hddIdEntrega" type="hidden" value="' + this.id + '">' +
                                        '<div id="btnIdEntrega_' + this.id + '" name="btnIdEntrega" class="labelicon icon-info"></div>' +
                                        '</td>' +
                                        '</tr>');
                                });
                                $('#tblEntregasGeral').append('<input id="hddEntregas" name="hddEntregas" type="hidden" value="1">');
                                emptySelect("#slcFiltrarEntregas");
                                $.each(dados.dados_fi, function() {
                                    if (this.id == id_empresa) {
                                        $('#slcFiltrarEntregas').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                                    } else {
                                        $('#slcFiltrarEntregas').append($('<option></option>').val(this.id).text(this.nome));
                                    }
                                });
                                emptySelect("#slcFiltrarEntregasTipo");
                                $.each(dados.dados_ti, function() {
                                    if (this.id == id_tipo) {
                                        $('#slcFiltrarEntregasTipo').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                                    } else {
                                        $('#slcFiltrarEntregasTipo').append($('<option></option>').val(this.id).text(this.nome));
                                    }
                                });
                                $('#btnDeleteDeclaracao').closest('.linha').show();
                                $('#tblEntregasGeral').show();
                            } else {
                                emptyTable('#tblEntregasGeral');
                                $('#tblEntregasGeral').hide();
                                $('#btnDeleteDeclaracao').closest('.linha').hide();
                                $('#tblDecVazia').show();
                            }
                            $('#btnDeleteDeclaracao').hide();
                            $('#chkAllEntregas').prop('checked', false);
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
            $('.error').show().html('<span id="error">Selecione pelo menos uma linha</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnDeleteDecRet(event) {
    if (event.handler !== true) {
        var dados = {};
        $.each($('#tblDecRetGeral tr'), function(key, value) {
            if (key != "0") {
                if ($(this).find('input[type=checkbox].chk').prop('checked') === true) {
                    dados[key] = {};
                    dados[key].id = $(this).find('input[name="chkDecRet"]').val();
                }
            }
        });
        if (Object.size(dados) > 0) {
            if (confirm('Deseja mesmo remover as linhas selecionadas?')) {
                var id_empresa = $('#slcFiltrarDecRet').val();
                var id_filtro = $('#slcOrdenarDecRet').val();
                var dataString = "dados=" + JSON.stringify(dados) + "&id_filtro=" + id_filtro + "&id_empresa=" + id_empresa + "&tipo=del_dec_ret";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function() {
                        showLoading();
                        $('#tblDecRetGeral').hide();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                $('#tblDecVazia').hide();
                                emptyTable('#tblDecRetGeral');
                                $.each(dados.dados_in, function() {
                                    $('#tblDecRetGeral').append('<tr>' +
                                        '<td style="background-color: transparent; padding: 2px;">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkDecRet_' + this.id + '" name="chkDecRet" type="checkbox" class="chk" value="' + this.id + '">' +
                                        '<label for="chkDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdDecRet" name="hddIdDecRet" type="hidden" value="' + this.id + '">' +
                                        '</div>' +
                                        '</td>' +
                                        '<td style="padding: 2px;">' + this.data + '</td>' +
                                        '<td style="padding: 2px;">' + this.residentes + '</td>' +
                                        '<td style="padding: 2px;">' + this.pago + '</td>' +
                                        '<td style="padding: 2px;">' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                        '<td style="padding: 2px;">' + this.empresa + '</td>' +
                                        '<td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">' +
                                        '<input name="hddIdDecRet" type="hidden" value="' + this.id + '">' +
                                        '<div name="btnIdDecRet_' + this.id + '" class="labelicon icon-info"></div>' +
                                        '</td>' +
                                        '</tr>');
                                });
                                $('#tblDecRetGeral').append('<input id="hddDecRet" name="hddDecRet" type="hidden" value="1">');
                                emptySelect("#slcFiltrarDecRet");
                                $.each(dados.dados_fi, function() {
                                    if (this.id == id_empresa) {
                                        $('#slcFiltrarDecRet').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                                    } else {
                                        $('#slcFiltrarDecRet').append($('<option></option>').val(this.id).text(this.nome));
                                    }
                                });
                                $('#btnDeleteDecRet').closest('.linha').show();
                                $('#tblDecRetGeral').show();
                            } else {
                                emptyTable('#tblDecRetGeral');
                                $('#tblDecRetGeral').hide();
                                $('#btnDeleteDecRet').closest('.linha').hide();
                                $('#tblDecVazia').show();
                            }
                            $('#btnDeleteDecRet').hide();
                            $('#chkAllDecRet').prop('checked', false);
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
            $('.error').show().html('<span id="error">Selecione pelo menos uma linha</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnIdDecRet(event) {
    if (event.handler !== true) {
        $('#slcOrdenarDecRet').closest('.linha').hide();
        $('#radVOthersGroup').hide();
        var id_dec_ret = $(this).closest('td').children('input[name="hddIdDecRet"]').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_dec_ret=" + id_dec_ret + "&tipo=dec_ret_detalhes",
            beforeSend: function() {
                showLoading();
                $('#divDecRetDetalhes').closest('.linha').hide();
                $('#tblDecRetGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                $('#divDecRetDetalhes').closest('.linha').find('#lblDecRet').text("Declaração de retenções - " + dados.dados_in[0].empresa);
                emptyTable('#divDecRetDetalhes');
                $.each(dados.dados_in, function() {
                    $('#divDecRetDetalhes').append('<tr>' +
                        '<td>' + this.rubrica + ' <a id="aVerCodigos" class="bodyLink" title="Consultar Códigos">[...]</a></td>' +
                        '<td>' + this.zona + '</td>' +
                        '<td>' + number_format(this.valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                        '</tr>');
                });
                $('#divDecRetDetalhes').closest('.linha').show();
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnIdEntrega(event) {
    if (event.handler !== true) {
        $('#slcOrdenarEntregas').closest('.linha').hide();
        $('#radVOthersGroup').hide();
        var id_entrega = $(this).closest('td').children('#hddIdEntrega').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_entrega=" + id_entrega + "&tipo=ver_entregas",
            beforeSend: function() {
                showLoading();
                $('#divEntregasDetalhes').closest('.linha').hide();
                $('#tblEntregasGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                $('#divEntregasDetalhes').find('#txtDataEntrega').val(dados.data_entrega);
                $('#divEntregasDetalhes').find('#txtTipoDeclaracao').val(dados.designacao);
                $('#divEntregasDetalhes').find('#txtPago').val(dados.pago);
                $('#divEntregasDetalhes').find('#txtForaPrazo').val(dados.f_prazo);
                $('#divEntregasDetalhes').find('#txtEmpresa').val(dados.nome);
                $('#divEntregasDetalhes').find('#txtValorEntrega').val(number_format(dados.valor, 2, ',', '.'));
                $('#divEntregasDetalhes').find('#txtAnoEntrega').val(dados.ano);
                $('#divEntregasDetalhes').find('#txtMesEntrega').val(conv_mes(parseInt(dados.mes)));
                $('#divEntregasDetalhes').find('#imgPdfEntrega').closest('a').attr('href', dados.ficheiro);
                $('#divEntregasDetalhes').closest('.linha').show();
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnGuardarTipo(event) {
    if (event.handler !== true) {
        var id_entrega;
        var nome;
        var dados = {};
        var i = 0;
        if (findDuplicates() === true) {
            $('#tblTipoEntrega tr').each(function(key, value) {
                if (key > 0) {
                    id_entrega = $(this).find('#hddIdTipoEnt').val();
                    nome = $(this).find('input[name^="txtNomeDesignacao"]').val();
                    dados[i] = {};
                    dados[i].id_entrega = id_entrega;
                    dados[i].nome = nome;
                    i = i + 1;
                }
            });
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=up_tipo_ent";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === false) {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
        } else {
            $('.error').show().html('<span id="error">Existem campos duplicados</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnInserirTipo(event) {
    if (event.handler !== true) {
        var nome = $('input[name="txtNomeTipo"]').val();
        if (nome !== "") {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "nome=" + nome + "&tipo=inserir_tipo_ent",
                beforeSend: function() {
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        $('input[name="txtNomeTipo"]').val('');
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
        } else {
            $('.error').show().html('<span id="error">Tem de inserir um valor</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnVoltarDecRet(event) {
    if (event.handler !== true) {
        $('#divDecRetDetalhes').closest('.linha').hide();
        $('#slcOrdenarDecRet').closest('.linha').show();
        $('#radVOthersGroup').show();
        $('#tblDecRetGeral').show();
        event.handler = true;
    }
    return false;
}

function fBtnVoltarEntregas(event) {
    if (event.handler !== true) {
        $('#divEntregasDetalhes').closest('.linha').hide();
        $('#slcOrdenarEntregas').closest('.linha').show();
        $('#radVOthersGroup').show();
        $('#tblEntregasGeral').show();
        event.handler = true;
    }
    return false;
}

function fChkAllDecRet(event) {
    if (event.handler !== true) {
        if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
            $('#tblDecRetGeral').find('input[type=checkbox].chk').prop('checked', false);
            $('#btnDeleteDecRet').hide();
        } else {
            $('#tblDecRetGeral').find('input[type=checkbox].chk').prop('checked', true);
        }
        event.handler = true;
    }
    return false;
}

function fChkAllEntregas(event) {
    if (event.handler !== true) {
        if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
            $('#tblEntregasGeral').find('input[type=checkbox].chk').prop('checked', false);
            $('#btnDeleteDeclaracao').hide();
        } else {
            $('#tblEntregasGeral').find('input[type=checkbox].chk').prop('checked', true);
        }
        event.handler = true;
    }
    return false;
}

function fSlcFiltrarDecRet(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&tipo=filtrar_dec_ret",
            beforeSend: function() {
                $('#tblDecRetGeral').hide();
                $('#tblDecVazia').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.vazio === false) {
                    emptyTable('#tblDecRetGeral');
                    $.each(dados.dados_in, function() {
                        $('#tblDecRetGeral').append('<tr>' +
                            '<td class="transparent">' +
                            '<div class="checkbox">' +
                            '<input id="chkDecRet_' + this.id + '" name="chkDecRet" type="checkbox" class="chk" value="' + this.id + '">' +
                            '<label for="chkDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                            '<input id="hddIdDecRet" name="hddIdDecRet" type="hidden" value="' + this.id + '">' +
                            '</div>' +
                            '</td>' +
                            '<td>' + this.data + '</td>' +
                            '<td>' + this.residentes + '</td>' +
                            '<td>' + this.pago + '</td>' +
                            '<td>' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                            '<td>' + this.empresa + '</td>' +
                            '<td class="iconwrapper">' +
                            '<input name="hddIdDecRet" type="hidden" value="' + this.id + '">' +
                            '<div name="btnIdDecRet_' + this.id + '" class="novolabelicon icon-info"></div>' +
                            '</td>' +
                            '</tr>');
                    });
                    $('#tblDecRetGeral').append('<input id="hddDecRet" name="hddDecRet" type="hidden" value="1">');
                    $('#tblDecRetGeral').show();
                } else {
                    emptyTable('#tblDecRetGeral');
                    $('#tblDecRetGeral').hide();
                    $('#tblDecVazia').show();
                }
                $('#btnDeleteDecRet').hide();
                $('#chkAllDecRet').prop('checked', false);
            }
        });
        event.handler = true;
    }
    return false;
}

function fSlcFiltrarEntregas(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).val();
        var id_tipo = $('#slcFiltrarEntregasTipo').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&id_tipo=" + id_tipo + "&tipo=filtrar_entregas",
            beforeSend: function() {
                $('#tblEntregasGeral').hide();
                $('#tblDecVazia').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.vazio === false) {
                    emptyTable('#tblEntregasGeral');
                    $.each(dados.dados_in, function() {
                        $('#tblEntregasGeral').append('<tr>' +
                            '<td class="transparent">' +
                            '<div class="checkbox">' +
                            '<input id="chkDeclaracao_' + this.id + '" name="chkDeclaracao" type="checkbox" class="chk" value="' + this.id + '">' +
                            '<label for="chkDeclaracao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                            '<input id="hddIdDeclaracao" name="hddIdDeclaracao" type="hidden" value="' + this.id + '">' +
                            '</div>' +
                            '</td>' +
                            '<td>' + this.data + '</td>' +
                            '<td>' + this.tipo + '</td>' +
                            '<td>' + this.pago + '</td>' +
                            '<td>' + number_format(this.valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                            '<td>' + this.f_prazo + '</td>' +
                            '<td>' + this.empresa + '</td>' +
                            '<td class="iconwrapper">' +
                            '<input id="hddIdEntrega" name="hddIdEntrega" type="hidden" value="' + this.id + '">' +
                            '<div id="btnIdEntrega_' + this.id + '" name="btnIdEntrega" class="novolabelicon icon-info"></div>' +
                            '</td>' +
                            '</tr>');
                    });
                    $('#tblEntregasGeral').append('<input id="hddEntregas" name="hddEntregas" type="hidden" value="1">');
                    $('#tblEntregasGeral').show();
                } else {
                    emptyTable('#tblEntregasGeral');
                    $('#tblEntregasGeral').hide();
                    $('#tblDecVazia').show();
                }
                $('#btnDeleteDeclaracao').hide();
                $('#chkAllEntregas').prop('checked', false);
            }
        });
        event.handler = true;
    }
    return false;
}

function fSlcFiltrarEntregasTipo(event) {
    if (event.handler !== true) {
        var id_tipo = $(this).val();
        var id_empresa = $('#slcFiltrarEntregas').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_tipo=" + id_tipo + "&id_empresa=" + id_empresa + "&tipo=filtrar_tipo",
            beforeSend: function() {
                $('#tblEntregasGeral').hide();
                $('#tblDecVazia').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.vazio === false) {
                    emptyTable('#tblEntregasGeral');
                    $.each(dados.dados_in, function() {
                        $('#tblEntregasGeral').append('<tr>' +
                            '<td class="transparent">' +
                            '<div class="checkbox">' +
                            '<input id="chkDeclaracao_' + this.id + '" name="chkDeclaracao" type="checkbox" class="chk" value="' + this.id + '">' +
                            '<label for="chkDeclaracao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                            '<input id="hddIdDeclaracao" name="hddIdDeclaracao" type="hidden" value="' + this.id + '">' +
                            '</div>' +
                            '</td>' +
                            '<td>' + this.data + '</td>' +
                            '<td>' + this.tipo + '</td>' +
                            '<td>' + this.pago + '</td>' +
                            '<td>' + number_format(this.valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                            '<td>' + this.f_prazo + '</td>' +
                            '<td>' + this.empresa + '</td>' +
                            '<td class="iconwrapper">' +
                            '<input id="hddIdEntrega" name="hddIdEntrega" type="hidden" value="' + this.id + '">' +
                            '<div id="btnIdEntrega_' + this.id + '" name="btnIdEntrega" class="novolabelicon icon-info"></div>' +
                            '</td>' +
                            '</tr>');
                    });
                    $('#tblEntregasGeral').append('<input id="hddEntregas" name="hddEntregas" type="hidden" value="1">');
                    $('#tblEntregasGeral').show();
                } else {
                    emptyTable('#tblEntregasGeral');
                    $('#tblEntregasGeral').hide();
                    $('#tblDecVazia').show();
                }
                $('#btnDeleteDeclaracao').hide();
                $('#chkAllEntregas').prop('checked', false);
            }
        });
        event.handler = true;
    }
    return false;
}

function fSlcOrdenarDecRet(event) {
    if (event.handler !== true) {
        var dataString = "id_filtro=" + $(this).val() + "&tipo=ordenar_dec_ret";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#tblDecRetGeral').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#btnDeleteDecRet').hide();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        $('.chosenSelect').chosen('destroy');
                        $('.chosenSelect').chosen({
                            allow_single_deselect: true,
                            no_results_text: 'Sem resultados!'
                        });
                        emptyTable('#tblDecRetGeral');
                        $.each(dados.dados_in, function() {
                            $('#tblDecRetGeral').append('<tr>' +
                                '<td class="transparent">' +
                                '<div class="checkbox">' +
                                '<input id="chkDecRet_' + this.id + '" name="chkDecRet" type="checkbox" class="chk" value="' + this.id + '">' +
                                '<label for="chkDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                '<input id="hddIdDecRet" name="hddIdDecRet" type="hidden" value="' + this.id + '">' +
                                '</div>' +
                                '</td>' +
                                '<td>' + this.data + '</td>' +
                                '<td>' + this.residentes + '</td>' +
                                '<td>' + this.pago + '</td>' +
                                '<td>' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td>' + this.empresa + '</td>' +
                                '<td class="iconwrapper">' +
                                '<input name="hddIdDecRet" type="hidden" value="' + this.id + '">' +
                                '<div name="btnIdDecRet_' + this.id + '" class="novolabelicon icon-info"></div>' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblDecRetGeral').append('<input id="hddDecRet" name="hddDecRet" type="hidden" value="1">');
                        emptySelect("#slcFiltrarDecRet");
                        $.each(dados.dados_fi, function() {
                            $('#slcFiltrarDecRet').append($('<option></option>').val(this.id).text(this.nome));
                        });
                        $('#slcFiltrarDecRet').val(0);
                        $('#chkAllDecRet').prop('checked', false);
                        $('#slcFiltrarEntregas').trigger("chosen:updated");
                        $('#tblDecRetGeral').show();
                    } else {
                        emptyTable('#tblDecRetGeral');
                        $('#tblDecRetGeral').hide();
                        $('#slcFiltrarDecRet').val(0);
                        $('#hideSlcFiltrarDecRet').hide();
                        $('#tblDecVazia').show();
                    }
                }
            }
        });
        if ($(this).val() == "2") {
            $('#hideSlcFiltrarDecRet').show();
        } else {
            $('#hideSlcFiltrarDecRet').hide();
        }
        event.handler = true;
    }
    return false;
}

function fSlcOrdenarEntregas(event) {
    if (event.handler !== true) {
        var dataString = "id_filtro=" + $(this).val() + "&tipo=ordenar_entregas";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#tblEntregasGeral').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#btnDeleteDeclaracao').hide();
                $('#slcFiltrarEntregas').val('0');
                $('#slcFiltrarEntregasTipo').val('0');
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        $('.chosenSelect').chosen('destroy');
                        $('.chosenSelect').chosen({
                            allow_single_deselect: true,
                            no_results_text: 'Sem resultados!'
                        });
                        emptyTable('#tblEntregasGeral');
                        $.each(dados.dados_in, function() {
                            $('#tblEntregasGeral').append('<tr>' +
                                '<td class="transparent">' +
                                '<div class="checkbox">' +
                                '<input id="chkDeclaracao_' + this.id + '" name="chkDeclaracao" type="checkbox" class="chk" value="' + this.id + '">' +
                                '<label for="chkDeclaracao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                '<input id="hddIdDeclaracao" name="hddIdDeclaracao" type="hidden" value="' + this.id + '">' +
                                '</div>' +
                                '</td>' +
                                '<td>' + this.data + '</td>' +
                                '<td>' + this.tipo + '</td>' +
                                '<td>' + this.pago + '</td>' +
                                '<td>' + number_format(this.valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td>' + this.f_prazo + '</td>' +
                                '<td>' + this.empresa + '</td>' +
                                '<td class="iconwrapper">' +
                                '<input id="hddIdEntrega" name="hddIdEntrega" type="hidden" value="' + this.id + '">' +
                                '<div id="btnIdEntrega_' + this.id + '" name="btnIdEntrega" class="novolabelicon icon-info"></div>' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblEntregasGeral').append('<input id="hddEntregas" name="hddEntregas" type="hidden" value="1">');
                        emptySelect("#slcFiltrarEntregas");
                        $.each(dados.dados_fi, function() {
                            $('#slcFiltrarEntregas').append($('<option></option>').val(this.id).text(this.nome));
                        });
                        $('#slcFiltrarEntregas').trigger("chosen:updated");
                        $('#chkAllEntregas').prop('checked', false);
                        $('#tblEntregasGeral').show();
                    } else {
                        emptyTable('#tblEntregasGeral');
                        $('#tblEntregasGeral').hide();
                        $('#slcFiltrarEntregas').val('0');
                        $('#hideSlcFiltrarEntregas').hide();
                        $('#tblDecVazia').show();
                    }
                }
            }
        });
        if ($(this).val() == "2") {
            $('#hideSlcFiltrarEntregas').show();
            $('#hideSlcFiltrarEntregasTipo').show();
        } else if ($(this).val() == "3") {
            $('#hideSlcFiltrarEntregasTipo').hide();
            $('#hideSlcFiltrarEntregas').show();
        } else {
            $('#hideSlcFiltrarEntregasTipo').hide();
            $('#hideSlcFiltrarEntregas').hide();
        }
        event.handler = true;
    }
    return false;
}