/*
 * @Author: Ricardo Órfão
 * @Date:   2014-05-04 13:22:10
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-10-01 18:53:51
 */

var intervalo = 1000; //milissegundos entre cada chamada
var timer = null;

if (!Math.round10) {
    Math.round10 = function (value, exp) {
        return decimalAdjust('round', value, exp);
    };
}
if (!Math.floor10) {
    Math.floor10 = function (value, exp) {
        return decimalAdjust('floor', value, exp);
    };
}
if (!Math.ceil10) {
    Math.ceil10 = function (value, exp) {
        return decimalAdjust('ceil', value, exp);
    };
}

Object.size = function (obj) {
    var size = 0,
            key;
    for (key in obj) {
        if (obj.hasOwnProperty(key))
            size++;
    }
    return size;
};

function acoesMask() {
    $('.acoes').inputmask('decimal', {
        rightAlignNumerics: true,
        allowPlus: false,
        allowMinus: false,
        radixPoint: ",",
        digits: 3,
        autoGroup: true,
        groupSeparator: ".",
        groupSize: 3
    });
}

function apagarLinhaFactoring(nodo) {
    var tot_valor = 0;
    var tot_comissao = 0;
    var tot_juros = 0;
    var tot_seguro = 0;
    var juro_anual = 0.00;
    var juro_mensal = 0.00;
    var seguro = 0.00;
    var valores = [];
    var count = $('#tblFactoring tr').length - 1;
    if (count == 2) {
        $('#tblFactoring').fadeOut();
        $('#btnEfetuarFactoring').closest('.linha').fadeOut();
    }
    $(nodo).parent().parent().remove();

    if (count > 2) {
        $.each($('#tblFactoring tr'), function (key, value) {
            if (key > 0 && key < ($('#tblFactoring tr').length - 1)) {
                if ($(this).children('td').eq(4).text() !== 0) {
                    tot_valor += parseFloat(formatValor($(this).children('td').eq(4).text()));
                    valores.push($(this).children('td').eq(5).text());
                }
            }
        });
        var comissao = $('#hddComissaoFact').val();
        tot_comissao = (comissao / 100) * tot_valor;
        var recurso = $('input[name=chkAllFact]').prop('checked');
        var tempo = Math.max.apply(null, valores);
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
        $('#tblFactoring tr:last-child').children('td').eq(4).text(number_format(tot_valor, 2, ',', '.'));
        $('#tblFactoring tr:last-child').children('td').eq(5).text(tempo);
        $('#tblFactoring tr:last-child').children('td').eq(7).text(number_format(tot_comissao, 2, ',', '.'));
        $('#tblFactoring tr:last-child').children('td').eq(8).text(number_format(tot_juros, 2, ',', '.'));
        $('#tblFactoring tr:last-child').children('td').eq(9).text(number_format(tot_seguro, 2, ',', '.'));
    }
}

function calcCalendarHeight() {
    var h = $(window).height() - 40;
    return h;
}

function carregaMail(BrowserDetect) {
    $('#pag_raw').hide();
    $('#var_content').show().load('conteudo_user.php #email', function () {
        if ($('input[name="windowCommEvent"]').length) {
            $('form[name="windowComm"]').remove();
        }
        $.initWindowMsg();
        var childWin;
        hideLoading();
        $('#divLerMail').hide();
        $('#divNovaMensagem').hide();
        $('#tblEmailsEliminados').hide();
        $('#tblEmailsEnviados').hide();
        $('#txtaEditor').ckeditor();
        $('#txtaMensagem').ckeditor();
        var qs = $('input.txtProcEmails').quicksearch('.tab_emails tbody tr', {
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

        $(document).on('click', '.ler', {
            BrowserDetect: BrowserDetect
        }, fLerEmail);
        $(document).on('click', '.voltarDir', fVoltarEmail);
        $(document).on('click', '#btnAnexar', fBtnAnexar);
        $(document).on('click', '#btnDelEmail', {
            qs: qs
        }, fBtnDelEmail);
        $(document).on('click', '#btnEliminar', {
            qs: qs
        }, fBtnElimEmail);
        $(document).on('click', '#btnEnviar', fBtnEnviarEmail);
        $(document).on('click', '#btnLimpar', fBtnLimparEmail);
        $(document).on('click', '#btnNovaMsg', fNovoEmail);
        $(document).on('click', '#btnReencaminhar', {
            BrowserDetect: BrowserDetect
        }, fBtnReencEmail);
        $(document).on('click', '#btnRsp', {
            BrowserDetect: BrowserDetect
        }, fBtnRespEmail);
        $(document).on('change', '#fileAnexar', fFileAnexar);
        $(document).on('change', '#slcFiltrarEmails', {
            qs: qs
        }, fSlcFiltrarEmails);
        $(document).on('click', '#txtDestinatario', {
            childWin: childWin
        }, fDestinatarioEmail);
        $(document).on('click', 'button[name^="btn_"]', {
            qs: qs
        }, fBtnMenuEmail);

        $.windowMsg("idEmpresa", function (message) {
            if ($("#hddDestinatario").length == 1) {
                if ($("#hddDestinatario").val() === "") {
                    $("#hddDestinatario").remove();
                }
            }
            $('#txtDestinatario').after('<input id="hddDestinatario" name="hddDestinatario" type="hidden" value="' + message + '">');
        });
        $.windowMsg("nomeEmpresa", function (message) {
            if ($('#txtDestinatario').val() === "") {
                $('#txtDestinatario').val(message);
            } else {
                var content = $('#txtDestinatario').val();
                $('#txtDestinatario').val(content + '; ' + message);
            }
        });
    });
}

function clearDuplicates(table) {
    $('#' + table + ' tr').each(function () {
        $(this).find('.inputarea_col1').css("background-color", "transparent");
    });
}

function contaMask() {
    $('.conta').inputmask('9999');
}

function conv_mes(num) {
    var mes;
    switch (num) {
        case 1:
            mes = "Janeiro";
            break;
        case 2:
            mes = 'Fevereiro';
            break;
        case 3:
            mes = 'Março';
            break;
        case 4:
            mes = 'Abril';
            break;
        case 5:
            mes = 'Maio';
            break;
        case 6:
            mes = 'Junho';
            break;
        case 7:
            mes = 'Julho';
            break;
        case 8:
            mes = 'Agosto';
            break;
        case 9:
            mes = 'Setembro';
            break;
        case 10:
            mes = 'Outubro';
            break;
        case 11:
            mes = 'Novembro';
            break;
        case 12:
            mes = 'Dezembro';
            break;
    }
    return mes;
}

function decimalAdjust(type, value, exp) {
    if (typeof exp == 'undefined' || +exp == "0") {
        return Math[type](value);
    }
    value = +value;
    exp = +exp;
    if (isNaN(value) || !(typeof exp == 'number' && exp % 1 == "0")) {
        return NaN;
    }
    value = value.toString().split('e');
    value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
}

function despiste(href) {
    if (href == 'grupo_novo_pag' || href == 'grupo_afet_pag' || href == 'grupo_edit_pag') {
        $.getScriptOnce('js/functions/funcoesCentroDados.js');
        loadContent(href, loadGrupos);
    } else if (href == 'v_empresas' || href == 'del_empresas') {
        $.getScriptOnce("js/functions/funcoesCentroDados.js");
        loadContent(href, loadEmpresas);
    } else if (href == 'user_afet_pag' || href == 'v_users' || href == 'novo_admin') {
        $.getScriptOnce("js/functions/funcoesCentroDados.js");
        loadContent(href, loadUsers);
    } else if (href == 'def_cal' || href == 'e_cal') {
        $.getScriptOnce("js/functions/funcoesCentroDados.js");
        loadContent(href, loadCalendario);
    } else if (href == 'e_atividade' || href == 'n_atividade') {
        $.getScriptOnce("js/functions/funcoesCentroDados.js");
        loadContent(href, loadAtividade);
    } else if (href == 'v_entregas' || href == 'e_tipos' || href == 'n_tipo') {
        $.getScriptOnce("js/functions/funcoesCentralPublica.js");
        loadContent(href, loadEntregas);
        // } else if (href == 'e_produtos' || href == 'add_produtos') {
    } else if (href == 'e_produtos' || href == 'add_produtos' || href == 'afet_produtos' || href == 'add_desc' || href == 'edit_desc') {
        $.getScriptOnce("js/functions/funcoesCentralComercial.js");
        loadContent(href, loadProdutos);
    } else if (href == 'v_faturas') {
        $.getScriptOnce("js/functions/funcoesCentralComercial.js");
        loadContent(href, loadFaturas);
    } else if (href == 'add_familias' || href == 'asc_familias' || href == 'e_familias' || href == 'afet_familias') {
        $.getScriptOnce("js/functions/funcoesCentralComercial.js");
        loadContent(href, loadCategorias);
    } else if (href == 'mod_pass') {
        $.getScriptOnce("js/functions/funcoesCentroDados.js");
        loadContent(href, loadMudarPass);
    } else if (href == 'emprestimos') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadEmprestimo);
    } else if (href == 'extratos') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadExtrato);
    } else if (href == 'locacoes') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadLocacao);
    } else if (href == 'calc_acoes') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadAcao);
    } else if (href == 'titulos_banc') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadTitulo);
    } else if (href == 'outras_op') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadOp);
    } else if (href == 'taxas') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadTaxa);
    } else if (href == 'alertas') {
        $.getScriptOnce("js/functions/funcoesCentralFinanceira.js");
        loadContent(href, loadAlerta);
    }
    //-- Funcionalidades "admin"
    else if (href == 'def_tasks_cal' || href == 'e_tasks_cal') {
        $.getScriptOnce("js/functions/funcoesCentroDados.js");
        loadContent(href, loadTasks);
    }
}

function dynamicInput(tabela) {
    $('#' + tabela).find('.dynamicInput').each(function () {
        $(this).attr('size', ($(this).val().length - 3) + "px");
    });
}

function dynamicInputResize(event) {
    if (event.handler !== true) {
        var tamanho = $(this).val().length;
        if (tamanho <= 4)
            tamanho = 1;
        $(this).attr('size', tamanho + "px");
        event.handler = true;
    }
    return false;
}

function emptySelect(slc) {
    var current = $(slc).index(this);
    while (++current < $(slc).length) {
        $(slc).get(current).options.length = 1;
    }
}

function emptyTable(table) {
    $(table).find("tr:gt(0)").remove();
}

function fChkClick(event) {
    if (event.handler !== true) {
        if ($(this).attr("for") == "chkCategoria") {
            return false;
        } else {
            var dataString;
            if ($(this).closest('#tblDadosFatInt').find('#hddFatInt').val() == 1) {
                if ($('#tblDadosFatInt').find('input:checkbox').length > 1) {
                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                        $(this).closest('#tblDadosFatInt').find('#chkAllInt').closest('.checkbox').find('input').prop('checked', false);
                        $(this).closest('.checkbox').find('input').prop('checked', false);
                    } else {
                        if (($(this).closest('#tblDadosFatInt').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblDadosFatInt').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                            $(this).closest('#tblDadosFatInt').find('#chkAllInt').closest('.checkbox').find('input').prop('checked', true);
                        }
                        $(this).closest('.checkbox').find('input').prop('checked', true);
                        if ($(this).closest('#tblDadosFatInt').find('input[type=checkbox].chk').length > 1) {
                            $('#btnRemFatInt').show();
                        }
                    }
                    if ($(this).closest('#tblDadosFatInt tbody').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                        $('#btnRemFatInt').hide();
                    }
                } else {
                    return false;
                }
            } else if ($(this).closest('#tblDadosFatExt').find('#hddFatExt').val() == 1) {
                if ($('#tblDadosFatExt').find('input:checkbox').length > 1) {
                    if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                        $(this).closest('#tblDadosFatExt').find('#chkAllExt').closest('.checkbox').find('input').prop('checked', false);
                        $(this).closest('.checkbox').find('input').prop('checked', false);
                    } else {
                        if (($(this).closest('#tblDadosFatExt').find('input[type=checkbox].chk').length - 1) == ($(this).closest('#tblDadosFatExt').find('input[type=checkbox].chk').filter(':checked').length + 1)) {
                            $(this).closest('#tblDadosFatExt').find('#chkAllExt').closest('.checkbox').find('input').prop('checked', true);
                        }
                        $(this).closest('.checkbox').find('input').prop('checked', true);
                        if ($(this).closest('#tblDadosFatExt').find('input[type=checkbox].chk').length > 1) {
                            $('#btnRemFatExt').show();
                        }
                    }
                    if ($(this).closest('#tblDadosFatExt tbody').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                        $('#btnRemFatExt').hide();
                    }
                } else {
                    return false;
                }
            } else if ($(this).closest('#frmAtiCat').children('#hddAtiCat').val() == 1 && $(this).closest('#frmAtiCat').find('#slcAtividade').val() != "0") {
                var id_cat = $(this).closest('.checkbox').find('#hddIdCategoria').val();
                var id_ati = $(this).closest('#frmAtiCat').find('#slcAtividade').val();
                var checked = $(this).closest('.checkbox').find('input').prop('checked');
                dataString = "id_categoria=" + id_cat + "&id_atividade=" + id_ati + "&checked=" + checked + "&tipo=alt_ati_fam";
                if (checked === true) {
                    $(this).closest('.checkbox').find('input').prop('checked', false);
                } else {
                    $(this).closest('.checkbox').find('input').prop('checked', true);
                }
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function () {
                        $('#frmCatAti').hide();
                        $('#frmAtiCat').hide();
                        showLoading();
                    },
                    success: function (dados) {
                        if (dados.sucesso === true) {
                            hideLoading();
                            $('#frmAtiCat').show();
                        }
                    }
                });
            } else if ($(this).closest('#frmCatAti').children('#hddCatAti').val() == 1 && $(this).closest('#frmCatAti').find('#slcCatAti').val() != "0") {
                var id_categ = $(this).closest('#frmCatAti').find('#slcCatAti').val();
                var id_ativ = $(this).closest('.checkbox').find('#hddIdAtividade').val();
                var check = $(this).closest('.checkbox').find('input').prop('checked');
                dataString = "id_categoria=" + id_categ + "&id_atividade=" + id_ativ + "&checked=" + check + "&tipo=alt_ati_fam";
                if (check === true) {
                    $(this).closest('.checkbox').find('input').prop('checked', false);
                } else {
                    $(this).closest('.checkbox').find('input').prop('checked', true);
                }
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function () {
                        $('#frmAtiCat').hide();
                        $('#frmCatAti').hide();
                        showLoading();
                    },
                    success: function (dados) {
                        if (dados.sucesso === true) {
                            hideLoading();
                            $('#frmCatAti').show();
                        }
                    }
                });
            } else if ($(this).closest('#tblEntregasGeral').find('#hddEntregas').val() == 1) {
                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                    $(this).closest('#tblEntregasGeral').find('#chkAllEntregas').closest('.checkbox').find('input').prop('checked', false);
                    $(this).closest('.checkbox').find('input').prop('checked', false);
                } else {
                    if (($('#tblEntregasGeral').find('input[type=checkbox].chk').length - 1) == $('#tblEntregasGeral').find('input[type=checkbox].chk').filter(':checked').length + 1) {
                        $(this).closest('#tblEntregasGeral').find('#chkAllEntregas').closest('.checkbox').find('input').prop('checked', true);
                    }
                    $(this).closest('.checkbox').find('input').prop('checked', true);
                    $('#btnDeleteDeclaracao').show();
                }
                if ($(this).closest('#tblEntregasGeral').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                    $('#btnDeleteDeclaracao').hide();
                }
            } else if ($(this).closest('#tblDecRetGeral').find('#hddDecRet').val() == 1) {
                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                    $(this).closest('#tblDecRetGeral').find('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', false);
                    $(this).closest('.checkbox').find('input').prop('checked', false);
                } else {
                    if (($('#tblDecRetGeral').find('input[type=checkbox].chk').length - 1) == $('#tblDecRetGeral').find('input[type=checkbox].chk').filter(':checked').length + 1) {
                        $(this).closest('#tblDecRetGeral').find('#chkAllDecRet').closest('.checkbox').find('input').prop('checked', true);
                    }
                    $(this).closest('.checkbox').find('input').prop('checked', true);
                    $('#btnDeleteDecRet').show();
                }
                if ($(this).closest('#tblDecRetGeral').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                    $('#btnDeleteDecRet').hide();
                }
            } else if ($('#tblEmpresasGeral').data("value") == 1) {
                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                    $(this).closest('#tblEmpresasGeral').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                    $(this).closest('.checkbox').find('input').prop('checked', false);
                } else {
                    if (($('#tblEmpresasGeral').find('input[type=checkbox].chk').length - 1) == $('#tblEmpresasGeral').find('input[type=checkbox].chk').filter(':checked').length + 1) {
                        $(this).closest('#tblEmpresasGeral').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', true);
                    }
                    $(this).closest('.checkbox').find('input').prop('checked', true);
                    $('#btnRemEmpresa').show();
                }
                if ($(this).closest('#tblEmpresasGeral').find('input[type=checkbox].chk').filter(':checked').length == "0") {
                    $('#btnRemEmpresa').hide();
                }
            } else if ($('#email').data("value") == "1") {
                if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
                    var td1 = $(this).closest('tr').children('td');
                    $.each(td1, function () {
                        $(this).removeClass('selecionada');
                    });
                    $(this).closest('.checkbox').find('input').prop('checked', false);
                } else if ($(this).closest('.checkbox').find('input').prop('checked') === false) {
                    var td2 = $(this).closest('tr').children('td');
                    $.each(td2, function () {
                        $(this).addClass('selecionada');
                    });
                    $(this).closest('.checkbox').find('input').prop('checked', true);
                }
            }
        }
        event.handler = true;
    }
}

function fDetectBrowser() {
    var BrowserDetect = {
        init: function () {
            this.browser = this.searchString(this.dataBrowser) || "Other";
            this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "Unknown";
        },
        searchString: function (data) {
            for (var i = 0; i < data.length; i++) {
                var dataString = data[i].string;
                this.versionSearchString = data[i].subString;

                if (dataString.indexOf(data[i].subString) != -1) {
                    return data[i].identity;
                }
            }
        },
        searchVersion: function (dataString) {
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
    return BrowserDetect;
}

function fEditableDate(event) {
    if (event.handler !== true) {
        $(this).attr('readonly', false);
        $(this).datetimepicker({
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
        $(this).keydown(function (e) {
            if (e.which == 13) {
                $(this).attr('readonly', true);
                $(this).removeClass("datetimepicker");
            }
        });
        $(this).focusout(function () {
            $(this).attr('readonly', true);
            $(this).removeClass("datetimepicker");
        });
        event.handler = true;
    }
    return false;
}

function fEditableTextClick(event) {
    if (event.handler !== true) {
        $(this).attr('readonly', false);
        $(this).keydown(function (e) {
            if (e.which == 13) {
                $(this).attr('readonly', true);
            }
        });
        $(this).focusout(function () {
            $(this).attr('readonly', true);
        });
        event.handler = true;
    }
    return false;
}

function fEsconderErro(event) {
    if (event.which == 1) {
        hideError();
    }
}

function findDuplicates() {
    var isDuplicate = false;
    $('input[name^="txtNome"]').each(function (i, el1) {
        var current_val = $(el1).val();
        if (current_val !== "") {
            $('input[name^="txtNome"]').each(function (j, el2) {
                if ($(el2).val() == current_val && $(el1).index(i) != $(el2).index(j)) {
                    isDuplicate = true;
                    $(el1).closest('.inputarea_col1').css("background-color", "yellow");
                    $(el2).closest('.inputarea_col1').css("background-color", "yellow");
                    return;
                }
            });
        }
    });
    if (isDuplicate) {
        return false;
    } else {
        return true;
    }
}

function floatMask() {
    $(':input.dinheiro').inputmask('decimal', {
        rightAlignNumerics: true,
        allowPlus: false,
        allowMinus: false,
        radixPoint: ",",
        digits: 2,
        autoGroup: true,
        groupSeparator: ".",
        groupSize: 3
    });
}

function formatValor(valor) {
    return valor.toString().split('.').join("").replace(',', '.');
}

function fRadioClick(event) {
    if (event.handler !== true) {
        var index = $(this).index() + 1;
        index = (index / 2) - 1;
        if ($(this).closest('.radio').find('input').eq(index).prop('checked') !== true) {
            $(this).closest('.radio').find('input').eq(index).prop('checked', true);
        }
        if ($(this).attr("for") == "radFatInt") {
            $('#tblDadosFatExt').hide();
            $('#tblFatVazia').hide();
            $('#divFatExtDetail').closest('.linha').hide();
            $('#divFatIntDetail').closest('.linha').hide();
            $('#hddFatInt').val('1');
            $('#hddFatExt').val('0');
            $('#slcFiltrarFatInt').closest('.linha').show();
            $('#slcFiltrarFatExt').closest('.linha').hide();
            if (($('#tblDadosFatInt tr').length) == 1) {
                $('#btnRemFatInt').closest('.linha').hide();
                $('#tblFatVazia').show();
            } else {
                $('#btnRemFatInt').closest('.linha').show();
                $('#tblDadosFatInt').show();
            }
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radFatExt") {
            $('#tblDadosFatInt').hide();
            $('#tblFatVazia').hide();
            $('#divFatExtDetail').closest('.linha').hide();
            $('#divFatIntDetail').closest('.linha').hide();
            $('#hddFatInt').val('0');
            $('#hddFatExt').val('1');
            $('#slcFiltrarFatInt').closest('.linha').hide();
            $('#slcFiltrarFatExt').closest('.linha').show();
            if (($('#tblDadosFatExt tr').length) == 1) {
                $('#btnRemFatExt').closest('.linha').hide();
                $('#tblFatVazia').show();
            } else {
                $('#btnRemFatExt').closest('.linha').show();
                $('#tblDadosFatExt').show();
            }
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radCat") {
            $('#frmCategoria').show();
            $('#frmSubcategoria').hide();
            $('#frmFamilia').hide();
        } else if ($(this).attr("for") == "radSubcat") {
            $('#frmCategoria').hide();
            $('#frmSubcategoria').show();
            $('#frmFamilia').hide();
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radFam") {
            $('#frmCategoria').hide();
            $('#frmSubcategoria').hide();
            $('#frmFamilia').show();
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radEmpCat") {
            $('#frmCatEmp').hide();
            $('#hddEmpCat').val('1');
            $('#hddCatEmp').val('0');
            $('#frmEmpCat').show();
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radCatEmp") {
            $('#frmEmpCat').hide();
            $('#hddEmpCat').val('0');
            $('#hddCatEmp').val('1');
            $('#frmCatEmp').show();
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radAtiCat") {
            $('#frmCatAti').hide();
            $('#hddAtiCat').val('1');
            $('#hddCatAti').val('0');
            /*$.each($('#frmAtiCat').find('.checkbox').find('.chk'), function() {
             $(this).prop('checked', false);
             });
             $('#slcAtividade').val(0);*/
            $('#frmAtiCat').show();
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radCatAti") {
            $('#frmAtiCat').hide();
            $('#hddAtiCat').val('0');
            $('#hddCatAti').val('1');
            /*$.each($('#frmCatAti').find('.checkbox').find('.chk'), function() {
             $(this).prop('checked', false);
             });
             $('#slcCatAti').val(0);*/
            $('#frmCatAti').show();
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radVOutros") {
            $('#tblDecRetGeral').hide();
            $('#tblDecVazia').hide();
            $('#divDecRetDetalhes').closest('.linha').hide();
            $('#divEntregasDetalhes').closest('.linha').hide();
            $('#hddEntregas').val('1');
            $('#hddDecRet').val('0');
            $('#slcFiltrarDecRet').closest('.linha').hide();
            $('#slcFiltrarEntregas').closest('.linha').show();
            if ($('#tblEntregasGeral tr').length == 1) {
                $('#tblDecVazia').show();
            } else {
                $('#tblEntregasGeral').show();
            }
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        } else if ($(this).attr("for") == "radVDecRet") {
            $('#tblEntregasGeral').hide();
            $('#tblDecVazia').hide();
            $('#divDecRetDetalhes').closest('.linha').hide();
            $('#divEntregasDetalhes').closest('.linha').hide();
            $('#hddEntregas').val('0');
            $('#hddDecRet').val('1');
            $('#slcFiltrarEntregas').closest('.linha').hide();
            $('#slcFiltrarDecRet').closest('.linha').show();
            if ($('#tblDecRetGeral tr').length == 1) {
                $('#tblDecVazia').show();
            } else {
                $('#tblDecRetGeral').show();
            }
            $('.chosenSelect').chosen('destroy');
            $('.chosenSelect').chosen({
                allow_single_deselect: true,
                no_results_text: 'Sem resultados!'
            });
        }
        event.handler = true;
    }
    return false;
}

/* COM DADOS CACHE (EM TESTE) */
function ganhoPerda(param) {
    // if ($('#tblAcoesDetalhes')) {
    var dataString = 'nome_acao=' + param + '&tipo=ganhoPerda';
    $.ajax({
        type: "POST",
        url: "functions/funcoes_banco.php",
        data: dataString,
        dataType: "json",
        success: function (dados) {
            hideLoading();
            /* if (dados.vazio == true) {
             ganhoPerda(param);
             } else { */
            $("#tblAcoesDetalhes tr").each(function (index) {
                if (index > 0) {
                    if ($(this).children('td').eq(0).text() == param) {
                        var max = dados.LastTradePriceOnly;
                        var maximo = dados.DaysHigh;
                        var minimo = dados.DaysLow;
                        var volume = dados.Volume;
                        $('#txtPrecoAtualAcao').val(number_format(max, 3, ',', '.'));
                        $('#hddMaxPrecoAtual').val(maximo);
                        $('#hddMinPrecoAtual').val(minimo);
                        $('#hddVolAtual').val(volume);
                        // var preco_act = parseFloat(dados.LastTradePriceOnly).toFixed(3);
                        var preco_act = dados.LastTradePriceOnly;
                        var qtd = formatValor($(this).children('td').eq(3).text());
                        var total = formatValor($(this).children('td').eq(4).text());
                        var variacao = (preco_act * qtd) - total;
                        $(this).children('td').eq(5).text(number_format(preco_act, 3, ',', '.'));
                        $(this).children('td').eq(6).text(number_format(variacao, 3, ',', '.'));
                        if (variacao >= 0) {
                            $(this).children('td').eq(6).css("color", "#42BF32");
                        } else {
                            $(this).children('td').eq(6).css("color", "#F00A0A");
                        }
                    }
                }
            });
            // }
        }
    });
    setTimeout(function () {
        ganhoPerda(param);
    }, intervalo);
    // }
}
/* */

/* COM DADOS DA YAHOO (TEMPORÁRIO)
 function ganhoPerda(nome_pais, nome_acao) {
 var urlAcoes;
 // Verificar Bolsa
 if (nome_pais == 'Alemanha')
 urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ADS.DE%22%2C%22ALV.DE%22%2C%22BAS.DE%22%2C%22BAYN.DE%22%2C%22BEI.DE%22%2C%22BMW.DE%22%2C%22CBK.DE%22%2C%22CON.DE%22%2C%22DAI.DE%22%2C%22DB1.DE%22%2C%22DBK.DE%22%2C%22DPW.DE%22%2C%22DTE.DE%22%2C%22EOAN.DE%22%2C%22FME.DE%22%2C%22FRE.DE%22%2C%22HEI.DE%22%2C%22HEN3.DE%22%2C%22IFX.DE%22%2C%22LHA.DE%22%2C%22LIN.DE%22%2C%22MRK.DE%22%2C%22MUV2.DE%22%2C%22RWE.DE%22%2C%22SAP.DE%22%2C%22SDF.DE%22%2C%22SIE.DE%22%2C%22TKA.DE%22%2C%22VNA.DE%22%2C%22VOW3.DE%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 else if (nome_pais == 'Bélgica')
 urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ABI.BR%22%2C%22ACKB.BR%22%2C%22AGS.BR%22%2C%22BEFB.BR%22%2C%22BEKB.BR%22%2C%22BELG.BR%22%2C%22BPOST.BR%22%2C%22COFB.BR%22%2C%22COLR.BR%22%2C%22DELB.BR%22%2C%22DIE.BR%22%2C%22DL.BR%22%2C%22ELI.BR%22%2C%22GBLB.BR%22%2C%22GSZ.BR%22%2C%22KBC.BR%22%2C%22SOLB.BR%22%2C%22TNET.BR%22%2C%22UCB.BR%22%2C%22UMI.BR%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 else if (nome_pais == 'Estados Unidos da América')
 urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22AAPL%22%2C%22AXP%22%2C%22BA%22%2C%22CAT%22%2C%22CSCO%22%2C%22CVX%22%2C%22DD%22%2C%22DIS%22%2C%22GE%22%2C%22GS%22%2C%22HD%22%2C%22IBM%22%2C%22INTC%22%2C%22JNJ%22%2C%22JPM%22%2C%22KO%22%2C%22MCD%22%2C%22MMM%22%2C%22MRK%22%2C%22MSFT%22%2C%22NKE%22%2C%22PFE%22%2C%22PG%22%2C%22TRV%22%2C%22UNH%22%2C%22UTX%22%2C%22V%22%2C%22VZ%22%2C%22WMT%22%2C%22XOM%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 else if (nome_pais == 'França')
 urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22AC.PA%22%2C%22ACA.PA%22%2C%22AI.PA%22%2C%22AIR.PA%22%2C%22ALO.PA%22%2C%22ALU.PA%22%2C%22BN.PA%22%2C%22BNP.PA%22%2C%22CA.PA%22%2C%22CAP.PA%22%2C%22CS.PA%22%2C%22DG.PA%22%2C%22EDF.PA%22%2C%22EI.PA%22%2C%22EN.PA%22%2C%22FP.PA%22%2C%22FR.PA%22%2C%22GLE.PA%22%2C%22GSZ.PA%22%2C%22KER.PA%22%2C%22LG.PA%22%2C%22LR.PA%22%2C%22MC.PA%22%2C%22ML.PA%22%2C%22MT.PA%22%2C%22OR.PA%22%2C%22ORA.PA%22%2C%22PUB.PA%22%2C%22RI.PA%22%2C%22RNO.PA%22%2C%22SAF.PA%22%2C%22SAN.PA%22%2C%22SGO.PA%22%2C%22SOLB.PA%22%2C%22SU.PA%22%2C%22TEC.PA%22%2C%22UG.PA%22%2C%22UL.PA%22%2C%22VIE.PA%22%2C%22VIV.PA%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 else if (nome_pais == 'Reino Unido')
 urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22AAL.L%22%2C%22ABF.L%22%2C%22ADM.L%22%2C%22ADN.L%22%2C%22AHT.L%22%2C%22ANTO.L%22%2C%22ARM.L%22%2C%22AV.L%22%2C%22AZN.L%22%2C%22BA.L%22%2C%22BAB.L%22%2C%22BARC.L%22%2C%22BATS.L%22%2C%22BDEV.L%22%2C%22BG.L%22%2C%22BKG.L%22%2C%22BLND.L%22%2C%22BLT.L%22%2C%22BNZL.L%22%2C%22BP.L%22%2C%22BRBY.L%22%2C%22BT-A.L%22%2C%22CCL.L%22%2C%22CNA.L%22%2C%22CPG.L%22%2C%22CPI.L%22%2C%22CRH.L%22%2C%22DC.L%22%2C%22DGE.L%22%2C%22DLG.L%22%2C%22EXPN.L%22%2C%22EZJ.L%22%2C%22FRES.L%22%2C%22GFS.L%22%2C%22GKN.L%22%2C%22GLEN.L%22%2C%22GSK.L%22%2C%22HIK.L%22%2C%22HL.L%22%2C%22HMSO.L%22%2C%22HSBA.L%22%2C%22IAG.L%22%2C%22IHG.L%22%2C%22III.L%22%2C%22IMT.L%22%2C%22INTU.L%22%2C%22ISAT.L%22%2C%22ITRK.L%22%2C%22ITV.L%22%2C%22JMAT.L%22%2C%22KGF.L%22%2C%22LAND.L%22%2C%22LGEN.L%22%2C%22LLOY.L%22%2C%22LSE.L%22%2C%22MGGT.L%22%2C%22MKS.L%22%2C%22MNDI.L%22%2C%22MRW.L%22%2C%22NG.L%22%2C%22NXT.L%22%2C%22OML.L%22%2C%22PRU.L%22%2C%22PSON.L%22%2C%22RB.L%22%2C%22RBS.L%22%2C%22RDSA.L%22%2C%22RDSB.L%22%2C%22REL.L%22%2C%22RIO.L%22%2C%22RMG.L%22%2C%22RR.L%22%2C%22RRS.L%22%2C%22RSA.L%22%2C%22SAB.L%22%2C%22SBRY.L%22%2C%22SDR.L%22%2C%22SGE.L%22%2C%22SHP.L%22%2C%22SKY.L%22%2C%22SL.L%22%2C%22SMIN.L%22%2C%22SN.L%22%2C%22SPD.L%22%2C%22SSE.L%22%2C%22STAN.L%22%2C%22STJ.L%22%2C%22SVT.L%22%2C%22TPK.L%22%2C%22TSCO.L%22%2C%22TUI.L%22%2C%22TW.L%22%2C%22ULVR.L%22%2C%22UU.L%22%2C%22VOD.L%22%2C%22WOS.L%22%2C%22WPP.L%22%2C%22WTB.L%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 else if (nome_pais == 'Portugal')
 // urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ALTR.LS%22%2C%22BANIF.LS%22%2C%22BCP.LS%22%2C%22BPI.LS%22%2C%22CTT.LS%22%2C%22EDP.LS%22%2C%22EDPR.LS%22%2C%22EGL.LS%22%2C%22GALP.LS%22%2C%22IPR.LS%22%2C%22JMT.LS%22%2C%22NOS.LS%22%2C%22PTC.LS%22%2C%22PTI.LS%22%2C%22RENE.LS%22%2C%22SEM.LS%22%2C%22SON.LS%22%2C%22TDSA.LS%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 urlAcoes = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20symbol%2C%20Name%2C%20LastTradePriceOnly%2C%20LastTradeDate%2C%20LastTradeTime%2C%20Change%2C%20Open%2C%20DaysHigh%2C%20DaysLow%2C%20Volume%20FROM%20yahoo.finance.quoteslist%20WHERE%20symbol%20IN%20(%22ALTR.LS%22%2C%22BCP.LS%22%2C%22BPI.LS%22%2C%22CTT.LS%22%2C%22EDP.LS%22%2C%22EDPR.LS%22%2C%22EGL.LS%22%2C%22GALP.LS%22%2C%22IPR.LS%22%2C%22JMT.LS%22%2C%22NOS.LS%22%2C%22PHR.LS%22%2C%22PTI.LS%22%2C%22RENE.LS%22%2C%22SEM.LS%22%2C%22SON.LS%22%2C%22TDSA.LS%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
 
 $.ajax({
 type: "POST",
 url: urlAcoes,
 dataType: "json",
 success: function(dados) {
 hideLoading();
 // if (dados.query.count == "0") {
 //    ganhoPerda(nome_pais, nome_acao);
 // } else {
 $.each(dados.query.results.quote, function(i, item) {
 // var name = dados.query.results.quote[i].symbol.split('.');
 // if (nome_acao == name[0]) {
 if (nome_acao == dados.query.results.quote[i].symbol) {
 $("#tblAcoesDetalhes tr").each(function(index) {
 if (index > 0) {
 if (nome_acao == $(this).children('td').eq(0).text()) {
 var max = dados.query.results.quote[i].LastTradePriceOnly;
 var maximo = dados.query.results.quote[i].DaysHigh;
 var minimo = dados.query.results.quote[i].DaysLow;
 var volume = dados.query.results.quote[i].Volume;
 $('#txtPrecoAtualAcao').val(number_format(max, 3, ',', '.'));
 $('#hddMaxPrecoAtual').val(maximo);
 $('#hddMinPrecoAtual').val(minimo);
 $('#hddVolAtual').val(volume);
 var preco_act = parseFloat(dados.query.results.quote[i].LastTradePriceOnly).toFixed(3);
 var qtd = formatValor($(this).children('td').eq(3).text());
 var total = formatValor($(this).children('td').eq(4).text());
 var variacao = (preco_act * qtd) - total;
 $(this).children('td').eq(5).text(number_format(preco_act, 3, ',', '.'));
 $(this).children('td').eq(6).text(number_format(variacao, 3, ',', '.'));
 if (variacao >= 0) {
 $(this).children('td').eq(6).css("color", "#42BF32");
 } else {
 $(this).children('td').eq(6).css("color", "#F00A0A");
 }
 }
 }
 });
 }
 });
 // }
 }
 });
 setTimeout(function() {
 ganhoPerda(nome_pais, nome_acao);
 }, intervalo);
 }
 /* */

function get_calendar_height(div) {
    return $('#' + div).height() - 50;
}

function getDoc(frame) {
    var doc = null;
    try {
        if (frame.contentWindow) {
            doc = frame.contentWindow.document;
        }
    } catch (err) {
    }
    if (doc) {
        return doc;
    }
    try {
        doc = frame.contentDocument ? frame.contentDocument : frame.document;
    } catch (err) {
        doc = frame.document;
    }
    return doc;
}

function getVirtualDate() {
    var data = "";
    var dia = "";
    var mes = "";
    var ano = "";
    var horas = "";
    var minutos = "";
    var segundos = "";
    var tempoString = "";
    $.ajax({
        type: "POST",
        async: false,
        url: "functions/funcoes_geral.php",
        data: "tipo=data_virtual",
        dataType: "json",
        success: function (dados) {
            if (dados.sucesso === true) {
                data = new Date(dados.mensagem);
                dia = data.getDate();
                mes = data.getMonth() + 1;
                ano = data.getFullYear();
                horas = data.getHours();
                minutos = data.getMinutes();
                segundos = data.getSeconds();

                mes = (mes < 10 ? "0" : "") + mes;
                minutos = (minutos < 10 ? "0" : "") + minutos;
                segundos = (segundos < 10 ? "0" : "") + segundos;

                tempoString = mes + "/" + dia + "/" + ano + " " + horas + ":" + minutos + ":" + segundos;
            }
        }
    });
    return tempoString;
}

function getVirtualDateAdmin(destinatario) {
    var data = "";
    var dia = "";
    var mes = "";
    var ano = "";
    var horas = "";
    var minutos = "";
    var segundos = "";
    var tempoString = "";
    $.ajax({
        type: "POST",
        async: false,
        url: "functions/funcoes_geral.php",
        data: "id_destinatario=" + destinatario + "&tipo=data_virtual",
        dataType: "json",
        success: function (dados) {
            if (dados.sucesso === true) {
                data = new Date(dados.mensagem);
                dia = data.getDate();
                mes = data.getMonth() + 1;
                ano = data.getFullYear();
                horas = data.getHours();
                minutos = data.getMinutes();
                segundos = data.getSeconds();

                mes = (mes < 10 ? "0" : "") + mes;
                minutos = (minutos < 10 ? "0" : "") + minutos;
                segundos = (segundos < 10 ? "0" : "") + segundos;

                tempoString = mes + "/" + dia + "/" + ano + " " + horas + ":" + minutos + ":" + segundos;
            }
        }
    });
    return tempoString;
}

function heartbeat() {
    $.ajax({
        type: "POST",
        url: "functions/funcoes_geral.php",
        data: "tipo=heartbeat"
    });
}

function hideError() {
    $('.error').hide();
}

function hideLoading() {
    $('.loading').hide();
    $('.loading').nextAll('.linha').show();
    $('.loading').nextAll('.linha10').show();
}

function inputPagination(objeto) {
    if (Math.floor(objeto.val()) == objeto.val() && $.isNumeric(objeto.val())) {
        if (parseInt(objeto.val()) > parseInt($('#pagTotal').val())) {
            $('#pagAtual').val($('#pagTotal').val());
        } else {
            if (objeto.val() === "" || objeto.val() == "0") {
                $('#pagAtual').val(1);
            } else {
                $('#pagAtual').val(objeto.val());
            }
        }
    }
    objeto.prop("readonly", true);
    objeto.val($('#pagAtual').val() + " de " + $('#pagTotal').val());
    $.ajax({
        type: "POST",
        url: "functions/funcoes_banco.php",
        data: "pagina_atual=" + $('#pagAtual').val() + "&limite=" + $('#slcPag').val() + "&tipo=movimentos_limite",
        dataType: "json",
        beforeSend: function () {
            $('#tblMovimentos').hide();
            $('#tblMovVazia').hide();
            showLoading();
        },
        success: function (dados) {
            hideLoading();
            if (dados.sucesso === true) {
                $('#txtDataI').val('');
                $('#txtDataF').val('');
                emptyTable('#tblMovimentos');
                if (dados.vazio === false) {
                    if (dados.linhas <= $('#slcPag').val()) {
                        $('.pagination').hide();
                    }
                    $.each(dados.dados_in, function () {
                        var debito;
                        var credito;
                        if (this.debito == "0") {
                            debito = "";
                        } else {
                            debito = number_format(this.debito, 2, ',', '.') + ' ' + dados.moeda;
                        }
                        if (this.credito == "0") {
                            credito = "";
                        } else {
                            credito = number_format(this.credito, 2, ',', '.') + ' ' + dados.moeda;
                        }

                        var txt_pos = "";
                        if (this.descricao == '-')
                            txt_pos = 'center';
                        else
                            txt_pos = 'left';
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
                    $('#pagInput').val(dados.pag_inicial + " de " + dados.paginas);
                    $('#pagAtual').val(dados.pag_inicial);
                    $('#pagTotal').val(dados.paginas);
                    $('#pagLinhas').val(dados.linhas);
                } else {
                    $('#tblMovimentos').hide();
                    $('#tblMovVazia').show();
                }
            }
        }
    });
}

function intMask() {
    $('.numero').inputmask('integer', {
        allowPlus: false,
        allowMinus: false,
        autoGroup: true,
        groupSeparator: ".",
        groupSize: 3,
        radixPoint: ","
    });
}

/**
 * [loadContent Função responsável por chamar as funções que lêem o conteúdo corretas]
 * @param  {[String]} href   [Referência à âncora para carregar]
 * @param  {[String]} funcao [Nome da função a chamar para carregar o conteúdo]
 * @return {[void]}        [void]
 */
function loadContent(href, funcao) {
    $('#var_content').load('conteudo_admin.php #' + href, funcao);
}

function menu() {
    $('#menu').multilevelpushmenu({
        containersToPush: [$('.content')],
        backText: 'Voltar',
        collapsed: true,
        type: 'cover',
        menuWidth: '32%'
    });
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep == 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point == 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function ordenarFloats(parent, childSelector, keySelector) {
    var items = parent.find(childSelector).sort(function (a, b) {
        var vA = parseFloat(formatValor($(keySelector, a).children('span').text()));
        var vB = parseFloat(formatValor($(keySelector, b).children('span').text()));
        return (vA > vB) ? -1 : (vA < vB) ? 1 : 0;
    });
    parent.append(items);
}

function pagination(recordsPerPage, totalNumRecords) {
    $(".pagination").show();
    for (var i = 1; i <= totalNumRecords; i++) {
        $("#tblMovimentos tr:not(:first)").hide();
    }
    for (var j = 1; j <= recordsPerPage; j++) {
        $($('#tblMovimentos tr')[j]).show();
    }
    return Math.ceil(totalNumRecords / recordsPerPage);
}

function paraRefresh() {
    if (timer !== null) {
        clearTimeout(timer);
    }
}

function paraGanhoPerda() {
    if (timer !== null) {
        clearTimeout(timer);
    }
}

function povTblProd(idTabela, dados, slcCateg, id, qs) {
    if (dados.vazio === false) {
        emptyTable('#' + idTabela);
        $.each(dados.dados_in, function (i, item) {
            var tx_iva = dados.dados_in[i].taxa > 0 ? number_format(dados.dados_in[i].taxa, 0, ',', '.') + '%' : '-';
            var tx_irc = dados.dados_in[i].taxa < 0 ? number_format(Math.abs(dados.dados_in[i].taxa), 0, ',', '.') + '%' : '-';
            var preco_un = dados.dados_in[i].taxa < 0 ? number_format(dados.dados_in[i].preco_un, 2, ',', '.') : number_format(dados.dados_in[i].preco, 2, ',', '.');
            $('#' + idTabela).append('<tr class="tbody">' +
                '<td style="text-align: left; padding: 1%;">' + dados.dados_in[i].nome + '<input id="hddIdFornecedor" type="hidden" value="' + dados.dados_in[i].id_fornecedor + '"><input id="hddTaxaIva" type="hidden" value="' + dados.dados_in[i].taxa + '"></td>' +
                '<td style="text-align: left; padding: 1%;">' + dados.dados_in[i].nome_fornecedor + '</td>' +
                '<td class="preco" style="padding: 1%;">' + preco_un + '&nbsp;' + dados.dados_in[i].moeda + '</td>' +
                // '<td style="padding: 1%;">' + number_format(dados.dados_in[i].taxa, 0, ',', '.') + '%</td>' +
                '<td style="padding: 1%;">' + tx_iva + '</td>' + 
                '<td style="padding: 1%;">' + tx_irc + '</td>' + 
                '<td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">' +
                '<div id="btnAddCarr" name="btnAddCarr" class="labelicon icon-carrinho add_carrinho" style="margin: 0;">' +
                '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + dados.dados_in[i].id_produto + '">' +
                '</div>' +
                '</td>' +
                '</tr>');
        });
        // $('#' + idTabela).append('<tr><td class="noresults" colspan="5">Não existem resultados</td></tr>');
        $('#' + idTabela).show();
        if (qs != "") qs.cache();
    } else {
        emptyTable('#' + idTabela);
        ('#' + idTabela).hide();
        emptySelect("#slcSubcategoria");
        emptySelect("#slcFamilia");
        $('#tblProdVazia').show();
    }
    if (slcCateg !== "") {
        if (slcCateg == "slcSubcategoria") {
            emptySelect('#' + slcCateg);
            emptySelect("#slcFamilia");
        } else if (slcCateg == "slcFamilia") {
            emptySelect('#' + slcCateg);
        }
        $.each(dados.dados_cat, function (i, item) {
            if (dados.dados_cat[i].cat_vazia === true) {
                if (id !== 0) {
                    $('#' + slcCateg).append($("<option selected='selected'></option>").val(this.id).text(this.desig));
                }
            } else {
                $('#' + slcCateg).append($("<option></option>").val(this.id).text(this.desig));
            }
        });
    }
}

function povTblProdDetail(dados) {
    emptyTable('#tblCarrDetail');
    $.each(dados.dados_in, function (i, item) {
        var taxa_iva = dados.dados_in[i].taxa > 0 ? dados.dados_in[i].taxa : 0;
        var taxa_irc = dados.dados_in[i].taxa < 0 ? Math.abs(dados.dados_in[i].taxa) : 0;
        var test=1.00;
        $('#tblCarrDetail').append('<tr>' +
                '<td id="num_itens" style="padding: 0.5%;">' +
                    '<input id="hddIdProduto" name="hddIdProduto" type="hidden" value="' + dados.dados_in[i].id_produto + '">' +
                    '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + dados.dados_in[i].id_fornecedor + '">' +
                    '<input id="hddTaxaIva" name="hddTaxaIva" type="hidden" value="' + taxa_iva + '">' +
                    '<input id="hddTaxaIrc" name="hddTaxaIrc" type="hidden" value="' + taxa_irc + '">' +
                    '<input id="hddTaxaDesc" name="hddTaxaDesc" type="hidden" value="' + dados.dados_in[i].taxa_desc + '">' + (i + 1) +
                '</td>' +
                '<td style="text-align: left; padding: 0.5%;">' + dados.dados_in[i].nome + '</td>' +
                '<td style="text-align: left; padding: 0.5%;">' + dados.dados_in[i].nome_fornecedor + '</td>' +
                '<td class="tdInput" style="padding: 0.8%;"><input id="txtPonderacao" name="txtPonderacao" type="text" class="editableText dinheiro"  readonly="readonly" value="' + number_format(dados.dados_in[i].ponderacao, 2, ',', '.') + '"></td>' +
                '<td style="padding: 0.5%;">' + number_format(dados.dados_in[i].preco, 2, ',', '.') + '</td>' +
                '<td class="tdInput" style="padding: 0.5%;"><input id="txtQuantEnc" name="txtQuantEnc" type="text" class="editableText dinheiro" readonly="readonly" value="' + number_format(dados.dados_in[i].quantidade, 2, ',', '.') + '"></td>' +
                '<td style="padding: 0.5%;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + '</td>' +
                '<td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">' +
                '<div class="labelicon icon-garbage rem_linha">' +
                '<input id="hddProdCarr" name="hddProdCarr" type="hidden" value="' + dados.dados_in[i].id_item_add + '">' +
                '</div>' +
                '</td>' +
                '</tr>');
    });
    $('#txtTotSDesc').val('Total sem desconto: ' + number_format(dados.total_s_desc, 2, ',', '.') + ' ' + dados.moeda);
    $('#txtDesconto').val('Desconto: ' + number_format(dados.desconto_valor, 2, ',', '.') + ' ' + dados.moeda);
    $('#txtIva').val('IVA: ' + number_format(dados.valor_iva, 2, ',', '.') + ' ' + dados.moeda);
    $('#txtIrc').val('IRC: ' + number_format(dados.valor_irc, 2, ',', '.') + ' ' + dados.moeda);
    $('#txtSoma').val('Total: ' + number_format(dados.total, 2, ',', '.') + ' ' + dados.moeda);
    floatMask();
    $('#divCarrDetail').show();
}

/**
 * [rdmsrTblCredito Shrinks a form to small shape]
 * @param  {[String]} form [Name of the form to shrink]
 * @return {[void]}
 */
function rdmsrTblCredito(form) {
    $('#' + form).removeClass("form_esq70").addClass("form_esq45");
    $('#' + form).find('.esq30').children('.labelNormal').each(function () {
        $(this).css("font-size", "8pt");
        $(this).css("line-height", "2.8em");
    });
    $('#' + form).find('.dir70').children('.moneyarea_col1').each(function () {
        $(this).css("width", "120px");
        $(this).css("margin-right", "10px");
        $(this).children('input').css("width", "96px");
        $(this).parent().find('.inputNoBackground').css("width", "95px");
        $(this).parent().find('.inputNoBackground').css("font-size", "7pt");
    });
    $('#' + form).find('.dir70').children('.inputarea_col1').each(function () {
        $(this).css("width", "120px");
        $(this).css("margin-right", "10px");
        $(this).parent().find('.inputNoBackground').css("width", "95px");
        $(this).parent().find('.inputNoBackground').css("font-size", "7pt");
    });
    $('#btnSimCre').text("Plano");
}

/**
 * [rdmsrTblCreditoRev Unshrinks a form to its normal shape]
 * @param  {[String]} form [Name of the form to unshrink]
 * @return {[void]}
 */
function rdmsrTblCreditoRev(form) {
    $('#' + form).removeClass("form_esq45").addClass("form_esq70");
    $('#' + form).find('.esq30').children('.labelNormal').each(function () {
        $(this).removeAttr('style');
    });
    $('#' + form).find('.dir70').children('.moneyarea_col1').each(function () {
        $(this).removeAttr('style');
        $(this).css("margin-right", "15px");
        $(this).children('input').removeAttr('style');
        $(this).parent().find('.inputNoBackground').css("width", "150px");
        $(this).parent().find('.inputNoBackground').css("font-size", "10pt");
    });
    $('#' + form).find('.dir70').children('.inputarea_col1').each(function () {
        $(this).removeAttr('style');
        $(this).parent().find('.inputNoBackground').css("width", "150px");
        $(this).parent().find('.inputNoBackground').css("font-size", "10pt");
    });
    $('#btnSimCre').text("Plano de crédito");
}

/**
 * [rdmsrTblLeas Shrinks a form to small shape]
 * @param  {[String]} form [Name of the form to shrink]
 * @return {[void]}
 */
function rdmsrTblLeas(form) {
    $('#' + form).css("width", "36%");
    $('#' + form).find('.esq30').children('.labelNormal').each(function () {
        $(this).css("font-size", "8pt");
        $(this).css("line-height", "2.8em");
    });
    $('#' + form).find('.dir70').children('.moneyarea_col1').each(function () {
        $(this).css("width", "120px");
        $(this).css("margin-right", "5px");
        $(this).children('input').css("width", "96px");
        $(this).parent().find('.inputNoBackground').css("width", "95px");
        $(this).parent().find('.inputNoBackground').css("font-size", "7pt");
    });
    $('#' + form).find('.dir70').children('.inputarea_col1').each(function () {
        $(this).css("width", "120px");
        $(this).css("margin-right", "5px");
        $(this).parent().find('.inputNoBackground').css("width", "95px");
        $(this).parent().find('.inputNoBackground').css("font-size", "7pt");
    });
    $('#' + form).find('.dir70').children('textarea').attr('cols', '20');
    $('#btnLeasingSim').text("Plano");
}

/**
 * [rdmsrTblLeasRev Unshrinks a form to its normal shape]
 * @param  {[String]} form [Name of the form to unshrink]
 * @return {[void]}
 */
function rdmsrTblLeasRev(form) {
    $('#' + form).css("width", "60%");
    $('#' + form).find('.esq30').children('.labelNormal').each(function () {
        $(this).removeAttr('style');
    });
    $('#' + form).find('.dir70').children('.moneyarea_col1').each(function () {
        $(this).removeAttr('style');
        $(this).css("margin-right", "15px");
        $(this).children('input').removeAttr('style');
        $(this).parent().find('.inputNoBackground').css("width", "150px");
        $(this).parent().find('.inputNoBackground').css("font-size", "10pt");
    });
    $('#' + form).find('.dir70').children('.inputarea_col1').each(function () {
        $(this).removeAttr('style');
        $(this).parent().find('.inputNoBackground').css("width", "150px");
        $(this).parent().find('.inputNoBackground').css("font-size", "10pt");
    });
    $('#' + form).find('.dir70').children('textarea').attr('cols', '30');
    $('#btnLeasingSim').text("Plano de leasing");
}

/* COM DADOS CACHE (EM TESTE) */
function refresh() {
    // if ($('#tblAcoes')) { // Fazer request de cotações se página de mercado estiver aberta
    var nome_bolsa = $('#slcPaisAcao option:selected').text().split(' ')[2];
    // Solução para que possa funcionar para Reino Unido
    if (nome_bolsa == '-')
        nome_bolsa = $('#slcPaisAcao option:selected').text().split(' ')[3];
    // Solução para evitar disposição de todas Ações na 1ª execução
    if (nome_bolsa == '' || typeof nome_bolsa == 'undefined')
        nome_bolsa = 'PSI20';

    var linhas = $('#tblAcoes tr').length;

    var dataString = "nome_bolsa=" + nome_bolsa + "&linhas=" + linhas + "&tipo=carrega_cotacao";
    $.ajax({
        type: "POST",
        url: "functions/funcoes_banco.php",
        data: dataString,
        dataType: "json",
        success: function (dados) {
            /* if (dados.vazio == true) {
             refresh();
             } else { */
            
            if (dados.vazio == false) { // Parse array apenas se houver dados
                if (linhas >= 2) {
                    $.each(dados.dados_in, function (i, item) {
                        var nome = dados.dados_in[i].nome_acao;
                        if ($('#tblAcoes tr').eq(i + 1).children('td').eq(0).text() == nome) {
                            $('#tblAcoes tr').eq(i + 1).children('td').eq(0).text(nome);
                            $('#tblAcoes tr').eq(i + 1).children('td').eq(1).text(dados.dados_in[i].nome_empresa);
                            $('#tblAcoes tr').eq(i + 1).children('td').eq(2).text(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                            if (dados.dados_in[i].change > 0) {
                                $('#tblAcoes tr').eq(i + 1).children('td').eq(3).text('+' + number_format(dados.dados_in[i].change, 3, ',', '.'));
                            } else {
                                $('#tblAcoes tr').eq(i + 1).children('td').eq(3).text(number_format(dados.dados_in[i].change, 3, ',', '.'));
                            }
                            $('#tblAcoes tr').eq(i + 1).children('td').eq(4).text(number_format(dados.dados_in[i].open, 3, ',', '.'));
                            $('#tblAcoes tr').eq(i + 1).children('td').eq(5).text(number_format(dados.dados_in[i].days_high, 3, ',', '.') + ' / ' + number_format(dados.dados_in[i].days_low, 3, ',', '.'));

                            // var name = $('#frmOrdemCompra').find('input[name="txtNome"]').val();
                            var name = $('#frmOrdemCompra').find('input[name="txtNomeAcao"]').val();
                            // var preco_ant = $('#frmOrdemCompra').find('input[name="txtPreco"]').val();
                            var preco_ant = $('#frmOrdemCompra').find('input[name="txtPrecoAcao"]').val();
                            if (name == nome) {
                                // $('#frmOrdemCompra').find('input[name="txtPreco"]').val(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                                $('#frmOrdemCompra').find('input[name="txtPrecoAcao"]').val(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                                if (preco_ant !== "" && preco_ant != number_format(dados.dados_in[i].last_trade_price, 3, ',', '.')) {
                                    // $('#frmOrdemCompra').find('input[name="txtQuantidade"]').val('');
                                    $('#frmOrdemCompra').find('input[name="txtQtdAcao"]').val('');
                                    // $('#frmOrdemCompra').find('input[name="txtSubtotal"]').val('0,000');
                                    $('#frmOrdemCompra').find('input[name="txtSubtotalAcao"]').val('0,000');
                                    // $('#frmOrdemCompra').find('input[name="txtTotal"]').val('0,000');
                                    $('#frmOrdemCompra').find('input[name="txtTotalAcao"]').val('0,000');
                                }
                            }
                        }
                    });
                } else {
                    $.each(dados.dados_in, function (i, item) {
                        var nome = dados.dados_in[i].nome_acao;
                        // var name = $('#frmOrdemCompra').find('input[name="txtNome"]').val();
                        var name = $('#frmOrdemCompra').find('input[name="txtNomeAcao"]').val();
                        // var preco_ant = $('#frmOrdemCompra').find('input[name="txtPreco"]').val();
                        var preco_ant = $('#frmOrdemCompra').find('input[name="txtPrecoAcao"]').val();
                        if (name == nome) {
                            // $('#frmOrdemCompra').find('input[name="txtPreco"]').val(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                            $('#frmOrdemCompra').find('input[name="txtPrecoAcao"]').val(number_format(dados.dados_in[i].last_trade_price, 3, ',', '.'));
                            if (preco_ant !== "" && preco_ant != number_format(dados.dados_in[i].last_trade_price, 3, ',', '.')) {
                                // $('#frmOrdemCompra').find('input[name="txtQuantidade"]').val('');
                                $('#frmOrdemCompra').find('input[name="txtQtdAcao"]').val('');
                                // $('#frmOrdemCompra').find('input[name="txtSubtotal"]').val('0,000');
                                $('#frmOrdemCompra').find('input[name="txtSubtotalAcao"]').val('0,000');
                                // $('#frmOrdemCompra').find('input[name="txtTotal"]').val('0,000');
                                $('#frmOrdemCompra').find('input[name="txtTotalAcao"]').val('0,000');
                            }
                        }
                        $('#tblAcoes').append('<tr>' +
                                '<td class="inputAccao">' + nome + '</td>' +
                                '<td class="inputAccao">' + dados.dados_in[i].nome_empresa + '</td>' +
                                '<td class="inputAccao">' + number_format(dados.dados_in[i].last_trade_price, 3, ',', '.') + '</td>' +
                                '<td class="inputAccao">' + number_format(dados.dados_in[i].change, 3, ',', '.') + '</td>' +
                                '<td class="inputAccao">' + number_format(dados.dados_in[i].open, 3, ',', '.') + '</td>' +
                                '<td class="inputAccao">' + number_format(dados.dados_in[i].days_high, 3, ',', '.') + ' / ' + number_format(dados.dados_in[i].days_low, 3, ',', '.') + '</td>' +
                                '<td style="background-color: #77a4d7; padding: 4px; cursor: pointer;"><div id="btnCompraAcao_' + i + '" name="btnCompraAcao" class="labelicon icon-carrinho comprarAcao" style="font-size: 14px;"></div></td>' +
                                // '<td style="color: white; background-color: #77a4d7; padding: 4px; cursor: pointer;"><div id="btnGraficoAcao_' + i + '" name="btnGraficoAcao" class="graficoAcao" style="font-size: 11px;"> Gráfico </div></td>' +
                                // '<td style="background-color: #77a4d7; padding: 4px; cursor: pointer;"><div id="btnGraficoAcao_' + i + '" name="btnGraficoAcao" class="graficoAcao" style="content: url(./images/chart.png)"></div></td>' +
                                '<td style="background-color: #77a4d7; padding: 4px; cursor: pointer;"><div id="btnGraficoAcao_' + i + '" name="btnGraficoAcao" class="graficoAcao" style="overflow: hidden;"><img src="./images/chart.png"></div></td>' +
                                '</tr>');
                    });
                }
            } // END OF (vazio == false);
            // }
        }
    });
    clearTimeout(timer);
    timer = setTimeout(function () {
        refresh();
    }, intervalo);
    // }
}
/* */

/* COM DADOS YAHOO (TEMPORÁRIO)
 function refresh() {
 var paisAcao = $('#slcPaisAcao option:selected').text().split(' ')[0];
 var urlAcoes;
 var linhas = $('#tblAcoes tr').length;
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
 
 $.ajax({
 type: "POST",
 url: urlAcoes,
 dataType: "json",
 success: function(dados) {
 // if (dados.query.count == "0") {
 //    refresh();
 // } else {
 if (linhas >= 2) {
 $.each(dados.query.results.quote, function(i, item) {
 // var nome = dados.query.results.quote[i].symbol.split('.');
 // $('#tblAcoes tr').eq(i + 1).children('td').eq(0).text(nome[0]);
 $('#tblAcoes tr').eq(i + 1).children('td').eq(0).text(dados.query.results.quote[i].symbol);
 $('#tblAcoes tr').eq(i + 1).children('td').eq(1).text(number_format(dados.query.results.quote[i].LastTradePriceOnly, 3, ',', '.'));
 if (dados.query.results.quote[i].Change > 0) {
 $('#tblAcoes tr').eq(i + 1).children('td').eq(2).text('+' + number_format(dados.query.results.quote[i].Change, 3, ',', '.'));
 } else {
 $('#tblAcoes tr').eq(i + 1).children('td').eq(2).text(number_format(dados.query.results.quote[i].Change, 3, ',', '.'));
 }
 
 $('#tblAcoes tr').eq(i + 1).children('td').eq(3).text(number_format(dados.query.results.quote[i].Open, 3, ',', '.'));
 $('#tblAcoes tr').eq(i + 1).children('td').eq(4).text(number_format(dados.query.results.quote[i].DaysHigh, 3, ',', '.') + ' / ' + number_format(dados.query.results.quote[i].DaysLow, 3, ',', '.'));
 var name = $('#frmOrdemCompra').find('input[name="txtNome"]').val();
 var preco_ant = $('#frmOrdemCompra').find('input[name="txtPreco"]').val();
 // if (name == nome[0]) {
 if (name == dados.query.results.quote[i].symbol) {
 $('#frmOrdemCompra').find('input[name="txtPreco"]').val(number_format(dados.query.results.quote[i].LastTradePriceOnly, 3, ',', '.'));
 if (preco_ant !== "" && preco_ant != number_format(dados.query.results.quote[i].LastTradePriceOnly, 3, ',', '.')) {
 $('#frmOrdemCompra').find('input[name="txtQuantidade"]').val('');
 $('#frmOrdemCompra').find('input[name="txtSubtotal"]').val('0,000');
 $('#frmOrdemCompra').find('input[name="txtTotal"]').val('0,000');
 }
 }
 });
 } else {
 $.each(dados.query.results.quote, function(i, item) {
 // var nome = dados.query.results.quote[i].symbol.split('.');
 var name = $('#frmOrdemCompra').find('input[name="txtNome"]').val();
 var preco_ant = $('#frmOrdemCompra').find('input[name="txtPreco"]').val();
 // if (name == nome[0]) {
 if (name == dados.query.results.quote[i].symbol) {
 $('#frmOrdemCompra').find('input[name="txtPreco"]').val(number_format(dados.query.results.quote[i].LastTradePriceOnly, 3, ',', '.'));
 if (preco_ant !== "" && preco_ant != number_format(dados.query.results.quote[i].LastTradePriceOnly, 3, ',', '.')) {
 $('#frmOrdemCompra').find('input[name="txtQuantidade"]').val('');
 $('#frmOrdemCompra').find('input[name="txtSubtotal"]').val('0,000');
 $('#frmOrdemCompra').find('input[name="txtTotal"]').val('0,000');
 }
 }
 $('#tblAcoes').append('<tr>' +
 // '<td class="inputAccao">' + nome[0] + '</td>' +
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
 // }
 }
 });
 clearTimeout(timer);
 timer = setTimeout(function() {
 refresh();
 }, intervalo);
 }
 /* */

function resizeInput(BrowserDetect) {
    $('.inputNoBackground').each(function () {
        var tamanho = $(this).val().length;
        if (tamanho <= 4) {
            tamanho = 1;
        }
        if (BrowserDetect.browser == 'Firefox') {
            tamanho = tamanho + 1;
            $(this).attr('size', tamanho + "px");
        } else {
            $(this).attr('size', tamanho + "px");
        }
    });
}

function resizeSelect(slc) {
    $(".width_tmp").html($('#' + slc + ' option:selected').text());
    if ($(".width_tmp").width() > 145) {
        $('#' + slc).closest('.inputarea_col1').width($(".width_tmp").width() + 45);
    } else {
        $('#' + slc).closest('.inputarea_col1').width(190);
    }
}

function showLoading() {
    $('.loading').nextAll('.linha').hide();
    $('.loading').nextAll('.linha10').hide();
    // $('.loading').closest('.linha').show();
    $('.loading').show();
}

function updateRelogio() {
    $.ajax({
        type: "POST",
        url: "functions/funcoes_geral.php",
        dataType: "json",
        data: "tipo=data_virtual",
        success: function (dados) {
            var data = new Date(dados.mensagem);
            var dia = (data.getDate() < 10 ? "0" : "") + data.getDate();
            var mes = ((data.getMonth() + 1) < 10 ? "0" : "") + (data.getMonth() + 1);
            var ano = data.getFullYear();
            var horas = (data.getHours() < 10 ? "0" : "") + data.getHours();
            var minutos = (data.getMinutes() < 10 ? "0" : "") + data.getMinutes();
            var segundos = (data.getSeconds() < 10 ? "0" : "") + data.getSeconds();
            var tempoString = dia + "/" + mes + "/" + ano + " " + horas + ":" + minutos + ":" + segundos;
            $('#txtDataVirtual').html(tempoString);
        }
    });
}

function validaDecRet() {
    var valido = true;
    $('#tblDadosDecRet .tr').each(function (key, value) {
        if (key > 0 && key < $('#tblDadosDecRet .tr:last-child').index()) {
            $(this).children('.td').eq(1).find('.styled-select').css("background-color", "#fff");
            $(this).children('.td').eq(2).find('.styled-select').css("background-color", "#fff");
            $(this).children('.td').eq(4).find('.moneyarea_col1').css("background-color", "#fff");
        }
    });
    $('#tblDadosDecRet .tr').each(function (key, value) {
        if (key > 0 && key < $('#tblDadosDecRet .tr:last-child').index()) {
            if ($(this).children('.td').eq(1).find('select[name="slcZona"] :selected').val() == "0") {
                valido = false;
                $(this).children('.td').eq(1).find('.styled-select').css("background-color", "yellow");
            }
            if ($(this).children('.td').eq(2).find('select[name="slcRubrica"] :selected').val() == "0") {
                valido = false;
                $(this).children('.td').eq(2).find('.styled-select').css("background-color", "yellow");
            }
            if ($(this).children('.td').eq(4).find('input[name="txtImportancia"]').val() === "") {
                $(this).children('.td').eq(4).find('.moneyarea_col1').css("background-color", "yellow");
            }
        }
    });
    if (valido === true) {
        return true;
    } else {
        data = "Existem campos vazios";
        return data;
    }
}

function validaEntrega(valor, fich) {
    valido = true;

    /*
    if (valor.length == "0") {
        valido = false;
        data = "Insira um valor";
        return data;
    }
    if (!/^([1-9]{1}[0-9]{0,}([,.][0-9]{1,2})?|0([,.][0-9]{0,2}))$/.test(valor)) {
        valido = false;
        data = "O valor introduzido não é válido (100,50)";
        return data;
    }
    */
    if (fich.length == "0") {
        valido = false;
        data = "Insira um ficheiro para enviar";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaFactoring(num_fatura, tempo) {
    valido = true;

    if (num_fatura.length == "0") {
        valido = false;
        data = "Escolha o número de fatura";
        return data;
    }
    if (tempo == "0") {
        valido = false;
        data = "Escolha um tempo válido";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaForm(niss, nipc, nome, tipo, atividade, cap_soc_m, cap_soc_e, cap_soc_o, morada, cod_postal, localidade, pais, grupo) {
    valido = true;
    if (niss.length == "0") {
        data = "Insira o NISS da empresa";
        valido = false;
        return data;
    }
    if (niss.length !== 11) {
        data = "Insira um NISS válido (11 números)";
        valido = false;
        return data;
    }
    if (!/^[0-9]+$/.test(niss)) {
        data = "Insira um NISS válido (apenas números)";
        valido = false;
        return data;
    }
    if (nipc.length == "0") {
        data = "Insira o NIPC da empresa";
        valido = false;
        return data;
    }
    if (nipc.length !== 9) {
        data = "Insira um NIPC válido (9 números)";
        valido = false;
        return data;
    }
    if (!/^[0-9]+$/.test(nipc)) {
        data = "Insira um NIPC válido (apenas números)";
        valido = false;
        return data;
    }
    if (nome.length == "0") {
        data = "Insira o nome da empresa";
        valido = false;
        return data;
    }
    if (nome.length > 50) {
        data = "O nome da empresa é demasiado longo";
        valido = false;
        return data;
    }
    if (tipo.length == "0") {
        data = "Escolha o tipo de empresa";
        valido = false;
        return data;
    }
    if (atividade.length == "0") {
        data = "Escolha a atividade da empresa";
        valido = false;
        return data;
    }
    if (morada.length == "0") {
        data = "Insira a morada da empresa";
        valido = false;
        return data;
    }
    if (morada.length > 100) {
        data = "A morada da empresa é demasiado longa";
        valido = false;
        return data;
    }
    if (cod_postal.length == "0") {
        data = "Insira o código postal da empresa";
        valido = false;
        return data;
    }
    if (!/^\d{4}\-\d{3}$/.test(cod_postal)) {
        data = "Insira um código postal válido (ex: 5300-131)";
        valido = false;
        return data;
    }
    if (localidade.length == "0") {
        data = "Insira a localidade da empresa";
        valido = false;
        return data;
    }
    if (!/^[a-zA-ZéúíóáÉÚÍÓÁàÀõãÕÃêôâÊÔÂçÇ\s\-]+$/.test(localidade)) {
        data = "Insira uma localidade válida";
        valido = false;
        return data;
    }
    if (pais.length == "0") {
        data = "Insira o país onde se localiza a empresa";
        valido = false;
        return data;
    }
    if (!/^[a-zA-ZéúíóáÉÚÍÓÁàÀõãÕÃêôâÊÔÂçÇ\s\-]+$/.test(pais)) {
        data = "Insira um país válido";
        valido = false;
        return data;
    }
    if (cap_soc_m.length == "0") {
        data = "Preencha o capital social monetário";
        valido = false;
        return data;
    }
    var resultado = (parseFloat(cap_soc_m) + parseFloat(cap_soc_e));
    if (resultado > cap_soc_o) {
        data = "A soma dos capitais sociais ultrapassa o limite permitido: " + number_format(cap_soc_o, 2, ',', '.');
        valido = false;
        return data;
    }
    if (grupo == "0") {
        data = "Escolha um grupo";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaLogin(username, password) {
    valido = true;

    if (username.length == "0") {
        data = "Escreva o nome de utilizador";
        valido = false;
        return data;
    }
    if (username.length > 30) {
        data = "O nome de utilizador é demasiado extenso";
        valido = false;
        return data;
    }
    if (password.length == "0") {
        data = "Escreva a palavra-passe";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaTransf(cd1, cd2, cd3, montante) {
    valido = true;
    if (cd1.length == "0" || cd2.length == "0" || cd3.length == "0") {
        data = "Introduza uma conta destino válida";
        valido = false;
        return data;
    }
    if (!/^[0-9]{4}$/.test(cd1) && /^[0-9]{4}$/.test(cd2) && /^[0-9]{4}$/.test(cd3)) {
        data = "Introduza uma conta destino válida";
        valido = false;
        return data;
    }
    if (montante.length == "0") {
        data = "Introduza um montante a transferir";
        valido = false;
        return data;
    }
    if (!/^([1-9]{1}[0-9]{0,}([,.][0-9]{1,2})?|0([,.][0-9]{0,2}))$/.test(montante)) {
        data = "O montante introduzido não é um valor válido";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaUserLDAP(login, password, grupo) {
    valido = true;
    if (login.length == "0") {
        data = "Escreva o nome de utilizador";
        valido = false;
        return data;
    }
    if (password.length == "0") {
        data = "Escreva a palavra-passe";
        valido = false;
        return data;
    }
    if (grupo == "0") {
        data = "Escolha um grupo";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaUserNLDAP(nome_completo, login, password, conf_pass, email, grupo) {
    valido = true;
    if (login.length == "0") {
        data = "Escreva o nome de utilizador";
        valido = false;
        return data;
    }
    if (password.length == "0") {
        data = "Escreva a palavra-passe";
        valido = false;
        return data;
    }
    if (nome_completo.length == "0") {
        data = "Escreva o seu nome completo";
        valido = false;
        return data;
    }
    if (!/\s/.test(nome_completo)) {
        data = "Escreva o seu nome completo";
        valido = false;
        return data;
    }
    if (conf_pass.length == "0") {
        data = "Escreva a confirmação da palavra-passe";
        valido = false;
        return data;
    }
    if (password != conf_pass) {
        data = "Palavras-passe não correspondem";
        valido = false;
        return data;
    }
    if (!/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/.test(email)) {
        data = "Insira um email válido";
        valido = false;
        return data;
    }
    if (grupo == "0") {
        data = "Escolha um grupo";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaVendaAccao(p_venda, qtd_venda, qtd_comprada, max, total, imediato) {
    valido = true;

    if (max == "" || isNaN(parseFloat(max))) { // Para evitar vendas qndo, por algum motivo, não for possivel obter "preço actual"
        valido = false;
        data = "Funcionalidade temporariamente indisponivel. Por favor, informe o admin";
        return data;
    }
    if (p_venda == "0") {
        valido = false;
        data = "Insira um preço válido";
        return data;
    }
    if (!/^([1-9]{1}[0-9]{0,}([,.][0-9]{1,3})?|0([,.][0-9]{1,3}))$/.test(p_venda)) {
        data = "O montante introduzido não é um valor válido";
        valido = false;
        return data;
    }
    if (qtd_venda == "0") {
        valido = false;
        data = "Insira uma quantidade válida";
        return data;
    }
    if (imediato == true && parseFloat(p_venda) > parseFloat(max)) {
        valido = false;
        data = "O preço pelo qual está a tentar vender é superior ao actual";
        return data;
    }
    if (parseFloat(qtd_venda) > parseFloat(qtd_comprada)) {
        valido = false;
        data = "A quantidade inserida supera a quantidade detida";
        return data;
    }
    // if (parseFloat(qtd_venda) * parseFloat(p_venda) != parseFloat(total)) {
    if (parseFloat(total) - parseFloat(qtd_venda) * parseFloat(p_venda) > 0.1) {
        valido = false;
        data = "Por favor, preencha o formulário novamente";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validarLeasing(valor_leasing, prazo_l, per_car, per_paga, descricao, valor_min, valor_max) {
    valido = true;

    if (valor_leasing.length == "0") {
        valido = false;
        data = "Insira o valor de locação financeira";
        return data;
    }
    if ((parseFloat(valor_leasing) < parseFloat(valor_min)) || (parseFloat(valor_leasing) > parseFloat(valor_max))) {
        valido = false;
        data = "O valor introduzido não é válido [" + number_format(valor_min, 2, ',', '.') + " - " + number_format(valor_max, 2, ',', '.') + "]";
        return data;
    }
    if (prazo_l.length == "0") {
        valido = false;
        data = "Insira o prazo de locação financeira";
        return data;
    }
    if (descricao.length == "0") {
        valido = false;
        data = "Insira a descrição do bem";
        return data;
    }
    if (!/^([1-9]|[1-7][0-9]|(8[0-4]))$/.test(prazo_l)) {
        valido = false;
        data = "O prazo de leasing não é válido [1 - 84]";
        return data;
    }
    if (per_car == "0" && per_paga > "0") {
        valido = false;
        data = "Escolha um período de carência superior a zero";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validarSimulacao(montante, prazo, carencia, per_paga, valor_min, valor_max) {
    valido = true;

    if (montante.length == "0") {
        valido = false;
        data = "Insira o montante do empréstimo";
        return data;
    }
    if ((parseFloat(montante) < parseFloat(valor_min)) || (parseFloat(montante) > parseFloat(valor_max))) {
        valido = false;
        data = "O montante introduzido não é válido [" + number_format(valor_min, 0, ',', '.') + " - " + number_format(valor_max, 0, ',', '.') + "]";
        return data;
    }
    if (prazo.length == "0") {
        valido = false;
        data = "Insira o prazo de financiamento";
        return data;
    }
    if (!/^([1-9]|[1-7][0-9]|(8[0-4]))$/.test(prazo)) {
        valido = false;
        data = "O prazo de financiamento não é válido [1 - 84]";
        return data;
    }
    if (carencia == "0" && per_paga > 0) {
        valido = false;
        data = "Escolha um período de carência superior a zero";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

//-- Função que preenche e desenha dashboards, em página inicial
function buildDashBoards() {
    //-- Change made DIRECTLY on file "fullcalendar_1.6.4_yearview/fullcalendar.css, line 110; //-- $('.fc-content').css('clear', 'none');
    google.charts.load('current', {'packages':['gauge']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        /*//-- Executar pedido de AJAX para obter valores
        $.ajax({
            type: "POST",
            url: "functions/funcoes_banco.php",
            data: "tipo=draw_chart_data",
            async: false,
            success: function (dados) {
                if (dados.sucesso === true) {
                    // alert('Success!');
                    // $('#dash_balnc_val').val(dados.saldo_val);
                    // $('#dash_gains_val').val(dados.valor);
                    $('input[name="dash_balnc_val"]').text('Abc');
                    $('input[name="dash_gains_val"]').val('Def');
                }
            }
        });
        */
        
        //-- Desenhar "dashboard" de SALDO
        var saldo = parseFloat($('#dash_balnc_val').val());
        var data_balnc = google.visualization.arrayToDataTable([
            ['Label', 'Value'],
            ['Saldo', {v: saldo, f: number_format(saldo, 2, ',', '.')}]
            // ['Saldo', 100]
        ]);

        var options_balnc = {
            width: 400, height: 180,
            min: 0, max: 2500000,
            // redFrom: 90, redTo: 100,
            // yellowFrom:75, yellowTo: 90,
            minorTicks: 5,
            majorTicks: [
                '0 €', '', '', '', '', ''
            ]
        };

        var chart_balnc = new google.visualization.Gauge(document.getElementById('dash_balnc'));
        chart_balnc.draw(data_balnc, options_balnc);
        
        
        //-- Desenhar "dashboard" de RENTABILIDADE
        var valor_carteira = parseFloat($('#dash_gains_val').val());
        var data_gains = google.visualization.arrayToDataTable([
            ['Label', 'Value'],
            ['Carteira', {v: valor_carteira, f: number_format(valor_carteira, 2, ',', '.')}]
            // ['Carteira', 200]
        ]);

        var options_gains = {
            width: 400, height: 180,
            min: -10000, max: 50000,
            redFrom: -10000, redTo: 0,
            // yellowFrom:75, yellowTo: 90,
            minorTicks: 5,
            majorTicks: [
                '- €', '0 €', '', '', '', '', ''
            ]
        };

        var chart_gains = new google.visualization.Gauge(document.getElementById('dash_gains'));
        chart_gains.draw(data_gains, options_gains);
    }
}

function fillCalendTasks() {
    // var cell = $('td.fc-day[data-date="2018-05-10"]');
    // $(cell).find('.fc-day-content').text('**');
    
    var curr_date_v = getVirtualDate();
    var curr_year_v = curr_date_v.split('/')[2].split(' ')[0];
    
    $.ajax({
        type: "POST",
        url: "functions/funcoes_users.php",
        data: "curr_year_v=" + curr_year_v + "&tipo=calendario_tasks",
        dataType: "json",
        success: function (dados) {
            if (dados.sucesso == true && dados.vazio == false) {
                $.each(dados.dados_in, function(i, item) {
                    var nr = $('td.fc-day[data-date="' + i + '"]').find('.fc-day-number').text();
                    $('td.fc-day[data-date="' + i + '"]').find('.fc-day-number').text(nr + ' *');
                });
            }
        }
    });
}