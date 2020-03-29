/*
 * @Author: Ricardo Órfão
 * @Date:   2014-08-04 17:12:23
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-08-28 18:41:48
 */

function loadAcao() {
    hideError();
    hideLoading();
    $('#btnOrdenar').hide();
    // if ($('#tblAcoes tr').length > 1) {
	if ($('#tblAcoes tr').length == 1) {
        $('#tblAcoesVazia').hide();
        $('#btnCalcular').closest('.linha').show();
        $('#tblAcoes').show();
    } else {
        $('#tblAcoes').hide();
        $('#btnCalcular').closest('.linha').hide();
        $('#tblAcoesVazia').show();
    }
    $('#btnOrdenar').data("sortKey", ".sortMe");
    $(document).on('click', '#btnCalcular', fBtnCalcular);
    $(document).on('click', '#btnOrdenar', fBtnOrdenar);
	$('.chosenSelect').chosen('destroy');
	$('.chosenSelect').chosen({
		allow_single_deselect: true,
		no_results_text: 'Sem resultados!'
	});
    $(document).on('change', 'select[name="slcGrupoRanking"]', fSlcGrupoRanking);
}

function loadEmprestimo() {
    hideError();
    hideLoading();
    $('#tblEmprestDetail').closest('.linha').hide();
    if ($('#tblEmprestGeral tr').length > 1) {
        $('#tblEmprestGeralVazia').hide();
        $('#tblEmprestGeral').show();
    } else {
        $('#tblEmprestGeral').hide();
        $('#tblEmprestGeralVazia').show();
    }
    $(document).on('click', '#btnVoltarEmprest', fBtnVoltarEmprest);
    $(document).on('click', 'div[name="divImgEmprest"]', fDivImgEmprest);
}

function loadExtrato() {
    hideError();
    hideLoading();
    $('#tblExtratoDetail').closest('.linha').hide();
    if ($('#tblExtratoGeral tr').length > 1) {
        $('#tblExtratoGeralVazia').hide();
        $('#tblExtratoGeral').show();
    } else {
        $('#tblExtratoGeral').hide();
        $('#tblExtratoGeralVazia').show();
    }
    $(document).on('click', '#btnVoltarExtrato', fBtnVoltarExtrato);
    $(document).on('click', 'div[name="divImgEmpresa"]', fDivImgEmpresa);
	
	$(document).on('click', '#btnUpdExtrato', fBtnCorrigirExtrato);
    $(document).on('click', '#btnCorrigExtrato', fbtnCorrigExtrato);
}

function loadLocacao() {
    hideError();
    hideLoading();
    $('#tblLocDetail').closest('.linha').hide();
    if ($('#tblLocGeral tr').length > 1) {
        $('#tblLocGeralVazia').hide();
        $('#tblLocGeral').show();
    } else {
        $('#tblLocGeral').hide();
        $('#tblLocGeralVazia').show();
    }
    $(document).on('click', '#btnVoltarLocacao', fBtnVoltarLocacao);
    $(document).on('click', 'div[name="divImgLeas"]', fDivImgLeas);
}

function loadOp() {
    $.getScriptOnce('js/functions/validacaoCentralFinanceira.js');
    $.initWindowMsg();
    var childWin;
    hideError();
    hideLoading();
    floatMask();
    var dataAgr = new Date();
    $('#txtDataOp').val((('0' + dataAgr.getDate()).slice(-2)) + '-' + (('0' + (dataAgr.getMonth() + 1)).slice(-2)) + '-' + dataAgr.getFullYear() + ' ' + (('0' + dataAgr.getHours()).slice(-2)) + ':' + (('0' + dataAgr.getMinutes()).slice(-2)) + ':' + (('0' + dataAgr.getSeconds()).slice(-2)));
    $('.datetimepicker').datetimepicker({
        lang: 'pt',
        i18n: {
            pt: {
                months: [
                    'Janeiro', 'Fevereiro', 'Março', 'Abril',
                    'Maio', 'Junho', 'Julho', 'Agosto',
                    'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ],
                dayOfWeek: [
                    "D", "S", "T", "Q", "Q", "S", "S"
                ]
            }
        },
        timepicker: true,
        format: 'd-m-Y H:i:s'
    });
    $.windowMsg("idEmpresa", function(message) {
        if ($('#hddDestinatario').length == "1") {
            if ($('#hddDestinatario').val() === "") {
                $('#hddDestinatario').remove();
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
    $(document).on('click', '#btnEfOp', fBtnEfOp);
    $(document).on('click', '#txtDestinatario', fTxtDestinatario);
}

function loadAlerta(){
    //alert("Isto tem de abrir as paginas dos alertas");
    $.getScriptOnce("js/functions/validacaoCentroDados.js");
    hideError();
    hideLoading();
    $(document).on('click', 'div[name="fDivImgRemAlerta"]', fDivImgRemAlerta);
}

function loadTaxa() {
    hideError();
    hideLoading();
    dynamicInput('tblDadosRegras');
    floatMask();
    /* */
	if ($('#tblDadosRegras tr').length > 1) {
        $('#tblDadosRegrasVazia').hide();
        $('#btnGuardarTaxas').closest('.linha').show();
        $('#tblDadosRegras').show();
    } else {
        $('#tblDadosRegras').hide();
        // $('#btnGuardarTaxas').closest('.linha').hide();
        $('#tblDadosRegrasVazia').show();
    }
	/* */
	// $('.chosenSelect').chosen('destroy');
	$('.chosenSelect').chosen({
		allow_single_deselect: true,
		no_results_text: 'Sem resultados!'
	});
	/* */
    // $(document).on('keyup', '.dynamicInput', dynamicInputResize); // Função não implementada
    $(document).on('click', '#btnGuardarTaxas', fBtnGuardarTaxas);
    $(document).on('keyup', '.dynamicInput', fEditableTextChange);
    $(document).on('change', '#slcGrupoTaxas', fSlcGrupoTaxas);
	$(document).on('change', '#slcEmpresaTaxas', fSlcEmpresaTaxas);
    $(document).on('change', '#slcRegraTaxas', fSlcRegraTaxas);
}

function loadTitulo() {
    hideError();
    hideLoading();
    $('#tblTitulosDetail').closest('.linha').hide();
    if ($('#tblTitulosGeral tr').length > 1) {
        $('#tblTitulosGeralVazia').hide();
        $('#tblTitulosGeral').show();
    } else {
        $('#tblTitulosGeral').hide();
        $('#tblTitulosGeralVazia').show();
    }
    $(document).on('click', '#btnVoltarTitulos', fBtnVoltarTitulos);
    $(document).on('click', 'div[name="divImgTitulo"]', fDivImgTitulo);
}

function fBtnCalcular(event) {
    $('#tblAcoes').hide();
    showLoading();
	if (event.handler !== true) {
        var dadosString = "tipo=comparar";
        var dataStore = (function() {
            var json;
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                data: dadosString,
                dataType: "json",
				async: false,
                success: function(dados) {
                    json = dados;
                }
            });
            return {
                getJson: function() {
                    if (json)
                        return json;
                }
            };
        })();
		/* Versão atualizada (utilizando dados de cache) - 20180227 */
        var dataString = "tipo=get_rank_data";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
			async: false,
            success: function (data) {
                var total = 0.000;
                var moeda;
                $('#tblAcoes tr').each(function (key, value) {
                    if (key > 0) {
                        var nome_tab = $(this).children('td').eq(0).text();
                        $.each(dataStore.getJson(), function (key, value) {
                            var nome_empresa = dataStore.getJson()[key].nome_empresa;
                            if (nome_tab == nome_empresa) {
                                var nome_acao = dataStore.getJson()[key].nome;
                                $.each(data.dados_in, function (i, item) {
                                    if (nome_acao == data.dados_in[i].nome_acao) {
                                        var prco_cmpra = dataStore.getJson()[key].preco;
                                        var quantidade = dataStore.getJson()[key].quantidade;
                                        var prco_atual = data.dados_in[i].last_trade_price;
                                        var tot = (quantidade * parseFloat(prco_atual).toFixed(3)) - (quantidade * prco_cmpra);
                                        var moeda_emp = dataStore.getJson()[key].moeda_emp;
                                        var moeda_acao = dataStore.getJson()[key].moeda_acao;
                                        if (moeda_emp != moeda_acao) {
                                            if (moeda_acao == "USD")
                                                tot *= data.USDtoEUR_rate;
                                            else if (moeda_acao == "GBP")
                                                tot *= data.GBPtoEUR_rate;
                                        }
                                        total += tot;
                                        moeda = dataStore.getJson()[key].simbolo_moeda;
                                    }
                                });
                            }
                        });
                        var l_real = formatValor($(this).find('#tdLucroReal').text());
                        var l_r_p = parseFloat(l_real) + total;
                        $(this).find('#tdLucroPotencial').text(number_format(total, 2, ',', '.') + ' ' + moeda);
                        $(this).find('#tdLucroPotencialReal').html('<span>' + number_format(l_r_p, 2, ',', '.') + '</span>' + ' ' + moeda);
                        total = 0.000;
                    }
                });
                hideLoading();
                $('#tblAcoes').show();
                $('#btnOrdenar').show();
            }
        });
        /* */
        event.handler = true;
    }
    return false;
}

function fBtnEfOp(event) {
    if (event.handler !== true) {
        var data_v;
        var destinatario;
        var valor = formatValor($('#txtValor').val());
        var descricao = $('#txtDescricao').val();
        var op = $('.radio').find('input:checked').val();
        var dados = {};
        if (validaOp(valor, descricao, destinatario) === true) {
            $.each($('.dados_imp').find('input[name="hddDestinatario"]'), function(key, value) {
                destinatario = $(this).val();
                data_v = getVirtualDateAdmin(destinatario);
                dados[key] = {};
                dados[key].destinatario = destinatario;
                dados[key].data_v = data_v;
            });
        } else {
            $('.error').show().html('<span id="error">' + data + '</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        if (Object.size(dados) > 0) {
            var dataString = "valor=" + valor + "&descricao=" + descricao + "&op=" + op + "&destinatario=" + JSON.stringify(dados) + "&tipo=outras_op";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblCalendarioGeral').closest('.linha').hide();
                    showLoading();
                },
                success: function(dados) {
                    if (dados.sucesso === true) {
                        hideLoading();
                        $('#txtValor').val('');
                        $('#txtDescricao').val('');
                        var dataAgr = new Date();
                        $('#txtDataOp').val((('0' + dataAgr.getDate()).slice(-2)) + '-' + (('0' + (dataAgr.getMonth() + 1)).slice(-2)) + '-' + dataAgr.getFullYear() + ' ' + (('0' + dataAgr.getHours()).slice(-2)) + ':' + (('0' + dataAgr.getMinutes()).slice(-2)) + ':' + (('0' + dataAgr.getSeconds()).slice(-2)));
                        $('.dados_imp').empty().append('<textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq caixaTextoPeqEd"></textarea>' +
                            '<input id="hddDestinatario" name="hddDestinatario" type="hidden" value="">');
                        $('.radio').find('input:radio[value=0]').prop('checked', true);
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
        }
        event.handler = true;
    }
}

function carregaRegras() {
    var dados_taxas = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_grupo=0&id_empresa=0&id_regra=0&tipo=regras",
            async: false,
            success: function(dados) {
                json = dados.dados_in;
            }
        });
        return {
            getJson: function() {
                if (json)
                    return json;
            }
        };
    })();
    return dados_taxas;
}

function fBtnGuardarTaxas(event) {
    if (event.handler !== true) {
        var id_filt_grupo = $('#slcGrupoTaxas').val();
		var id_filt_empresa = $('#slcEmpresaTaxas').val();
        var id_filt_regra = $('#slcRegraTaxas').val();
		var dados = {};
        dados_taxas = carregaRegras();
        $('#tblDadosRegras tr').each(function(key, value) {
            if (key > 0) {
                var id_empresa_tab = $(this).find('#hddIdEmpresa').val();
                var id_regra_tab = $(this).find('#hddIdRegra').val();
                var id_banco_tab = $(this).find('#hddIdBanco').val();
                var simbolo_tab = $(this).find('#hddSimboloRegra').val();
                var valor_tab = formatValor($(this).find('#txtValorRegra').val());
                $.each(dados_taxas.getJson(), function(i, item) {
                    if (id_empresa_tab == item.id_empresa && id_regra_tab == item.id_regra && valor_tab != Math.round10(item.valor, -2)) {
                        dados[key] = {};
                        dados[key].id_empresa = id_empresa_tab;
                        dados[key].id_regra = id_regra_tab;
                        dados[key].valor = valor_tab;
                        dados[key].simbolo = simbolo_tab;
                        dados[key].id_banco = id_banco_tab;
                    }
                });
            }
        });
        if (Object.size(dados) > 0) {
            var dataString = "id_filt_grupo=" + id_filt_grupo + "&id_filt_empresa=" + id_filt_empresa + "&id_filt_regra=" + id_filt_regra + "&dados=" + JSON.stringify(dados) + "&tipo=g_regra_empresa";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblDadosRegras').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#tblDadosRegras').show();
                    if (dados.sucesso === true) {
                        emptyTable('#tblDadosRegras');
                        $.each(dados.dados_in, function () {
                            $('#tblDadosRegras').append('<tr>' +
                                '<td style="padding: 4px;"><input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + this.id_empresa + '">' + this.empresa + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdBanco" name="hddIdBanco" type="hidden" value="' + this.id_banco + '">' + this.banco + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' + this.nome_regra + '</td>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' +
                                '<div class="inputareaTable">' +
                                '<input id="txtValorRegra" name="txtValorRegra" type="text" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.valor, 2, ',', '.') + '">' + this.simbolo +
                                '<input id="hddSimboloRegra" name="hddSimboloRegra" type="hidden" value="' + this.simbolo + '">' +
                                '</div>' +
                                '</td>' +
                            '</tr>');
                        });
                        dynamicInput('tblDadosRegras');
                        floatMask();
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnOrdenar(event) {
    if (event.handler !== true) {
        ordenarFloats($('#tblAcoes'), ".tbody", $(this).data("sortKey"));
        event.handler = true;
    }
    return false;
}

function fBtnVoltarEmprest(event) {
    if (event.handler !== true) {
        $('#tblEmprestDetail').closest('.linha').hide();
        $('#tblEmprestGeral').show();
        event.handler = true;
    }
    return false;
}

function fBtnVoltarExtrato(event) {
    if (event.handler !== true) {
        $('#lblEmpresa').parent('div').children('span').remove();
		$('#tblExtratoDetail').closest('.linha').hide();
        $('#tblExtratoGeral').show();
        event.handler = true;
    }
    return false;
}

function fBtnVoltarLocacao(event) {
    if (event.handler !== true) {
        $('#tblLocDetail').closest('.linha').hide();
        $('#tblLocGeral').show();
        event.handler = true;
    }
    return false;
}

function fBtnVoltarTitulos(event) {
    if (event.handler !== true) {
        $('#tblTitulosDetail').closest('.linha').hide();
        $('#tblTitulosGeral').show();
        event.handler = true;
    }
    return false;
}

function fDivImgEmpresa(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).closest('td').children('#hddIdEmpresa').val();
		/* */
		$('#btnCorrigExtrato').parent('div').remove();
		$('#btnUpdExtrato').parent('div').remove();
		/* */
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&tipo=ver_extrato",
            beforeSend: function() {
                showLoading();
                $('#tblExtratoGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblExtratoDetail');
						/* */
                        $('#tblExtratoDetail').closest('div').append('<div class="width15 left" style="text-align: right;margin-top: 7px">' +
                            '<input id="hddIdEmpExt" type="hidden" value="' + id_empresa + '">' +
                            '<button id="btnUpdExtrato" name="btnUpdExtrato" class="botao" style="width: 60%">Corrigir</button>' + 
                        '</div>');
                        /* */
                        $.each(dados.dados_in, function() {
                            var op;
							//if (this.debito != "0") {
                            if (this.debito != 0) {
                                op = "-" + number_format(this.debito, 2, ',', '.');
                            //} else if (this.credito != "0") {
							} else if (this.credito != 0) {
                                op = "+" + number_format(this.credito, 2, ',', '.');
                            }
                            $('#tblExtratoDetail').append('<tr>' +
                                '<td style="padding: 3px;">' + this.data + '</td>' +
                                '<td style="padding: 3px;">' + this.tipo + '</td>' +
                                '<td style="padding: 3px;">' + this.descricao + '</td>' +
                                '<td style="padding: 3px;">' + op + '</td>' +
                                '<td style="padding: 3px;">' + number_format(this.saldo, 2, ',', '.') + '</td>' +
                                '</tr>');
                        });
                        $('#lblEmpresa').text(dados.dados_in[0].nome);
                    }
                    $('#tblExtratoDetail').closest('.linha').show();
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fDivImgEmprest(event) {
    if (event.handler !== true) {
        var id_emprest = $(this).closest('td').children('#hddIdEmprest').val();
        var id_empresa = $(this).closest('td').children('#hddIdEmpresa').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_emprest=" + id_emprest + "&id_empresa=" + id_empresa + "&tipo=ver_emprestimo",
            beforeSend: function() {
                showLoading();
                $('#tblEmprestGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblEmprestDetail');
                        $.each(dados.dados_in, function() {
                            $('#tblEmprestDetail').append('<tr>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' + this.pago + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.cap_p, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.juros, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.amort, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.prest, 2, ',', '.') + '</td>' +
                                '</tr>');
                        });
                        $('#lblEmpresa').text(dados.dados_in[0].nome);
                    }
                    $('#tblEmprestDetail').closest('.linha').show();
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fDivImgLeas(event) {
    if (event.handler !== true) {
        var id_leas = $(this).closest('td').children('#hddIdLeasing').val();
        var id_empresa = $(this).closest('td').children('#hddIdEmpresa').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_leas=" + id_leas + "&id_empresa=" + id_empresa + "&tipo=ver_leasing",
            beforeSend: function() {
                showLoading();
                $('#tblLocGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblLocDetail');
                        $.each(dados.dados_in, function() {
                            $('#tblLocDetail').append('<tr>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' + this.pago + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.cap_p, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.juros, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.amort, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.prest_s_iva, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.iva, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.prest_c_iva, 2, ',', '.') + '</td>' +
                                '</tr>');
                        });
                        $('#lblEmpresa').text(dados.dados_in[0].nome);
                        $('#tblLocDetail').closest('.linha').show();
                    }
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fDivImgTitulo(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).closest('td').children('#hddIdEmpresa').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&tipo=ver_titulo",
            beforeSend: function() {
                showLoading();
                $('#tblTitulosGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblTitulosDetail');
                        $.each(dados.dados_in, function() {
                            $('#tblTitulosDetail').append('<tr>' +
                                '<td style="padding: 4px;">' + this.nome + '</td>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.preco, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + this.quantidade + '</td>' +
                                '<td style="padding: 4px;">' + number_format(this.subtotal, 2, ',', '.') + '</td>' +
                                '<td style="padding: 4px;">' + this.tipo + '</td>' +
                                '</tr>');
                        });
                        $('#lblEmpresa').text(dados.dados_in[0].empresa);
                    }
                    $('#tblTitulosDetail').closest('.linha').show();
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fEditableTextChange() {
    if ($('#slcEmpresaTaxas').val() == "0" && $('#slcRegraTaxas').val() != "0") {
        var valor = $(this).val();
        $('#tblDadosRegras .dynamicInput').each(function() {
            $(this).val(valor);
        });
    }
}

function fSlcEmpresaTaxas(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).val();
        var id_grupo = $('#slcGrupoTaxas').val();
        var id_regra = $('#slcRegraTaxas').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&id_regra=" + id_regra + "&id_grupo=" + id_grupo + "&tipo=regras",
            beforeSend: function() {
                $('#tblDadosRegrasVazia').hide();
                $('#tblDadosRegras').hide();
                $('#hideSlcGrupoTaxas').hide();
                $('#hideSlcEmpresaTaxas').hide();
                $('#hideSlcRegraTaxas').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#tblDadosRegras').show();
                $('#hideSlcGrupoTaxas').show();
                $('#hideSlcEmpresaTaxas').show();
                $('#hideSlcRegraTaxas').show();
                if (dados.sucesso === true) {
                    emptyTable('#tblDadosRegras');
                    if (dados.vazio === false) {
                        $.each(dados.dados_in, function() {
                            $('#tblDadosRegras').append('<tr>' +
                                '<td style="padding: 4px;"><input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + this.id_empresa + '">' + this.empresa + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdBanco" name="hddIdBanco" type="hidden" value="' + this.id_banco + '">' + this.banco + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' + this.nome_regra + '</td>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' +
                                '<div class="inputareaTable">' +
                                '<input id="txtValorRegra" name="txtValorRegra" type="text" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.valor, 2, ',', '.') + '">' + this.simbolo +
                                '<input id="hddSimboloRegra" name="hddSimboloRegra" type="hidden" value="' + this.simbolo + '">'+
                                '</div>' +
                                '</td>' +
                                '</tr>');
                        });
                        dynamicInput('tblDadosRegras');
                        floatMask();
                    } else {
                        $('#tblDadosRegras').hide();
                        $('#tblDadosRegrasVazia').show();
                    }
                } else {
                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
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

function fSlcGrupoRanking(event) {
    if (event.handler !== true) {
        $('#btnOrdenar').hide();
        var id_grupo = $(this).val();
        var dataString = "id_grupo=" + id_grupo + "&tipo=comparar_esp";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#tblAcoes').hide();
                $('#tblAcoesVazia').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblAcoes');
                        $.each(dados.dados_in, function() {
                            $('#tblAcoes').append('<tr class="tbody">' +
                                '<td style="text-align: left; padding: 4px;">' + this.nome_empresa + '</td>' +
                                '<td id="tdLucroReal" style="padding: 4px;">' + number_format(this.lucro_real, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td id="tdLucroPotencial" style="padding: 4px;"></td>' +
                                '<td id="tdLucroPotencialReal" class="td sortMe" style="padding: 4px;"></td>' +
                                '</tr>');
                        });
                        $('#btnCalcular').show();
                        $('#tblAcoes').show();
                    } else {
                        emptyTable('#tblAcoes');
                        $('#tblAcoes').hide();
                        $('#btnCalcular').hide();
                        $('#tblAcoesVazia').show();
                    }
                } else {
                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
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

function fSlcRegraTaxas(event) {
    if (event.handler !== true) {
        var id_regra = $(this).val();
        var id_grupo = $('#slcGrupoTaxas').val();
        var id_empresa = $('#slcEmpresaTaxas').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&id_regra=" + id_regra + "&id_grupo=" + id_grupo + "&tipo=regras",
            beforeSend: function() {
                $('#tblDadosRegrasVazia').hide();
                $('#tblDadosRegras').hide();
                $('#hideSlcGrupoTaxas').hide();
                $('#hideSlcEmpresaTaxas').hide();
                $('#hideSlcRegraTaxas').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#tblDadosRegras').show();
                $('#hideSlcGrupoTaxas').show();
                $('#hideSlcEmpresaTaxas').show();
                $('#hideSlcRegraTaxas').show();
                if (dados.sucesso === true) {
                    emptyTable('#tblDadosRegras');
                    if (dados.vazio === false) {
                        $.each(dados.dados_in, function() {
                            $('#tblDadosRegras').append('<tr>' +
                                '<td style="padding: 4px;"><input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + this.id_empresa + '">' + this.empresa + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdBanco" name="hddIdBanco" type="hidden" value="' + this.id_banco + '">' + this.banco + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' + this.nome_regra + '</td>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' +
                                '<div class="inputareaTable">' +
                                '<input id="txtValorRegra" name="txtValorRegra" type="text" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.valor, 2, ',', '.') + '">' + this.simbolo +
                                '<input id="hddSimboloRegra" name="hddSimboloRegra" type="hidden" value="' + this.simbolo + '">'+
                                '</div>' +
                                '</td>' +
                                '</tr>');
                        });
                        dynamicInput('tblDadosRegras');
                        floatMask();
                    } else {
                        $('#tblDadosRegras').hide();
                        $('#tblDadosRegrasVazia').show();
                    }
                } else {
                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
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

/* Filtro pelo Grupo */
function fSlcGrupoTaxas(event) {
    if (event.handler !== true) {
        var id_grupo = $(this).val();
        var id_empresa = $('#slcEmpresaTaxas').val();
        var id_regra = $('#slcRegraTaxas').val();
        if (id_grupo != 0) id_empresa = 0;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&id_regra=" + id_regra + "&id_grupo=" + id_grupo + "&tipo=regras",
            beforeSend: function() {
                $('#tblDadosRegrasVazia').hide();
                $('#tblDadosRegras').hide();
                $('#hideSlcGrupoTaxas').hide();
                $('#hideSlcEmpresaTaxas').hide();
                $('#hideSlcRegraTaxas').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#tblDadosRegras').show();
                $('#hideSlcGrupoTaxas').show();
                $('#hideSlcEmpresaTaxas').show();
                $('#hideSlcRegraTaxas').show();
                if (dados.sucesso === true) {
                    emptyTable('#tblDadosRegras');
                    if (dados.vazio === false) {
                        $.each(dados.dados_in, function() {
                            $('#tblDadosRegras').append('<tr>' +
                                '<td style="padding: 4px;"><input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + this.id_empresa + '">' + this.empresa + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdBanco" name="hddIdBanco" type="hidden" value="' + this.id_banco + '">' + this.banco + '</td>' +
                                '<td style="padding: 4px;"><input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' + this.nome_regra + '</td>' +
                                '<td style="padding: 4px;">' + this.data + '</td>' +
                                '<td style="padding: 4px;">' +
                                '<div class="inputareaTable">' +
                                '<input id="txtValorRegra" name="txtValorRegra" type="text" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.valor, 2, ',', '.') + '">' + this.simbolo +
                                '<input id="hddSimboloRegra" name="hddSimboloRegra" type="hidden" value="' + this.simbolo + '">'+
                                '</div>' +
                                '</td>' +
                                '</tr>');
                        });
                        dynamicInput('tblDadosRegras');
                        floatMask();
                    } else {
                        $('#tblDadosRegras').hide();
                        $('#tblDadosRegrasVazia').show();
                        $('#slcEmpresaTaxas').empty();
                        $('#slcEmpresaTaxas').val('');
                    }
					$("#slcEmpresaTaxas").empty();
                    $('#slcEmpresaTaxas').append('<option selected="selected" value="0"></option>');
                    $.each(dados.empresa_grupo, function () {
                        $('#slcEmpresaTaxas').append($('<option></option>').val(this.id_empresa).text(this.empresa));
                    });
                    $('#slcEmpresaTaxas').trigger("chosen:updated");
                } else {
                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
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

function fTxtDestinatario(event) {
    if (event.handler !== true) {
        $('.dados_imp').empty().append('<textarea id="txtDestinatario" name="txtDestinatario" rows="5" class="caixaTextoPeq caixaTextoPeqEd"></textarea>' +
            '<input id="hddDestinatario" name="hddDestinatario" type="hidden" value="">');
        var left = (screen.width / 2) - (550 / 2);
        var top = (screen.height / 2) - (360 / 2);
        childWin = window.open('pag_destinatarios.php?tipo=admin', 'Destinatarios', 'resizable=no,width=550,height=360,scrollbars=yes,top=' + top + ', left=' + left);
        if (window.focus) {
            childWin.focus();
        }
        event.handler = true;
    }
}

/* */
function fBtnCorrigirExtrato(event) {
    if (event.handler !== true) {
        var id_empresa = $('#hddIdEmpExt').val();
        $('#btnUpdExtrato').parent('div').remove();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&tipo=ver_extrato",
            beforeSend: function () {
                showLoading();
                $('#tblExtratoGeral').hide();
            },
            success: function (dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblExtratoDetail');
                        $('#tblExtratoDetail').closest('div').append('<div class="width15 left" style="text-align: right;margin-top: 7px">' +
                            '<button id="btnCorrigExtrato" name="btnCorrigExtrato" class="botao" style="width: 60%">Atualizar</button>' + 
                        '</div>');
                        $.each(dados.dados_in, function () {
                            var op;
                            //if (this.debito != "0") {
                            if (this.debito != 0) {
                                op = "-" + number_format(this.debito, 2, ',', '.');
                                //} else if (this.credito != "0") {
                            } else if (this.credito != 0) {
                                op = "+" + number_format(this.credito, 2, ',', '.');
                            }
                            $('#tblExtratoDetail').append('<tr>' +
                                '<td style="padding: 3px;">' + this.data + '</td>' +
                                '<td style="padding: 3px;">' + this.tipo + '</td>' +
                                '<td style="padding: 3px;">' + this.descricao + '</td>' +
                                '<td style="padding: 3px;">' + op + '</td>' +
                                '<td style="padding: 3px;">' + number_format(this.saldo, 2, ',', '.') + '</td>' +
                                '<td style="padding: 3px; background-color: transparent">' +
                                    '<input name="idEmpExt" type="radio" value="' + this.id_mov + '">' +
                                '</td>' +
								'</tr>');
                        });
                        $('#lblEmpresa').text(dados.dados_in[0].nome);
                        $('#lblEmpresa').parent('div').append(
                            '<span><label style="font-size: 10pt; color: #e52727">&nbsp;&nbsp;(Escolha o ÚLTIMO MOVIMENTO CORRECTO)</label></span>'
                        );
                    }
                    $('#tblExtratoDetail').closest('.linha').show();
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fbtnCorrigExtrato(event) {
    if (event.handler !== true) {
        var id_mov = $('#tblExtratoDetail tr').find('input[name="idEmpExt"]').filter(':checked').val();
        if (confirm('Tem a certeza que pretende alterar o extrato da empresa?')) {
            window.open("././impressao/admin_movimentos_backup_print.php?id_mov=" + id_mov);
            setTimeout(function(){
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: "id_mov=" + id_mov + "&tipo=alterar_extrato",
                    success: function (dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.changes === true) {
                                $('#btnVoltarExtrato').click();
                                $('#divImgEmpresa_' + dados.id_empresa).parent('td').prev('td').text(dados.saldo);                                                                                                                       
                            } else {
                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                $("body").animate({
                                    scrollTop: 0
                                });
                            }
                        }
                    }
                });
            }, 1000);
        }
        event.handler = true;
    }
    return false;
}

//apagar Alertas (Inicio)--------------
function fDivImgRemAlerta(event) {
    if (event.handler !== true) {
        if (confirm('Deseja mesmo apagar este alerta?')) {
            var id_alerta = $(this).closest('td').children('#hddIdAlerta').val();
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_alerta=" + id_alerta + "&tipo=apagar_alerta",
                beforeSend: function() {
                    $('#tblAlertasGeral').closest('.linha').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            emptyTable('#tblAlertasGeral');
                            $.each(dados.dados_in, function() {
                                $('#tblAlertasGeral').append('<tr>' +
                                    '<td>' + this.id_acao_trans + '</td>' +
                                    '<td>' + this.login + '</td>' +
                                    '<td>' + this.nome + '</td>' +
                                    '<td>' + this.simbolo + '</td>' +
                                    '<td>' + this.preco_compra + '</td>' +
                                    '<td>' + this.quantidade + '</td>' +
                                    '<td>' + this.preco_atual + '</td>' +
                                    '<td>' + this.date_reg + '</td>' +
                                    '<td class="width5 iconwrapper">' +
                                    '<input id="hddIdAlerta" name="hddIdAlerta" type="hidden" value="' + this.id + '">' +
                                    '<div id="fDivImgRemAlerta_' + this.id + '" name="fDivImgRemAlerta" class="novolabelicon icon-garbage rem_linha"></div>' +
                                    '</td>' +
                                    '</tr>');
                            });
                            $('#tblAlertasGeral').closest('.linha').show();
                            //$('#frmDetalhesCalTask').closest('.linha').hide();
                            // $('#btnVoltarCal').click();
                        } else {
                            $('#tblAlertasGeral').append('<tr>' +
                                '<td>Não existem tarefas definidas</td>' +
                                '</tr>');
                        }
                    }
                }
            });
        }
        event.handler = true;
    }
    return false;
}
//apagar Alertas (FIM)--------------
/* */