/*
 * @Author: Ricardo Órfão
 * @Date:   2014-05-04 13:22:10
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-15 18:55:13
 */

//

//
var activeWindow;
    $(document).on('click', 'a.modalLink', function(e) {
        e.preventDefault();
        var id = $(this).attr('href');
        //
        /*if(id == "#OpenModalDicas") {
            $("#vid1").trigger("play");
            //$("video").get(0).play();
        }
        else {
            $("#vid2").trigger("play");
            //$("video").get(1).play();
        }
        //*/
        activeWindow = $(id)
            .css({
                'opacity': '0',
                'left': '50%',
                'top': '30%',
                'margin-left': -$(id).width() / 2,
                'margin-top': -$(id).height() / 2
            }).fadeTo(500, 1);
        $('#modalAcoes').append('<div class="blind"></div>')
            .find('.blind')
            .css('opacity', '0')
            .fadeTo(500, 0.9).on('click', function(e) {
                closeModal();
            });
    });
    $(document).on('click', 'a.close', function(e) {
        e.preventDefault();
        $("video").trigger("pause");
        $('#vid1')[0].currentTime = 0;
        $('#vid2')[0].currentTime = 0;
        closeModal();
    });
    
    $(window).resize(function() {
        activeWindow = $('#openModalAcoes #openModalDicas').css({
            'left': '50%',
            'top': '30%',
            'margin-left': -$('#openModalAcoes #openModalDicas').width() / 1,
            'margin-top': -$('#openModalAcoes #openModalDicas').height() / 1
        });
        activeWindow.css('top', '-1000px').css('left', '-1000px');
    });
    
    function closeModal() {
        activeWindow.fadeOut(250, function() {
            $("video").trigger("pause");
            $('#vid1')[0].currentTime = 0;
            $('#vid2')[0].currentTime = 0;
            $(this).css('top', '-1000px').css('left', '-1000px');
        });
        $('.blind').fadeOut(250, function() {
            $(this).remove();
        });
    }
//

$(document).ready(function() {
    var BrowserDetect = {
        init: function() {
            this.browser = this.searchString(this.dataBrowser) || "Other";
            this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "Unknown";
        },
        searchString: function(data) {
            for (var i = 0; i < data.length; i++) {
                var dataString = data[i].string;
                this.versionSearchString = data[i].subString;

                if (dataString.indexOf(data[i].subString) != -1) {
                    return data[i].identity;
                }
            }
        },
        searchVersion: function(dataString) {
            var index = dataString.indexOf(this.versionSearchString);
            if (index == -1)
                return;
            return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
        },
        dataBrowser: [{
            string: navigator.userAgent,
            subString: "Chrome",
            identity: "Chrome"
        }, {
            string: navigator.userAgent,
            subString: "MSIE",
            identity: "Explorer"
        }, {
            string: navigator.userAgent,
            subString: "Firefox",
            identity: "Firefox"
        }, {
            string: navigator.userAgent,
            subString: "Safari",
            identity: "Safari"
        }, {
            string: navigator.userAgent,
            subString: "Opera",
            identity: "Opera"
        }]

    };
    BrowserDetect.init();
    menu();
    heartbeat();
    setInterval(heartbeat, 60000);
    $.initWindowMsg();
    var childWin;
    var recordsPerPage;
    var totalNumRecords;
    $(document).ajaxError(function() {
        refresh();
    });
    updateRelogio();
    setInterval(function(){updateRelogio()}, 1000);
    $(document).on('mousedown', fEsconderErro);
    $('#var_content').load('conteudo_banco.php #movimentos', function() {
        hideError();
        hideLoading();
        paraRefresh();
		paraGanhoPerda();
        floatMask();
        acoesMask();
        intMask();
        contaMask();

        $('.campoData').datetimepicker({
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
            timepicker: false,
            format: 'd-m-Y',
            onClose: function() {
                if ($(this.val() === "")) {
                    $(this.val(""));
                }
            }
        });

        $(document).on('click', '#btnProcurarMov', function(event) {
            if (event.handler !== true) {
                var data_inicial = $('#txtDataI').val();
                var data_final = $('#txtDataF').val();
                if (data_inicial !== "" && data_final !== "") {
                    var data_i_bruto = $('#txtDataI').val();
                    var data_i_format = data_i_bruto.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, "$2/$1/$3");
                    var data_i = new Date(data_i_format);
                    var data_f_bruto = $('#txtDataF').val();
                    var data_f_format = data_f_bruto.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, "$2/$1/$3");
                    var data_f = new Date(data_f_format);
                    if (data_f >= data_i) {
                        var dataString = "data_i=" + data_inicial + "&data_f=" + data_final + "&tipo=procurar_mov";
                        $.ajax({
                            type: "POST",
                            url: "functions/funcoes_banco.php",
                            data: dataString,
                            dataType: "json",
                            beforeSend: function() {
                                $('#tblMovimentos').hide();
                                $('#tblMovVazia').hide();
                                showLoading();
                            },
                            success: function(dados) {
                                hideLoading();
                                if (dados.sucesso === true) {
                                    emptyTable('#tblMovimentos');
                                    if (dados.vazio === false) {
                                        $.each(dados.dados_in, function(i, item) {
                                            var debito;
                                            var credito;
                                            if (dados.dados_in[i].debito == 0) {
                                                debito = "";
                                            } else {
                                                debito = number_format(dados.dados_in[i].debito, 2, ',', '.') + ' ' + dados.dados_in[i].moeda;
                                            }
                                            if (dados.dados_in[i].credito == 0) {
                                                credito = "";
                                            } else {
                                                credito = number_format(dados.dados_in[i].credito, 2, ',', '.') + ' ' + dados.dados_in[i].moeda;
                                            }
											
											var txt_pos = "";
											if (this.descricao == '-') txt_pos = 'center'; else txt_pos = 'left';
                                            $('#tblMovimentos').append('<tr>' +
                                                '<td style="padding: 2px;">' + dados.dados_in[i].data + '</td>' +
                                                '<td style="padding: 2px;">' + dados.dados_in[i].tipo + '</td>' +
                                                '<td style="text-align: ' + txt_pos + '; padding: 2px;">' + dados.dados_in[i].descricao + '</td>' +
                                                '<td style="padding: 2px;">' + debito + '</td>' +
                                                '<td style="padding: 2px;">' + credito + '</td>' +
                                                '<td style="padding: 2px;">' + number_format(dados.dados_in[i].saldo_controlo, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                '<td class="botaoMovPrint icon-printer" style="background-color: #77a4d7; padding: 0;">' +
                                                '<input id="hddIdMov" name="hddIdMov" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                '<img src="images/printer_icon.png" alt="Imprimir">' +
                                                '</td>' +
                                                '</tr>');
                                        });
                                        $('#tblMovimentos').show();
                                    } else {
                                        $('#tblMovimentos').hide();
                                        $('#tblMovVazia').show();
                                    }
                                }
                            }
                        });
                    } else {
                        $('.error').show().html('<span id="error">Data inicial superior à final</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                } else {
                    $('.error').show().html('<span id="error">Insira uma data inicial e uma final</span>');
                    $("body").animate({
                        scrollTop: 0
                    });
                }
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '#btnPrintMov', function(event) {
            if (event.handler !== true) {
                var data_inicial = $('#txtDataI').val();
                var data_final = $('#txtDataF').val();
                if (data_inicial !== "" && data_final !== "") {
                    var data_i_bruto = $('#txtDataI').val();
                    var data_i_format = data_i_bruto.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, "$2/$1/$3");
                    var data_i = new Date(data_i_format);
                    var data_f_bruto = $('#txtDataF').val();
                    var data_f_format = data_f_bruto.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, "$2/$1/$3");
                    var data_f = new Date(data_f_format);
                    if (data_f >= data_i) {
                        var data_v_tmp = getVirtualDate();
                        var data_c = new Date(data_v_tmp);
                        var data_v = ('0' + data_c.getDate()).slice(-2) + '-' + (('0' + (data_c.getMonth() + 1)).slice(-2)) + '-' + data_c.getFullYear() + ' ' + (('0' + (data_c.getHours())).slice(-2)) + ':' + (('0' + (data_c.getMinutes())).slice(-2)) + ':' + (('0' + (data_c.getSeconds())).slice(-2));
                        var win = window.open("./impressao/movimentos.php?data_inicial=" + data_inicial + "&data_final=" + data_final + "&data_virtual=" + data_v);
                        win.focus();
                    } else {
                        $('.error').show().html('<span id="error">Data inicial superior à final</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                } else {
                    $('.error').show().html('<span id="error">Insira uma data inicial e uma final</span>');
                    $("body").animate({
                        scrollTop: 0
                    });
                }
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '#btnPrintExcel', function(event) {
            if (event.handler !== true) {
                var data_inicial = $('#txtDataI').val();
                var data_final = $('#txtDataF').val();
                if (data_inicial !== "" && data_final !== "") {
                    var data_i_bruto = $('#txtDataI').val();
                    var data_i_format = data_i_bruto.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, "$2/$1/$3");
                    var data_i = new Date(data_i_format);
                    var data_f_bruto = $('#txtDataF').val();
                    var data_f_format = data_f_bruto.replace(/(\d\d)-(\d\d)-(\d\d\d\d)/, "$2/$1/$3");
                    var data_f = new Date(data_f_format);
                    if (data_f >= data_i) {
                        window.open("./impressao/excelPrint.php?data_inicial=" + data_inicial + "&data_final=" + data_final);
                    } else {
                        $('.error').show().html('<span id="error">Data inicial superior à final</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                } else {
                    $('.error').show().html('<span id="error">Insira uma data inicial e uma final</span>');
                    $("body").animate({
                        scrollTop: 0
                    });
                }
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '.botaoMovPrint', function(event) {
            if (event.handler !== true) {
                var data_v_tmp = getVirtualDate();
                var data_c = new Date(data_v_tmp);
                var data_v = ('0' + data_c.getDate()).slice(-2) + '-' + (('0' + (data_c.getMonth() + 1)).slice(-2)) + '-' + data_c.getFullYear() + ' ' + (('0' + (data_c.getHours())).slice(-2)) + ':' + (('0' + (data_c.getMinutes())).slice(-2)) + ':' + (('0' + (data_c.getSeconds())).slice(-2));
                var id_mov = $(this).children('#hddIdMov').val();
                var win = window.open("./impressao/movimento.php?id_mov=" + id_mov + "&data_virtual=" + data_v);
                win.focus();
                event.handler = true;
            }
            return false;
        });

        $.ajax({
            type: "POST",
            url: "functions/funcoes_banco.php",
            data: "tipo=movimentos",
            dataType: "json",
            beforeSend: function() {
                $('#tblMovimentos').hide();
                $('#tblMovVazia').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    emptyTable('#tblMovimentos');
                    if (dados.vazio === false) {
                        if (dados.linhas <= 5) {
                            $('.pagination').hide();
                        }
                        $.each(dados.dados_in, function() {
                            var debito;
                            var credito;
                            if (this.debito == 0) {
                                debito = "";
                            } else {
                                debito = number_format(this.debito, 2, ',', '.') + ' ' + dados.moeda;
                            }
                            if (this.credito == 0) {
                                credito = "";
                            } else {
                                credito = number_format(this.credito, 2, ',', '.') + ' ' + dados.moeda;
                            }
							
							var txt_pos = "";
                            if (this.descricao == '-') txt_pos = 'center'; else txt_pos = 'left';
                            $('#tblMovimentos').append('<tr>' +
                                '<td style="padding: 2px;">' + this.data_op + '</td>' +
                                '<td style="padding: 2px;">' + this.tipo + '</td>' +
                                '<td style="text-align: ' + txt_pos + '; padding: 2px;">' + this.descricao + '</td>' +
                                '<td style="padding: 2px;">' + debito + '</td>' +
                                '<td style="padding: 2px;">' + credito + '</td>' +
                                '<td style="padding: 2px;">' + number_format(this.saldo_controlo, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td class="botaoMovPrint icon-printer" style="background-color: #77a4d7; padding: 0;">' +
                                '<input id="hddIdMov" name="hddIdMov" type="hidden" value="' + this.id + '">' +
                                '<img src="images/printer_icon.png" alt="Imprimir">' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblMovimentos').show();
                        $('#pagInput').val("1 de " + dados.paginas);
                        $('#pagAtual').val(1);
                        $('#pagTotal').val(dados.paginas);
                        $('#pagLinhas').val(dados.linhas);
                    } else {
                        $('#tblMovimentos').hide();
                        $('#tblMovVazia').show();
                    }
                }
            }
        });

        $(document).on('click', '#pagInput', function(event) {
            $(this).val($('#pagAtual').val());
            $(this).select();
            $(this).prop("readonly", false);
        });

        $(document).on('click', '#pagInput', function(event) {
            if (event.handler !== true) {
                $(this).focusout(function() {
                    inputPagination($(this));
                });
                $(this).keydown(function(event) {
                    // Permitir: backspace, tab, enter, escape, delete
                    if ($.inArray(event.keyCode, [8, 9, 27, 46]) !== -1 ||
                        // Permitir: Ctrl+A
                        (event.keyCode == 65 && event.ctrlKey === true) ||
                        // Permitir: home, end, left, right
                        (event.keyCode >= 35 && event.keyCode <= 39)) {
                        // Não fazer nada
                        // return;
                    }
                    // Se pressionar enter
                    if (event.keyCode == 13) {
                        // inputPagination($(this));
                        $(this).blur();
                    }
                    if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                        event.preventDefault();
                    }
                });
                event.handler = true;
            }
            return false;
        });

        $(document).on('change', '#slcPag', function(event) {
            if (event.handler !== true) {
                $('#pagAtual').val(1);
                inputPagination($('#pagInput'));
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '.first', function(event) {
            if (event.handler !== true) {
                $('#pagAtual').val(1);
                inputPagination($('#pagInput'));
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '.previous', function(event) {
            if (event.handler !== true) {
                if(parseInt($('#pagAtual').val()) - 1 <= 0) {
                    $('#pagAtual').val(1);
                } else {
                    $('#pagAtual').val(parseInt($('#pagAtual').val()) - 1);
                }
                inputPagination($('#pagInput'));
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '.next', function(event) {
            if (event.handler !== true) {
                if(parseInt($('#pagAtual').val()) + 1 >= parseInt($('#pagTotal').val())) {
                    $('#pagAtual').val($('#pagTotal').val());
                } else {
                    $('#pagAtual').val(parseInt($('#pagAtual').val()) + 1);
                }
                inputPagination($('#pagInput'));
                event.handler = true;
            }
            return false;
        });
        $(document).on('click', '.last', function(event) {
            if (event.handler !== true) {
                $('#pagAtual').val($('#pagTotal').val());
                inputPagination($('#pagInput'));
                event.handler = true;
            }
            return false;
        });
    });
    $(document).on('mouseover', '#conteudo', function(event) {
        if (event.handler !== true) {
            $('#menu').multilevelpushmenu('collapse');
            event.handler = true;
        }
    });
    $('#menu ul li a').on('click', function() {
        if ($(this).attr('href') != '#') {
            if ($(this).attr('href') != 'movimentos') {
                if ($(this).attr('href') == 'email') {
                    carregaMail(BrowserDetect);
                } else {
                    paraRefresh();
					paraGanhoPerda();
                    if ($(this).attr('href') == "acoes") {
                        refresh();
						/*
						$.ajax({
                            type: "POST",
                            url: "functions/funcoes_banco.php",
                            dataType: "json",
                            data: "tipo=em_update",
                            success: function(dados) {
                                if (dados.permitido === false) {
                                    $('#var_content').load('conteudo_banco.php #carteira_titulos', function(){
                                        hideLoading();
                                        $('#btnVoltarAcoes').click();
										if ($('table[name="tblCarteiraAcoes"] tr').length > 1) {
                                            $('#tblCarteiraAcoesVazia').hide();
                                            $('table[name="tblCarteiraAcoes"]').show();
                                        } else {
                                            $('table[name="tblCarteiraAcoes"]').hide();
                                            $('#tblCarteiraAcoesVazia').show();
                                        }
                                        $.notify.create("Indisponivel de momento. Estamos em atualizações. Prometemos ser breves. Obrigado pela compreensão.", {sticky: false, type: 'warning', style: 'bar', adjustContent: false});
                                        $(this).notify("show");
                                    });
                                }
                                else
                                    refresh();
                            }
                        });
						/* */
					
					} /* */ else {
                        paraRefresh();
                    }
					/* */
					
					/*
					if ($(this).attr('href') == "carteira_titulos") {
						$.ajax({
                            type: "POST",
                            url: "functions/funcoes_banco.php",
                            dataType: "json",
                            data: "tipo=em_update",
                            success: function(dados) {
                                if (dados.permitido === false) {
                                    $('#var_content').load('conteudo_banco.php #acoes', function(){
                                        // refresh();
										$('#frmOrdemCompra').hide();
										hideLoading();
                                        $.notify.create("Em atualizações. Prometemos ser breves. Obrigado pela compreensão.", {sticky: false, type: 'warning', style: 'bar', adjustContent: false});
                                        $(this).notify("show");
                                    });
                                }
                            }
                        });
					}
					/* */
                    $('#var_content').load('conteudo_banco.php #' + $(this).attr('href'), function() {
                        hideError();
                        hideLoading();
                        $('#btnEmprestimo').hide();
                        $('#btnLeasing').hide();
                        $('#btnPagarDecRet').hide();
                        $('#btnPagarEntrega').hide();
                        $('#btnPagarFatura').hide();
                        $('#btnPagarPrestacoes').hide();
                        $('#btnVoltarCredito').hide();
                        $('#btnVoltarLeasing').hide();
                        $('#btnVoltarAcoes').closest('div').hide();
                        $('#tblTransferencias').hide();
                        $('#btnTransf').closest('.linha10').hide();
                        $('#divVenda').hide();
                        $('#frmOrdemCompra').hide();
                        $('#frmVendaAcoes').hide();
                        $('#radOthersGroup').hide();
                        $('#tblAcoesDetalhes').hide();
                        $('#tblCreditoDetail').hide();
                        $('#tblDecRet').hide();
                        $('#tblDiversos').hide();
                        $('#tblFaturas').hide();
                        $('#tblLeasing').hide();
                        $('#tblLeasingDetail').hide();
                        $('#tblSimCre').hide();
                        $('#ordem_venda').hide();
                        
                        //-- Conta a Prazo
                        $('#subtituloDP').hide();
                        $('#MontanteDP').hide();
                        $('#txJuroDP').hide();
                        $('#txIRCDP').hide();
                        $('#slcPrazoDP').hide();
                        $('#TotalDP').hide();
                        $('#btnAddDP').hide();
                        $('#contrato_contaprazo').hide();
                        $('#contrato_rencontaprazo').hide();
                        $('#contrato_termcontaprazo').hide();
                        //$('#btnCriarCPrazo').closest('.linha').hide();
                        $('#tblDepositoPrazo').hide();
                        
                        //-- Letras
                        $('#letClienteExt').hide();
                        
                        //-- Recebimentos
                        $('#limiteAdiantamento').hide();
                        $('#clienteAdiantamento').hide();
                        if ($('#tblAdiantReceb tr').length > 1) {
                            $('#tblAdiantRecebVazia').hide();
                        } else {
                            $('#tblAdiantReceb').hide();
                        }
                        
						/* MOVED to user.js
						if ($('#tblAdiantEfet tr').length > 1) {
                            $('#tblAdiantEfetVazia').hide();
                        } else {
                            $('#tblAdiantEfet').hide();
                        } */
                        
						//-- Ações agendadas
                        $('#acoesCompraAgendada').hide();
                        $('#acoesVendaAgendada').hide();
                        
                        if ($('#tblCompraDetail tr').length > 1) {
                            $('#tblCompraDetailVazia').hide();
                        } else {
                            $('#tblCompraDetail').hide();
                        }
                        
                        if ($('#tblVendaDetail tr').length > 1) {
                            $('#tblVendaDetailVazia').hide();
                        } else {
                            $('#tblVendaDetail').hide();
                        }
                        //--
                        if ($('#tblPrestacoes tr').length > 1) {
                            $('#tblPrestacoesVazia').hide();
                            $('#slcOrdenarPrestacoes').closest('.linha').show();
                            $('#tblPrestacoes').show();
                        } else {
                            $('#slcOrdenarPrestacoes').closest('.linha').hide();
                            $('#tblPrestacoes').hide();
                            $('#tblPrestacoesVazia').show();
                        }
                        /*
						if ($('#tblCarteiraAcoes tr').length > 1) {
                            $('#tblCarteiraAcoesVazia').hide();
                            $('#tblCarteiraAcoes').show();
                        } else {
                            $('#tblCarteiraAcoes').hide();
                            $('#tblCarteiraAcoesVazia').show();
                        }
						*/
						if ($('table[name="tblCarteiraAcoes"] tr').length > 1) {
                            $('#tblCarteiraAcoesVazia').hide();
                            $('table[name="tblCarteiraAcoes"]').show();
                        } else {
                            $('table[name="tblCarteiraAcoes"]').hide();
                            $('#tblCarteiraAcoesVazia').show();
                        }
                        if ($('#tblConsultarFact tr').length > 1) {
                            $('#tblConsultarFactVazia').hide();
                            $('#tblConsultarFact').show();
                        } else {
                            $('#tblConsultarFact').hide();
                            $('#tblConsultarFactVazia').show();
                        }
                        if ($('#tblTransfRec tr').length > 1) {
                            $('#tblTransfRecVazia').hide();
                            $('#tblTransfRec').show();
                        } else {
                            $('#tblTransfRec').hide();
                            $('#tblTransfRecVazia').show();
                        }
                        if ($('#tblCreditos tr').length > 1) {
                            $('#tblCreditosVazia').hide();
                            $('#tblCreditos').show();
                        } else {
                            $('#tblCreditos').hide();
                            $('#tblCreditosVazia').show();
                        }
                        if ($('#tblConsLeas tr').length > 1) {
                            $('#tblConsLeasVazia').hide();
                            $('#tblConsLeas').show();
                        } else {
                            $('#tblConsLeas').hide();
                            $('#tblConsLeasVazia').show();
                        }
                        if ($('#tblFactoring tr').length > 2) {
                            $('#tblFactoring').show();
                            $('#btnEfetuarFactoring').closest('.linha').show();
                        } else {
                            $('#btnEfetuarFactoring').closest('.linha').hide();
                            $('#tblFactoring').hide();
                        }
                        //
                        if ($('#tblLetras tr').length > 1) {
                            $('#tblLetraVazia').hide();
                        }
                        else {
                            $('#tblLetras').hide();
                            $('#btnAceitaLetra').hide();
                        }
                        if ($('#tblCarteiraLetras tr').length > 1) {
                            $('#tblCarteiraLetraVazia').hide();
                        }
                        else {
                            $('#tblCarteiraLetras').hide();
                        }
                        //
                        acoesMask();
                        floatMask();
                        intMask();
                        contaMask();
                        var data_op = getVirtualDate();
                        var temp = data_op.toString().split(' ');
                        var data_op_v = new Date(temp[0]);
                        var data_completa = new Date(data_op);
                        $('#txtDataVirtTransf').val(('0' + data_op_v.getDate()).slice(-2) + '-' + (('0' + (data_op_v.getMonth() + 1)).slice(-2)) + '-' + data_op_v.getFullYear());
                        $('#txtDataLeas').val(('0' + data_completa.getDate()).slice(-2) + '-' + (('0' + (data_completa.getMonth() + 1)).slice(-2)) + '-' + data_completa.getFullYear());
                        $('#txtDataCre').val(('0' + data_completa.getDate()).slice(-2) + '-' + (('0' + (data_completa.getMonth() + 1)).slice(-2)) + '-' + data_completa.getFullYear());
                        $('.campoData').datetimepicker({
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
                            timepicker: false,
                            format: 'd-m-Y'
                        });
                        $(document).on('keyup', '.conta', function(event) {
                            var focusables = $('.inputsConta').find("input");
                            var maxchar = false;
                            var minchar = false;
                            var current;
                            if ($(this).attr("maxlength")) {
                                if ($(this).val().toString().split('_').join("").length >= $(this).attr("maxlength")) {
                                    maxchar = true;
                                } else if ($(this).val().toString().split('_').join("").length == "0") {
                                    minchar = true;
                                }
                            }
                            if (event.keyCode == 13 || maxchar) {
                                current = focusables.index(this),
                                next = focusables.eq(current + 1).length ? focusables.eq(current + 1) : focusables.eq(this);
                                next.focus();
                            }
                            if (minchar) {
                                current = focusables.index(this),
                                previous = (current == "0") ? focusables.eq(current) : focusables.eq(current - 1);
                                previous.focus();
                            }
                        });
                        $(document).on('click', '#chkAllFat', function(event) {
                            if (event.handler !== true) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $('input[class="chk"]').prop('checked', false);
                                    $('#btnPagarFatura').hide();
                                } else {
                                    $('input[class="chk"]').prop('checked', true);
                                }
                                event.handler = true;
                            }
                            return false;
                        });

                        $(document).on('click', '#chkAllDiv', function(event) {
                            if (event.handler !== true) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $('input[class="chk"]').prop('checked', false);
                                    $('#btnPagarEntrega').hide();
                                } else {
                                    $('input[class="chk"]').prop('checked', true);
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '#chkAllDecRet', function(event) {
                            if (event.handler !== true) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $('input[class="chk"]').prop('checked', false);
                                    $('#btnPagarDecRet').hide();
                                } else {
                                    $('input[class="chk"]').prop('checked', true);
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '#chkAllPrest', function(event) {
                            if (event.handler !== true) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $('input[class="chk"]').prop('checked', false);
                                    $('#btnPagarPrestacoes').hide();
                                } else {
                                    $('input[class="chk"]').prop('checked', true);
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '#chkAllTransf', function(event) {
                            if (event.handler !== true) {
                                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                    $('#tblTransferencias').find('input[type=checkbox].chk').prop('checked', false);
                                } else {
                                    $('#tblTransferencias').find('input[class="chk"].chk').prop('checked', true);
                                }
                                event.handler = true;
                            }
                        });

                        $(document).on('click', '.comprarAcao', function(event) {
                            if (event.handler !== true) {
                                var form = $('#frmOrdemCompra');
                                var nome = $(this).closest('tr').children('td').eq(0).text();
                                var id_pais = $('#slcPaisAcao').val();
								// var preco = parseFloat(formatValor($(this).closest('tr').children('td').eq(1).text())).toFixed(2).replace('.', ',');
								var preco = parseFloat(formatValor($(this).closest('tr').children('td').eq(2).text())).toFixed(3).replace('.', ',');
                                var data_virtual = getVirtualDate();
                                var data_temp = data_virtual.toString().split(' ');
                                var data_oc = new Date(data_temp[0]);
                                form.find('input[name="txtNomeAcao"]').val(nome);
                                form.find('input[name="txtPrecoAcao"]').val(preco);
                                form.find('input[name="txtDataAcao"]').val(('0' + data_oc.getDate()).slice(-2) + '-' + (('0' + (data_oc.getMonth() + 1)).slice(-2)) + '-' + data_oc.getFullYear());
                                form.find('input[name="txtDataCompletaAcao"]').val(data_virtual);
                                if ($('#frmOrdemCompra:hidden').find('input[name="txtQtdAcao"]').val() != "0") {
                                    var subtotal = formatValor(form.find('input[name="txtPrecoAcao"]').val()) * formatValor(form.find('input[name="txtQtdAcao"]').val());
                                    var dataString = "id_pais=" + id_pais + "&subtotal=" + subtotal + "&trans=compra" + "&tipo=acao_taxas";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblAcoes').hide();
                                            $('.minilogo').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            $('#tblAcoes').show();
                                            $('.minilogo').show();
                                            if (dados.sucesso === true) {
                                                form.find('input[name="hddEncargoAcao"]').val(dados.encargo);
                                                form.find('input[name="hddISAcao"]').val(dados.is);
                                                form.find('input[name="txtSubtotalAcao"]').val(number_format(subtotal, 2, ',', '.'));
                                                form.find('input[name="txtTotalAcao"]').val(number_format(dados.total, 2, ',', '.'));
												form.find('input[name="txtMoeda"]').val(dados.simbolo);
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    form.find('input[name="txtSubtotalAcao"]').val('0,00');
                                    form.find('input[name="txtTotalAcao"]').val('0,00');
                                }
                                $('#frmOrdemCompra').show(1000);
                                event.handler = true;
                            }
                        });
                        $(document).on('click', 'input[name="btnVoltar"]', function(event) {
                            if (event.handler !== true) {
                                $('#frmOrdemCompra').hide();
                                $('#ordem_venda').hide();
                                $('#tblAcoes').show();
                                event.handler = true;
                            }
                        });
                        $(document).on('keyup', 'input[name="txtQtdAcao"], #txtPrecoAlvoAcaoComprar', function(event) {
                            if (event.handler !== true) {
                                var form = $('#frmOrdemCompra');
                                if ($(this).val() != "0") {
                                    setTimeout(function() {
                                        // var nome = $('#frmOrdemCompra').find('input[name="txtNomeAcao"]').val();
                                        var id_pais = $('#slcPaisAcao').val();
                                        var subtotal;
                                        if ($('#chkOrdemAcoes').prop('checked'))
                                            subtotal = parseFloat(formatValor(form.find('input[name="txtPrecoAcao"]').val())) * parseFloat(formatValor(form.find('input[name="txtQtdAcao"]').val()));
                                        else
                                            subtotal = parseFloat(formatValor(form.find('input[name="txtPrecoAlvoAcao"]').val())) * parseFloat(formatValor(form.find('input[name="txtQtdAcao"]').val()));
                                        
                                        // var dataString = "nome_acao=" + nome + "&subtotal=" + subtotal + "&tipo=acao_taxas";
                                        var dataString = "id_pais=" + id_pais + "&subtotal=" + subtotal + "&trans=compra" + "&tipo=acao_taxas";
                                    
                                        $.ajax({
                                            type: "POST",
                                            url: "functions/funcoes_banco.php",
                                            data: dataString,
                                            dataType: "json",
                                            success: function(dados) {
                                                if (dados.sucesso === true) {
                                                    form.find('input[name="hddEncargoAcao"]').val(dados.encargo);
                                                    form.find('input[name="hddISAcao"]').val(dados.is);
                                                    form.find('input[name="txtSubtotalAcao"]').val(number_format(subtotal, 2, ',', '.'));
                                                    form.find('input[name="txtTotalAcao"]').val(number_format(dados.total, 2, ',', '.'));
                                                    form.find('input[name="txtMoeda"]').val(dados.simbolo);
                                                } else {
                                                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                    $("body").animate({
                                                        scrollTop: 0
                                                    });
                                                }
                                            }
                                        });
                                    }, 1000); 
                                
                                } else {
                                    form.find('input[name="txtSubtotalAcao"]').val('0,00');
                                    form.find('input[name="txtTotalAcao"]').val('0,00');
                                }
                                event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnComprar', function(event) {
                            if (event.handler !== true) {
                                var form = $('#frmOrdemCompra');
                                if (form.find('input[name="txtQtdAcao"]').val() === "" || form.find('input[name="txtQtdAcao"]').val() == "0") {
                                    $('.error').show().html('<span id="error">Insira uma quantidade válida</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
								else if (formatValor(form.find('input[name="txtPrecoAcao"]').val()) == 0) {
									$('.error').show().html('<span id="error">As ações escolhidas têm um preço demasiado baixo</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
								}
                                else {
                                    setTimeout(function() {
                                        var nome = form.find('input[name="txtNomeAcao"]').val();
                                        var id_pais = $('#slcPaisAcao').val();
                                        
                                        var preco;
                                        var curDate = new Date();
										var data;
                                        var imediato;
                                        
										var quantidade = formatValor(form.find('input[name="txtQtdAcao"]').val());
                                        if ($('#chkOrdemAcoes').prop('checked')) {
                                            preco = formatValor(form.find('input[name="txtPrecoAcao"]').val());
                                            data = form.find('input[name="txtDataCompletaAcao"]').val();
                                            imediato = true;
                                        } else {
                                            preco = formatValor(form.find('input[name="txtPrecoAlvoAcao"]').val());
                                            data = form.find('input[name="txtDataLimiteAcao"]').val();
                                            imediato = false;
                                        }
                                        
                                        // var subtotal = formatValor(form.find('input[name="txtSubtotalAcao"]').val());
										var subtotal = parseFloat(quantidade) * parseFloat(preco);										
										var encargo = parseFloat(form.find('input[name="hddEncargoAcao"]').val());
                                        var is = parseFloat(form.find('input[name="hddISAcao"]').val());
                                        // var total = formatValor(form.find('input[name="txtTotalAcao"]').val());
										var total = subtotal + encargo + is;
										
										var dataString = "txtNome=" + nome + "&id_pais=" + id_pais + "&txtPreco=" + preco + "&txtDataCompleta=" + data + "&txtQuantidade=" + quantidade + "&txtSubtotal=" + subtotal + "&txtTotal=" + total + "&hddEncargo=" + encargo + "&hddIS=" + is + "&imediato=" + imediato + "&tipo=comprar_acoes";
                                        if (imediato == false && new Date(data) < curDate) {
											$('.error').show().html('<span id="error"> Por favor, escolha uma data superior à atual </span>');
											$("body").animate({
												scrollTop: 0
											});
										}
										/* else if (parseFloat(subtotal) - parseFloat(preco) * parseFloat(quantidade) > 0.1) {
											$('.error').show().html('<span id="error">Por favor, preencha o formulário novamente</span>');
											$("body").animate({
												scrollTop: 0
											});
										} */
										else {
											$.ajax({
												type: "POST",
												url: "functions/funcoes_banco.php",
												data: dataString,
												dataType: "json",
												beforeSend: function() {
													$('#tblAcoes').hide();
													$('#frmOrdemCompra').hide();
													showLoading();
												},
												success: function(dados) {
													hideLoading();
													$('#tblAcoes').show();
													$('#frmOrdemCompra').show();
													if (dados.sucesso === true) {
														$('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + dados.moeda);
														$('#frmOrdemCompra').hide(1000);
														if (imediato == false) {
															$('#tblAcoes').hide();
															$('#var_content').load('conteudo_banco.php #compras_agendadas', function(){
																$('.loading').hide();
																$('#tblCompraDetailVazia').hide();
															});
														}
													} else {
														$('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
														$("body").animate({
															scrollTop: 0
														});
													}
												}
											});
										}
                                    }, 1000);
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '.control', function(event) {
                            if (event.handler !== true) {
                                /* ELIMINAR nome_pais DEPOIS DE ALTERAÇÃO PARA DADOS CACHE
								var nome_pais = $(this).closest('tr').children('td').eq(0).text();
								/* */
								
                                /* */
								var id_acao = $(this).closest('td').children('input').val();
								var nome = $(this).closest('tr').children('td').eq(2).text();
								$.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: "id_acao=" + id_acao + "&nome=" + nome + "&tipo=acoes_especifico",
                                    dataType: "json",
                                    beforeSend: function() {
                                        // $('#tblCarteiraAcoes').hide();
                                        $('.espacamento').hide();
                                        $('table[name="tblCarteiraAcoes"]').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        $('#tblAcoesDetalhes').show();
                                        $('#btnVoltarAcoes').closest('div').show();
                                        emptyTable('#tblAcoesDetalhes');
                                        if (dados.sucesso === true) {
                                            $.each(dados.dados_in, function(i, item) {
                                                var date = new Date(dados.dados_in[i].data);
                                                $('#tblAcoesDetalhes').append('<tr>' +
                                                    '<td class="inputAccao">' + dados.dados_in[i].nome + 
                                                        '<input id="hddIdPais" type="hidden" value="' + dados.dados_in[i].id_pais + '">' +
                                                        '<input id="hddIdAcao" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                    '</td>' + 
//                                                    '<td class="inputAccao">' + number_format('10', 3, ',', '.') + '<input id="hddMoeda" type="hidden" value="' + dados.dados_in[i].simbolo + '"></td>' + //preço de compra para testes
                                                    '<td class="inputAccao">' + number_format(dados.dados_in[i].preco, 3, ',', '.') + '<input id="hddMoeda" type="hidden" value="' + dados.dados_in[i].simbolo + '"></td>' + //preço de compra 
                                                    '<td class="inputAccao">' + (('0' + date.getDate()).slice(-2)) + '-' + (('0' + (date.getMonth() + 1)).slice(-2)) + '-' + date.getFullYear() + '</td>' +
                                                    '<td class="inputAccao">' + number_format(dados.dados_in[i].quantidade, 0, ',', '.') + '</td>' +
                                                    '<td class="inputAccao">' + number_format(dados.dados_in[i].total, 2, ',', '.') + '</td>' +
                                                    '<td class="inputAccao"></td>' +
                                                    '<td class="inputAccao"></td>' +
                                                    '<td style="background-color: #77a4d7; padding: 2px; cursor: pointer;"><div id="btnVenderAcao_' + i + '" name="btnVenderAcao" class="labelicon icon-mny btnVender"></div></td>' +
                                                    '</tr>');
                                            });
                                        } else {
                                            $('.erro').show().html('<span id="error">' + dados.mensagem + '</span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }
                                    }
                                });
                                ganhoPerda(nome);
								// ganhoPerda(nome_pais, nome);
                                $('.control').ajaxError(function() {
                                    ganhoPerda(nome);
									// ganhoPerda(nome_pais, nome);
                                });
								/* */
                                
								/* * /
								$.notify.create("Indisponivel de momento. Estamos em atualizações. Obrigado pela compreensão. Prometemos ser breves.", {sticky: false, type: 'warning', style: 'bar', adjustContent: false});
								$(this).notify("show");
								});
								/* */
								
								event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnVoltarAcoes', function(event) {
                            if (event.handler !== true) {
                                paraGanhoPerda();
								$('#tblAcoesDetalhes').hide();
                                $(this).closest('div').hide();
                                $('#frmVendaAcoes').hide();
                                $('.espacamento').show();
                                $('table[name="tblCarteiraAcoes"]').show();
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '.btnVender', function(event) {
                            if (event.handler !== true) {
                                var form = $('#frmVendaAcoes');
                                form.find('#txtPrecoVendaAcoes').val('');
                                form.find('#txtQtdVendaAcoes').val('');
                                var id_pais = $(this).closest('tr').children('td').eq(0).children('input:first').val();
                                var id = $(this).closest('tr').children('td').eq(0).children('input:last').val();
                                var nome = $(this).closest('tr').children('td').eq(0).text();
                                var preco = $(this).closest('tr').children('td').eq(1).text(); //preco de compra
                                var moeda = $(this).closest('tr').children('td').eq(1).children('input').val();
                                var qtd = $(this).closest('tr').children('td').eq(3).text();
                                //----meti esta-----//
                                var preco_atual = $(this).closest('tr').children('td').eq(5).text();
                                var preco_atual_format = formatValor(preco_atual);
                                var preco_format=formatValor(preco);
                                var preco_percentagem=1-(preco_format/preco_atual_format);
                                
                               if(preco_percentagem>0.15){ // caso o preço de compra se ja muito inferior ao preco atual aparaceesta mensagem
//                                   alert('Não é possivel efetuar a venda operação em análise');
                                    //inserir alertas na base de dados (INICIO)------------
                                    $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: "&id_acao_trans=" + id + "&nome=" + nome + "&precoCompra=" + preco + "&quantidade=" + qtd + "&precoAtual=" + preco_atual  + "&tipo=acoes_alerta",
                                    dataType: "json",
//                                    success: function(dados) {
//                                        if (dados.sucesso === true) {
//                                           
//                                        } else {
//                                            $('.erro').show().html('<span id="error">' + dados.mensagem + '</span>');
//                                            $("body").animate({
//                                                scrollTop: 0
//                                            });
//                                        }
//                                    }
                                });
                                    //inserir alertas na base de dados (FIM)---------------
                                    $('.error').show().html('<span id="error">' + 'Não é possivel efetuar a venda, operação em análise'+ '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                   event.handler = true;
                               }else{
                                   form.find('#hddIdPaisAcao').val(id_pais);
                                form.find('#hddIdAcao').val(id);
                                form.find('#txtNomeAcao').val(nome);
                                form.find('#txtPrecoCompraAcao').val(preco);
                                form.find('input[name="txtMoeda"]').val(moeda);
                                form.find('#txtQtdAcoesCompradas').val(qtd);
                                form.find('#txtTotalVendaAcoes').val('0,00');
                                form.show(1000);
                                event.handler = true;
                               }                       
                                //--------------//
//                                form.find('#hddIdPaisAcao').val(id_pais);
//                                form.find('#hddIdAcao').val(id);
//                                form.find('#txtNomeAcao').val(nome);
//                                form.find('#txtPrecoCompraAcao').val(preco);
//                                form.find('input[name="txtMoeda"]').val(moeda);
//                                form.find('#txtQtdAcoesCompradas').val(qtd);
//                                form.find('#txtTotalVendaAcoes').val('0,00');
//                                form.show(1000);
//                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('keyup', '#txtPrecoVendaAcoes, #txtPrecoAlvoAcaoVender, #txtQtdVendaAcoes', function(event) {
                            if (event.handler !== true) {
                                var form = $('#frmVendaAcoes');
                                if (form.find('#txtPrecoVendaAcoes').val() != 0 && form.find('#txtQtdVendaAcoes').val() != 0) {
                                    setTimeout(function() {
                                        var id_pais = form.find('#hddIdPaisAcao').val();
                                        // var total = formatValor(form.find('#txtPrecoVendaAcoes').val()) * formatValor(form.find('#txtQtdVendaAcoes').val());
                                        var total;
                                        if ($('#chkOrdemAcoesVender').prop('checked'))
                                            total = parseFloat(formatValor(form.find('#txtPrecoVendaAcoes').val())) * parseFloat(formatValor(form.find('#txtQtdVendaAcoes').val()));
                                        else
                                            total = parseFloat(formatValor(form.find('#txtPrecoAlvoAcaoVender').val())) * parseFloat(formatValor(form.find('#txtQtdVendaAcoes').val()));
				
                                        var dataString = "id_pais=" + id_pais + "&subtotal=" + total + "&trans=venda" + "&tipo=acao_taxas";
                                        $.ajax({
                                            type: "POST",
                                            url: "functions/funcoes_banco.php",
                                            data: dataString,
                                            dataType: "json",
                                            success: function(dados) {
                                                if (dados.sucesso === true) {
                                                    form.find('input[name="hddEncargoAcao"]').val(dados.encargo);
                                                    form.find('input[name="hddISAcao"]').val(dados.is);
                                                    // form.find('#txtTotalVendaAcoes').val(number_format(total, 2, ',', '.'));
													form.find('#txtTotalVendaAcoes').val(number_format(dados.total, 2, ',', '.'));
                                                } else {
                                                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                    $("body").animate({
                                                        scrollTop: 0
                                                    });
                                                }
                                            }
                                        });
                                    }, 1000);
                                } else {
                                    $('#txtTotalVendaAcoes').val('0,00');
                                }
                                event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnVenderAcoes', function(event) {
                            if (event.handler !== true) {
                                var form = $('#frmVendaAcoes');
								var p_venda;
								var curDate = new Date();
								var data_v;
								var imediato;
								
								var qtd_venda = formatValor(form.find('#txtQtdVendaAcoes').val());
								var qtd_comprada = formatValor(form.find('#txtQtdAcoesCompradas').val());
								var max = formatValor(form.find('#txtPrecoAtualAcao').val());
								var id_pais = form.find('#hddIdPaisAcao').val();
								var nome = form.find('#txtNomeAcao').val();
								var encargo = form.find('input[name="hddEncargoAcao"]').val();
								var is = form.find('input[name="hddISAcao"]').val();
								
								if ($('#chkOrdemAcoesVender').prop('checked')) {
									p_venda = formatValor(form.find('#txtPrecoVendaAcoes').val());
									data_v = getVirtualDate();
									imediato = true;
								} else {
									p_venda = formatValor(form.find('#txtPrecoAlvoAcaoVender').val());
									data_v = form.find('input[name="txtDataLimiteAcao"]').val();
									imediato = false;    
								}
								
								// var total = formatValor(form.find('#txtTotalVendaAcoes').val());
                                var total = parseFloat(p_venda) * parseFloat(qtd_venda) - parseFloat(encargo) - parseFloat(is);
								
								if (imediato == false && new Date(data_v) < curDate) {
									$('.error').show().html('<span id="error"> Por favor, escolha uma data superior à atual </span>');
									$("body").animate({
										scrollTop: 0
									});
								} else if (validaVendaAccao(p_venda, qtd_venda, qtd_comprada, max, total, imediato) === true) {
									var id = form.find('#hddIdAcao').val();
									var dataString = "id=" + id + "&id_pais=" + id_pais + "&nome=" + nome + "&qtd_comprada=" + qtd_comprada + "&quantidade=" + qtd_venda + "&preco=" + p_venda + "&total=" + total + "&data=" + data_v + "&hddEncargo=" + encargo + "&hddIS=" + is + "&imediato=" + imediato + "&tipo=acoes_vender";
									form.hide();
									$.ajax({
										type: "POST",
										url: "functions/funcoes_banco.php",
										data: dataString,
										dataType: "json",
										beforeSend: function() {
											// form.hide();
											$('#tblAcoesDetalhes').hide();
											$('#btnVoltarAcoes').closest('div').hide();
											showLoading();
										},
										success: function(dados) {
											hideLoading();
											form.show();
											$('#tblAcoesDetalhes').show();
											$('#btnVoltarAcoes').closest('div').show();
											if (dados.sucesso === true) {
												/* */
												if (imediato === true && dados.vazio === true) {
													if (typeof(dados.mensagem) == "undefined") {
														$('#btnVoltarAcoes').click();
														emptyTable('#tblCarteiraAcoes');
														// emptyTable('table[name="tblCarteiraAcoes"]');
														$.each(dados.dados_in, function() {
															$('#tblCarteiraAcoes').append('<tr>' +
																'<td>' + this.nome_pais + '</td>' +
																'<td>' + this.nome_bolsa + '</td>' +
																'<td>' + this.nome + '</td>' +
																'<td>' + this.total + '</td>' +
																'<td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">' +
																'<input id="hddIdAcao" name="hddIdAcao" type="hidden" value="' + this.id + '">' +
																'<div name="btnIdAcao" class="labelicon icon-info control"></div>' +
																'</td>' +
																'</tr>');
														});
													} else {
														$('#frmVendaAcoes').hide();
														$('#btnVoltarAcoes').click();
														$('#tblCarteiraAcoes').hide();
														$('#tblCarteiraAcoesVazia').show();
													}
												} else if (imediato === true && dados.vazio === false) {
													// $('#frmVendaAcoes').hide();
													$('#btnVoltarAcoes').click();
													
													emptyTable('table[name="tblCarteiraAcoes"]');
													$.each(dados.dados_in, function(i, item) {
														$('#carteira_titulos').find('#tblCarteiraAcoes_' + this.abrev_pais).append('<tr>' +
															'<td class="td35">' + this.nome_pais + '</td>' + 
															'<td class="td20">' + this.nome_bolsa + '</td>' +
															'<td class="td20">' + this.nome + '</td>' +
															'<td class="td20">' + this.total + '</td>' +
															'<td style="background-color: #77a4d7; padding: 2px; cursor: pointer;">' + 
																'<input id="hddIdAcao" name="hddIdAcao" type="hidden" value="' + this.id_acao + '">' +
																'<div name="btnIdAcao" class="labelicon icon-info control"></div>' +
															'</td>' +
														'</tr>');
													});
												}
												// /*
												else if (imediato === false) {
													/*
													$('#frmVendaAcoes').hide();
													$('#btnVoltarAcoes').click();
													$('#tblCarteiraAcoes').show();
													*/
													$('#frmVendaAcoes').hide();
													$('#btnVoltarAcoes').click();
													$('#tblCarteiraAcoes').hide();
													$('#var_content').load('conteudo_banco.php #vendas_agendadas', function(){
														$('.loading').hide();
														$('#tblVendaDetailVazia').hide();
													});
												}
												// /*
												
												$('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + " " + dados.moeda);
												/* */
												
												/*
												showLoading();
												$('#carteira_titulos').hide();
												location.reload();
												*/
											} else {
												$('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
												$("body").animate({
													scrollTop: 0
												});
											}
										}
									});
								} else {
									$('.error').show().html('<span id="error">' + data + '</span>');
									$("body").animate({
										scrollTop: 0
									});
								}
							event.handler = true;
                            }
                            return false;
                        });

                        $(document).on('click', '#btnTransf', function(event) {
                            if (event.handler !== true) {
                                var dados = {};
                                $('#tblTransferencias tr').each(function(key, value) {
                                    if (key != "0") {
                                        dados[key] = {};
                                        dados[key].conta_destino = $(this).children('td').eq(1).text();
                                        dados[key].montante = formatValor($(this).children('td').eq(2).text());
                                        dados[key].descricao = $(this).children('td').eq(3).text();
                                        dados[key].data_op = getVirtualDate();
                                        dados[key].empresahidden = $(this).children('td').eq(5).text();
                                    }
                                });
                                if (Object.size(dados) > 0) {
                                    var dataString = "dados=" + JSON.stringify(dados) + "&tipo=transf_banc";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblTransferencias').hide();
                                            $('#btnTransf').closest('.linha10').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            $('#tblTransferencias').show();
                                            $('#btnTransf').closest('.linha10').show();
                                            if (dados.sucesso === true) {
                                                emptyTable('#tblTransferencias');
                                                $('#tblTransferencias').hide();
                                                $('#btnTransf').closest('.linha10').hide();
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + " " + dados.moeda);
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    $('.error').show().html('<span id="error">Não existem transferências a realizar</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '#btnLimparTransf', function(event) {
                            if (event.handler !== true) {
                                $('input[name="txtContaDestino1"]').val("");
                                $('input[name="txtContaDestino2"]').val("");
                                $('input[name="txtContaDestino3"]').val("");
                                $('input[name="txtMontanteTransf"]').val("");
                                $('input[name="txtDescricaoTransf"]').val("");
                                event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnAddTransf', function(event) {
                            if (event.handler !== true) {
                                var next = $('#tblTransferencias tr').length - 1;
                                var cd1 = $('input[name="txtContaDestino1"]').val();
                                var cd2 = $('input[name="txtContaDestino2"]').val();
                                var cd3 = $('input[name="txtContaDestino3"]').val();
                                var conta = cd1 + cd2 + cd3;
                                var montante = formatValor($('input[name="txtMontanteTransf"]').val());
                                var descricao = $('input[name="txtDescricaoTransf"]').val().replace(/[^a-z0-9\s]/gi, '');
                                var data_op_v = $('input[name="txtDataVirtTransf"]').val();
                                var empresa = $('input[name="hddNomeEmpresa"]').val();
                                var id_empresa = $('input[name="hddIdEmpresa"]').val();
                                if ((validaTransf(cd1, cd2, cd3, montante)) === true) {
                                    $('#tblTransferencias').append('<tr>' +
                                        '<td>' +
                                        '<div class="checkbox">' +
                                        '<input id="chkTransf_' + (next + 1) + '" name="chkTransf" type="checkbox" class="chk" value="' + id_empresa + '">' +
                                        '<label for="chkTransf" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '</div>' +
                                        '</td>' +
                                        '<td class="inputAccao">' + conta + '</td>' +
                                        '<td class="inputAccao">' + number_format(montante, 2, ',', '.') + '</td>' +
                                        '<td class="inputAccao">' + descricao + '</td>' +
                                        '<td class="inputAccao">' + data_op_v + '</td>' +
                                        '<td class="inputAccao">' + empresa + '</td>' +
                                        '</tr>');
                                    $('input[name="txtContaDestino1"]').val("");
                                    $('input[name="txtContaDestino2"]').val("");
                                    $('input[name="txtContaDestino3"]').val("");
                                    $('input[name="txtMontanteTransf"]').val("");
                                    $('input[name="txtDescricaoTransf"]').val("");
                                    $('#tblTransferencias').show();
                                    $('#btnTransf').closest('.linha10').show();
                                } else {
                                    $('.error').show().html('<span id="error">' + data + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '#btnSimCre', function(event) {
                            if (event.handler !== true) {
                                var montante = formatValor($('#txtMontanteCre').val());
                                var prazo = $('#txtPrazoCre').val();
                                var taxa = $('#txtTaxaCre').val();
                                var per_paga = $('#slcPerPagaCre').val();
                                var carencia = $('#slcCarenciaCre').val();
                                var valor_string = $('#txtMontanteCre').closest('.dir70').children('div').eq(1).find('input').val();
                                var valor_max_string2 = valor_string.split(' - ');
                                var valor_min = valor_max_string2[0].split('[');
                                var valor_max = valor_max_string2[1].split(']');
                                emptyTable('#tblSimCre');
                                if (validarSimulacao(montante, prazo, carencia, per_paga, formatValor(valor_min[1]), formatValor(valor_max[0])) === true) {
                                    var dataString = "montante=" + montante + "&prazo=" + prazo + "&taxa=" + taxa + "&carencia=" + carencia + "&per_paga=" + per_paga + "&tipo=simulador_emprestimo";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#frmCredito').hide();
                                            $('#tblSimCre').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            $('#frmCredito').show();
                                            if (dados.sucesso === true) {
                                                $.each(dados.dados_in, function(i, item) {
                                                    var amortizacao = 0;
                                                    if (dados.dados_in[i].amortizacao > 0)
                                                        amortizacao = number_format(dados.dados_in[i].amortizacao, 2, ",", ".");
                                                    $('#tblSimCre').append('<tr>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + dados.dados_in[i].id + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].capital_divida, 2, ",", ".") + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].juros, 2, ",", ".") + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + /*number_format(dados.dados_in[i].amortizacao, 2, ",", ".")*/ amortizacao + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].prestacao, 2, ",", ".") + '</td>' +
                                                        '</tr>');
                                                });
                                                $('#tblSimCre').append('<tr>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff; font-weight: bold; letter-spacing: 2px;">Total</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">0</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.juros_total, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.amort_total, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.prestacao_total, 2, ",", ".") + '</td>' +
                                                    '</tr>');
                                                rdmsrTblCredito("frmCredito");
                                                $('#btnEmprestimo').show();
                                                $('#tblSimCre').show();
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    $('#tblSimCre').hide();
                                    $('#btnEmprestimo').hide();
                                    rdmsrTblCreditoRev("frmCredito");
                                    $('.error').show().html('<span id="error">' + data + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });

                        $(document).on('click', '#btnEmprestimo', function(event) {
                            if (event.handler !== true) {
                                var emprestimo = {};
                                var valorCre;
                                var tamTabela = $('#tblSimCre tr').length - 1;
                                $('#tblSimCre tr').each(function(key) {
                                    if (key > 0 && key < tamTabela) {
                                        if (key == 1) {
                                            valorCre = formatValor($(this).children('td').eq(1).text());
                                        }
                                        emprestimo[key] = {};
                                        emprestimo[key].txtNPer = $(this).children('td').eq(0).text();
                                        emprestimo[key].txtCapP = formatValor($(this).children('td').eq(1).text());
                                        emprestimo[key].txtJuros = formatValor($(this).children('td').eq(2).text());
                                        emprestimo[key].txtAmort = formatValor($(this).children('td').eq(3).text());
                                        emprestimo[key].txtPrestacao = formatValor($(this).children('td').eq(4).text());
                                    }
                                });
                                var dataString = "txtData=" + getVirtualDate() + "&emprestimo=" + JSON.stringify(emprestimo) + "&valorCre=" + valorCre + "&tipo=emprestimo";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    beforeSend: function() {
                                        $('#frmCredito').hide();
                                        $('#tblSimCre').hide();
                                        $('#btnEmprestimo').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        $('#frmCredito').show();
                                        if (dados.sucesso === true) {
                                            rdmsrTblCreditoRev("frmCredito");
                                            $('#txtPlafondCre').val(dados.plafond);
                                            $('#txtMontanteCre').val('');
                                            $('#txtPrazoCre').val('');
                                            $('#slcCarenciaCre option').eq(0).prop("selected", true);
                                            $('#slcPerPagaCre option').eq(0).prop("selected", true);
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
                        });
                        $(document).on('change', '#txtValorLeas', function(event) {
                            if (event.handler !== true) {
                                var leasing = formatValor($('#txtValorLeas').val());
                                var valor_res = formatValor($('#txtValResLeas').val());
                                var total = number_format((leasing * (valor_res / 100)), 2, ',', '.');
                                if (leasing === "") {
                                    $('#txtValorResLeas').val('');
                                } else {
                                    $('#txtValorResLeas').val(total);
                                }
                                event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnLeasingSim', function(event) {
                            if (event.handler !== true) {
                                var valor_leasing = formatValor($('#txtValorLeas').val());
                                var prazo_l = $('#txtPrazoLeas').val();
                                var valor_res = formatValor($('#txtValorResLeas').val());
                                var taxa_leas = formatValor($('#txtTaxaLeas').val());
                                var per_car = $('#slcCarenciaLeas').val();
                                var per_paga = $('#slcPerPagaLeas').val();
								
                                // var taxa_iva = $('#hddTaxaIvaLea').val();
								var taxa_iva = $('#slcTxIvaLeas').val();
								
                                var valor_string = $('#txtValorLeas').closest('.dir70').children('div').eq(1).find('input').val();
                                var valor_max_string2 = valor_string.split(' - ');
                                var valor_min = valor_max_string2[0].split('[');
                                var valor_max = valor_max_string2[1].split(']');
                                var descricao = $('#txtaDescBemLes').val();
                                emptyTable('#tblLeasing');
                                if (validarLeasing(valor_leasing, prazo_l, per_car, per_paga, descricao, formatValor(valor_min[1]), formatValor(valor_max[0])) === true) {
                                    var dataString = "valor_leasing=" + valor_leasing + "&prazo_leasing=" + prazo_l + "&taxa_leasing=" + taxa_leas + "&valor_res=" + valor_res + "&carencia_leasing=" + per_car + "&per_paga_leasing=" + per_paga + "&taxa_iva=" + taxa_iva + "&tipo=leasing";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#frmLeasing').hide();
                                            $('#tblLeasing').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            $('#frmLeasing').show();
                                            if (dados.sucesso === true) {
                                                $.each(dados.dados_in, function(i, item) {
                                                    var amortizacao = 0;
                                                    if (dados.dados_in[i].amortizacao > 0)
                                                        amortizacao = number_format(dados.dados_in[i].amortizacao, 2, ",", ".");
                                                    $('#tblLeasing').append('<tr>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + dados.dados_in[i].id + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].capital_pendente, 2, ",", ".") + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].juros, 2, ",", ".") + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + /*number_format(dados.dados_in[i].amortizacao, 2, ",", ".")*/ amortizacao + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].prestacao_s_iva, 2, ",", ".") + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].iva, 2, ",", ".") + '</td>' +
                                                        '<td style="padding: 2px; font-size: 8pt;">' + number_format(dados.dados_in[i].prestacao_c_iva, 2, ",", ".") + '</td>' +
                                                        '</tr>');
                                                });
                                                $('#tblLeasing').append('<tr>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff; font-weight: bold; letter-spacing: 2px;"">Total</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">0</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.juros_total, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.amort_total, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.prestacao_total_s_iva, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.iva_total, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.prestacao_total_c_iva, 2, ",", ".") + '</td>' +
                                                    '</tr>' +
                                                    '<tr>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">Valor residual</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.valor_residual, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.juros_residual, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.valor_residual, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.prestacao_residual, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.iva_residual, 2, ",", ".") + '</td>' +
                                                    '<td style="padding: 2px; font-size: 8pt; background-color: #2b6db9; color: #fff;">' + number_format(dados.prestacao_iva_residual, 2, ",", ".") + '</td>' +
                                                    '</tr>');
                                                rdmsrTblLeas("frmLeasing");
                                                $('#hddDescricaoLea').val(descricao);
                                                $('#tblLeasing').show();
                                                $('#btnLeasing').show();
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    $('#btnLeasing').hide();
                                    $('#tblLeasing').hide();
                                    rdmsrTblLeasRev("frmLeasing");
                                    $('.error').show().html('<span id="error">' + data + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '#btnLeasing', function(event) {
                            if (event.handler !== true) {
                                var leasing = {};
                                var valorLea;
                                var tamTabela = $('#tblLeasing tr').length - 2;
                                $.each($('#tblLeasing tr'), function(key, value) {
                                    if (key > 0 && key < tamTabela) {
                                        if (key == 1) {
                                            valorLea = $(this).children('td').eq(1).text();
                                        }
                                        leasing[key] = {};
                                        leasing[key].txtNPer = $(this).children('td').eq(0).text();
                                        leasing[key].txtCapPendente = formatValor($(this).children('td').eq(1).text());
                                        leasing[key].txtJurosT = formatValor($(this).children('td').eq(2).text());
                                        leasing[key].txtAmortizacao = formatValor($(this).children('td').eq(3).text());
                                        leasing[key].txtPSIVA = formatValor($(this).children('td').eq(4).text());
                                        leasing[key].txtIVA = formatValor($(this).children('td').eq(5).text());
                                        leasing[key].txtPCIVA = formatValor($(this).children('td').eq(6).text());
                                    }
                                });
                                var descricao = $('#hddDescricaoLea').val();
                                var dataString = "txtData=" + getVirtualDate() + "&leasing=" + JSON.stringify(leasing) + "&valorLea=" + valorLea + "&descricao=" + descricao + "&tipo=pedido_leasing";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    beforeSend: function() {
                                        $('#frmLeasing').hide();
                                        $('#tblLeasing').hide();
                                        $('#btnLeasing').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        $('#frmLeasing').show();
                                        if (dados.sucesso === true) {
                                            rdmsrTblLeasRev("frmLeasing");
                                            $('#txtValorLeas').val('');
                                            $('#txtPrazoLeas').val('');
                                            $('#txtValorResLeas').val('');
                                            $('#txtaDescBemLes').val('');
                                            $('#slcCarenciaLeas option').eq(0).prop("selected", true);
                                            $('#slcPerPagaLeas option').eq(0).prop("selected", true);
                                            var leas = dados.leas;
                                            var win = window.open("./impressao/leasing.php?leas=" + leas);
                                            win.focus();
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
                        });
                        $(document).on('change', '#chkRecursoFact', function(event) {
                            if (event.handler !== true) {
                                var comissao = $('#hddComissaoFact').val();
                                var juro;
                                if ($('#chkRecursoFact').prop('checked')) {
                                    juro = $('#hddJuroCRFact').val();
                                    $('#txtSeguroFact').val('');
                                } else {
                                    juro = $('#hddJuroSRFact').val();
                                    var seguro = $('#hddSeguroFact').val();
                                    $('#txtSeguroFact').val(number_format(seguro, 2, ',', '.'));
                                }
                                $('#txtJurosFact').val(number_format(juro, 2, ',', '.'));
                                $('#txtComissaoFact').val(number_format(comissao, 2, ',', '.'));
                                event.handler = true;
                            }
                        });
                        $(document).on('change', '#slcNumFatFact', function(event) {
                            if (event.handler !== true) {
                                var id = $(this).val();
                                if (id == "0") {
                                    $('#txtClienteFact').val('');
                                    $('#txtDataFact').val('');
                                    $('#txtValorFact').val('');
                                    $('#txtComissaoFact').val('');
                                    $('#txtJurosFact').val('');
                                    $('#txtSeguroFact').val('');
                                } else {
                                    var dataString = 'id=' + id + "&tipo=factoring_dados";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            $('#txtClienteFact').val(dados.nome);
                                            $('#txtDataFact').val(dados.data);
                                            $('#txtValorFact').val(number_format(dados.valor, 2, ',', '.'));
                                            var comissao = $('#hddComissaoFact').val();
                                            var juro;
                                            if ($('#chkRecursoFact').prop('checked')) {
                                                juro = $('#hddJuroCRFact').val();
                                                $('#txtSeguroFact').val('');
                                            } else {
                                                juro = $('#hddJuroSRFact').val();
                                                var seguro = $('#hddSeguroFact').val();
                                                $('#txtSeguroFact').val(number_format(seguro, 2, ',', '.'));
                                            }
                                            $('#txtJurosFact').val(number_format(juro, 2, ',', '.'));
                                            $('#txtComissaoFact').val(number_format(comissao, 2, ',', '.'));
                                        }
                                    });
                                }
                                event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnAddFact', function(event) {
                            if (event.handler !== true) {
                                var id = $('#slcNumFatFact option:selected').val();
                                var plafond_temp = $('#txtPlafondFact').val().split(' ');
                                var plafond = parseFloat(formatValor(plafond_temp[0]));
                                if (id != "0") {
                                    var valor = parseFloat(formatValor($('#txtValorFact').val()));
                                    if (plafond >= valor) {
                                        var tamTabela = $('#tblFactoring tr').length - 1;
                                        var valido = true;
                                        $.each($('#tblFactoring tr'), function(key, value) {
                                            if (key > 0 && key < tamTabela) {
                                                if ($(this).children('td').eq(1).text() == $('#slcNumFatFact option:selected').text()) {
                                                    valido = false;
                                                }
                                            }
                                        });
                                        if (valido === true) {
                                            var tot_valor = 0.00;
                                            var tot_comissao = 0.00;
                                            var tot_juros = 0.00;
                                            var tot_seguro = 0.00;
                                            var n_fat_id = $('#slcNumFatFact').val();
                                            var n_fat = $('#slcNumFatFact option:selected').text();
                                            var cliente = $('#txtClienteFact').val();
                                            var date = $('#txtDataFact').val();
                                            var tempo = $('#slcTempoFact').val();
                                            var recurso = $('#chkRecursoFact').prop('checked');
                                            var comissao = formatValor($('#txtComissaoFact').val());
                                            var juro_anual = formatValor($('#txtJurosFact').val());
                                            var seguro = formatValor($('#txtSeguroFact').val());
                                            var juro_mensal = (Math.pow(1 + juro_anual / 100, 1 / 12)) - 1;
                                            var comissao_eur = (comissao / 100) * valor;
                                            var juro_eur = juro_mensal * valor * tempo;
                                            var seguro_eur;
                                            if (seguro !== "") {
                                                seguro_eur = (seguro / 100) * valor;
                                            } else {
                                                seguro_eur = 0.00;
                                            }
                                            if (recurso === true) {
                                                recurso = "checked";
                                            } else {
                                                recurso = "";
                                            }
                                            if ((validaFactoring(n_fat_id, tempo)) === true) {
                                                var data_op = getVirtualDate();
                                                var temp = data_op.toString().split(' ');
                                                var data_op_sh = new Date(temp[0]);
                                                var valores = [];
                                                if (recurso == "checked") {
                                                    $('#chkAllFact').prop('checked', true);
                                                    $('input[name="txtRecursoFact"]').prop('checked', true);
                                                } else {
                                                    $('#chkAllFact').prop('checked', false);
                                                    $('input[name="txtRecursoFact"]').prop('checked', false);
                                                }
                                                $('<tr>' +
                                                    '<td style="background-color: transparent;"></td>' +
                                                    '<td style="padding: 2px;">' + n_fat + '</td>' +
                                                    '<td style="padding: 2px;">' + cliente + '</td>' +
                                                    '<td style="padding: 2px;">' + date + '</td>' +
                                                    '<td style="padding: 2px;">' + number_format(valor, 2, ',', '.') + '</td>' +
                                                    '<td style="padding: 2px;">' + tempo + '</td>' +
                                                    '<td style="padding: 2px;"><div class="checkbox"><input id="txtRecursoFact" name="txtRecursoFact" type="checkbox" class="chk" ' + recurso + '><label for="txtRecursoFact" class="label_chk" style="padding-left: 0;">&nbsp;</label></div></td>' +
                                                    '<td style="padding: 2px;">' + number_format(comissao_eur, 2, ',', '.') + '</td>' +
                                                    '<td style="padding: 2px;">' + number_format(juro_eur, 2, ',', '.') + '</td>' +
                                                    '<td style="padding: 2px;">' + number_format(seguro_eur, 2, ',', '.') + '</td>' +
                                                    '<td style="cursor: pointer; background-color: #77a4d7; padding: 0;"><div class="labelicon icon-garbage apagarLinhaFactoring"></div></td>' +
                                                    '<input id="hddIdFatura" name="hddIdFatura" type="hidden" readonly="readonly" value="' + n_fat_id + '">' +
                                                    '</tr>').insertBefore("#tblFactoring tr:last-child");
                                                if ($('#tblFactoring tr:last-child').children('td').eq(4).text() !== "") {
                                                    $.each($('#tblFactoring tr'), function(key, value) {
                                                        if (key > 0 && key < tamTabela + 1) {
                                                            if ($(this).children('td').eq(4).text() != "0") {
                                                                tot_valor += parseFloat(formatValor($(this).children('td').eq(4).text()));
                                                                valores.push($(this).children('td').eq(5).text());
                                                            }
                                                        }
                                                    });
                                                    comissao = $('#hddComissaoFact').val();
                                                    tot_comissao = (comissao / 100) * tot_valor;
                                                    recurso = $('input[name="chkAllFact"]').prop('checked');
                                                    tempo = Math.max.apply(null, valores);
                                                    if (recurso === true) {
                                                        juro_anual = $('#hddJuroCRFact').val();
                                                        juro_mensal = (Math.pow(1 + juro_anual / 100, 1 / 12)) - 1;
                                                        tot_juros = juro_mensal * tot_valor * tempo;
                                                    } else {
                                                        juro_anual = $('#hddJuroSRFact').val();
                                                        juro_mensal = (Math.pow(1 + juro_anual / 100, 1 / 12)) - 1;
                                                        tot_juros = juro_mensal * tot_valor * tempo;
                                                        seguro = $('#hddSeguroFact').val();
                                                        tot_seguro = tot_valor * seguro / 100;
                                                    }
                                                } else {
                                                    tot_valor = valor;
                                                    tot_comissao = comissao_eur;
                                                    tot_juros = juro_eur;
                                                    tot_seguro = seguro_eur;
                                                }
                                                $('#tblFactoring tr:last-child').children('td').eq(3).text(('0' + data_op_sh.getDate()).slice(-2) + '-' + (('0' + (data_op_sh.getMonth() + 1)).slice(-2)) + '-' + data_op_sh.getFullYear());
                                                $('#tblFactoring tr:last-child').children('td').eq(4).text(number_format(tot_valor, 2, ',', '.'));
                                                $('#tblFactoring tr:last-child').children('td').eq(5).text(tempo);
                                                $('#tblFactoring tr:last-child').children('td').eq(7).text(number_format(tot_comissao, 2, ',', '.'));
                                                $('#tblFactoring tr:last-child').children('td').eq(8).text(number_format(tot_juros, 2, ',', '.'));
                                                $('#tblFactoring tr:last-child').children('td').eq(9).text(number_format(tot_seguro, 2, ',', '.'));
                                                $('#slcNumFatFact option').eq(0).prop('selected', true);
                                                $('#txtClienteFact').val("");
                                                $('#txtDataFact').val("");
                                                $('#txtValorFact').val("");
                                                $('#slcTempoFact option').eq(0).prop('selected', true);
                                                $('#chkRecursoFact').attr('checked', false);
                                                $('#txtComissaoFact').val("");
                                                $('#txtJurosFact').val("");
                                                $('#txtSeguroFact').val("");
                                                $('#frmFactoring').show();
                                                $('#tblFactoring').fadeIn();
                                                $('#btnEfetuarFactoring').closest('.linha').fadeIn();
                                            } else {
                                                $('.error').show().html('<span id="error">' + data + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        } else {
                                            $('.error').show().html('<span id="error">A fatura já foi adicionada</span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }
                                    } else {
                                        $('.error').show().html('<span id="error">Não tem plafond suficiente</span>');
                                        $("body").animate({
                                            scrollTop: 0
                                        });
                                    }
                                } else {
                                    $('.error').show().html('<span id="error">Escolha o número de fatura</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('change', '#chkAllFact', function(event) {
                            if (event.handler !== true) {
                                var recurso = $('#chkAllFact').prop('checked');
                                var tot_valor = formatValor($('#tblFactoring tr:last-child').children('td').eq(4).text());
                                var tempo = $('#tblFactoring tr:last-child').children('td').eq(5).text();
                                var comissao = $('#hddComissaoFact').val() / 100;
                                var tot_comissao = tot_valor * comissao;
                                var seguro = 0.00;
                                var juro_mensal = 0.00;
                                var tot_juros = 0.00;
                                var tot_seguro = 0.00;
                                if (recurso === true) {
                                    var juro_cr = $('#hddJuroCRFact').val() / 100;
                                    juro_mensal = (Math.pow(1 + juro_cr, 1 / 12)) - 1;
                                    tot_juros = juro_mensal * tot_valor * tempo;
                                } else {
                                    var juro_sr = $('#hddJuroSRFact').val() / 100;
                                    seguro = $('#hddSeguroFact').val() / 100;
                                    juro_mensal = (Math.pow(1 + juro_sr, 1 / 12)) - 1;
                                    tot_juros = juro_mensal * tot_valor * tempo;
                                    tot_seguro = seguro * tot_valor;
                                }
                                $('#tblFactoring tr:last-child').children('td').eq(7).text(number_format(tot_comissao, 2, ',', '.'));
                                $('#tblFactoring tr:last-child').children('td').eq(8).text(number_format(tot_juros, 2, ',', '.'));
                                $('#tblFactoring tr:last-child').children('td').eq(9).text(number_format(tot_seguro, 2, ',', '.'));
                                $('#tblFactoring tr').find('input[class="chk"]').prop('checked', this.checked);
                                event.handler = true;
                            }
                        });
                        $(document).on('click', '#btnEfetuarFactoring', function(event) {
                            if (event.handler !== true) {
                                var factoring = {};
                                var tamTabela = $('#tblFactoring tr').length - 1;
                                $.each($('#tblFactoring tr'), function(key, value) {
                                    if (key > 0 && key < tamTabela) {
                                        factoring[key] = {};
                                        factoring[key].hddIdFatura = $(this).children('input[name="hddIdFatura"]').val();
                                    }
                                });
                                var recurso = $('#chkAllFact').prop("checked");
                                var dataHdd = getVirtualDate();
                                var data = $('#tblFactoring tr:last-child').children('td').eq(3).text();
                                var total = $('#tblFactoring tr:last-child').children('td').eq(4).text();
                                var tempo = $('#tblFactoring tr:last-child').children('td').eq(5).text();
                                var comissao = $('#tblFactoring tr:last-child').children('td').eq(7).text();
                                var juro = $('#tblFactoring tr:last-child').children('td').eq(8).text();
                                var seguro = $('#tblFactoring tr:last-child').children('td').eq(9).text();
                                var dataString = "txtTotalValor=" + total + "&txtData=" + data + "&txtDataHdd=" + dataHdd + "&txtTotalTempo=" + tempo + "&txtTotalComissao=" + comissao + "&txtTotalJuro=" + juro + "&txtTotalSeguro=" + seguro + "&recurso=" + recurso + "&factoring=" + JSON.stringify(factoring) + "&tipo=pedir_factoring";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function(dados) {
                                        if (dados.sucesso === true) {
                                            emptyTable("#tblFactoring");
                                            $('#tblFactoring').append('<tr>' +
                                                '<td style="padding: 3px;">Total</td>' +
                                                '<td style="padding: 3px;">-</td>' +
                                                '<td style="padding: 3px;">-</td>' +
                                                '<td style="padding: 3px;"></td>' +
                                                '<td style="padding: 3px;"></td>' +
                                                '<td style="padding: 3px;"></td>' +
                                                '<td style="padding: 3px;">-</td>' +
                                                '<td style="padding: 3px;"></td>' +
                                                '<td style="padding: 3px;"></td>' +
                                                '<td style="padding: 3px;"></td>' +
                                                '</tr>');
                                            $('#tblFactoring').fadeOut();
                                            $('#btnEfetuarFactoring').closest('.linha').fadeOut();
                                            emptySelect("#slcNumFatFact");
                                            if (typeof(dados.dados_in) != "undefined") {
                                                $.each(dados.dados_in, function(i, item) {
                                                    $('#slcNumFatFact').append($('<option></option>').val(this.id_fatura).text(this.num_fatura));
                                                });
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
                        });
                        $(document).on('click', '#btnDelTransf', function(event) {
                            if (event.handler !== true) {
                                $.each($('#tblTransferencias tr'), function(key, value) {
                                    if (key != "0") {
                                        if ($(this).find('input[type=checkbox].chk').prop('checked') === true) {
                                            var checked = $(this).find('input[type=checkbox].chk');
                                            $('#chkAllTransf').prop('checked', false);
                                            if ($('#tblTransferencias tr').length == 2) {
                                                $('#tblTransferencias').fadeOut();
                                                $('#btnTransf').closest('.linha10').hide();
                                            }
                                            checked.closest('tr').fadeOut(function() {
                                                checked.closest('tr').remove();
                                            });
                                        }
                                    }
                                });
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('click', '.apagarLinhaFactoring', function(event) {
                            if (event.handler !== true) {
                                $('#chkAllFact').prop('checked', false);
                                apagarLinhaFactoring(this);
                                event.handler = true;
                            }
                        });
                        $(document).on('change', '#slcOrdenarPrestacoes', function(event) {
                            if (event.handler !== true) {
                                var id = $(this).val();
                                var tipo_prest;
                                $(this).closest('#pag_prest').find('.radio').children('input').each(function(i, val) {
                                    if ($(this).prop('checked') === true) {
                                        tipo_prest = $(this).val();
                                    }
                                });
                                var dataString = "id=" + id + "&tipo_prestacao=" + tipo_prest + "&tipo=ordenar_prestacoes";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    beforeSend: function() {
                                        $('#tblPrestacoes').hide();
                                        $('#tblPrestacoesVazia').hide();
                                        $('#btnPagarPrestacoes').hide();
                                        $('#slcOrdenarPrestacoes').closest('.linha').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        if (dados.sucesso === true) {
                                            emptyTable('#tblPrestacoes');
                                            $.each(dados.dados_in, function(i, item) {
                                                $('#tblPrestacoes').append('<tr>' +
                                                    '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                    '<div class="checkbox">' +
                                                    '<input id="chkPrestacao_' + dados.dados_in[i].id + '" name="chkPrestacao" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                    '<label for="chkPrestacao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                    '<input id="hddIdPrestacao" name="hddIdPrestacao" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                    '</div>' +
                                                    '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                    '<td style="padding: 4px;">' + dados.dados_in[i].data_limite + '</td>' +
                                                    '<td style="padding: 4px;">' + number_format(dados.dados_in[i].prestacao, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                    '</tr>');
                                            });
                                            $('#btnPagarPrestacoes').hide();
                                            $('#slcOrdenarPrestacoes').closest('.linha').show();
                                            $('#chkAllPrest').prop('checked', false);
                                            $('#tblPrestacoes').show();
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
                        $(document).on('click', '#btnPagarPrestacoes', function(event) {
                            if (event.handler !== true) {
                                var count_checked = $('#tblPrestacoes').find('input[type=checkbox].chk').filter(':checked').length;
                                var dados = {};
                                $.each($('#tblPrestacoes').find('input[type=checkbox].chk'), function(key, value) {
                                    if (key != "0") {
                                        if ($(this).prop('checked') === true) {
                                            dados[key] = {};
                                            dados[key].id = $(this).closest('.checkbox').find('#hddIdPrestacao').val();
                                        }
                                    }
                                });
                                if (count_checked > 0) {
                                    var dataString = "dados=" + JSON.stringify(dados) + "&data=" + getVirtualDate();
                                    var tipo_prest;
                                    $(this).closest('#pag_prest').find('.radio').children('input').each(function(i, val) {
                                        if ($(this).prop('checked') === true) {
                                            tipo_prest = $(this).val();
                                        }
                                    });
                                    if (tipo_prest == 1) {
                                        dataString += "&tipo=pag_prest_emp";
                                    } else if (tipo_prest == 2) {
                                        dataString += "&tipo=pag_prest_lea";
                                    }
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblPrestacoes').hide();
                                            $('#tblPrestacoesVazia').hide();
                                            $('#btnPagarPrestacoes').hide();
                                            $('#slcOrdenarPrestacoes').closest('.linha').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            emptyTable('#tblPrestacoes');
                                            $('#slcOrdenarPrestacoes').val(0);
                                            if (dados.sucesso === true) {
                                                if (dados.vazio === false) {
                                                    $('#tblPrestacoesVazia').hide();
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblPrestacoes').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkPrestacao_' + dados.dados_in[i].id + '" name="chkPrestacao" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkPrestacao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddIdPrestacao" name="hddIdPrestacao" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data_limite + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].prestacao, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#tblPrestacoes').show();
                                                    $('#slcOrdenarPrestacoes').closest('.linha').show();
                                                } else {
                                                    $('#tblPrestacoesVazia').show();
                                                }
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + dados.moeda);
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                            $('#slcOrdenarPrestacoes').val(0);
                                            $('#btnPagarPrestacoes').hide();
                                        }
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        $(document).on('change', 'select[name="slcTipoDiversos"]', function(event) {
                            if (event.handler !== true) {
                                $('#tblFaturas').hide();
                                $('#tblDecRet').hide();
                                $('#btnPagarFatura').hide();
                                $('#btnPagarEntrega').hide();
                                $('#btnPagarDecRet').hide();
                                $('#chkAllFat').closest('.checkbox').find('input').prop('checked', false);
                                $('#chkAllDiv').closest('.checkbox').find('input').prop('checked', false);
                                $('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', false);
                                $('#tblDecRet').data('value', '0');
                                $('#tblFaturas').data('value', '0');
                                $('#tblDiversos').data('value', '1');
                                if ($(this).val() == 4) {
                                    $('#radOthersGroup').find('#radOutros').eq(0).prop('checked', true);
                                    $('#radOthersGroup').show();
                                } else {
                                    $('#radOthersGroup').hide();
                                }
                                resizeSelect("slcTipoDiversos");
                                if ($(this).val() > 0) {
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: "id=" + $(this).val() + "&tipo=tipo_diversos",
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                $('#tblVazia').hide();
                                                if (dados.vazio === false) {
                                                    emptyTable('#tblDiversos');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblDiversos').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkDiverso_' + dados.dados_in[i].id + '" name="chkDiverso" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkDiverso" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddIdDiverso" name="hddIdDiverso" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].tipo + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].f_prazo + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].mes + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].ano + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#tblDiversos').show();
                                                } else {
                                                    $('#tblDiversos').hide();
                                                    $('#tblFaturas').hide();
                                                    $('#tblDecRet').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                            } else {
                                                $('#tblDiversos').hide();
                                                $('#tblFaturas').hide();
                                                $('#tblDecRet').hide();
                                                $('#tblVazia').hide();
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    $('#btnPagarFatura').hide();
                                    $('#btnPagarEntrega').hide();
                                    $('#btnPagarDecRet').hide();
                                    $('#tblDiversos').hide();
                                    $('#tblFaturas').hide();
                                    $('#tblDecRet').hide();
                                    $('#tblVazia').hide();
                                    $('#radOthersGroup').hide();
                                }
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
                                if ($(this).attr("for") == "radOutros") {
                                    $('#tblDecRet').data('value', '0');
                                    $('#tblFaturas').data('value', '0');
                                    $('#tblDiversos').data('value', '1');
                                    $('#tblFaturas').hide();
                                    $('#tblDecRet').hide();
                                    $('#chkAllDiv').closest('.checkbox').find('input').prop('checked', false);
                                    $('#chkAllFat').closest('.checkbox').find('input').prop('checked', false);
                                    $('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', false);
                                    $('#tblVazia').hide();
                                    $('#btnPagarFatura').hide();
                                    $('#btnPagarEntrega').hide();
                                    $('#btnPagarDecRet').hide();
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: "id=" + $('select[name="slcTipoDiversos"]').val() + "&tipo=tipo_diversos",
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                $('#tblVazia').hide();
                                                if (dados.vazio === false) {
                                                    $('#tblFaturas').hide();
                                                    emptyTable('#tblDiversos');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblDiversos').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkDiverso_' + dados.dados_in[i].id + '" name="chkDiverso" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkDiverso" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddIdDiverso" name="hddIdDiverso" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].tipo + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].f_prazo + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].mes + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].ano + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#tblDiversos').show();
                                                } else {
                                                    $('#tblDiversos').hide();
                                                    $('#tblFaturas').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                            } else {
                                                $('#tblDiversos').hide();
                                                $('#tblFaturas').hide();
                                                $('#tblVazia').hide();
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else if ($(this).attr("for") == "radFaturas") {
                                    $('#tblDecRet').data('value', '0');
                                    $('#tblDiversos').data('value', '0');
                                    $('#tblFaturas').data('value', '1');
                                    $('#tblDiversos').hide();
                                    $('#tblDecRet').hide();
                                    $('#chkAllDiv').closest('.checkbox').find('input').prop('checked', false);
                                    $('#chkAllFat').closest('.checkbox').find('input').prop('checked', false);
                                    $('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', false);
                                    $('#tblVazia').hide();
                                    $('#btnPagarFatura').hide();
                                    $('#btnPagarEntrega').hide();
                                    $('#btnPagarDecRet').hide();
                                    dataString = "tipo=tipo_faturas";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        dataType: "json",
                                        data: dataString,
                                        beforeSend: function() {
                                            $('#tblFaturas').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                hideLoading();
                                                $('#tblVazia').hide();
                                                if (dados.vazio === false) {
                                                    emptyTable('#tblFaturas');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblFaturas').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkFatura_' + dados.dados_in[i].id + '" name="chkFatura" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].ref + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].fornecedor + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].pais + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].iva, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
                                                            '<td style="background-color: transparent; padding: 0; cursor: pointer;">' + 
                                                                '<a href="./impressao/fatura.php?id=' + dados.dados_in[i].id + '&p=' + dados.dados_in[i].abrev_pais + '" target="_blank">' + 
                                                                    '<img width="33" height="33" src="images/adobe_logo.png">' + 
                                                                '</a>' + 
                                                            '</td>' + 
															'</tr>');
                                                    });
                                                    $('#tblFaturas').show();
                                                } else {
                                                    $('#tblDiversos').hide();
                                                    $('#tblFaturas').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                            } else {
                                                $('#tblDiversos').hide();
                                                $('#tblFaturas').hide();
                                                $('#tblVazia').hide();
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else if ($(this).attr("for") == "radDecRet") {
                                    $('#tblFaturas').data('value', '0');
                                    $('#tblDiversos').data('value', '0');
                                    $('#tblDecRet').data('value', '1');
                                    $('#tblDiversos').hide();
                                    $('#tblFaturas').hide();
                                    $('#chkAllDiv').closest('.checkbox').find('input').prop('checked', false);
                                    $('#chkAllFat').closest('.checkbox').find('input').prop('checked', false);
                                    $('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', false);
                                    $('#tblVazia').hide();
                                    $('#btnPagarFatura').hide();
                                    $('#btnPagarEntrega').hide();
                                    $('#btnPagarDecRet').hide();
                                    dataString = "tipo=tipo_dec_ret";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        dataType: "json",
                                        data: dataString,
                                        beforeSend: function() {
                                            $('#tblDecRet').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                hideLoading();
                                                $('#tblVazia').hide();
                                                if (dados.vazio === false) {
                                                    emptyTable('#tblDecRet');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblDecRet').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkDecRet_' + dados.dados_in[i].id + '" name="chkDecRet" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddDecRet" name="hddDecRet" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].n_res + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#tblDecRet').show();
                                                } else {
                                                    $('#tblDiversos').hide();
                                                    $('#tblFaturas').hide();
                                                    $('#tblDecRet').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                            } else {
                                                $('#tblDiversos').hide();
                                                $('#tblFaturas').hide();
                                                $('#tblDecRet').hide();
                                                $('#tblVazia').hide();
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else if (($(this).attr("for") == "radEmprestimo") || ($(this).attr("for") == "radLeasing")) {
                                    var id = $(this).closest('.radio').find('#' + $(this).attr("for")).val();
                                    dataString = "id=" + id + "&tipo=tipo_prestacao";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblPrestacoes').hide();
                                            $('#tblPrestacoesVazia').hide();
                                            $('#btnPagarPrestacoes').hide();
                                            $('#slcOrdenarPrestacoes').closest('.linha').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            hideLoading();
                                            if (dados.sucesso === true) {
                                                if (dados.vazio === false) {
                                                    $('#tblPrestacoesVazia').hide();
                                                    emptyTable('#tblPrestacoes');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblPrestacoes').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkPrestacao_' + dados.dados_in[i].id + '" name="chkPrestacao" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkPrestacao" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddIdPrestacao" name="hddIdPrestacao" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data_limite + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].prestacao, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#slcOrdenarPrestacoes').val(0);
                                                    $('#slcOrdenarPrestacoes').closest('.linha').show();
                                                    $('#tblPrestacoes').show();
                                                } else {
                                                    $('#tblPrestacoesVazia').show();
                                                }
                                                $('#chkAllPrest').prop('checked', false);
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
                        });
						$(document).on('click', '.label_chk', function(event) {
                            if (event.handler !== true) {
                                if ($(this).closest('#tblDiversos').data('value') == 1) {
                                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                        $(this).closest('#tblDiversos').find('#chkAllDiv').closest('.checkbox').find('input').prop('checked', false);
                                        $(this).closest('.checkbox').find('input').prop('checked', false);
                                    } else {
                                        if (($(this).closest('#tblDiversos').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblDiversos').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                                            $(this).closest('#tblDiversos').find('#chkAllDiv').closest('.checkbox').find('input').prop('checked', true);
                                            $(this).closest('#pag_div').find('#btnPagarEntrega').hide();
                                        }
                                        $(this).closest('.checkbox').find('input').prop('checked', true);
                                        $('#btnPagarEntrega').show();
                                    }
                                    if ($(this).closest('#tblDiversos').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                                        $('#btnPagarEntrega').hide();
                                    }
                                } else if ($(this).closest('#tblFaturas').data('value') == 1) {
                                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                        $(this).closest('#tblFaturas').find('#chkAllFat').closest('.checkbox').find('input').prop('checked', false);
                                        $(this).closest('.checkbox').find('input').prop('checked', false);
                                    } else {
                                        if (($(this).closest('#tblFaturas').find('input[type=checkbox].chk').length - 1) == $(this).closest('#tblFaturas').find('input[type=checkbox].chk').filter(':checked').length + 1) {
                                            $(this).closest('#pag_div').find('#btnPagarFatura').hide();
                                            $(this).closest('#tblFaturas').find('#chkAllFat').closest('.checkbox').find('input').prop('checked', true);
                                        }
                                        $(this).closest('.checkbox').find('input').prop('checked', true);
                                        $('#btnPagarFatura').show();
                                    }
                                    if ($(this).closest('#tblFaturas').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                                        $('#btnPagarFatura').hide();
                                    }
                                } else if ($(this).closest('#tblDecRet').data('value') == 1) {
                                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                        $(this).closest('#tblDecRet').find('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', false);
                                        $(this).closest('.checkbox').find('input').prop('checked', false);
                                    } else {
                                        if (($(this).closest('#tblDecRet').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblDecRet').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                                            $(this).closest('#tblDecRet').find('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', true);
                                            $(this).closest('#pag_div').find('#btnPagarDecRet').hide();
                                        }
                                        $(this).closest('.checkbox').find('input').prop('checked', true);
                                        $('#btnPagarDecRet').show();
                                    }
                                    if ($(this).closest('#tblDecRet').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                                        $('#btnPagarDecRet').hide();
                                    }
                                } else if ($(this).closest('#tblPrestacoes').data('value') == 1) {
                                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                        $(this).closest('#tblPrestacoes').find('#chkAllPrest').closest('.checkbox').find('input').prop('checked', false);
                                        $(this).closest('.checkbox').find('input').prop('checked', false);
                                    } else {
                                        if (($(this).closest('#tblPrestacoes').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblPrestacoes').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                                            $(this).closest('#tblPrestacoes').find('#chkAllPrest').closest('.checkbox').find('input').prop('checked', true);
                                            $(this).closest('#pag_prest').find('#btnPagarPrestacoes').hide();
                                        }
                                        $(this).closest('.checkbox').find('input').prop('checked', true);
                                        $('#btnPagarPrestacoes').show();
                                    }
                                    if ($(this).closest('#tblPrestacoes').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                                        $('#btnPagarPrestacoes').hide();
                                    }
                                } else if ($(this).closest('#tblTransferencias').data('value') == 1) {
                                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                                        $(this).closest('#tblTransferencias').find('#chkAllTransf').closest('.checkbox').find('input').prop('checked', false);
                                        $(this).closest('.checkbox').find('input').prop('checked', false);
                                    } else {
                                        if (($(this).closest('#tblTransferencias').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblTransferencias').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                                            $(this).closest('#tblTransferencias').find('#chkAllTransf').closest('.checkbox').find('input').prop('checked', true);
                                        }
                                        $(this).closest('.checkbox').find('input').prop('checked', true);
                                    }
                                }
                                event.handler = true;
                            }
                        });
						$(document).on('click', '#btnPagarEntrega', function(event) {
                            if (event.handler !== true) {
                                var count_checked = $('#tblDiversos').find('input[type=checkbox].chk').filter(':checked').length;
                                var dados = {};
                                $.each($('#tblDiversos').find('input[type=checkbox].chk'), function(key, value) {
                                    if (key != "0") {
                                        if ($(this).prop('checked') === true) {
                                            dados[key] = {};
                                            dados[key].id = $(this).closest('.checkbox').find('#hddIdDiverso').val();
                                            dados[key].id_tipo = $('#slcTipoDiversos option:selected').val();
                                        }
                                    }
                                });
                                var dataString = "dados=" + JSON.stringify(dados) + "&data=" + getVirtualDate() + "&tipo=pag_entrega";
                                if (count_checked >= 1) {
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblDiversos').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                hideLoading();
                                                $('#tblVazia').hide();
                                                $('#btnPagarEntrega').hide();
                                                if (dados.vazio === false) {
                                                    emptyTable('#tblDiversos');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblDiversos').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkDiverso_' + dados.dados_in[i].id + '" name="chkDiverso" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkDiverso" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddIdDiverso" name="hddIdDiverso" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].tipo + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].f_prazo + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].mes + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].ano + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#tblDiversos').show();
                                                } else {
                                                    $('#tblDiversos').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + dados.moeda);
                                            } else {
                                                $('#tblDiversos').show();
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
                        $(document).on('click', '#btnPagarFatura', function(event) {
                            if (event.handler !== true) {
                                var count_checked = $('#tblFaturas').find('input[type=checkbox].chk').filter(':checked').length;
                                var dados = {};
                                $.each($('#tblFaturas').find('input[type=checkbox].chk'), function(key, value) {
                                    if (key != "0") {
                                        if ($(this).prop('checked') === true) {
                                            dados[key] = {};
                                            dados[key].id = $(this).closest('.checkbox').find('#hddIdFatura').val();
                                        }
                                    }
                                });
                                var dataString = "dados=" + JSON.stringify(dados) + "&data=" + getVirtualDate() + "&tipo=pag_fatura";
                                if (count_checked >= 1) {
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblFaturas').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                hideLoading();
                                                $('#tblVazia').hide();
                                                $('#btnPagarFatura').hide();
                                                if (dados.vazio === false) {
                                                    emptyTable('#tblFaturas');
													$.each(dados.dados_in, function(i, item) {
														$('#tblFaturas').append('<tr>' +
															'<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
															'<div class="checkbox">' +
															'<input id="chkFatura_' + dados.dados_in[i].id + '" name="chkFatura" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
															'<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
															'<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + dados.dados_in[i].id + '">' +
															'</div>' +
															'</td>' +
															'<td style="padding: 4px;">' + dados.dados_in[i].ref + '</td>' +
															'<td style="padding: 4px;">' + dados.dados_in[i].fornecedor + '</td>' +
															'<td style="padding: 4px;">' + dados.dados_in[i].pais + '</td>' +
															'<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
															'<td style="padding: 4px;">' + number_format(dados.dados_in[i].iva, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
															'<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.dados_in[i].moeda + '</td>' +
															'<td style="background-color: transparent; padding: 0; cursor: pointer;">' + 
																'<a href="./impressao/fatura.php?id=' + dados.dados_in[i].id + '&p=' + dados.dados_in[i].abrev_pais + '" target="_blank">' + 
																	'<img width="33" height="33" src="images/adobe_logo.png">' + 
																'</a>' + 
															'</td>' + 
															'</tr>');
													});
                                                    $('#tblFaturas').show();
                                                } else {
                                                    $('#tblFaturas').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + dados.moeda);
                                            } else {
                                                $('#tblVazia').hide();
                                                $('#tblFaturas').show();
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
						$(document).on('click', '#btnPagarDecRet', function(event) {
                            if (event.handler !== true) {
                                var count_checked = $('#tblDecRet').find('input[type=checkbox].chk').filter(':checked').length;
                                var dados = {};
                                $.each($('#tblDecRet').find('input[type=checkbox].chk'), function(key, value) {
                                    if (key != "0") {
                                        if ($(this).prop('checked') === true) {
                                            dados[key] = {};
                                            dados[key].id = $(this).closest('.checkbox').find('#hddDecRet').val();
                                        }
                                    }
                                });
                                var dataString = "dados=" + JSON.stringify(dados) + "&data=" + getVirtualDate() + "&tipo=pag_dec_ret";
                                if (count_checked >= 1) {
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        beforeSend: function() {
                                            $('#tblDecRet').hide();
                                            showLoading();
                                        },
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                hideLoading();
                                                $('#tblVazia').hide();
                                                $('#btnPagarDecRet').hide();
                                                if (dados.vazio === false) {
                                                    emptyTable('#tblDecRet');
                                                    $.each(dados.dados_in, function(i, item) {
                                                        $('#tblDecRet').append('<tr>' +
                                                            '<td style="background-color: transparent; padding: 4px; cursor: pointer;">' +
                                                            '<div class="checkbox">' +
                                                            '<input id="chkDecRet_' + dados.dados_in[i].id + '" name="chkDecRet" type="checkbox" class="chk" value="' + dados.dados_in[i].id + '">' +
                                                            '<label for="chkDecRet" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                                            '<input id="hddDecRet" name="hddDecRet" type="hidden" value="' + dados.dados_in[i].id + '">' +
                                                            '</div>' +
                                                            '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].data + '</td>' +
                                                            '<td style="padding: 4px;">' + dados.dados_in[i].n_res + '</td>' +
                                                            '<td style="padding: 4px;">' + number_format(dados.dados_in[i].total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                                            '</tr>');
                                                    });
                                                    $('#tblDecRet').show();
                                                } else {
                                                    $('#tblDecRet').hide();
                                                    $('#tblVazia').empty().append('<tr><td>' + dados.mensagem + '</td></tr>').show();
                                                }
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + dados.moeda);
                                            } else {
                                                $('#tblDecRet').show();
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
						$(document).on('click', '#btnBeneficiarios', function(event) {
                            if (event.handler !== true) {
                                var left = (screen.width / 2) - (550 / 2);
                                var top = (screen.height / 2) - (360 / 2);
                                childWin = window.open('pag_empresas.php', 'Destinatários', 'resizable=no,width=550,height=360,scrollbars=yes,top=' + top + ', left=' + left);
                                if (window.focus) {
                                    childWin.focus();
                                }
                                event.handler = true;
                            }
                            return false;
                        });
						$(document).on('click', 'div[name="btnIdCre"]', function(event) {
                            if (event.handler !== true) {
                                var id_emprest = $(this).closest('td').children('input[name="hddIdEmprest"]').val();
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    dataType: "json",
                                    data: "id_emprest=" + id_emprest + "&tipo=ver_emprestimo",
                                    beforeSend: function() {
                                        $('#tblCreditos').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        if (dados.sucesso === true) {
                                            var pago;
                                            emptyTable('#tblCreditoDetail');
                                            $.each(dados.dados_in, function(i, item) {
                                                if (dados.dados_in[i].pago == "0") {
                                                    pago = "Não";
                                                } else {
                                                    pago = "Sim";
                                                }
                                                $('#tblCreditoDetail').append('<tr>' +
                                                    '<td>' + dados.dados_in[i].n_per + '</td>' +
                                                    '<td>' + dados.dados_in[i].cap + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].juros + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].amort + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].prest + " " + dados.moeda + '</td>' +
                                                    '<td>' + pago + '</td>' +
                                                    '</tr>');
                                            });
                                            $('#btnVoltarCredito').show();
                                            $('#tblCreditoDetail').show();
                                        }
                                    }
                                });
                                event.handler = true;
                            }
                            return false;
                        });
						$(document).on('click', '#btnVoltarCredito', function(event) {
                            if (event.handler !== true) {
                                $('#tblCreditoDetail').hide();
                                $('#btnVoltarCredito').hide();
                                $('#tblCreditos').show();
                                event.handler = true;
                            }
                            return false;
                        });
						$(document).on('click', 'div[name="btnIdLeas"]', function(event) {
                            if (event.handler !== true) {
                                var id_leas = $(this).closest('td').children('input[name="hddIdLeasing"]').val();
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    dataType: "json",
                                    data: "id_leas=" + id_leas + "&tipo=ver_leasing",
                                    beforeSend: function() {
                                        $('#tblConsLeas').hide();
                                        showLoading();
                                    },
                                    success: function(dados) {
                                        hideLoading();
                                        if (dados.sucesso === true) {
                                            var pago;
                                            emptyTable('#tblLeasingDetail');
                                            $.each(dados.dados_in, function(i, item) {
                                                if (dados.dados_in[i].pago == "0") {
                                                    pago = "Não";
                                                } else {
                                                    pago = "Sim";
                                                }
                                                $('#tblLeasingDetail').append('<tr>' +
                                                    '<td>' + dados.dados_in[i].n_per + '</td>' +
                                                    '<td>' + dados.dados_in[i].cap + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].juros + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].amort + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].prests + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].iva + " " + dados.moeda + '</td>' +
                                                    '<td>' + dados.dados_in[i].prestc + " " + dados.moeda + '</td>' +
                                                    '<td>' + pago + '</td>' +
                                                    '</tr>');
                                            });
                                            $('#tblLeasingDetail').show();
                                            $('#btnVoltarLeasing').show();
                                        }
                                    }
                                });
                                event.handler = true;
                            }
                            return false;
                        });
						$(document).on('click', '#btnVoltarLeasing', function(event) {
                            if (event.handler !== true) {
                                $('#tblLeasingDetail').hide();
                                $('#btnVoltarLeasing').hide();
                                $('#tblConsLeas').show();
                                event.handler = true;
                            }
                            return false;
                        });
						$.windowMsg("txtContaDestino1", function(message) {
                            $('#transferencia').find('input[name="txtContaDestino1"]').val(message);
                        });
                        $.windowMsg("txtContaDestino2", function(message) {
                            $('#transferencia').find('input[name="txtContaDestino2"]').val(message);
                        });
                        $.windowMsg("txtContaDestino3", function(message) {
                            $('#transferencia').find('input[name="txtContaDestino3"]').val(message);
                        });
                        $.windowMsg("idEmpresa", function(message) {
                            $('#transferencia').find('input[name="hddIdEmpresa"]').val(message);
                            //--
                            $('#desconto_letra').find('input[name="hddIdEmpresaSacado"]').val(message);
                        });
                        $.windowMsg("nomeEmpresa", function(message) {
                            $('#transferencia').find('input[name="hddNomeEmpresa"]').val(message);
                            //--
                            $('#desconto_letra').find('input[name="txtClienteLetInt"]').val(message);
                        });
                        //---
                        $(document).on('click', '#btnGenIBAN', function(event) {
                            if (event.handler !== true) {
                                var dataString = "tipo=gera_iban";
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function(dados) {
                                        if (dados.sucesso === true) {
                                            $('#txtNIBAN').val(dados.iban);
                                            $('#txtNNC').val(dados.num_conta);
                                            $('#txtNNIB').val(dados.nib);
                                            $('#btnGenIBAN').hide();
                                            $('#subtituloDP').show();
                                            $('#MontanteDP').show();
                                            $('#txJuroDP').show();
                                            $('#txIRCDP').show();
                                            $('#slcPrazoDP').show();
                                            $('#TotalDP').show();
                                            $('#btnAddDP').show();
                                        }
                                        else{
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
                        //Calcula valor Total
                        $(document).on('click', '#btnCalcularTotal', function(event) {
                            if (event.handler !== true) {
                                if ($('#txtMontanteDP').val() == "") {
                                    $('.error').show().html('<span id="error">' + "Por favor, preencha o Montante" + '</span>');
                                        $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else{
                                    var montante = $('#txtMontanteDP').val().replace('.', '').replace(',', '.');
                                    var prazo = $('#slcPrazo').val();
                                    var irc = $('#txIRC').val() / 100;
                                    var juroAnual = $('#txtJuros').val() / 100;
                                    var juroMens = Math.pow((1 + juroAnual), (1/12)) - 1;
                                    var juroBrt = montante * juroMens * prazo;
                                    var juroLiq = juroBrt - (juroBrt * irc);
                                    var total = +montante + +juroLiq;
                                   
                                    total = number_format(total, 2, ',', '.');
                                    $('#txtTotalDP').val(total);
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        //Apresenta Contrato;
                        $(document).on('click', '#btnAddDP', function(event) {
                            if (event.handler !== true) {
                                if ($('#txtMontanteDP').val() == "") {
                                    $('.error').show().html('<span id="error">' + "Por favor, preencha o Montante" + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else {
                                   //Contrato
                                   var win = window.open("./impressao/dep_prazo.php");
                                   win.focus();
                                   $('#criarcontaprazo').css("height", "200px");
                                   $('#criarcontaprazo .linha').hide();
                                   $('#contrato_contaprazo').show();
                                }
                                
                                event.handler = true;
                            }
                            return false;
                        });
                        //---
                        $(document).on('click', '#btnCancelDP', function(event) {
                            if (event.handler !== true) {
                                $('#criarcontaprazo .linha').show();
                                $('#contrato_contaprazo').hide();
                                event.handler = true;
                            }
                            return false;
                        });
                        //---
                        $(document).on('click', '#btnCriarCPrazo', function(event) {
                            if (event.handler !== true) {
                                if ($('#chkDP').prop('checked')) {
                                    var id_emp = $('#hddIdEmp').val();
                                    var id_banco = $('#hddIdBanco').val();
                                    var iban = $('#txtNIBAN').val();
                                    var nib = $('#txtNNIB').val();
                                    var num_conta = $('#txtNNC').val();
                                    var montante = $('#txtMontanteDP').val();
                                    var tx_juros = $('#txtJuros').val();
                                    var prazo = $('#slcPrazo').val();
                                    var irc = $('#txIRC').val();
                                    var dataVirt = getVirtualDate();
									var dataString = "id_emp=" + id_emp + "&id_banco=" + id_banco + "&iban=" + iban + "&nib=" + nib + "&num_conta=" + num_conta + "&montante=" + montante + "&tx_juros=" + tx_juros + "&tx_irc=" + irc + "&prazo=" + prazo + "&datavirt=" + dataVirt + "&tipo=criar_CP";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                $('#menu a[href="criarcontaprazo"]').hide();
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + " " + dados.moeda);
                                                $('#var_content').load('conteudo_banco.php #plano_deposito_prazo', function(){
                                                    $('.loading').hide();
                                                });
                                            }
                                            else{
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                    event.handler = true;
                                }
                                else{
                                    $('.error').show().html('<span id="error">' + "Por favor, aceite os termos" + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                            }
                        return false;
                    });
                    //Apresenta Contrato Renovação;
                    $(document).on('click', '#btnRenovDP', function(event) {
                        //Contrato
                        var win = window.open("./impressao/dep_prazo.php");
                        win.focus();
                        $('#renovardp').css("height", "200px");
                        $('#renovardp .linha').hide();
                        $('#contrato_rencontaprazo').show();
                        event.handler = true;
                        return false;
                        });
                        //---
                        $(document).on('click', '#btnCancelRenDP', function(event) {
                            if (event.handler !== true) {
                                $('#renovardp .linha').show();
                                $('#contrato_rencontaprazo').hide();
                                event.handler = true;
                            }
                            return false;
                        });
                        //---
                        $(document).on('click', '#btnRenCPrazo', function(event) {
                            if (event.handler !== true) {
                                if ($('#chkrenDP').prop('checked')) {
                                    var id_conta = $('#IdConta').val();
                                    var montante = $('#txtSaldoCP').val();
                                    var tx_juros = $('#txtRJuros').val();
                                    var irc = $('#txRIRC').val();
                                    var prazo = $('#slcPrazoRCP').val();
                                    var dataVirt = getVirtualDate();
									var dataString = "id_conta=" + id_conta + "&montante=" + montante + "&tx_juros=" + tx_juros + "&tx_irc=" + irc + "&prazo=" + prazo + "&datavirt=" + dataVirt + "&tipo=ren_CP";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                $('#menu a[href="terminardp"]').hide();
                                                $('#menu a[href="renovardp"]').hide();
                                                $('#var_content').load('conteudo_banco.php #plano_deposito_prazo', function(){
                                                    $('.loading').hide();
                                                });
                                            }
                                            else{
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                    event.handler = true;
                                }
                                else{
                                    $('.error').show().html('<span id="error">' + "Por favor, aceite os termos" + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                            }
                            return false;
                        });
						//Apresenta Contrato Término;
                        $(document).on('click', '#btnTerminarCP', function(event) {
                            $('#terminardp').css("height", "200px");
                            $('#terminardp .linha').hide();
                            $('#contrato_termcontaprazo').show();
                            event.handler = true;
                            return false;
                        });
                        //---
                        $(document).on('click', '#btnCancelTermDP', function(event) {
                            if (event.handler !== true) {
                                $('#terminardp .linha').show();
                                $('#contrato_termcontaprazo').hide();
                                event.handler = true;
                            }
                            return false;
                        });
                        //---
                        $(document).on('click', '#btnTermCPrazo', function(event) {
                            if (event.handler !== true) {
                                if ($('#chktermDP').prop('checked')) {
                                    var montante = $('#SaldoCP').val();
                                    var dataVirt = getVirtualDate();
									var dataString = "montante=" + montante + "&datavirt=" + dataVirt + "&tipo=term_CP";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + " " + dados.moeda);
                                                location.reload();
                                            }
                                            else{
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                    event.handler = true;
                                }
                                else{
                                    $('.error').show().html('<span id="error">' + "Por favor, aceite os termos" + '</span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                            }
                            return false;
                        });
						//---
                        $(document).on('change', '#chkSacadoLet', function(event) {
                            if (event.handler !== true) {
                                $('#txtValorLet').val("");
                                $('#txtPrazoLet').val("");
                                $('#txtEncargosLet').val("");
                                $('#txtValorLiqLet').val("");
                                if ($(this).prop('checked')){
                                    $('#letClienteExt').fadeOut("linear");
                                    $('#btnAddLetExt').fadeOut("linear");
                                    $('#letClienteInt').fadeIn("linear");
                                    $('#btnAddLetInt').fadeIn("linear");
                                }
                                else {
                                    $('#letClienteInt').fadeOut("linear");
                                    $('#btnAddLetInt').fadeOut("linear");
                                    $('#letClienteExt').fadeIn("linear");
                                    $('#btnAddLetExt').fadeIn("linear");
                                    $('#slcNumFatLet').val("0");
                                    $('#txtClienteLetExt').val("");
                                    $('#txtDataLet').val("");
                                    $('#txtPrazoMax').val("");
                                    $('#txtTotalLetExt').val("");
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        //--
                        $(document).on('click', '#btnSacadoLet', function(event) {
                            if (event.handler !== true) {
                                var left = (screen.width / 2) - (550 / 2);
                                var top = (screen.height / 2) - (360 / 2);
                                childWin = window.open('pag_empresas.php', 'Sacado', 'resizable=no,width=550,height=360,scrollbars=yes,top=' + top + ', left=' + left);
                                if (window.focus) {
                                    childWin.focus();
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        //--
                        $(document).on('change', '#slcNumFatLet', function(event) {
                            if (event.handler !== true) {
                                var id = $(this).val();
                                if (id == "0") {
                                    $('#txtClienteLetExt').val('');
                                    $('#txtDataLet').val('');
                                    $('#txtTotalLetExt').val('');
                                    $('#txtPrazoMax').val('');
                                } else {
                                    var data_virt = getVirtualDate();
                                    var dataString = 'id=' + id + "&data_virt=" + data_virt + "&tipo=letra_dados";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            $('#txtClienteLetExt').val(dados.nome);
                                            $('#txtDataLet').val(dados.data);
                                            $('#txtPrazoMax').val(dados.prazomax);
                                            $('#txtTotalLetExt').val(number_format(dados.valor, 2, ',', '.'));
                                        }
                                    });
                                }
                                event.handler = true;
                            }
                        });
                        //--
                        $(document).on('keyup', '#txtValorLet, #txtPrazoLet', function(event) {
                            if (event.handler !== true) {
                                if($('#txtValorLet').val() !== "" && $('#txtPrazoLet').val() !== "") {
                                    var valor = $('#txtValorLet').val().replace('.', '').replace(',', '.');
                                    var prazo = +$('#txtPrazoLet').val() + +2;
                                    var txcomissao = $('#txtComissaoLet').val() / 100;
                                    var comissao = valor * txcomissao;
                                    var txis = $('#txtISLet').val() / 100;
                                    var is = (+valor + +comissao) * txis;
                                    var txjuroAnual = $('#txtJurosLet').val() / 100;
                                    var txjuroDiario = Math.pow((1 + txjuroAnual), (1/365)) - 1;
                                    var juroLiq = valor * txjuroDiario * prazo;
                                    var encargos = +is + +comissao + +juroLiq;
                                    var total = valor - encargos;
									encargos = number_format(encargos, 2, ',', '.');
                                    total = number_format(total, 2, ',', '.');
                                    $('#txtEncargosLet').val(encargos);
                                    $('#txtValorLiqLet').val(total);
                                }
                            }
                        });
                        //--
                        $(document).on('click', '#btnAddLet', function(event) {
                            if (event.handler !== true) {
                                if ($('#txtValorLet').val() === "" || $('#txtValorLet').val() == "0") {
                                    $('.error').show().html('<span id="error"> Por favor, indique o valor da letra </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else if ($('#txtPrazoLet').val() === "" || $('#txtPrazoLet').val() == "0") {
                                    $('.error').show().html('<span id="error"> Por favor, indique o prazo da letra </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else {
                                    var valor = $('#txtValorLet').val();
                                    var prazo = $('#txtPrazoLet').val();
                                    var txis = $('#txtISLet').val();
                                    var txcomissao = $('#txtComissaoLet').val();
                                    var txjuro = $('#txtJurosLet').val();
                                    var data_virt = getVirtualDate();
                                    var dataString = "";
                                    var valido = false;
									if ($('#chkSacadoLet').prop('checked')) {
                                        if($('#hddIdEmpresaSacado').val() === "") {
                                            $('.error').show().html('<span id="error"> Por favor, escolha o sacado </span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }
                                        else {
                                            var id_emp = $('#hddIdEmpresaSacado').val();
                                            var nome_sacado = $('#txtClienteLetInt').val();
                                            valido = true;
                                            dataString = "id_emp=" + id_emp + "&nome_sacado=" + nome_sacado + "&valor=" + valor + "&prazo=" + prazo + "&txis=" + txis + "&txcomissao=" + txcomissao + "&txjuro=" + txjuro + "&data_virt=" + data_virt + "&tipocli=interno" + "&tipo=pedido_letra";
                                        }
                                    }
									//No caso de for externo
                                    else {
                                        if($('#txtTotalLetExt').val() === "") {
                                            $('.error').show().html('<span id="error"> Por favor, escolha uma fatura </span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }
										else if(parseFloat($('#txtPrazoLet').val()) > parseFloat($('#txtPrazoMax').val())) {
                                            $('.error').show().html('<span id="error"> Por favor, insira um prazo menor que o prazo máximo </span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }
										else if(parseInt($('#txtValorLet').val().replace('.', '').replace(',', '.')) > parseInt($('#txtTotalLetExt').val().replace('.', '').replace(',', '.'))) {
                                            $('.error').show().html('<span id="error"> Por favor, insira um valor menor do que o total da fatura </span>');
                                            $("body").animate({
                                                scrollTop: 0
                                            });
                                        }
                                        else {
                                            var id_fat = $('#slcNumFatLet').val();
                                            var valor_total = $('#txtTotalLetExt').val();
                                            var prazo_max = $('#txtPrazoMax').val();
                                            valido = true;
                                            dataString = "id_fat=" + id_fat + "&valor_total=" + valor_total + "&valor=" + valor + "&prazo=" + prazo + "&prazo_max=" + prazo_max + "&txis=" + txis + "&txcomissao=" + txcomissao + "&txjuro=" + txjuro + "&data_virt=" + data_virt + "&tipocli=externo" + "&tipo=pedido_letra";
                                        } 
                                    }
                                    if (valido === true) {
                                        $.ajax({
                                            type: "POST",
                                            url: "functions/funcoes_banco.php",
                                            data: dataString,
                                            dataType: "json",
                                            success: function(dados) {
                                                if (dados.sucesso === true) {
                                                    $('input[name="saldo"]').val(number_format(dados.saldo, 2, ',', '.') + " " + dados.moeda);
                                                    $('#var_content').load('conteudo_banco.php #carteira_letra', function(){
                                                        $('.loading').hide();
                                                        $('#tblCarteiraLetraVazia').hide();
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
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        //--
                        $(document).on('click', '#btnAceitaLetra', function(event) {
                            if (event.handler !== true) {
                                var dados = {};
                                $('#tblLetras tr').each(function(key, value) {
                                    if (key != "0") {
                                        dados[key] = {};
                                        dados[key].id_letra = $(this).find('input[type=checkbox]').val();
                                        var checked = "";
                                        if ($(this).find('input[type=checkbox].chk').prop('checked') === true) {
                                            checked = 1;
                                        } else
                                            checked = 0;
                                        dados[key].aceite = checked;
                                    }
                                });
                                if (Object.size(dados) > 0) {
                                    var dataString = "dados=" + JSON.stringify(dados) + "&tipo=aceita_letra";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                location.reload();
                                            } else {
                                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                                $("body").animate({
                                                    scrollTop: 0
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    $('.error').show().html('<span id="error"> Não existem letras </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        //--
                        $(document).on('change', '#slcDescAumento', function(event) {
                            if (event.handler !== true) {
                                var tipo_adiant = "oth";
								var limt_adiant = $('#hddLimiteAdiantamento').val();
								if ($('#slcDescAumento').val() == "Adiantamento do cliente") {
                                    tipo_adiant = "cli";
                                }
                                
								var dataString = "tipo_adiant=" + tipo_adiant + "&limt_adiant=" + limt_adiant + "&tipo=chk_plfnd_adiant";
                                $.ajax({
									type: "POST",
									url: "functions/funcoes_banco.php",
									data: dataString,
									dataType: "json",
									success: function(dados) {
										if (dados.sucesso === true) {
											$('#hddIdRegra').val(dados.id_regra);
											// $('#txtPlafond').val(number_format(dados.plafond, 2, ',', '.'));
											$('#txtPlafond').attr('value', number_format(dados.plafond, 2, ',', '.'));
											$('#limiteAdiantamento').children('input').val('[Limite está definido em ' + number_format(limt_adiant, 2, ',', '.') + '% do Plafond: ' + number_format(dados.max_aum, 2, ',', '.') + '€]');
											if (tipo_adiant == "cli") {
												$('#limiteAdiantamento').show();
												$('#clienteAdiantamento').show();
											}
											else {
												$('#limiteAdiantamento').hide();
												$('#clienteAdiantamento').hide();
											}
										}
										else{
											$('#txtPlafond').val('0,00');
											$('#slcDescAumento option').eq(0).prop("selected", true);
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
						//--
                        $(document).on('click', '#btnRegAumento', function(event) {
                            if (event.handler !== true) {
                                if ($('#slcDescAumento').val() == "0") {
                                    $('.error').show().html('<span id="error"> Por favor, escolha a descrição </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else if ($('#txtValorAumento').val() === "" || $('#txtValorAumento').val() == "0") {
                                    $('.error').show().html('<span id="error"> Por favor, indique o valor </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else if (parseFloat($('#txtValorAumento').val().replace('.', '').replace(',', '.')) > parseFloat($('#txtPlafond').val().replace('.', '').replace(',', '.'))) {
                                    $('.error').show().html('<span id="error"> Não tem plafond suficiente disponivel </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else if ($('#slcDescAumento').val() == "Adiantamento do cliente" && parseFloat($('#txtValorAumento').val().replace('.', '').replace(',', '.')) > parseFloat($('#txtPlafond').val().replace('.', '').replace(',', '.') * $('#hddLimiteAdiantamento').val() / 100)) {
                                    $('.error').show().html('<span id="error"> Insira um valor inferior ao limite de adiantamento </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else {
                                    var id_banco = $('#hddIdBanco').val();
                                    var id_conta = $('#hddIdConta').val();
                                    var id_regra = $('#hddIdRegra').val();
                                    var plafond = $('#txtPlafond').val();
                                    var valor = $('#txtValorAumento').val();
                                    var moeda = $('input[name="txtMoeda"]').val();
                                    var descricao = $('#slcDescAumento').val();
                                    var data_virt = getVirtualDate();
                                    var adiantamento_cliente = false;
                                    var cliente = "";
                                    var dataString = "";
                                    if ($('#slcDescAumento').val() == "Adiantamento do cliente") {
                                        adiantamento_cliente = true;
                                        cliente = $('#txtCliente').val();
                                        dataString = "id_banco=" + id_banco + "&id_conta=" + id_conta + "&id_regra=" + id_regra + "&descricao=" + descricao + "&plafond=" + plafond + "&valor=" + valor + "&moeda=" + moeda + "&adiantamento=" + adiantamento_cliente + "&cliente=" + cliente + "&data_virt=" + data_virt + "&tipo=adiantamento";
                                    }
                                    else
                                        dataString = "id_banco=" + id_banco + "&id_conta=" + id_conta + "&id_regra=" + id_regra + "&descricao=" + descricao + "&plafond=" + plafond + "&valor=" + valor + "&moeda=" + moeda + "&adiantamento=" + adiantamento_cliente + "&data_virt=" + data_virt + "&tipo=adiantamento";
                                    
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                location.reload();
                                            }
                                            else{
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
                        
						/*//-- MOVED TO user.js
                        $(document).on('change', '#slcFornecedorA', function(event) {
                            if (event.handler !== true) {
                                var id = $(this).val();
                                if (id != "0") {
                                    var dataString = 'id=' + id + "&tipo=moeda_fornecedor";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            $('input[name="txtMoeda"]').val(dados.moeda);
                                            $('#ISOmoeda').val(dados.isomoeda);
                                        }
                                    });
                                }
                                event.handler = true;
                            }
                        });
                        //--
                        $(document).on('click', '#btnRegAdiantamentoF', function(event) {
                            if (event.handler !== true) {
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
                                else if (parseFloat($('#txtAdiantamentoFornec').val().replace('.', '').replace(',', '.')) > parseFloat($('input[name="saldo"]').val().replace('.', '').replace(',', '.'))) {
                                    $('.error').show().html('<span id="error"> Lamentamos, mas não tem saldo suficiente </span>');
                                    $("body").animate({
                                        scrollTop: 0
                                    });
                                }
                                else {
                                    var id_fornecedor = $('#slcFornecedorA').val();
                                    var fornecedor = $('#slcFornecedorA option:selected').text();
                                    var valor = $('#txtAdiantamentoFornec').val();
                                    var isomoeda = $('#ISOmoeda').val();
                                    var data_virt = getVirtualDate();
                                    var dataString = "id_fornecedor=" + id_fornecedor + "&fornecedor=" + fornecedor + "&valor=" + valor + "&ISO4217=" + isomoeda + "&data_virt=" + data_virt + "&tipo=adiant_fornec";
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        data: dataString,
                                        dataType: "json",
                                        success: function(dados) {
                                            if (dados.sucesso === true) {
                                                location.reload();
                                            }
                                            else{
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
                        //-- */
						
						/* COM DADOS CACHE (EM TESTE) */
						$(document).on('change', '#slcPaisAcao', function(event) {
                            if (event.handler !== true) {
                                var nome_bolsa = $('#slcPaisAcao option:selected').text().split(' ')[2];
								// Solução para que possa funcionar para Reino Unido
								if (nome_bolsa == '-') nome_bolsa = $('#slcPaisAcao option:selected').text().split(' ')[3];
								
								var dataString = "nome_bolsa=" + nome_bolsa + "&linhas=0&tipo=carrega_cotacao";
								
                                $('#frmOrdemCompra').hide();
                                $('#frmOrdemCompra').find('input[name="txtPrecoAlvoAcao"]').val("");
                                $('#frmOrdemCompra').find('input[name="txtDataLimiteAcao"]').val("");
                                emptyTable('#tblAcoes');

                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function(dados) {
                                        /* if (dados.vazio == true) {
                                            refresh();
                                        } else { */
                                            $.each(dados.dados_in, function(i, item) {
                                                var nome = dados.dados_in[i].nome_acao;
                                                $('#tblAcoes tr').eq(i + 1).children('td').eq(0).text(nome);
                                                $('#tblAcoes tr').eq(i + 1).children('td').eq(1).text(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                                                // $('#tblAcoes tr').eq(i + 1).children('td').eq(2).text(number_format(dados.dados_in[i].change, 3, ',', '.'));
												if (dados.dados_in[i].change > 0) {
													$('#tblAcoes tr').eq(i + 1).children('td').eq(3).text('+' + number_format(dados.dados_in[i].change, 3, ',', '.'));
												} else {
													$('#tblAcoes tr').eq(i + 1).children('td').eq(3).text(number_format(dados.dados_in[i].change, 3, ',', '.'));
												}
                                                $('#tblAcoes tr').eq(i + 1).children('td').eq(3).text(number_format(dados.dados_in[i].open, 3, ',', '.'));
                                                $('#tblAcoes tr').eq(i + 1).children('td').eq(4).text(number_format(dados.dados_in[i].days_high, 3, ',', '.') + ' / ' + number_format(dados.dados_in[i].days_low, 3, ',', '.'));
                                                var name = $('#frmOrdemCompra').find('input[name="txtNome"]').val();
                                                var preco_ant = $('#frmOrdemCompra').find('input[name="txtPreco"]').val();
                                                if (name == nome) {
                                                    $('#frmOrdemCompra').find('input[name="txtPreco"]').val(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                                                    if (preco_ant !== "" && preco_ant != number_format(dados.dados_in[i].last_trade_price, 3, ',', '.')) {
                                                        $('#frmOrdemCompra').find('input[name="txtQuantidade"]').val('');
                                                        $('#frmOrdemCompra').find('input[name="txtSubtotal"]').val('0,000');
                                                        $('#frmOrdemCompra').find('input[name="txtTotal"]').val('0,000');
                                                    }
                                                }
                                            });
                                        // }
                                    }
                                });
                                event.handler = true;
                            }
                            return false;
                        });
						/* */
                        
						/*
						$(document).on('change', '#slcPaisAcao', function(event) {
							if (event.handler !== true) {
								var paisAcao = $('#slcPaisAcao option:selected').text().split(' ')[0];
								var urlAcoes;
								// Verificar Bolsa
								if (paisAcao == 'Alemanha')
									urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ADS.DE%22%2C%22ALV.DE%22%2C%22BAS.DE%22%2C%22BAYN.DE%22%2C%22BEI.DE%22%2C%22BMW.DE%22%2C%22CBK.DE%22%2C%22CON.DE%22%2C%22DAI.DE%22%2C%22DB1.DE%22%2C%22DBK.DE%22%2C%22DPW.DE%22%2C%22DTE.DE%22%2C%22EOAN.DE%22%2C%22FME.DE%22%2C%22FRE.DE%22%2C%22HEI.DE%22%2C%22HEN3.DE%22%2C%22IFX.DE%22%2C%22LHA.DE%22%2C%22LIN.DE%22%2C%22MRK.DE%22%2C%22MUV2.DE%22%2C%22RWE.DE%22%2C%22SAP.DE%22%2C%22SDF.DE%22%2C%22SIE.DE%22%2C%22TKA.DE%22%2C%22VNA.DE%22%2C%22VOW3.DE%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
								else if (paisAcao == 'Bélgica')
									urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ABI.BR%22%2C%22ACKB.BR%22%2C%22AGS.BR%22%2C%22BEFB.BR%22%2C%22BEKB.BR%22%2C%22BELG.BR%22%2C%22BPOST.BR%22%2C%22COFB.BR%22%2C%22COLR.BR%22%2C%22DELB.BR%22%2C%22DIE.BR%22%2C%22DL.BR%22%2C%22ELI.BR%22%2C%22GBLB.BR%22%2C%22GSZ.BR%22%2C%22KBC.BR%22%2C%22SOLB.BR%22%2C%22TNET.BR%22%2C%22UCB.BR%22%2C%22UMI.BR%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
								else if (paisAcao == 'EUA')
									urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22AAPL%22%2C%22AXP%22%2C%22BA%22%2C%22CAT%22%2C%22CSCO%22%2C%22CVX%22%2C%22DD%22%2C%22DIS%22%2C%22GE%22%2C%22GS%22%2C%22HD%22%2C%22IBM%22%2C%22INTC%22%2C%22JNJ%22%2C%22JPM%22%2C%22KO%22%2C%22MCD%22%2C%22MMM%22%2C%22MRK%22%2C%22MSFT%22%2C%22NKE%22%2C%22PFE%22%2C%22PG%22%2C%22TRV%22%2C%22UNH%22%2C%22UTX%22%2C%22V%22%2C%22VZ%22%2C%22WMT%22%2C%22XOM%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
								else if (paisAcao == 'França')
									urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22AC.PA%22%2C%22ACA.PA%22%2C%22AI.PA%22%2C%22AIR.PA%22%2C%22ALO.PA%22%2C%22ALU.PA%22%2C%22BN.PA%22%2C%22BNP.PA%22%2C%22CA.PA%22%2C%22CAP.PA%22%2C%22CS.PA%22%2C%22DG.PA%22%2C%22EDF.PA%22%2C%22EI.PA%22%2C%22EN.PA%22%2C%22FP.PA%22%2C%22FR.PA%22%2C%22GLE.PA%22%2C%22GSZ.PA%22%2C%22KER.PA%22%2C%22LG.PA%22%2C%22LR.PA%22%2C%22MC.PA%22%2C%22ML.PA%22%2C%22MT.PA%22%2C%22OR.PA%22%2C%22ORA.PA%22%2C%22PUB.PA%22%2C%22RI.PA%22%2C%22RNO.PA%22%2C%22SAF.PA%22%2C%22SAN.PA%22%2C%22SGO.PA%22%2C%22SOLB.PA%22%2C%22SU.PA%22%2C%22TEC.PA%22%2C%22UG.PA%22%2C%22UL.PA%22%2C%22VIE.PA%22%2C%22VIV.PA%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
								else if (paisAcao == 'Reino')
									urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22AAL.L%22%2C%22ABF.L%22%2C%22ADM.L%22%2C%22ADN.L%22%2C%22AHT.L%22%2C%22ANTO.L%22%2C%22ARM.L%22%2C%22AV.L%22%2C%22AZN.L%22%2C%22BA.L%22%2C%22BAB.L%22%2C%22BARC.L%22%2C%22BATS.L%22%2C%22BDEV.L%22%2C%22BG.L%22%2C%22BKG.L%22%2C%22BLND.L%22%2C%22BLT.L%22%2C%22BNZL.L%22%2C%22BP.L%22%2C%22BRBY.L%22%2C%22BT-A.L%22%2C%22CCL.L%22%2C%22CNA.L%22%2C%22CPG.L%22%2C%22CPI.L%22%2C%22CRH.L%22%2C%22DC.L%22%2C%22DGE.L%22%2C%22DLG.L%22%2C%22EXPN.L%22%2C%22EZJ.L%22%2C%22FRES.L%22%2C%22GFS.L%22%2C%22GKN.L%22%2C%22GLEN.L%22%2C%22GSK.L%22%2C%22HIK.L%22%2C%22HL.L%22%2C%22HMSO.L%22%2C%22HSBA.L%22%2C%22IAG.L%22%2C%22IHG.L%22%2C%22III.L%22%2C%22IMT.L%22%2C%22INTU.L%22%2C%22ISAT.L%22%2C%22ITRK.L%22%2C%22ITV.L%22%2C%22JMAT.L%22%2C%22KGF.L%22%2C%22LAND.L%22%2C%22LGEN.L%22%2C%22LLOY.L%22%2C%22LSE.L%22%2C%22MGGT.L%22%2C%22MKS.L%22%2C%22MNDI.L%22%2C%22MRW.L%22%2C%22NG.L%22%2C%22NXT.L%22%2C%22OML.L%22%2C%22PRU.L%22%2C%22PSON.L%22%2C%22RB.L%22%2C%22RBS.L%22%2C%22RDSA.L%22%2C%22RDSB.L%22%2C%22REL.L%22%2C%22RIO.L%22%2C%22RMG.L%22%2C%22RR.L%22%2C%22RRS.L%22%2C%22RSA.L%22%2C%22SAB.L%22%2C%22SBRY.L%22%2C%22SDR.L%22%2C%22SGE.L%22%2C%22SHP.L%22%2C%22SKY.L%22%2C%22SL.L%22%2C%22SMIN.L%22%2C%22SN.L%22%2C%22SPD.L%22%2C%22SSE.L%22%2C%22STAN.L%22%2C%22STJ.L%22%2C%22SVT.L%22%2C%22TPK.L%22%2C%22TSCO.L%22%2C%22TUI.L%22%2C%22TW.L%22%2C%22ULVR.L%22%2C%22UU.L%22%2C%22VOD.L%22%2C%22WOS.L%22%2C%22WPP.L%22%2C%22WTB.L%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
								else if (paisAcao == 'Portugal')
									// urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ALTR.LS%22%2C%22BANIF.LS%22%2C%22BCP.LS%22%2C%22BPI.LS%22%2C%22CTT.LS%22%2C%22EDP.LS%22%2C%22EDPR.LS%22%2C%22EGL.LS%22%2C%22GALP.LS%22%2C%22IPR.LS%22%2C%22JMT.LS%22%2C%22NOS.LS%22%2C%22PTC.LS%22%2C%22PTI.LS%22%2C%22RENE.LS%22%2C%22SEM.LS%22%2C%22SON.LS%22%2C%22TDSA.LS%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
									urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ALTR.LS%22%2C%22BCP.LS%22%2C%22BPI.LS%22%2C%22CTT.LS%22%2C%22EDP.LS%22%2C%22EDPR.LS%22%2C%22EGL.LS%22%2C%22GALP.LS%22%2C%22IPR.LS%22%2C%22JMT.LS%22%2C%22NOS.LS%22%2C%22PHR.LS%22%2C%22PTI.LS%22%2C%22RENE.LS%22%2C%22SEM.LS%22%2C%22SON.LS%22%2C%22TDSA.LS%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
								
								$('#frmOrdemCompra').hide();
                                $('#frmOrdemCompra').find('input[name="txtPrecoAlvoAcao"]').val("");
                                $('#frmOrdemCompra').find('input[name="txtDataLimiteAcao"]').val("");
								$('#frmOrdemCompra').find('input[name="txtQtdAcao"]').val("");
								$('#frmOrdemCompra').find('input[name="txtSubtotalAcao"]').val("");
								$('#frmOrdemCompra').find('input[name="txtTotalAcao"]').val("");
                                emptyTable('#tblAcoes');
								
								$.ajax({
									type: "POST",
									url: urlAcoes,
									dataType: "json",
									success: function(dados) {
										$.each(dados.query.results.quote, function(i, item) {
											$('#tblAcoes').append('<tr>' +
												'<td class="inputAccao">' + dados.query.results.quote[i].symbol + '</td>' +
												'<td class="inputAccao">' + number_format(dados.query.results.quote[i].LastTradePriceOnly, 3, ',', '.') + '</td>' +
												'<td class="inputAccao">' + number_format(dados.query.results.quote[i].Change, 3, ',', '.') + '</td>' +
												'<td class="inputAccao">' + number_format(dados.query.results.quote[i].Open, 3, ',', '.') + '</td>' +
												'<td class="inputAccao">' + number_format(dados.query.results.quote[i].DaysHigh, 3, ',', '.') + ' / ' + number_format(dados.query.results.quote[i].DaysLow, 3, ',', '.') + '</td>' +
												'<td style="background-color: #77a4d7; padding: 4px; cursor: pointer;"><div id="btnCompraAcao_' + i + '" name="btnCompraAcao" class="labelicon icon-carrinho comprarAcao" style="font-size: 14px;"></div></td>' +
												'<td style="color: white; background-color: #77a4d7; padding: 4px; cursor: pointer;"><div id="btnGraficoAcao_' + i + '" name="btnGraficoAcao" class="graficoAcao" style="font-size: 11px;"> Gráfico </div></td>' +
											'</tr>');
										});
									}
								});
								event.handler = true;
							}
							return false;
						});
						/* */
						
                        $(document).on('change', '#chkOrdemAcoes', function(event) {
                            if (event.handler !== true) {
                                $('#txtQtdAcao').val("");
                                $('#txtSubtotalAcao').val("");
                                
                                if ($(this).prop('checked')){
                                    $('input[name="txtQtdAcao"]').val("");
                                    $('input[name="txtSubtotalAcao"]').val("0,00");
                                    $('#acoesCompraAgendada').fadeOut("linear");
                                    $('#acoesCompraImediata').fadeIn("linear");
                                    
                                }
                                else {
                                    $('input[name="txtPrecoAlvoAcao"]').val("");
                                    $('input[name="txtDataLimiteAcao"]').val("");
                                    /* */
                                    var complVirtualDate = getVirtualDate();
                                    var splitVirtualDate = complVirtualDate.split("/");
                                    var virtualDate = new Date (splitVirtualDate[0] + '-' + splitVirtualDate[1] + '-' + splitVirtualDate[2]);
                                    virtualDate.setDate(virtualDate.getDate() + 1);
                                    virtualDate.setMonth(virtualDate.getMonth() + 1);
                                    
                                    $('.campoData').datetimepicker({
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
                                        timepicker: false,
                                        format: 'd-m-Y',
                                        formatDate: 'd-m-Y',
                                        yearStart: 2016,
                                        yearEnd: 2020,
                                        minDate: virtualDate.getDate() + '-' + virtualDate.getMonth() + '-' + virtualDate.getFullYear()
                                    });
                                    /* */
									$('input[name="txtQtdAcao"]').val("");
                                    $('input[name="txtSubtotalAcao"]').val("0,00");
                                    $('#acoesCompraImediata').fadeOut("linear");
                                    $('#acoesCompraAgendada').fadeIn("linear");
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        
                        $(document).on('change', '#chkOrdemAcoesVender', function(event) {
                            if (event.handler !== true) {
                                if ($(this).prop('checked')){
                                    $('input[name="txtPrecoVendaAcoes"]').val("");
									$('input[name="txtQtdVendaAcoes"]').val("");
                                    $('input[name="txtTotalVendaAcoes"]').val("0,000");
                                    $('#acoesVendaAgendada').fadeOut("linear");
                                    $('input[name="txtPrecoVendaAcoes"]').closest('.linha10').fadeIn("linear");
                                    
                                }
                                else {
                                    $('input[name="txtPrecoVendaAcoes"]').val("0,001"); // Senão não dispara evento "keyup" em preço alvo
									$('input[name="txtPrecoAlvoAcao"]').val("");
                                    $('input[name="txtDataLimiteAcao"]').val("");
                                    /* */
                                    var complVirtualDate = getVirtualDate();
                                    var splitVirtualDate = complVirtualDate.split("/");
                                    var virtualDate = new Date (splitVirtualDate[0] + '-' + splitVirtualDate[1] + '-' + splitVirtualDate[2]);
                                    virtualDate.setDate(virtualDate.getDate() + 1);
                                    virtualDate.setMonth(virtualDate.getMonth() + 1);
                                    
                                    $('.campoData').datetimepicker({
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
                                        timepicker: false,
                                        format: 'd-m-Y',
                                        formatDate: 'd-m-Y',
                                        yearStart: 2016,
                                        yearEnd: 2020,
                                        minDate: virtualDate.getDate() + '-' + virtualDate.getMonth() + '-' + virtualDate.getFullYear()
                                    });
                                    /* */
									$('input[name="txtQtdVendaAcoes"]').val("");
                                    $('input[name="txtTotalVendaAcoes"]').val("0,000");
                                    $('input[name="txtPrecoVendaAcoes"]').closest('.linha10').fadeOut("linear");
                                    $('#acoesVendaAgendada').fadeIn("linear");
                                }
                                event.handler = true;
                            }
                            return false;
                        });
                        
                        //--
                        $(document).on('click', '.graficoAcao', function(event) {
                            if (event.handler !== true) {
                                showLoading();
								var id_pais = $('#slcPaisAcao').val();
								var nome_bolsa = $('#slcPaisAcao option:selected').text().split(' ')[2];
								var nome = $(this).closest('tr').children('td').eq(0).text();
                                var chartData = (function() {
                                    var json;
                                    $.ajax({
                                        type: "POST",
                                        url: "functions/funcoes_banco.php",
                                        dataType: "json",
                                        // data: "nome_acao=" + nome + "&tipo=dados_grafico",
										data: "id_pais=" + id_pais + "&nome_bolsa=" + nome_bolsa + "&nome_acao=" + nome + "&tipo=dados_grafico",
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
                                
                                var chartDataJSON = chartData.getJson();
                                var chartDataConvert = [];
                                for(var i = 0; i < chartDataJSON.length; i++ ) {
                                  chartDataConvert.push({
                                    cotacao: chartDataJSON[i].cotacao,
                                    data_alteracao: chartDataJSON[i].data_alteracao
                                  });
                                }
                                
                                var chart = AmCharts.makeChart("chartdiv_pop_up", {
                                    "type": "serial",
                                    "theme": "light",
                                    "legend": {
                                        "useGraphSettings": true
                                    },
                                    "dataProvider": chartDataConvert,
                                    "valueAxes": [{
                                        "id":"v1",
                                        "axisColor": "#6fa5e2",
                                        "axisThickness": 2,
                                        "gridAlpha": 1,
                                        "axisAlpha": 1,
                                        "position": "left",
                                        "title": "Cotação"
                                    }],
                                    "graphs": [{
                                        "valueAxis": "v1",
                                        "lineColor": "#6fa5e2",
                                        "bullet": "round",
                                        "bulletBorderThickness": 1,
                                        "hideBulletsCount": 30,
                                        "title": nome,
                                        "valueField": "cotacao",
                                        "fillAlphas": 0
                                    }],
                                    "chartScrollbar": {},
                                    "chartCursor": {
                                        "cursorPosition": "mouse",
                                        "categoryBalloonDateFormat": "JJ:NN, DD MMMM",
                                        "selectWithoutZooming": true
                                    },
                                    "categoryField": "data_alteracao",
                                    "categoryAxis": {
                                        "parseDates": true,
                                        "axisColor": "#DADADA",
                                        "minorGridEnabled": true,
                                        "minPeriod": "mm"
                                    },
                                    "export": {
                                        "enabled": true,
                                        "position": "bottom-right"
                                     }
                                });

								function zoomChart(){
                                    chart.zoomToIndexes(chart.dataProvider.length - 20, chart.dataProvider.length - 1);
                                }
								
                                chart.addListener("dataUpdated", zoomChart);
                                zoomChart();
                                
								hideLoading();
                                $('#chartdiv_pop_up').show();
                                $('#chartdiv_pop_up').append('<a id="chartdiv_pop_up_close">x</a>');
                                event.handler = true;
                            }
                            return false;
                        });
                        
                        $(document).on('click', 'a#chartdiv_pop_up_close', function() { $('#chartdiv_pop_up').fadeOut("slow"); });
						
						//--
						$(document).on('click', '.rem_trans_agend', function() {
                           if (event.handler !== true) {
                                var id_pae = $(this).children('#hddIdCot').val();
                                var tipo_trans = $(this).children('#hddTipoTrans').val();
                                var dataString = "id_pae=" + id_pae + "&tipo_trans=" + tipo_trans + "&tipo=rem_linha";
                                
                                $.ajax({
                                    type: "POST",
                                    url: "functions/funcoes_banco.php",
                                    data: dataString,
                                    dataType: "json",
                                    success: function(dados) {
                                        if (dados.vazio == true) {
                                            if (tipo_trans == 'C') {
                                                $('#tblCompraDetail').hide();
                                                $('#tblCompraDetailVazia').show();
                                            } else {
                                                $('#tblVendaDetail').hide();
                                                $('#tblVendaDetailVazia').show();
                                            }   
                                        } else {
                                            var tbl = "";
                                            if (tipo_trans == 'C') tbl = '#tblCompraDetail';
                                            else tbl = '#tblVendaDetail';
											emptyTable(tbl);
                                            $.each(dados.dados_in, function(i, item) {
                                                $(tbl).append('<tr>' +
                                                    '<td style="padding: 6px;">' + dados.dados_in[i].nome_pais + '</td>' +
                                                    '<td style="padding: 6px;">' + dados.dados_in[i].nome_bolsa + '</td>' +
                                                    '<td style="padding: 6px;">' + dados.dados_in[i].nome_acao + '</td>' +
                                                    '<td style="padding: 6px;">' + dados.dados_in[i].qtd + '</td>' +
                                                    '<td style="padding: 6px;">' + number_format(dados.dados_in[i].preco_alvo, 3, ',', '.') + ' ' + dados.dados_in[i].simbolo + '</td>' +
                                                    '<td style="padding: 6px;">' + dados.dados_in[i].data_limite + '</td>' +
                                                    '<td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">' +
                                                        '<div class="labelicon icon-garbage rem_trans_agend">' +
                                                            '<input id="hddIdCot" name="hddIdCot" type="hidden" value="' + dados.dados_in[i].id_preco_alvo + '">' +
                                                            '<input id="hddTipoTrans" name="hddTipoTrans" type="hidden" value="' + tipo_trans + '">' +
                                                        '</div>' +
                                                    '</td>' +
                                                    '</tr>');
                                            });
                                        }
                                    }
                                });
                                event.handler = true;
                            }
                            return false;
						});
						
						/* Slide plugin */
						// document.getElementById('links').onclick = function (event) {
						$(document).on('click', '#links', function(event) {
							event = event || window.event;
							var target = event.target || event.srcElement,
							link = target.src ? target.parentNode : target,
							options = {index: link,
								event: event,
								interval: 5000,
								// Set to true to initialize the Gallery with carousel specific options:
								carousel: true,
								displayClass: 'blueimp-gallery-display',
								controlsClass: 'blueimp-gallery-controls',
								singleClass: 'blueimp-gallery-single',                  
								leftEdgeClass: 'blueimp-gallery-left',
								rightEdgeClass: 'blueimp-gallery-right',
								enableKeyboardNavigation: true,
								closeOnEscape: true
							},
							links = this.getElementsByTagName('a');
							blueimp.Gallery(links, options);
						});
					    //--
					   
                    });
                }
            } else {
                location.reload();
            }
        }
        return false;
    });
});