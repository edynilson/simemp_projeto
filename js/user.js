/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:10
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-28 17:43:57
*/

$(document).ready(function() {
    heartbeat();
    // setInterval(heartbeat, 60000);
	setInterval(heartbeat, 300000);
    var BrowserDetect = fDetectBrowser();
    BrowserDetect.init();
    updateRelogio();
    setInterval(function(){updateRelogio();}, 1000);
    hideError();
	//-- Desenha dashboards de página inicial (ADDED 20180222)
    buildDashBoards();
    $('#divDadosEmpresa').hide();
	$('#divDateTasks').hide();
    $('#var_content').hide();
    menu();
    $(document).on('mouseover', '#conteudo', function(event) {
        if (event.handler !== true) {
            $('#menu').multilevelpushmenu('collapse');
            event.handler = true;
        }
    });

    $(document).on('mousedown', fEsconderErro);

    $('#calendario_inicial').fullCalendar({
        header: {
            left: '',
            center: 'title',
            right: ''
        },
        height: get_calendar_height("calend_in"),
        defaultView: 'month',
        eventSources: [{
                url: 'functions/funcoes_geral.php',
                type: 'POST',
                data: {
                    tipo: "eventos"
                }
            }],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        dayNamesShort: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
        theme: true
    });

    $(window).resize(function() {
        $('#calendario_inicial').fullCalendar('option', 'height', get_calendar_height("calend_in"));
    });

    $(document).on('click', '#lblVerMais', function(event) {
        if (event.handler !== true) {
            $('#calend_in').hide();
            $('#divDadosEmpresa').show();
            event.handler = true;
        }
    });

	//-- Sinaliza datas com tarefas a serem cumpridas
    fillCalendTasks();
    
	//-- Falta CRIAR TABELAS na BD
    //-- Evento para mostrar MSG info clicar em Calendário
    $(document).on('click', '.fc-day', function (event) {
        if (event.handler !== true) {
            var data_r = $(this).attr('data-date');
             $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                data: "data_v=" + data_r + "&tipo=date_task",
                dataType: "json",
                success: function (dados) {
                    if (dados.sucesso == true && dados.vazio == false) {
                        $('#calend_in').hide();
                        $('#divDadosEmpresa').hide();
                        $('#divDateTasks').show();
                        
                        $('#txtDataRealTask').val(data_r.split('-')[2] + '-' + data_r.split('-')[1] + '-' + data_r.split('-')[0]);
                        $('#txtDataVirtTask').val(dados.data_virtual.split('-')[2] + '-' + dados.data_virtual.split('-')[1] + '-' + dados.data_virtual.split('-')[0]);
                        var text = "";
                        $.each(dados.dados_in, function(i, item) {
                            // alert('*' + dados.dados_in[i].descricao);
                            text += '*' + dados.dados_in[i].descricao + '; \n';
                            $('#txtTasks').text(text);
                        });
                    }
                }
            }); 
            event.handler = true;
        }
        return false;
    });
    //

    $(document).on('click', '#btnCalend', function (event) {
        if (event.handler !== true) {
            $('#divDadosEmpresa').hide();
            $('#divDateTasks').hide();
            $('#calend_in').show();
            event.handler = true;
        }
    });

    $('#menu ul li a').on('click', function() {
        if ($(this).attr('href') != '#') {
            var ext = $(this).attr('href').split('.');
            if (ext[1] == "php") {
                window.open($(this).attr('href'), '_self');
            } else if ($(this).attr('href') == 'email') {
                $(document).on('click', '.label_chk', fChkClick);
                carregaMail(BrowserDetect);
            } else {
                $('#pag_raw').hide();
                $('#var_content').show();
                $('#var_content').load('conteudo_user.php #' + $(this).attr('href'), function() {
                    hideLoading();
                    $('div[id^="divEnt"]').hide();
                    $('#btnRemDecRet').hide();
                    $('#divEntDecRet').show();
                    $('#divNIPC').hide();
                    $('#tblEntDecRetDetalhes').closest('.linha').hide();
                    $('#divFaturaDetail').hide();
                    $('#tblVDecRet').hide();
                    $('#tblVDecRetVazia').hide();
                    if ($('#tblFaturas tr').length > 1) {
                        $('#tblFaturasVazia').hide();
                        $('#tblFaturas').show();
                    } else {
                        $('#tblFaturas').hide();
                        $('#tblFaturasVazia').show();
                    }
                    if ($('#tblEncomendas tr').length > 1) {
                        $('#tblEncVazia').hide();
                        $('#slcFiltrarEnc').closest('.linha').show();
                        $('#tblEncomendas').show();
                    } else {
                        $('#tblEncomendas').hide();
                        $('#slcFiltrarEnc').closest('.linha').hide();
                        $('#tblEncVazia').show();
                    }
                    if ($('#tblVDiversos tr').length > 1) {
                        $('#tblVDiversosVazia').hide();
                        $('#tblVDiversos').show();
                    } else {
                        $('#tblVDiversos').hide();
                        $('#tblVDiversosVazia').show();
                    }
                    
                    //-- Adiantamento de faturas
                    $('#adiantamentoFat').hide();
                    //
                    
                    //-- Notas de Crédito
                    if ($('#tblEncomendasEfet tr').length > 1) {
                        //$('#tblNotaCreditoVazia').hide();
                        $('#slcFiltrarNotaCredito').closest('.linha').show();
                        $('#tblEncomendasEfet').show();
                    } else {
                        $('#tblEncomendasEfet').hide();
                        $('#slcFiltrarNotaCredito').closest('.linha').hide();
                        // $('#tblNotaCreditoVazia').show();
                    }
                    $('#divFatDetail').hide();
                    $('#tblFatVazia').hide();
                    
                    if ($('#tblNotasCredito tr').length > 1) {
                        $('#tblNotaCreditoVazia').hide();
                    } else {
                        $('#tblNotaCreditoVazia').show();
                        $('#tblNotasCredito').hide();
                    }
                    //--
                    
                    //-- Desconto de fornecedores
                    if ($('#tblDescFornec tr').length > 1) {
                        $('#tblDescFornecVazia').hide();
                    } else {
                        $('#tblDescFornecVazia').show();
                        $('#tblDescFornec').hide();
                    }
                    
					//-- Adiantamentos/Recebimentos
                    $('#limiteAdiantamento').hide();
                    $('#clienteAdiantamento').hide();
                    if ($('#tblAdiantReceb tr').length > 1) {
                        $('#tblAdiantRecebVazia').hide();
                    } else {
                        $('#tblAdiantReceb').hide();
                    }
                    if ($('#tblAdiantEfet tr').length > 1) {
                        $('#tblAdiantEfetVazia').hide();
                    } else {
                        $('#tblAdiantEfet').hide();
                    }
					
					/* Datepicker (plugin escolha de datas)
                    $('.campoData').datetimepicker();
                    $.datetimepicker.setLocale('pt');
                    $('.campoData').datetimepicker({
                        // lang: 'pt',
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
                        timepicker: false,
                        format: 'd-m-Y',
                        closeOnDateSelect: true,
                        yearStart: 2014,
                        yearEnd: 2020
                        /*
                        onClose: function() {
                            if ($(this.val() === "")) {
                                $(this.val(""));
                            }
                        }
                        //
                    });
                    /* */
					
                    var data_v = getVirtualDate();
                    var temp = data_v.toString().split(' ');
                    data_v = temp[0];
                    temp = data_v.split('/');
                    var mes = temp[0] - 1;
                    var ano = temp[2];
                    $('select[id^=slcAnoEnt]').each(function() {
                        $(this).append('<option value="' + (ano - 2) + '">' + (ano - 2) + '</option>' +
                                '<option value="' + (ano - 1) + '">' + (ano - 1) + '</option>' +
                                '<option value="' + ano + '" selected="selected">' + ano + '</option>' +
                                '<option value="' + (ano * 1 + 1) + '">' + (ano * 1 + 1) + '</option>');
                    });
                    $('select[id^=slcMesEnt]').each(function() {
                        $('option', this).eq(mes).prop('selected', true);
                    });
                    $('#slcTipoEntrega').val(5);
                    var dataStore = (function() {
                        var json;
                        $.ajax({
                            type: "POST",
                            url: "functions/funcoes_users.php",
                            data: "tipo=arr_rubrica",
                            dataType: "json",
                            success: function(dados) {
                                json = dados;
                            }
                        });
                        return {getJson: function()
                            {
                                if (json)
                                    return json;
                            }};
                    })();

                    $(document).on('change', '#slcFiltrarEnc', function(event) {
                        if (event.handler !== true) {
                            var id_fornecedor = $(this).val();
                            $.ajax({
                                type: "POST",
                                url: "functions/funcoes_users.php",
                                data: "id_fornecedor=" + id_fornecedor + "&tipo=filtrar_enc",
                                dataType: "json",
                                beforeSend: function() {
                                    $('#tblEncomendas').hide();
                                    showLoading();
                                },
                                success: function(dados) {
                                    hideLoading();
                                    emptyTable('#tblEncomendas');
                                    if (dados.sucesso === true) {
                                        $.each(dados.dados_in, function(i, item) {
                                            $('#tblEncomendas').append('<tr>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].ref + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].pago + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].nome_abrev + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].nome_pais + '</td>' +
                                                    '<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                    '<td style="background-color: transparent; padding: 0; cursor: pointer;">' +
                                                    '<a href="./impressao/fatura.php?id=' + dados.dados_in[i].id + '&p=PT" target="_blank">' +
                                                    '<img width="33" height="33" src="./images/adobe_logo.png">' +
                                                    '</a>' +
                                                    '</td>' +
                                                    '</tr>');
                                        });
                                        $('#tblEncomendas').show();
                                    } else {
                                        $('.erro').show().html('<span id="error">' + dados.mensagem + '</span>');
                                        $("body").animate({scrollTop: 0});
                                    }
                                }
                            });
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', 'button[id^=btnAnexarEnt]', function(event) {
                        if (event.handler !== true) {
                            var nome = $(this).attr('id').split('btnAnexarEnt');
                            $('#fileAnexarEnt' + nome[1]).click();
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('change', 'input[id^=fileAnexarEnt]', function(event) {
                        if (event.handler !== true) {
                            var nome = $(this).attr('id').split('fileAnexarEnt');
                            var nome_ficheiro = $(this).val().replace(/C:\\fakepath\\/i, '');
                            $('#txtPathEnt' + nome[1]).val(nome_ficheiro);
                            event.handler = true;
                        }
                    });

                    $(document).on('click', 'button[id^=btnEnviarEnt]', function(event) {
                        if (event.handler !== true) {
                            var formObj = $(this);
                            if (window.FormData !== undefined) {
                                var nome = $(this).attr('id').split('btnEnviarEnt');
                                var valor = formatValor($('input[id^=txtValorEnt' + nome[1] + ']').val());
                                var ficheiro = $('input[name="fileAnexarEnt' + nome[1] + '"]').prop("files")[0];
                                var fich = $('#fileAnexarEnt' + nome[1]).val();
                                if (validaEntrega(valor, fich) === true) {
                                    var fd = new FormData();
                                    fd.append('tipo_entrega', $('#slcTipoEntrega option:selected').val());
                                    fd.append('nome_tipo', $('#slcTipoEntrega option:selected').text());
                                    var prazo;
                                    if ($('#chkFPrazoEnt' + nome[1]).prop('checked') === true) {
                                        prazo = 'S';
                                    } else {
                                        prazo = 'N';
                                    }
                                    fd.append('f_prazo', prazo);
                                    fd.append('data_virtual', getVirtualDate());
                                    fd.append('valor', valor);
                                    fd.append('fileAnexar', ficheiro);
                                    fd.append('mes', $('#slcMesEnt' + nome[1]).val());
                                    fd.append('ano', $('#slcAnoEnt' + nome[1]).val());
                                    fd.append('tipo', "up_entrega");
                                    $.ajax({
                                        url: "functions/funcoes_users.php",
                                        type: 'POST',
                                        data: fd,
                                        mimeType: "multipart/form-data",
                                        contentType: false,
                                        cache: false,
                                        processData: false,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                var control = $('#fileAnexarEnt' + nome[1]);
                                                $('#slcMesEnt' + nome[1] + ' option').eq(mes).prop('selected', true);
                                                $('#slcAnoEnt' + nome[1] + ' option').eq(2).prop('selected', true);
                                                $('#chkFPrazoEnt' + nome[1]).prop('checked', false);
                                                $('#txtValorEnt' + nome[1]).val('');
                                                $('#txtPathEnt' + nome[1]).val('');
                                                control.replaceWith(control = control.clone(true));
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({scrollTop: 0});
                                            }
                                        }
                                    });
                                } else {
                                    $('.error').show().html('<span id="error">' + data + '</span>');
                                    $("body").animate({scrollTop: 0});
                                }
                            } else {
                                var iframeId = 'unique' + (new Date().getTime());
                                var iframe = $('<iframe src="javascript:false;" name="' + iframeId + '" />');
                                iframe.hide();
                                formObj.attr('target', iframeId);
                                iframe.appendTo('body');
                                iframe.load(function(e) {
                                    var doc = getDoc(iframe[0]);
                                    var docRoot = doc.body ? doc.body : doc.documentElement;
                                    var data = docRoot.innerHTML;
                                    $('.error').html('<pre><code>' + data + '</code></pre>');
                                });
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#btnLimparEntOutrasDec', function(event) {
                        if (event.handler !== true) {
                            var control = $('#fileAnexar');
                            $('#slcMesEntOutrasDec option').eq(mes).prop('selected', true);
                            $('#slcAnoEntOutrasDec option').eq(2).prop('selected', true);
                            $('#slcTipoEntOutrasDec option').eq(0).prop('selected', true);
                            $('#chkFPrazoEntOutrasDec').prop('checked', false);
                            $('#txtValorEntOutrasDec').val('');
                            $('#txtPathEntOutrasDec').val('');
                            control.replaceWith(control = control.clone(true));
                            event.handler = true;
                        }
                    });
                    $(document).on('click', '.btnRadio', function(event) {
                        if (event.handler !== true) {
                            var index1 = $(this).index() + 1;
                            var index = (index1 / 2) - 1;
                            var dataString;
                            if ($(this).closest('.radio').find('input').eq(index).prop('checked') !== true) {
                                $(this).closest('.radio').find('input').eq(index).prop('checked', true);
                            }
                            if ($(this).attr("for") == "radVOutros") {
                                $('#tblVDecRet').data('value', '0');
                                $('#tblVDiversos').data('value', '1');
                                $('#tblVDecRet').hide();
                                $('#tblVDecRetVazia').hide();
                                dataString = "tipo=ver_entregas";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_users.php",
                                    data: dataString,
                                    dataType: "json",
                                    beforeSend: function() {
                                        $('#tblVDiversos').hide();
                                        $('#tblVDiversosVazia').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        if (dados.sucesso === true) {
                                            hideLoading();
                                            if (dados.vazio === false) {
                                                emptyTable('#tblVDiversos');
                                                $.each(dados.dados_in, function(i, item) {
                                                    var mes = conv_mes(parseInt(dados.dados_in[i].mes));
													var col_guia = "";
                                                    if(dados.dados_in[i].designacao == 'Fundo de Compensação do Trabalho')
                                                        col_guia = '<a href="./impressao/fct.php?id=' + dados.dados_in[i].id + '" target="_blank"><img width="28" height="28" src="images/adobe_logo.png"></a>';
                                                    
													$('#tblVDiversos').append('<tr>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].valor, 2, ",", ".") + ' ' + dados.moeda + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].prazo + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].pago + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].designacao + '</td>' +
                                                            '<td style="padding: 4px;">' + mes + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].ano + '</td>' +
                                                            '<td style="background-color: transparent; padding: 0; cursor: pointer;">' +
                                                            '<a href="' + dados.dados_in[i].ficheiro + '" target="_blank">' +
                                                            '<img width="33" height="33" src="images/adobe_logo.png">' +
                                                            '</a>' +
                                                            '</td>' +
															'<td style="background-color: transparent; padding: 0; cursor: pointer;">' + col_guia + '</td>' +
                                                            '</tr>');
                                                });
                                                $('#tblVDiversos').show();
                                            } else {
                                                $('#tblVDiversos').hide();
                                                $('#tblVDiversosVazia').show();
                                            }
                                        } else {
                                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                            $("body").animate({scrollTop: 0});
                                        }
                                    }
                                });
                            } else if ($(this).attr("for") == "radVDecRet") {
                                $('#tblVDiversos').data('value', '0');
                                $('#tblVDecRet').data('value', '1');
                                $('#tblVDiversos').hide();
                                $('#tblVDiversosVazia').hide();
                                dataString = "tipo=ver_dec_ret";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_users.php",
                                    dataType: "json",
                                    data: dataString,
                                    beforeSend: function() {
                                        $('#tblVDecRet').hide();
                                        $('#tblVDecRetVazia').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        if (dados.sucesso === true) {
                                            hideLoading();
                                            if (dados.vazio === false) {
                                                emptyTable('#tblVDecRet');
                                                $.each(dados.dados_in, function(i, item) {
                                                    $('#tblVDecRet').append('<tr>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].n_res + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].pago + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                            '<td style="background-color: #77a4d7; padding: 4px; cursor: pointer;">' +
                                                            '<input id="hddIdDecRet" name="hddIdDecRet" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '<div id="btnDecRet_' + dados.dados_in[i].id + '" name="btnDecRet" class="labelicon icon-info"></div>' +
                                                            '</td>' +
                                                            '</tr>');
                                                });
                                                $('#tblVDecRet').show();
                                            } else {
                                                $('#tblVDecRet').hide();
                                                $('#tblVDecRetVazia').show();
                                            }
                                        } else {
                                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                            $("body").animate({scrollTop: 0});
                                        }
                                    }
                                });
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', 'div[name^="btnDecRet"]', function(event) {
                        if (event.handler !== true) {
                            var id_dec_ret = $(this).closest('td').children('input').val();
                            var dataString = "id_dec_ret=" + id_dec_ret + "&tipo=dec_ret_detalhes";
                            $.ajax({
                                type: "POST",
                                url: "functions/funcoes_users.php",
                                dataType: "json",
                                data: dataString,
                                beforeSend: function() {
                                    $('#tblVDecRet').hide();
                                    $('#radVOthersGroup').hide();
                                    showLoading();
                                },
                                success: function(dados) {
                                    if (dados.sucesso === true) {
                                        hideLoading();
                                        emptyTable('#tblEntDecRetDetalhes');
                                        $.each(dados.dados_in, function(i, item) {
                                            $('#tblEntDecRetDetalhes').append('<tr>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].rubrica + ' <a id="aVerCodigos" class="body-link" title="Consultar Códigos">[...]</a></td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].zona + '</td>' +
                                                    '<td style="padding: 4px;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                    '</tr>');
                                        });
                                        $('#frmVDecRet').find('input[name="txtDataLimDecRet"]').val(dados.data_completa);
                                        $('#frmVDecRet').find('input[name="txtResidentes"]').val(dados.res);
                                        $('#frmVDecRet').find('input[name="txtPago"]').val(dados.pago);
                                        $('#frmVDecRet').find('input[name="txtTotal"]').val(number_format(dados.total, 2, ',', '.'));
                                        $('#frmVDecRet').find('input[name="txtMoeda"]').val(dados.moeda);
                                        $('#frmVDecRet').find('img[name="txtDocDecRet"]').closest('a').attr('href', './impressao/dec_retencoes.php?id_dec_ret=' + id_dec_ret);
                                        $('#tblEntDecRetDetalhes').closest('.linha').show();
                                    }
                                }
                            });
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#btnVoltarDecRet', function(event) {
                        if (event.handler !== true) {
                            $('#tblEntDecRetDetalhes').closest('.linha').hide();
                            $('#radVOthersGroup').show();
                            $('#tblVDecRet').show();
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('change', '.select', function(event) {
                        if (event.handler !== true) {
                            var mes;
                            var ano;
                            if ($(this).attr("name") == "slcZona") {
                                $(this).closest('.styled-select').css("background-color", "#fff");
                            } else if ($(this).attr("name") == "slcRubrica") {
                                $(this).closest('.styled-select').css("background-color", "#fff");
                                if ($(this).val() != "0") {
                                    var numero = $(this).data("id");
                                    var dataString = "cod=" + $(this).val() + "&tipo=get_rubrica";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_users.php",
                                        dataType: "json",
                                        data: dataString,
                                        beforeSend: function() {
                                            $('#divEntDecRet').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            if (dados.sucesso === true) {
                                                $('#txtDescRetencao_' + numero).val(dados.desig);
                                                $('#divEntDecRet').show();
                                            }
                                        }
                                    });
                                } else {
                                    $(this).closest('.tr').children('.td').eq(3).find('input[name="txtDescRetencao"]').val('');
                                }
                            } else if ($(this).attr("name") == "slcMesRetencao") {
                                $('select[name="slcMesRetencao"]').closest('.inputarea_col1').css("background-color", "#eaedf1");
                                if ($('#slcAnoRetencao').val() != "0" && $(this).val() != "0") {
                                    mes = parseInt($("option:selected", this).val()) + 1;
                                    if (mes < 10) {
                                        mes = "0" + mes;
                                    }
                                    ano = $("#slcAnoRetencao option:selected").text();
                                    if (mes > 12) {
                                        mes = "01";
                                        ano = parseInt(ano) + 1;
                                    }
                                    $('#txtDataPagamento').val("20-" + mes + "-" + ano);
                                } else {
                                    $('#txtDataPagamento').val('');
                                }
                            } else if ($(this).attr("name") == "slcAnoRetencao") {
                                $('select[name="slcAnoRetencao"]').closest('.inputarea_col1').css("background-color", "#eaedf1");
                                if ($('#slcMesRetencao').val() != "0" && $(this).val() != "0") {
                                    mes = parseInt($('#slcMesRetencao').val()) + 1;
                                    if (mes < 10) {
                                        mes = "0" + mes;
                                    }
                                    ano = $("option:selected", this).text();
                                    if (mes > 12) {
                                        mes = "01";
                                        ano = parseInt(ano) + 1;
                                    }
                                    $('#txtDataPagamento').val("20-" + mes + "-" + ano);
                                } else {
                                    $('#txtDataPagamento').val('');
                                }
                            } else if ($(this).attr("name") == "slcTipoEntrega") {
                                resizeSelect("slcTipoEntrega");
                                if ($(this).val() == 1) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').show();
                                    $('#divEntIVA').show();
                                } else if ($(this).val() == 5) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntDecRet').show();
                                } else if ($(this).val() == 6) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntIES').show();
                                } else if ($(this).val() == 7) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntModelo10').show();
                                } else if ($(this).val() == 8) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntIRC').show();
                                } else if ($(this).val() == 9) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntRecap').show();
                                } else if ($(this).val() == 10) {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntModelo25').show();
                                } else {
                                    $('div[id^="divEnt"]').hide();
                                    $('#divNIPC').hide();
                                    $('#divEntOutrasDec').show();
                                }
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#chkAllDecRet', function(event) {
                        if (event.handler !== true) {
                            if ($(this).prop('checked') === true) {
                                $('#tblDadosDecRet').find('input[type=checkbox].chk').prop('checked', false);
                                $('#btnRemDecRet').hide();
                            } else {
                                $('#tblDadosDecRet').find('input[type=checkbox].chk').prop('checked', true);
                            }
                            event.handler = true;
                        }
                    });
                    $(document).on('click', '#chkResidentes', function(event) {
                        if (event.handler !== true) {
                            if ($(this).prop('checked') === true) {
                                $(this).prop('checked', false);
                            } else {
                                $(this).prop('checked', true);
                            }
                            event.handler = true;
                        }
                    });
                    $(document).on('click', '.label_chk', function(event) {
                        if (event.handler !== true) {
                            if ($(this).attr("for") == "chkFatPaga") {
                                return false;
                            } else if ($(this).closest('#divDecRet').data('value') == 1) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $(this).closest('#tblDadosDecRet').find('#chkAllDecRet').prop('checked', false);
                                    $(this).closest('.checkbox').find('input').prop('checked', false);
                                } else if ($(this).closest('.checkbox').find('input').prop('checked') === false) {
                                    if (($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                                        $(this).closest('#tblDadosDecRet').find('#chkAllDecRet').prop('checked', true);
                                    }
                                    $(this).closest('.checkbox').find('input').prop('checked', true);
                                    if ($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').length > 1) {
                                        $('#btnRemDecRet').show();
                                    }
                                }
                                if ($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                                    $('#btnRemDecRet').hide();
                                }
                            } else if ($(this).closest('#divEntDecRet').data('value') == 1) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $(this).closest('#tblDadosDecRet').find('#chkAllDecRet').prop('checked', false);
                                    $(this).closest('.checkbox').find('input').prop('checked', false);
                                } else if ($(this).closest('.checkbox').find('input').prop('checked') === false) {
                                    if (($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                                        $(this).closest('#tblDadosDecRet').find('#chkAllDecRet').prop('checked', true);
                                    }
                                    $(this).closest('.checkbox').find('input').prop('checked', true);
                                    if ($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').length > 1) {
                                        $('#btnRemDecRet').show();
                                    }
                                }
                                if ($(this).closest('#tblDadosDecRet').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                                    $('#btnRemDecRet').hide();
                                }
                            }
                            event.handler = true;
                        }
                    });
                    $(document).on('click', '#aAddLinhaRetencao', function(event) {
                        if (event.handler !== true) {
                            var id = parseInt($('#tblDadosDecRet').find('tr:last-child').prev().children('td').eq(0).text());
                            var opcoes = '<option value="0" style="background-color: #fff;"></option>';
                            $.each(dataStore.getJson().dados_in, function(key, value) {
                                opcoes += '<option value="' + dataStore.getJson().dados_in[key].id + '" style="background-color: #fff;">' + dataStore.getJson().dados_in[key].rubrica + '</option>';
                            });
                            $('#tblDadosDecRet').find('tr:last-child').before('<tr>' +
                                    '<td style="padding: 4px; font-size: 85%;">' + (id + 1) + '</td>' +
                                    '<td style="padding: 4px;">' +
                                    '<div class="inputarea_col1" style="width: 100%; margin-right: 0; height: 20px;">' +
                                    '<div class="styled-select" style="background-size: 20px 20px; background-color: #fff;">' +
                                    '<select id="slcZona_' + (id + 1) + '" name="slcZona" class="select" style="font-size: 85%;">' +
                                    '<option value="0" style="background-color: #fff;"></option>' +
                                    '<option value="1" style="background-color: #fff;">Continente</option>' +
                                    '<option value="2" style="background-color: #fff;">Açores</option>' +
                                    '<option value="3" style="background-color: #fff;">Madeira</option>' +
                                    '</select>' +
                                    '</div>' +
                                    '</div>' +
                                    '</td>' +
                                    '<td style="padding: 4px;">' +
                                    '<div class="inputarea_col1" style="width: 70%; margin-right: 0; height: 20px;">' +
                                    '<div class="styled-select" style="background-size: 20px 20px; background-color: #fff;">' +
                                    '<select id="slcRubrica_' + (id + 1) + '" name="slcRubrica" class="select" style="font-size: 85%;" data-id="' + (id + 1) + '">' +
                                    opcoes +
                                    '</select>' +
                                    '</div>' +
                                    '</div>' +
                                    '<a id="aVerCodigos" class="body-link" title="Consultar Códigos">[...]</a>' +
                                    '</td>' +
                                    '<td style="padding: 4px; font-size: 85%;">' +
                                    '<input id="txtDescRetencao_' + (id + 1) + '" name="txtDescRetencao" type="text" size="60" value="" readonly="readonly">' +
                                    '</td>' +
                                    '<td style="padding: 4px; font-size: 85%;">' +
                                    '<div class="moneyarea_col1" style="height: 22px; width: 100%; background-color: #fff;">' +
                                    '<input id="txtImportancia_' + (id + 1) + '" name="txtImportancia" type="text" class="dinheiro">' +
                                    '<div class="mnyLabel" style="width: 15px;">' +
                                    '<input name="txtMoeda" type="text" readonly="readonly" value="' + dataStore.getJson().moeda + '">' +
                                    '</div>' +
                                    '</div>' +
                                    '</td>' +
                                    '<td style="background-color: transparent; padding: 4px;">' +
                                    '<div class="checkbox">' +
                                    '<input id="chkLinhaDecRet_' + (id + 1) + '" name="chkLinhaDecRet" type="checkbox" class="chk" value="' + (id + 1) + '">' +
                                    '<label for="chkLinhaDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                    '<input id="hddIdLinhaDecRet" name="hddIdLinhaDecRet" type="hidden" value="' + (id + 1) + '">' +
                                    '</div>' +
                                    '</td>' +
                                    '</tr>');
                            floatMask();
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#aVerCodigos', function(event) {
                        if (event.handler !== true) {
                            var left = (screen.width / 2) - (550 / 2);
                            var top = (screen.height / 2) - (360 / 2);
                            var win = window.open('pag_codigos.php?tipo=user', 'Codigos', 'resizable=no,width=550,height=360,scrollbars=yes,top=' + top + ', left=' + left);
                            win.focus();
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('keyup', '.dinheiro', function(event) {
                        if (event.handler !== true) {
                            if ($(this).closest('#tblDadosDecRet').data('value') == 1) {
                                $(this).closest('.moneyarea_col1').css("background-color", "#fff");
                                var total = 0;
                                $('#tblDadosDecRet tr').each(function(key, value) {
                                    if (key > 0 && key < $('#tblDadosDecRet tr:last-child').index()) {
                                        $(this).children('td').eq(4).find('input[name^="txtImportancia"]').each(function(i, n) {
                                            if ($(n).val() !== "") {
                                                total += parseFloat(formatValor($(n).val()));
                                            }
                                        });
                                    }
                                });
                                if (total == "0") {
                                    $('#txtTotalDecRet').val('');
                                } else {
                                    $('#txtTotalDecRet').val(number_format(total, 2, ',', '.'));
                                }
                            }
                            floatMask();
                            event.handler = true;
                        }
                        return false;
                    });

                    $(document).on('click', '#btnRemDecRet', function(event) {
                        if (event.handler !== true) {
                            var data_i = $('#txtDataPagamento').val();
                            var ano_i = $('#slcAnoRetencao').val();
                            var mes_i = $('#slcMesRetencao').val();
                            var ult_linha = $('#tblDadosDecRet tr:last-child').index();
                            $('#tblDadosDecRet tr').each(function(key, value) {
                                if (key > 0 && key < ult_linha) {
                                    if ($(this).find('td').eq(5).find('.chk').prop('checked') === true) {
                                        var index = $(this).index();
                                        $('#tblDadosDecRet tr').each(function(key, value) {
                                            if (key > index && key < $('#tblDadosDecRet tr:last-child').index()) {
                                                $(this).find('td').eq(0).text($(this).index() - 1);
                                                $(this).find('td').eq(1).find('select').attr("id", "slcZona_" + ($(this).index() - 1));
                                                $(this).find('td').eq(2).find('select').attr("id", "slcRubrica_" + ($(this).index() - 1));
                                                $(this).find('td').eq(2).find('select').data("id", $(this).index() - 1);
                                                $(this).find('td').eq(3).children('input').attr("id", "txtDescRetencao_" + ($(this).index() - 1));
                                                $(this).find('td').eq(4).find('.moneyarea_col1').children('input').attr("id", "txtImportancia_" + ($(this).index() - 1));
                                                $(this).find('td').eq(5).find('input').eq(0).attr("id", "chkLinhaDecRet_" + ($(this).index() - 1));
                                                $(this).find('td').eq(5).find('input').eq(0).val(($(this).index() - 1));
                                                $(this).find('td').eq(5).find('input').eq(1).val(($(this).index() - 1));
                                            }
                                        });
                                        $(this).remove();
                                    }
                                }
                            });
                            $('#btnRemDecRet').hide();
                            var total = 0;
                            $('#tblDadosDecRet tr').each(function(key, value) {
                                if (key > 0 && key < $('#tblDadosDecRet tr:last-child').index()) {
                                    $(this).children('td').eq(4).find('input[name^="txtImportancia"]').each(function(i, n) {
                                        if ($(n).val() !== "") {
                                            total += parseFloat(formatValor($(n).val()));
                                        }
                                    });
                                }
                            });

                            if (total == "0") {
                                $('#txtTotalDecRet').val('');
                            } else {
                                $('#txtTotalDecRet').val(number_format(total, 2, ',', '.'));
                            }
                            if ($('#tblDadosDecRet tr').length == 2) {
                                var opcoes = '<option value="0" style="background-color: #fff;"></option>';
                                $.each(dataStore.getJson().dados_in, function(key, value) {
                                    opcoes += '<option value="' + dataStore.getJson().dados_in[key].id + '" style="background-color: #fff;">' + dataStore.getJson().dados_in[key].rubrica + '</option>';
                                });
                                $('#tblDadosDecRet').find('tr:last-child').before('<tr>' +
                                        '<td style="padding: 4px; font-size: 85%;">1</td>' +
                                        '<td style="padding: 4px;">' +
                                        '<div class="inputarea_col1" style="width: 100%; margin-right: 0; height: 20px;">' +
                                        '<div class="styled-select" style="background-size: 20px 20px; background-color: #fff;">' +
                                        '<select id="slcZona_1" name="slcZona" class="select" style="font-size: 85%;">' +
                                        '<option value="0" style="background-color: #fff;"></option>' +
                                        '<option value="1" style="background-color: #fff;">Continente</option>' +
                                        '<option value="2" style="background-color: #fff;">Açores</option>' +
                                        '<option value="3" style="background-color: #fff;">Madeira</option>' +
                                        '</select>' +
                                        '</div>' +
                                        '</div>' +
                                        '</td>' +
                                        '<td style="padding: 4px;">' +
                                        '<div class="inputarea_col1" style="width: 70%; margin-right: 0; height: 20px;">' +
                                        '<div class="styled-select" style="background-size: 20px 20px; background-color: #fff;">' +
                                        '<select id="slcRubrica_1" name="slcRubrica" class="select" style="font-size: 85%;" data-id="1">' +
                                        opcoes +
                                        '</select>' +
                                        '</div>' +
                                        '</div>' +
                                        '<a id="aVerCodigos" class="body-link" title="Consultar Códigos">[...]</a>' +
                                        '</td>' +
                                        '<td style="padding: 4px; font-size: 85%;">' +
                                        '<input id="txtDescRetencao_1" name="txtDescRetencao" type="text" size="60" value="" readonly="readonly">' +
                                        '</td>' +
                                        '<td style="padding: 4px; font-size: 85%;">' +
                                        '<div class="moneyarea_col1" style="height: 22px; width: 100%; background-color: #fff;">' +
                                        '<input id="txtImportancia_1" name="txtImportancia" type="text" class="dinheiro">' +
                                        '<div class="mnyLabel" style="width: 15px;">' +
                                        '<input name="txtMoeda" type="text" readonly="readonly" value="' + dataStore.getJson().moeda + '">' +
                                        '</div>' +
                                        '</div>' +
                                        '</td>' +
                                        '<td style="background-color: transparent; padding: 4px;">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkLinhaDecRet_1" name="chkLinhaDecRet" type="checkbox" class="chk" value="1">' +
                                        '<label for="chkLinhaDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdLinhaDecRet" name="hddIdLinhaDecRet" type="hidden" value="1">' +
                                        '</div>' +
                                        '</td>' +
                                        '</tr>');
                                $('#chkAllDecRet').prop('checked', false);
                                $('#slcAnoRetencao').val(ano_i);
                                $('#slcMesRetencao').val(mes_i);
                                $('#txtDataPagamento').val(data_i);
                            }
                            floatMask();
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#btnGuardarDecRet', function(event) {
                        if (event.handler !== true) {
                            var dados = {};
                            dados.linha = {};
                            var i = 0;
                            if (validaDecRet() === true) {
                                $('#tblDadosDecRet tr').each(function(key, value) {
                                    if (key > 0 && key < $('#tblDadosDecRet tr:last-child').index()) {
                                        var zona = $(this).children('td').eq(1).find('select[name="slcZona"] :selected').text();
                                        var rubrica = $(this).children('td').eq(2).find('select[name="slcRubrica"] :selected').val();
                                        var valor = formatValor($(this).children('td').eq(4).find('input[name="txtImportancia"]').val());
                                        dados.linha[i] = {};
                                        dados.linha[i].zona = zona;
                                        dados.linha[i].rubrica = rubrica;
                                        dados.linha[i].valor = valor;
                                        i = i + 1;
                                    }
                                });
                                if ($('select[name="slcMesRetencao"] :selected').val() != "0" && $('select[name="slcAnoRetencao"] :selected').val() != "0") {
                                    var mes = parseInt($('select[name="slcMesRetencao"] :selected').val()) + 1;
                                    if (mes < 10) {
                                        mes = "0" + mes;
                                    }
                                    var ano = $('select[name="slcAnoRetencao"] :selected').text();
                                    if (mes > 12) {
                                        mes = "01";
                                        ano = parseInt(ano) + 1;
                                    }
                                    dados.data_entrega = getVirtualDate();
                                    var data_limit = ano + "-" + mes + "-" + "20 23:59:59";
                                    dados.data_limit = data_limit;
                                    dados.total = formatValor($('input[name="txtTotalDecRet"]').val());
                                    dados.n_res = $('#chkResidentes').prop("checked");
                                    var dataString = "dados=" + JSON.stringify(dados) + "&tipo=guardar_decRet";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_users.php",
                                        dataType: "json",
                                        data: dataString,
                                        beforeSend: function() {
                                            $('#divEntDecRet').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            if (dados.sucesso === true) {
                                                $('#chkAllDecRet').prop('checked', true);
                                                $('#chkResidentes').prop('checked', false);
                                                $('#chkAllDecRet').click();
                                                $('#btnRemDecRet').click();
                                                $('#divEntDecRet').show();
                                                var id = dados.id;
                                                var win = window.open("./impressao/dec_retencoes.php?id_dec_ret=" + id);
                                                win.focus();
                                            }
                                        }
                                    });
                                } else {
                                    $('.error').show().html('<span id="error">Escolha uma data</span>');
                                    $("body").animate({scrollTop: 0});
                                    if ($('select[name="slcAnoRetencao"] :selected').val() != "0" && $('select[name="slcMesRetencao"] :selected').val() == "0") {
                                        $('select[name="slcMesRetencao"]').closest('.inputarea_col1').css("background-color", "yellow");
                                    } else if ($('select[name="slcAnoRetencao"] :selected').val() == "0" && $('select[name="slcMesRetencao"] :selected').val() != "0") {
                                        $('select[name="slcAnoRetencao"]').closest('.inputarea_col1').css("background-color", "yellow");
                                    } else {
                                        $('select[name="slcMesRetencao"]').closest('.inputarea_col1').css("background-color", "yellow");
                                        $('select[name="slcAnoRetencao"]').closest('.inputarea_col1').css("background-color", "yellow");
                                    }
                                }
                            } else {
                                $('.error').show().html('<span id="error">' + data + '</span>');
                                $("body").animate({scrollTop: 0});
                            }
                            event.handler = true;
                        }
                        return false;
                    });
					
					/* Escolher data - Fatura */
					$(document).on('click', '.campoData', function(event) {
                        if (event.handler !== true) {
                            var complVirtualDate = getVirtualDate();
                            var splitVirtualDate = complVirtualDate.split("/");
                            var virtualDate = new Date (splitVirtualDate[0] + '-' + splitVirtualDate[1] + '-' + splitVirtualDate[2]);
                            
                            $(this).datetimepicker("destroy");
							$.datetimepicker.setLocale('pt');
                            if ($(this).attr("id") == "txtDataVirtualFatura") {
                                var ddvlf = $('#txtDataVencFatura').val().split("-");
                                var dvlf = new Date (ddvlf[1] + '-' + ddvlf[0] + '-' + ddvlf[2]);
                                dvlf.setDate(dvlf.getDate() - 1); // Subtrai um dia à data limite, para garantir q é <
								
								if ($("#txtDataVencFatura").val() == "") {
									$(this).datetimepicker({
										onShow: function(){
											this.setOptions({
												format: "d-m-Y",
												timepicker: false,
												yearStart: 2014,
												yearEnd: 2020,
												defaultDate: virtualDate
												// maxDate:$('#txtDataVencFatura').val()?$('#txtDataVencFatura').val():false
												// maxDate: new Date ("04-10-2016")
												// maxDate: dvlf
											});
										}
									});
								}
                                // if ($("#txtDataVencFatura").val() != "") {
								else {
                                    $(this).datetimepicker({
                                        onShow: function(){
                                            this.setOptions({
                                                format: "d-m-Y",
                                                timepicker: false,
												yearStart: 2014,
												yearEnd: 2020,
                                                defaultDate: virtualDate,
												// maxDate:$('#txtDataVencFatura').val()?$('#txtDataVencFatura').val():false
                                                // maxDate: new Date ("04-10-2016")
                                                maxDate: dvlf
                                            });
                                        }
                                    });
                                }
                            } else if ($(this).attr("id") == "txtDataVencFatura") {
                                var ddvf = $('#txtDataVirtualFatura').val().split("-");
                                var dvf = new Date (ddvf[1] + '-' + ddvf[0] + '-' + ddvf[2]);
                                var minDate = dvf;
                                if (isNaN(dvf) == true || dvf < virtualDate) minDate = virtualDate;
								minDate.setDate(minDate.getDate() + 1); // Acrescenta um dia à data minima, para garantir q é >
								
                                $(this).datetimepicker({
                                    onShow: function(){
                                        this.setOptions({
                                            // format: "d-m-Y",
											format: "d-m-Y H:i:00",
                                            timepicker: true,
                                            yearStart: 2014,
											yearEnd: 2020,
											defaultDate: virtualDate,
                                            // maxDate:$('#txtDataVencFatura').val()?$('#txtDataVencFatura').val():false
                                            // minDate: new Date ("04-11-2016")
                                            minDate: minDate
                                        });
                                    }
                                });
                            }
                            $(this).datetimepicker('show');
                            /*
                            var dataAtual = new Date();
                            $('.error').show().html('<span id="error">Data fatura: ' + dvf + '</span>');
                            $("body").animate({scrollTop: 0});
                            /* */
                            event.handler = true;
                        }
                        return false;
                    });
					/* */
					
                    $(document).on('click', '#btnRegFat', function(event) {
                        if (event.handler !== true) {
                            var numFat = $('#txtNumFatura').val();
                            var cliente = $('#txtCliente').val();
                            var dvf = $('#txtDataVirtualFatura').val();
                            /* Added */ var dvlf = $('#txtDataVencFatura').val();
							var adiantamento = false;
                            var id_adiantamento = 0;
                            if ($('#chkAdiantamentoFatura').prop('checked')) {
                                adiantamento = true;
                                id_adiantamento = $('#slcAdiantamentoFatura').val();
                            }
                            var valor = formatValor($('#txtValorFatura').val()); 
                            // var adiantamento = formatValor($('#txtValorAdiantamentoFat').val());
                            if (numFat === "") {
                                $('.error').show().html('<span id="error">Tem de inserir o número da fatura</span>');
                                $("body").animate({scrollTop: 0});
                            } else if (cliente === "") {
                                $('.error').show().html('<span id="error">Tem de inserir o nome do cliente</span>');
                                $("body").animate({scrollTop: 0});
                            } else if (dvf === "" || dvlf === "") {
                                $('.error').show().html('<span id="error">Tem de escolher as datas</span>');
                                $("body").animate({scrollTop: 0});
                            } else if (valor === "") {
                                $('.error').show().html('<span id="error">Tem de inserir um valor</span>');
                                $("body").animate({scrollTop: 0});
                            } else if (!/^([1-9]{1}[0-9]{0,}([,.][0-9]{1,2})?|0([,.][0-9]{0,2}))$/.test(valor)) {
                                $('.error').show().html('<span id="error">O valor inserido não é válido</span>');
                                $("body").animate({scrollTop: 0});
                            } else {
                                valor -= formatValor($('#txtValorAdiantamentoFat').val());
                                // var dataString = "num_fatura=" + numFat + "&cliente=" + cliente + "&data_virtual_fatura=" + dvf + "&valor_fatura=" + valor + "&adiantamento=" + adiantamento + "&id_adiantamento=" + id_adiantamento + "&tipo=reg_faturas";
                                var dataString = "num_fatura=" + numFat + "&cliente=" + cliente + "&data_virtual_fatura=" + dvf + "&data_virtual_limite_fatura=" + dvlf + "&valor_fatura=" + valor + "&adiantamento=" + adiantamento + "&id_adiantamento=" + id_adiantamento + "&tipo=reg_faturas";
								$.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_users.php",
                                    data: dataString,
                                    dataType: "json",
                                    beforeSend: function() {
                                        $('#tblFaturasVazia').hide();
                                        $('#divRegistarFat').hide();
                                        $('#divFaturaDetail').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        if (dados.sucesso === true) {
                                            hideLoading();
                                            $('#txtNumFatura').val('');
                                            $('#txtCliente').val('');
                                            $('#txtDataVirtualFatura').val('');
                                            $('#txtDataVencFatura').val('');
                                            $('#txtValorFatura').val('');
                                            $('#txtPlafond').val(number_format(dados.valor, 2, ',', '.'));
                                            emptyTable('#tblFaturas');
                                            $.each(dados.dados_in, function(i, item) {
                                                $('#tblFaturas').append('<tr>' +
                                                        '<td style="padding: 2px;">' + dados.dados_in[i].num_fatura + '</td>' +
                                                        '<td style="padding: 2px;">' + dados.dados_in[i].cliente + '</td>' +
                                                        '<td style="padding: 2px;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + '</td>' +
                                                        '<td style="background-color: #77a4d7; padding: 0.1px; cursor: pointer;">' +
                                                        '<input name="hddIdFatura" type="hidden" value="' + dados.dados_in[i].id_fatura + '">' +
                                                        '<div id="btnVerFatura_' + dados.dados_in[i].id_fatura + '" name="btnVerFatura" class="labelicon icon-info"></div>' +
                                                        '</td>' +
                                                        '</tr>');
                                            });
                                            $('#tblFaturas').show();
                                            $('#divRegistarFat').show();
                                            
                                            $('#slcAdiantamentoFatura').empty();
                                            $('#slcAdiantamentoFatura').append('<option value="0" selected="selected">- Escolha o adiantamento -</option>');
                                            $.each(dados.dados_adiant, function() {
                                                $('#slcAdiantamentoFatura').append('<option value="' + this.id_adiantamento + '">' + this.nome_cliente + '</option>');
                                            });
                                            $('#txtValorAdiantamentoFat').val('');
                                            
                                        } else {
                                            $('#divRegistarFat').show();
                                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                            $("body").animate({scrollTop: 0});
                                        }
                                    }
                                });
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#btnLimparFatura', function(event) {
                        if (event.handler !== true) {
                            $('#txtNumFatura').val('');
                            $('#txtCliente').val('');
                            $('#txtDataVirtualFatura').val('');
                            $('#txtDataVencFatura').val('');
                            $('#txtValorFatura').val('');
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', 'div[name^="btnVerFatura"]', function(event) {
                        if (event.handler !== true) {
                            var id_fatura = $(this).closest('td').children('input').val();
                            var dataString = "id_fatura=" + id_fatura + "&tipo=fatura_detalhes";
                            $.ajax({
                                type: "POST",
                                url: "functions/funcoes_users.php",
                                dataType: "json",
                                data: dataString,
                                beforeSend: function() {
                                    $('#tblFaturas').hide();
                                    $('#divRegistarFat').hide();
                                    showLoading();
                                },
                                success: function(dados) {
                                    if (dados.sucesso === true) {
                                        hideLoading();
                                        $('#txtNFat').val(dados.num_fatura);
                                        $('#txtClient').val(dados.cliente);
                                        $('#txtValFat').val(number_format(dados.valor, 2, ',', '.'));
                                        $('#txtDataVirtFat').val(dados.data_virtual);
                                        $('#txtDataPagFat').val(dados.data_lim);
                                        if (dados.pago == "0") {
                                            $('#chkFatPaga').prop('checked', false);
                                        } else {
                                            $('#chkFatPaga').prop('checked', true);
                                        }
                                        $('#divRegistarFat').show();
                                        $('#divFaturaDetail').show();
                                    }
                                }
                            });
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('click', '#btnVoltarFat', function(event) {
                        if (event.handler !== true) {
                            $('#divFaturaDetail').hide();
                            $('#tblFaturas').show();
                            event.handler = true;
                        }
                        return false;
                    });
                    $(document).on('keyup', 'input[name="txtNumFatura"]', function(event) {
                        if (event.handler !== true) {
                            if ($(this).val().length == "0") {
                                $('#txtDataVirtualFatura').val('');
                                $('#txtDataVencFatura').val('');
                            } /* else {
                                var data_virtual = getVirtualDate();
                                var data_oc = new Date(data_virtual);
                                $('#txtDataVirtualFatura').val(('0' + data_oc.getDate()).slice(-2) + '-' + (('0' + (data_oc.getMonth() + 1)).slice(-2)) + '-' + data_oc.getFullYear() + ' ' + ('0' + data_oc.getHours()).slice(-2) + ':' + ('0' + data_oc.getMinutes()).slice(-2) + ':' + ('0' + data_oc.getSeconds()).slice(-2));
                                $('#txtDataVencFatura').val(('0' + data_oc.getDate()).slice(-2) + '-' + (('0' + (data_oc.getMonth() + 2)).slice(-2)) + '-' + data_oc.getFullYear() + ' ' + ('0' + data_oc.getHours()).slice(-2) + ':' + ('0' + data_oc.getMinutes()).slice(-2) + ':' + ('0' + data_oc.getSeconds()).slice(-2));
                            } */
                            event.handler = true;
                        }
                        return false;
                    });

                    $('#calendar').fullCalendar({
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'year,month,agendaWeek,agendaDay'
                        },
                        height: calcCalendarHeight(),
                        defaultView: 'year',
                        axisFormat: 'H:mm',
                        allDayText: 'todo o dia',
                        minTime: '0',
                        eventSources: [{
                                url: 'functions/funcoes_geral.php',
                                type: 'POST',
                                data: {
                                    tipo: "eventos"
                                }
                            }],
                        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                        dayNamesShortest: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
                        timeFormat: {
                            agenda: 'H:mm{ - H:mm}',
                            '': 'H:mm'
                        },
                        buttonText: {
                            today: 'hoje',
                            year: 'ano',
                            month: 'mês',
                            week: 'semana',
                            day: 'dia'
                        },
                        theme: true
                    });

                    $(window).resize(function() {
                        $('#calendar').fullCalendar('option', 'height', calcCalendarHeight());
                    });

                    $(document).on('click', '#btnModify', function(event) {
                        if (event.handler !== true) {
                            var pass_old = $('#txtPassOld').val();
                            var pass_new = $('#txtPassNew').val();
                            var conf_pass = $('#txtPassRep').val();
                            if (validaPass(pass_old, pass_new, conf_pass) === true) {
                                var dataString = "password_old=" + pass_old + "&password_new=" + pass_new + "&password_rep=" + conf_pass + "&tipo=modify_pass";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_geral.php",
                                    data: dataString,
                                    dataType: "json",
                                    beforeSend: function() {
                                        $('#divMudarPass').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        $('#divMudarPass').show();
                                        if (dados.sucesso === true) {
                                            $('#txtPassOld').val('');
                                            $('#txtPassNew').val('');
                                            $('#txtPassRep').val('');
                                        } else {
                                            $('.error').show().html('<span>' + dados.mensagem + '</span>');
                                            $("body").animate({scrollTop: 0});
                                        }
                                    }
                                });
                            } else {
                                $('.error').show().html('<span id="error">' + data + '</span>');
                                $("body").animate({scrollTop: 0});
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    
                    //-- Adiantamento de faturas
                    $(document).on('change', '#chkAdiantamentoFatura', function(event) {
                        if (event.handler !== true) {
                            if ($('#chkAdiantamentoFatura').prop('checked'))
                                $('#adiantamentoFat').show();
                            else {
                                $('#adiantamentoFat').hide();
                                $('#txtValorAdiantamentoFat').val('');
                                $('#txtCliente').val('');
                                $('#txtCliente').prop("readonly", false);
                            }
                        
                        event.handler = true;
                        }
                        return false;
                    });
                    
                    $(document).on('click', '#slcAdiantamentoFatura', function(event) {
                        if (event.handler !== true) {
                            var adiantamento = $('#slcAdiantamentoFatura').val();
                            var dataString = "id_adiantamento=" + adiantamento + "&tipo=dados_adiantamento";
                            
                            if ($('#slcAdiantamentoFatura').val() != 0) {
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_users.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function(dados) {
                                        if (dados.sucesso === true) {
                                            $('#txtValorAdiantamentoFat').val(number_format(dados.valor, 2, ',', '.'));
                                            $('#txtCliente').val(dados.cliente);
                                            $('#txtCliente').prop("readonly", true);
                                        }
                                        /*else {
                                            //$.notify.create(dados.mensagem, {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            //$("#").notify("show");
                                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }*/
                                    }
                                });
                            }
                            else {
                                $('#txtValorAdiantamentoFat').val('');
                                $('#txtCliente').val(''); 
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    
                    //-- Notas de Crédito
                    $(document).on('change', '#slcFiltrarNotaCredito', function() {
                        //if (event.handler !== true) {
                            var id_fornecedor = $(this).val();
                            $.ajax({
                                type: "POST",
                                url: "functions/funcoes_users.php",
                                data: "id_fornecedor=" + id_fornecedor + "&tipo=filtrar_enc",
                                dataType: "json",
                                beforeSend: function() {
                                    $('#tblEncomendasEfet').hide();
                                    showLoading();
                                },
                                success: function(dados) {
                                    hideLoading();
                                    emptyTable('#tblEncomendasEfet');
                                    if (dados.sucesso === true) {
                                        $.each(dados.dados_in, function(i, item) {
                                            $('#tblEncomendasEfet').append('<tr>' +
                                                    '<td class="refDetFatura" style="color: #2b6db9; padding: 4px; cursor: pointer;"><b>' + dados.dados_in[i].ref + '</b>' + 
                                                        '<input name="hddIdEnc" type="hidden" value="' + dados.dados_in[i].id + '">' + 
                                                    '</td>' + 
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].pago + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].nome_abrev + '</td>' +
                                                    '<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                    '<td style="background-color: transparent; padding: 0; cursor: pointer;">' +
                                                        '<a href="./impressao/fatura.php?id=' + dados.dados_in[i].id + '" target="_blank">' +
                                                            '<img width="33" height="33" src="./images/adobe_logo.png">' +
                                                        '</a>' +
                                                    '</td>' +
                                                '</tr>');
                                        });
                                        $('#tblEncomendasEfet').show();
                                    } /*else {
                                        $('.erro').show().html('<span id="error">' + dados.mensagem + '</span>');
                                        $("body").animate({scrollTop: 0});
                                    }*/
                                }
                            });
                            /*event.handler = true;
                        }
                        return false;*/
                    });
                    
                    $(document).on('click', '.refDetFatura', function(event) {
                        if (event.handler !== true) {
                            var id_fatura = $(this).find('input').val();
                            var dataString = "id_fatura=" + id_fatura + "&tipo=dados_fatura";
                            
                            $.ajax({
                                type: "POST",
                                url: "functions/funcoes_users.php",
                                data: dataString,
                                dataType: "json",
                                success: function(dados) {
                                    if (dados.sucesso === true) {
                                        $('#slcFiltrarNotaCredito').parent('div').parent('div').hide();
                                        $('#tblEncomendasEfet').hide();
                                        $('#tblFatVazia').hide();
                                        $('#tblFatDetail').show();
                                        $('#divTotalEncomenda').show();
                                        $('#btnDevolver').show();
                                        $('#refFaturaNC').val(dados.ref_enc);
                                        $('#hddIdFatNC').val(id_fatura);
                                        emptyTable('#tblFatDetail');
                                        $.each(dados.dados_in, function(i, item) {
                                            $('#tblFatDetail').append('<tr>' +
                                                '<td id="n_itens" style="padding: 0.5%;">' + 
                                                    '<input id="hddIdProdutoNC" name="hddIdProdutoNC" type="hidden" value="' + dados.dados_in[i].id_produto + '">' + 
                                                    '<input id="hddIdFornecedorNC" name="hddIdFornecedorNC" type="hidden" value="' + dados.dados_in[i].id_fornec + '">' + 
                                                    '<input id="hddValorDescNC" name="hddValorDescNC" type="hidden" value="' + dados.dados_in[i].valor_desc + '">' + 
                                                    '<input id="hddTaxaIVANC" name="hddTaxaIVANC" type="hidden" value="' + dados.dados_in[i].valor_regra + '">' + 
                                                    '<input id="hddSimbMoeda" name="hddSimbMoeda" type="hidden" value="' + dados.simbolo_moeda + '">' + (i + 1) + 
                                                '</td>' +
                                                '<td style="text-align: left; padding: 0.5%;">' + dados.dados_in[i].nome_produto + '</td>' +
                                                '<td style="text-align: left; padding: 0.5%;">' + dados.dados_in[i].nome_fornecedor + '</td>' +
                                                '<td style="padding: 0.5%;">' + number_format(dados.dados_in[i].preco, 2, ',', '.') + '</td>' +
                                                '<td style="padding: 0.5%;">' + number_format(dados.dados_in[i].qtd, 2, ',', '.') + '</td>' +
                                                '<td class="tdInput" style="padding: 0.5%;"><input id="txtQuantNC" name="txtQuantNC" type="text" class="editableText dinheiro" value="' + number_format(dados.dados_in[i].qtd, 2, ',', '.') + '"></td>' +
                                                '<td style="padding: 0.5%;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + '</td>' +
                                                '<td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">' +
                                                '<div class="labelicon icon-garbage rem_linha">' +
                                                '</div>' +
                                                '</td>' +
                                                '</tr>');
                                        });
                                        $('#txtTotNCsDesc').val('Total sem desconto: ' + number_format(dados.total_fat, 2, ',', '.') + ' ' + dados.simbolo_moeda);
                                        $('#txtDescontoNC').val('Desconto: ' + number_format(dados.total_desc, 2, ',', '.') + ' ' + dados.simbolo_moeda);
                                        $('#txtIvaNC').val('IVA: ' + number_format(dados.total_iva, 2, ',', '.') + ' ' + dados.simbolo_moeda);
                                        $('#txtSomaNC').val('Total: ' + number_format(dados.total_final, 2, ',', '.') + ' ' + dados.simbolo_moeda);
                                        floatMask();
                                        $('#divFatDetail').show();
                                    } else {
                                        /*$.notify.create(dados.mensagem, {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                        $("#").notify("show");*/
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
                    });
                    
                    $(document).on('click', '#btnVoltarNC', function(event) {
                        if (event.handler !== true) {
                            $('#divFatDetail').hide();
                            $('#slcFiltrarNotaCredito').parent('div').parent('div').show();
                            $('#tblEncomendasEfet').show();
                            event.handler = true;
                        }
                        return false;
                    });
                    
                    $(document).on('click', '.rem_linha', function(event) {
                        if (event.handler !== true) {
                            if (confirm('Deseja mesmo remover esta linha?')) {
                                $(this).closest('tr').remove();
                                var total = 0;
                                var total_iva = 0;
                                var total_desc = 0;
                                var total_final = 0;
                                var moeda = $('#hddSimbMoeda').val();
                                var tamTabela = $('#tblFatDetail tr').length - 1;
                                
                                if (tamTabela > 0) {
                                    $.each($('#tblFatDetail tr'), function(key, value) {
                                        if (key > 0) {
                                            var preco = formatValor($(this).children('td').eq(3).text());
                                            var qtd = formatValor($(this).find('#txtQuantNC').val());
                                            var tx_iva = formatValor($(this).find('#hddTaxaIVANC').val());
                                            var iva = (preco * qtd) * (tx_iva / 100);
                                            var desconto = formatValor($(this).find('#hddValorDescNC').val());

                                            total += preco * qtd;
                                            total_iva += iva;
                                            total_desc += (preco * qtd) * (desconto / 100);
                                            total_final = total + total_iva - total_desc;

                                            /*
                                            $.notify.create('Preço = ' + number_format(preco, 2, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Qtds = ' + number_format(qtd, 2, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Tx IVA = ' + number_format(tx_iva, 2, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Valor IVA = ' + number_format(iva, 2, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Valor desconto = ' + number_format(desconto, 2, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Total s/ desconto = ' + number_format(total, 3, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Total IVA = ' + number_format(total_iva, 3, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            $.notify.create('Total final = ' + number_format(total_final, 3, ',', '.'), {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                            $("#").notify("show");
                                            */
                                        }
                                    });

                                    $('#txtTotNCsDesc').val('Total sem desconto: ' + number_format(total, 2, ',', '.') + ' ' + moeda);
                                    $('#txtDescontoNC').val('Desconto: ' + number_format(total_desc, 2, ',', '.') + ' ' + moeda);
                                    $('#txtIvaNC').val('IVA: ' + number_format(total_iva, 2, ',', '.') + ' ' + moeda);
                                    $('#txtSomaNC').val('Total: ' + number_format(total_final, 2, ',', '.') + ' ' + moeda);
                                }
                                else {
                                    $('#tblFatDetail').hide();
                                    $('#btnDevolver').hide();
                                    $('#divTotalEncomenda').hide();
                                    $('#tblFatVazia').show();
                                }
                            }
                            event.handler = true;
                        }
                        return false;
                    });
                    
                    $(document).on('blur', '#txtQuantNC', function(event) {
                        if (event.handler !== true) {
                            var total = 0;
                            var total_iva = 0;
                            var total_desc = 0;
                            var total_final = 0;
                            var moeda = $('#hddSimbMoeda').val();
                            
                            if ($(this).val() > parseFloat($(this).closest('td').prev('td').text())) {
                                //$.notify.create(A quantidade devolvida não pode ser maior que a comprada, {sticky: true, type: 'error', style: 'bar', adjustContent: true, appendTo: '#conteudo'});
                                //$("#").notify("show");
                                $(this).val($(this).closest('td').prev('td').text());
                                $('.error').show().html('<span id="error"> A quantidade devolvida não pode ser maior que a comprada </span>');
                                $("body").animate({
                                    scrollTop: 0
                                });
                            } else {
                                $.each($('#tblFatDetail tr'), function(key, value) {
                                    if (key > 0) {
                                        /*
										var preco = parseFloat(formatValor($(this).children('td').eq(3).text()));
                                        var qtd = parseFloat(formatValor($(this).find('#txtQuantNC').val()));
                                        var tx_iva = $(this).find('#hddTaxaIVANC').val();
										var iva = (preco * qtd) * (tx_iva / 100);
                                        var desconto = formatValor($(this).find('#hddValorDescNC').val());
                                        var total_linha = preco * qtd + iva;

                                        $(this).children('td').eq(6).text(number_format(total_linha, 2, ',', '.'));
                                        total += preco * qtd;
                                        total_iva += iva;
                                        total_desc += (preco * qtd) * (desconto / 100);
                                        total_final = total + total_iva - total_desc;
										*/
										
										var preco = parseFloat(formatValor($(this).children('td').eq(3).text()));
                                        var qtd = parseFloat(formatValor($(this).find('#txtQuantNC').val()));
                                        var valor_ini = preco * qtd;
                                        var valor_base = parseFloat(valor_ini.toFixed(2));
                                        
                                        var desconto = $(this).find('#hddValorDescNC').val();
                                        var desc_ini = valor_base * (desconto / 100);
                                        var desc = parseFloat(desc_ini.toFixed(2));
                                        
                                        var tx_iva = $(this).find('#hddTaxaIVANC').val();
                                        var iva_ini = (valor_base - desc) * (tx_iva / 100);
                                        var iva = parseFloat(iva_ini.toFixed(2));
                                        
                                        var total_linha = valor_base - desc + iva;
                                        $(this).children('td').eq(6).text(number_format(total_linha, 2, ',', '.'));
                                        
                                        total += valor_base;
                                        total_iva += iva;
                                        total_desc += desc;
                                        total_final = total + total_iva - total_desc;
                                    }
                                });

                                $('#txtTotNCsDesc').val('Total sem desconto: ' + number_format(total, 2, ',', '.') + ' ' + moeda);
                                $('#txtDescontoNC').val('Desconto: ' + number_format(total_desc, 2, ',', '.') + ' ' + moeda);
                                $('#txtIvaNC').val('IVA: ' + number_format(total_iva, 2, ',', '.') + ' ' + moeda);
                                $('#txtSomaNC').val('Total: ' + number_format(total_final, 2, ',', '.') + ' ' + moeda);
                            }
                            
                            event.handler = true;
                        }
                        return false;
                    });
                    
                    $(document).on('click', '#btnDevolver', function(event) {
                        if (event.handler !== true) {
                            var id_fat = $('#hddIdFatNC').val();
                            var id_fornecedor = $('#hddIdFornecedorNC').val();
                            var total = 0;
                            var total_iva = 0;
                            var total_desc = 0;
                            var total_final = 0;
                            var det_nc = {};
                            $("#tblFatDetail tr").each(function(key, value) {
                                if (key > 0) {
                                    /*
									var id_produto = $(this).find('#hddIdProdutoNC').val();
                                    var preco = formatValor($(this).children('td').eq(3).text());
                                    var qtd = formatValor($(this).find('#txtQuantNC').val());
                                    var tx_iva = $(this).find('#hddTaxaIVANC').val();
                                    var desconto = $(this).find('#hddValorDescNC').val();
                                    
                                    var iva = (preco * qtd) * (tx_iva / 100);
                                    var total_linha = preco * qtd + iva;
                                    total += preco * qtd;
                                    total_iva += iva;
                                    total_desc += (preco * qtd) * (desconto / 100);
                                    total_final = total + total_iva - total_desc;
									*/
									
									/* */
                                    var id_produto = $(this).find('#hddIdProdutoNC').val();
                                    var preco = parseFloat(formatValor($(this).children('td').eq(3).text()));
                                    var qtd = parseFloat(formatValor($(this).find('#txtQuantNC').val()));
                                    var valor_ini = preco * qtd;
                                    var valor_base = parseFloat(valor_ini.toFixed(2));

                                    var desconto = $(this).find('#hddValorDescNC').val();
                                    var desc_ini = valor_base * (desconto / 100);
                                    var desc = parseFloat(desc_ini.toFixed(2));

                                    var tx_iva = $(this).find('#hddTaxaIVANC').val();
                                    var iva_ini = (valor_base - desc) * (tx_iva / 100);
                                    var iva = parseFloat(iva_ini.toFixed(2));
                                    var total_linha = valor_base - desc + iva;

                                    total += valor_base;
                                    total_iva += iva;
                                    total_desc += desc;
                                    total_final = total + total_iva - total_desc;
                                    /* */
                                    
                                    det_nc[key] = {};
                                    det_nc[key].id_produto = id_produto;
                                    det_nc[key].preco = preco;
                                    det_nc[key].qtd = qtd;
                                    det_nc[key].iva = iva;
                                    det_nc[key].total_linha = total_linha;
                                }
                            });
                            
                            var dataString = "id_fat=" + id_fat + "&id_fornecedor=" + id_fornecedor + "&det_nc=" + JSON.stringify(det_nc) + "&total_iva=" + total_iva + "&total_final=" + total_final + "&data_virt=" + getVirtualDate() + "&tipo=nota_credito";
                            $.ajax({
                                type: "POST",
                                url: "functions/funcoes_users.php",
                                data: dataString,
                                dataType: "json",
                                success: function(dados) {
                                    if (dados.sucesso === true) {
                                        $('#var_content').load('conteudo_user.php #ver_notas_credito', function(){
                                            $('.loading').hide();
                                            $('#tblNotaCreditoVazia').hide();
                                        });
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
                    });
					
					//-- /* MOVED FROM banco.js
                    $(document).on('change', '#slcPaisFornecedorA', function (event) {
                        // if (event.handler !== true) {
                            var id = $(this).val();
                            $('#slcFornecedorA').find('option').not(':first').remove();
                            $('#slcIvaAd').val(0);
                            if (id != 0) {
                                var dataString = 'id_pais=' + id + "&tipo=filter_fornec_pais";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_users.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function (dados) {
                                        if (dados.sucesso === true) {
                                            emptySelect("#slcFornecedorA");
											$.each(dados.dados_in, function(i, item) {
                                                $('#slcFornecedorA').append('<option value="' + dados.dados_in[i].id + '" selected="selected">' + dados.dados_in[i].nome + '</option>');
                                            });
                                            $('input[name="txtMoeda"]').val(dados.moeda);
                                            $('#ISOmoeda').val(dados.isomoeda);
                                            $('#slcFornecedorA').find('option').eq(0).prop('selected', true);
                                        }
                                    }
                                });
                                
                                if (id != 3 && id != 5 && id != 9) {
                                    if (!$('#slcIvaAd').closest('.linha').is(":visible"))
										$('#slcIvaAd').closest('.linha').show(500);
                                } else {
                                    $('#slcIvaAd').closest('.linha').hide(500);
                                }
                            }
                            
                            /* event.handler = true;
                        } */
                        
                        return false;
                    });
                    /* UNUSED
                    $(document).on('change', '#slcFornecedorA', function (event) {
                        if (event.handler !== true) {
                            var id = $(this).val();
                            if (id != "0") {
                                var dataString = 'id=' + id + "&tipo=moeda_fornecedor";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function (dados) {
                                        $('input[name="txtMoeda"]').val(dados.moeda);
                                        $('#ISOmoeda').val(dados.isomoeda);
                                    }
                                });
                            }
                            event.handler = true;
                        }
                    });
                    *///--
                    $(document).on('click', '#btnRegAdiantamentoF', function (event) {
                        if (event.handler !== true) {
                            // var saldo = parseFloat(formatValor($('input[name="saldo"]').val()));
                            var valor = parseFloat(formatValor($('#txtAdiantamentoFornec').val()));
                            if ($('#slcFornecedorA').val() == "0") {
                                $('.error').show().html('<span id="error"> Por favor, escolha um fornecedor </span>');
                                $("body").animate({
                                    scrollTop: 0
                                });
                            }
                            else if ($('#txtAdiantamentoFornec').val() == '' || $('#txtAdiantamentoFornec').val() == 0) {
                                $('.error').show().html('<span id="error"> Por favor, defina o valor </span>');
                                $("body").animate({
                                    scrollTop: 0
                                });
                            }
                            /* else if (valor > saldo) {
                                $('.error').show().html('<span id="error"> Lamentamos, mas não tem saldo suficiente </span>');
                                $("body").animate({
                                    scrollTop: 0
                                });
                            } */
                            else {
                                var id_fornecedor = $('#slcFornecedorA').val();
                                var fornecedor = $('#slcFornecedorA option:selected').text();
                                var tx_iva = $('#slcIvaAd').val();
                                var isomoeda = $('#ISOmoeda').val();
                                var data_virt = getVirtualDate();
                                var dataString = "id_fornecedor=" + id_fornecedor + "&fornecedor=" + fornecedor + "&tx_iva=" + tx_iva + "&valor=" + valor + "&ISO4217=" + isomoeda + "&data_virt=" + data_virt + "&tipo=adiant_fornec";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function (dados) {
                                        if (dados.sucesso === true) {
                                            var win = window.open("./impressao/adiantamento.php?id=" + dados.id_last_adiant + "&p=PT");
                                            win.focus();
                                            
                                            // location.reload();
                                            $('#var_content').load('conteudo_user.php #adiant_efet', function () {
                                                $('.loading').hide();
                                                $('#tblAdiantEfetVazia').hide();
                                            });
                                        }
                                        else {
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
                    });
                    //-- /* */
					
                    //-- Final implementação EVENTS
                });
            }
        }
        return false;
    });
});