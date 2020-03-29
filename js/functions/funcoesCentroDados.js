/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-21 17:15:54
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-24 17:29:56
 */

function carregaDadosAfetacao() {
    var dados_afet = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "tipo=dados_afet",
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
    return dados_afet;
}

function carregaDadosAfetUser() {
    var dados_afet = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "tipo=dados_afet_user",
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
    return dados_afet;
}

function carregaDadosGrupo() {
    var dados_grupo = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "tipo=dados_grupo",
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
    return dados_grupo;
}

function carregaDadosUser() {
    var id = $('#hddIdUserFrm').val();
    var dados_user = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id=" + id + "&tipo=dados_user",
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
    return dados_user;
}

function loadAtividade() {
    $.getScriptOnce('js/functions/validacaoCentroDados.js');
    hideError();
    hideLoading();
    floatMask();
    if ($('#tblAtividades tr').length > 1) {
        $('#tblAtividadesVazia').hide();
        $('#btnGuardarAtividade').closest('.linha').show();
        $('#tblAtividades').show();
    } else {
        $('#tblAtividades').hide();
        $('#btnGuardarAtividade').closest('.linha').hide();
        $('#tblAtividadesVazia').show();
    }
    $(document).on('click', '#btnGuardarAtividade', fBtnGuardarAtividade);
    $(document).on('click', 'button[name="btnInserirAtividade"]', fBtnInserirAtividade);
}

function loadCalendario() {
    $.getScriptOnce("js/functions/validacaoCentroDados.js");
    hideError();
    hideLoading();
    $('#frmDetalhesCal').closest('.linha').hide();
    if ($('#tblCalendarioGeral tr').length > 1) {
        $('#tblCalendarioGeralVazia').hide();
        $('#tblCalendarioGeral').show();
    } else {
        $('#tblCalendarioGeral').hide();
        $('#tblCalendarioGeralVazia').show();
    }
    $(document).on('click', '.fc-year-monthly-name a', function(event) {
        if (event.handler !== true) {
            event.preventDefault();
            event.handler = true;
        }
    });
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
    /* Added 12-03-2018, different datetime format depending on input class */
    $('.datetimepicker_ini').datetimepicker({
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
        // timepicker: true,
        timepicker: false,
        format: 'd-m-Y 00:00:00'
    });
    //
    $('.datetimepicker_fim').datetimepicker({
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
        // timepicker: true,
        timepicker: false,
        format: 'd-m-Y 23:59:59'
    });
    /* */
	
	$('.colorpicker').remove(); //-- Destroy previous instances before creating new ones. Prevents conflicts and misbehavior
    $('#colorSelector').ColorPicker({
        color: '#0000ff',
        onShow: function(colpkr) {
            $(colpkr).fadeIn(500);
            return false;
        },
        onHide: function(colpkr) {
            $(colpkr).fadeOut(500);
            return false;
        },
        onChange: function(hsb, hex, rgb) {
            $('#colorSelector div').css('backgroundColor', '#' + hex);
        }
    });
    $('.pickerCor').ColorPicker({
        onShow: function(colpkr) {
            $(colpkr).fadeIn(500);
            return false;
        },
        onHide: function(colpkr) {
            $(colpkr).fadeOut(500);
            return false;
        },
        onChange: function(hsb, hex, rgb) {
            $('.pickerCor div').css('backgroundColor', '#' + hex);
        }
    });
    $('#divCalendarioInicio').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        defaultView: 'year',
        axisFormat: 'H:mm',
        eventSources: [{
            url: 'functions/funcoes_geral.php',
            type: 'POST',
            data: {
                id_grupo: "0",
                tipo: "eventos"
            }
        }],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        dayNamesShort: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
        buttonText: {
            today: 'hoje'
        },
        theme: true,
        weekMode: "fixed"
    });
    $('#divCalendarioInicio').find('.fc-year-monthly-name a').replaceWith(function() {
        return $('<label />', {
            html: $(this).html()
        }).css({
            color: 'black',
            'font-weight': 'bold',
            'font-size': '15px'
        });
    });
    $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});
    $(document).on('click', '#btnGuardarData', fBtnGuardarData);
    $(document).on('click', '#btnUpdateData', fBtnUpdateData);
    $(document).on('click', '#btnVoltarCal', fBtnVoltarCal);
    $(document).on('click', 'div[name="divImgRemData"]', fDivImgRemData);
    $(document).on('click', 'div[name="divImgVerCal"]', fDivImgVerCal);
    $(document).on('change', '#slcGrupoDefCal', fSlcGrupoDefCal);
}

function loadEmpresas() {
    hideError();
    hideLoading();
    $('#btnRemEmpresa').hide();
    $('#frmDadosEmpresa').hide();
    if ($('#slcGrupoVerEmpresa option').size() == "1") {
        $('#slcGrupoVerEmpresa').closest('.form_esq50').hide();
        $('#tblEditEmpresaVazia').show();
    } else {
        $('#tblEditEmpresaVazia').hide();
        $('#slcGrupoVerEmpresa').closest('.form_esq50').show();
    }
    if ($('#tblEmpresasGeral tr').length > 1) {
        $('#tblEmpresasVazia').hide();
        $('#btnRemEmpresa').closest('.linha').show();
        $('#tblEmpresasGeral').show();
    } else {
        $('#tblEmpresasGeral').hide();
        $('#btnRemEmpresa').closest('.linha').hide();
        $('#tblEmpresasVazia').show();
    }
    $('#txtNipcDEmp').inputmask("999 999 999");
    $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});
    $(document).on('click', '#btnGuardarDadosEmp', fBtnGuardarDadosEmp);
    $(document).on('click', '#btnRemEmpresa', fBtnRemEmpresa);
    $(document).on('click', '#chkAllEmpresas', fChkAllEmpresas);
    $(document).on('click', '#divImgVerEmp', fDivImgVerEmpresa);
    $(document).on('change', '#slcFiltrarGrupo', fSlcFiltrarGrupo);
}

function loadGrupos() {
    $.getScriptOnce("js/functions/validacaoCentroDados.js");
    hideError();
    hideLoading();
    if ($('#tblGrupo tr').length > 1) {
        $('#tblGrupoVazia').hide();
        $('#btnGuardarGrupo').closest('.linha').show();
        $('#tblGrupo').show();
    } else {
        $('#tblGrupo').hide();
        $('#btnGuardarGrupo').closest('.linha').hide();
        $('#tblGrupoVazia').show();
    }
    if ($('#tblGrupoAfetacao tr').length > 1) {
        $('#tblEmpresaVazia').hide();
        $('#btnGuardarAfetGrupos').closest('.linha').show();
        $('#tblGrupoAfetacao').show();
    } else {
        $('#tblGrupoAfetacao').hide();
        $('#btnGuardarAfetGrupos').closest('.linha').hide();
        $('#tblEmpresaVazia').show();
    }
    $('.chosenTabelaSelect').chosen({no_results_text: 'Sem resultados!'});
    $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});
    $(document).on('click', '#btnGuardarAfetGrupos', fBtnGuardarAfetGrupos);
    $(document).on('click', '#btnGuardarGrupo', fBtnGuardarGrupo);
    $(document).on('click', '#btnInserirGrupo', fBtnInserirGrupo);
}

function loadMudarPass() {
    $.getScriptOnce("js/functions/validacaoCentroDados.js");
    hideError();
    hideLoading();
    $(document).on('click', '#btnModify', fBtnModify);
}

function loadUsers() {
    $.getScriptOnce("js/functions/validacaoCentroDados.js");
    hideError();
    hideLoading();
    $('#frmDadosUsers').hide();
    $('#txtNomeNovoAdmin').closest('.linha').hide();
    $('#txtPassNovoAdmin').closest('.linha').hide();
    $('#txtPassRepNovoAdmin').closest('.linha').hide();
    $('#txtEmailNovoAdmin').closest('.linha').hide();
    if ($('#tblUserAfet tr').length > 1) {
        $('#tblUserAfetVazia').hide();
        $('#btnGuardarAfet').closest('.linha').show();
        $('#tblUserAfet').show();
    } else {
        $('#tblUserAfet').hide();
        $('#btnGuardarAfet').closest('.linha').hide();
        $('#tblUserAfetVazia').show();
    }
    if ($('#tblUsersGeral tr').length > 1) {
        $('#tblUsersGeralVazia').hide();
        $('#tblUsersGeral').show();
    } else {
        $('#tblUsersGeral').hide();
        $('#tblUsersGeralVazia').show();
    }
    if ($('#tblAdministradores tr').length > 1) {
        $('#tblAdministradoresVazia').hide();
        $('#tblAdministradores').show();
    } else {
        $('#tblAdministradores').hide();
        $('#tblAdministradoresVazia').show();
    }
    $('.chosenTabelaSelect').chosen({no_results_text: 'Sem resultados!'});
    $(document).on('click', '#btnGuardarAfet', fBtnGuardarAfet);
    $(document).on('click', '#btnGuardarDadosUser', fBtnGuardarDadosUser);
    $(document).on('click', '#btnNovoAdmin', fBtnNovoAdmin);
    $(document).on('click', '#chkLDAP', fChkLdap);
    $(document).on('click', '#divImgVerUser', fDivImgVerUser);
}

function fBtnGuardarAfet(event) {
    if (event.handler !== true) {
        var dados = {};
        var dados_afet = carregaDadosAfetUser();
        $('#tblUserAfet tr').each(function(key, value) {
            if (key > 0) {
                var id_utilizador = $(this).children('td').eq(0).find('input').val();
                var id_empresa = $(this).children('td').eq(1).find('select[name="slcEmpresaAfet"]').val();
                $.each(dados_afet.getJson(), function(j, item) {
                    if (id_utilizador == item.id_user && id_empresa != item.id_empresa) {
                        dados[key] = {};
                        dados[key].id_utilizador = id_utilizador;
                        dados[key].id_empresa = id_empresa;
                    }
                });
            }
        });
        if (Object.size(dados) > 0) {
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=guardar_afet_user";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblUserAfet').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#tblUserAfet').show();
                }
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnGuardarAfetGrupos(event) {
    if (event.handler !== true) {
        var dados = {};
        var dados_afet = carregaDadosAfetacao();
        $('#tblGrupoAfetacao tr').each(function(key, value) {
            if (key > 0) {
                var id_empresa = $(this).find('input[name="hddIdEmpAfet"]').val();
                var id_grupo = $(this).find('select[name="slcGrupoAfet"]').val();
                $.each(dados_afet.getJson(), function(j, item) {
                    if (id_empresa == item.id_empresa && id_grupo != item.id_grupo) {
                        dados[key] = {};
                        dados[key].id_empresa = id_empresa;
                        dados[key].id_grupo = id_grupo;
                    }
                });
            }
        });
        if (Object.size(dados) > 0) {
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=guardar_afet_grupo";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblGrupoAfetacao').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        $('#tblGrupoAfetacao').show();
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
        } else {
            $('.error').show().html('<span id="error">Não há modificações a guardar</span>');
            $("body").animate({
                scrollTop: 0
            });
        }
        event.handler = true;
    }
    return false;
}

function fBtnGuardarAtividade(event) {
    if (event.handler !== true) {
        var id_atividade;
        var cap_soc;
        var nome;
        var dados = {};
        var i = 0;
        if (findDuplicates() === true) {
            $('#tblAtividades tr').each(function(key, value) {
                if (key > 0) {
                    id_atividade = $(this).find('#hddIdAtividade').val();
                    cap_soc = formatValor($(this).find('#txtCapSocAti').val());
                    nome = $(this).find('input[name="txtNomeDesigAti"]').val();
                    dados[i] = {};
                    dados[i].id_atividade = id_atividade;
                    dados[i].cap_soc = cap_soc;
                    dados[i].nome = nome;
                    i = i + 1;
                    $(this).find('.inputarea_col1').css("background-color", "transparent");
                }
            });
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=up_atividades";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    showLoading();
                },
                success: function(dados) {
                    if (dados.sucesso === true) {
                        hideLoading();
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

function fBtnGuardarDadosEmp(event) {
    if (event.handler !== true) {
        var id_empresa = $('#frmDadosEmpresa').find('#hddIdEmpresaDEmp').val();
        var niss = $('#frmDadosEmpresa').find('#txtNissDEmp').val();
        var nipc = $('#frmDadosEmpresa').find('#txtNipcDEmp').val().toString().split(' ').join("");
        var nome = $('#frmDadosEmpresa').find('#txtNomeDEmp').val();
        var morada = $('#frmDadosEmpresa').find('#txtMoradaDEmp').val();
        var cod_postal = $('#frmDadosEmpresa').find('#txtCodPostalDEmp').val();
        var localidade = $('#frmDadosEmpresa').find('#txtLocalidadeDEmp').val();
        var pais = $('#frmDadosEmpresa').find('#txtPaisDEmp').val();
        var email = $('#frmDadosEmpresa').find('#txtEmailDEmp').val();
        var id_grupo = $('#slcGrupoVerEmpresa').val();
        var dataString = "id=" + id_empresa + "&niss=" + niss + "&nipc=" + nipc + "&nome=" + nome + "&morada=" + morada + "&cod_postal=" + cod_postal + "&localidade=" + localidade + "&pais=" + pais + "&email=" + email + "&id_grupo=" + id_grupo + "&tipo=update_dados_empresa";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#frmDadosEmpresa').hide();
                $('#slcGrupoVerEmpresa').hide();
                $('#slcVerEmpresa').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#slcGrupoVerEmpresa').show();
                $('#slcVerEmpresa').show();
                $('#frmDadosEmpresa').show();
                if (dados.sucesso === true) {
                    $('#frmDadosEmpresa').find('#txtNissDEmp').val(niss);
                    $('#frmDadosEmpresa').find('#txtNipcDEmp').val(nipc);
                    $('#frmDadosEmpresa').find('#txtNomeDEmp').val(nome);
                    $('#frmDadosEmpresa').find('#txtMoradaDEmp').val(morada);
                    $('#frmDadosEmpresa').find('#txtCodPostalDEmp').val(cod_postal);
                    $('#frmDadosEmpresa').find('#txtLocalidadeDEmp').val(localidade);
                    $('#frmDadosEmpresa').find('#txtPaisDEmp').val(pais);
                    $('#frmDadosEmpresa').find('#txtEmailDEmp').val(email);
                    emptySelect("#slcVerEmpresa");
                    $.each(dados.dados_in, function() {
                        if (this.id == id_empresa) {
                            $('#slcVerEmpresa').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                        } else {
                            $('#slcVerEmpresa').append($('<option></option>').val(this.id).text(this.nome));
                        }
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
}

function fBtnGuardarDadosUser(event) {
    if (event.handler !== true) {
        var id = $('#frmDadosUsers').find('input[name="hddIdUserFrm"]').val();
        var login = $('#frmDadosUsers').find('input[name="txtLoginUser"]').val();
        var password = $('#frmDadosUsers').find('input[name="txtPassword"]').val();
        var conf_pass = $('#frmDadosUsers').find('input[name="txtConfPassword"]').val();
        var dados_user = carregaDadosUser();
        var dataString = "";
        if (id == dados_user.getJson().id_user && login != dados_user.getJson().login && password === "") {
            dataString = "id=" + id + "&login=" + login + "&modo=login" + "&tipo=up_dados_user";
        } else if (id == dados_user.getJson().id_user && login == dados_user.getJson().login && password !== "") {
            if (validaMudaPass(password, conf_pass) === true) {
                dataString = "id=" + id + "&password=" + password + "&conf_pass=" + conf_pass + "&modo=pass" + "&tipo=up_dados_user";
            } else {
                $('.error').show().html('<span id="error">' + data + '</span>');
                $("body").animate({
                    scrollTop: 0
                });
            }
        } else if (id == dados_user.getJson().id_user && login != dados_user.getJson().login && password !== "") {
            if (validaMudaPass(password, conf_pass) === true) {
                dataString = "id=" + id + "&login=" + login + "&password=" + password + "&conf_pass=" + conf_pass + "&modo=login_pass" + "&tipo=up_dados_user";
            } else {
                $('.error').show().html('<span id="error">' + data + '</span>');
                $("body").animate({
                    scrollTop: 0
                });
            }
        }
        if (dataString !== "") {
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
                    if (dados.sucesso === true) {
                        emptySelect("#slcUsers");
                        $.each(dados.dados_in, function() {
                            if (this.id == id) {
                                $('#slcUsers').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                            } else {
                                $('#slcUsers').append($('<option></option>').val(this.id).text(this.nome));
                            }
                        });
                        $('#frmDadosUsers').find('input[name="txtPassword"]').val('');
                        $('#frmDadosUsers').find('input[name="txtConfPassword"]').val('');
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

function fBtnGuardarData(event) {
    if (event.handler !== true) {
        var id_grupo = $('#frmCalendario').find('#slcGrupoDefCal').val();
        var mes = $('#frmCalendario').find('#slcMesDefCal').val();
        var ano_val = $('#frmCalendario').find('#slcAnoDefCal option:selected').val();
        var ano = $('#frmCalendario').find('#slcAnoDefCal option:selected').text();
        var data_i = $('#frmCalendario').find('input[name="txtDataI"]').val();
        var data_f = $('#frmCalendario').find('input[name="txtDataF"]').val();
        var cor = $('.colorpicker_hex').children('input').val();
        var date = new Date();
        var data = validaFormCalendario(id_grupo, mes, ano_val, date, data_i, data_f, cor);
        // alert('id_grupo: ' + id_grupo + '; mes: ' + mes + '; ano_val: ' + ano_val + '; ano: ' + ano + '; data_i: ' + data_i + '; data_f: ' + data_f + '; cor: ' + cor + '; date: ' + date + '; data: ' + data);
        
        if (data === true) {
            var events = {
                url: 'functions/funcoes_geral.php',
                type: 'POST',
                data: {
                    id_grupo: id_grupo,
                    tipo: "eventos"
                }
            };
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_grupo=" + id_grupo + "&mes=" + mes + "&ano=" + ano + "&data_i=" + data_i + "&data_f=" + data_f + "&cor=" + cor + "&tipo=guardar_data",
                beforeSend: function() {
                    $('#frmCalendario').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmCalendario').show();
                    if (dados.sucesso === true) {
                        $('input[name="txtDataI"]').val('');
                        $('input[name="txtDataF"]').val('');
                        $('#frmDetalhesCal').find('select[name="slcGrupoEditCal"]').val(dados.id_grupo);
                        $('#colorSelector div').css('backgroundColor', '#0000ff');
                        $('#colorSelector').ColorPickerSetColor('#0000ff');
                        $('#divCalendarioInicio').fullCalendar('removeEventSource', events);
                        $('#divCalendarioInicio').fullCalendar('addEventSource', events);
                        $('#divCalendarioInicio').fullCalendar('refetchEvents');
                        $('#slcGrupoDefCal').val(id_grupo);
                        $('#slcMesDefCal').val(0).trigger("chosen:updated");
                        $('#slcAnoDefCal').val(0).trigger("chosen:updated");
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
}

function fBtnGuardarGrupo(event) {
    if (event.handler !== true) {
        if (findDuplicates() === true) {
            var id_grupo;
            var nome;
            var estado;
            var tipo;
            var dados = {};
            var dados_grupo = carregaDadosGrupo();
            $('#tblGrupo tr').each(function(key, value) {
                if (key > 0) {
                    id_grupo = $(this).find('#hddIdGrupo').val();
                    nome = $(this).find('input[name="txtNomeGrupo"]').val();
                    estado = $(this).find('select[name="slcEstadoGrupo"]').val();
                    tipo = $(this).find('input[name="hddTipoGrupo"]').val();
                    //alert(JSON.stringify(dados_grupo.getJson()));
                    $.each(dados_grupo.getJson(), function(j, item) {
                        //alert(item.nome);
                        if (id_grupo == item.id_grupo && estado == item.estado && nome != item.nome) {
                            dados[key] = {};
                            dados[key].id_grupo = id_grupo;
                            dados[key].nome = nome;
                            dados[key].tipo = tipo;
                            $(this).find('.inputarea_col1').css("background-color", "transparent");
                        } else if (id_grupo == item.id_grupo && nome == item.nome && estado != item.estado) {
                            dados[key] = {};
                            dados[key].id_grupo = id_grupo;
                            dados[key].estado = estado;
                            dados[key].tipo = tipo;
                            $(this).find('.inputarea_col1').css("background-color", "transparent");
                        } else if (id_grupo == item.id_grupo && nome != item.nome && estado != item.estado) {
                            dados[key] = {};
                            dados[key].id_grupo = id_grupo;
                            dados[key].nome = nome;
                            dados[key].estado = estado;
                            dados[key].tipo = tipo;
                            $(this).find('.inputarea_col1').css("background-color", "transparent");
                        }
                    });
                }
            });
            if (Object.size(dados) > 0) {
                var dataString = "dados=" + JSON.stringify(dados) + "&tipo=up_grupo";
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
                        if (dados.sucesso === true) {

                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({
                                scrollTop: 0
                            });
                        }
                    }
                });
            } else {
                clearDuplicates("tblGrupo");
                $('.error').show().html('<span id="error">Não há modificações a guardar</span>');
                $("body").animate({
                    scrollTop: 0
                });
            }
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

function fBtnInserirAtividade(event) {
    if (event.handler !== true) {
        var nome = $('input[name="txtNomeAtividade"]').val();
        var cap_soc = formatValor($('input[name="txtCapSocM"]').val());
        if (validaAtividade(nome, cap_soc) === true) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "nome=" + nome + "&cap_soc=" + cap_soc + "&tipo=inserir_atividade",
                beforeSend: function() {
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        $('input[name="txtNomeAtividade"]').val('');
                        $('input[name="txtCapSocM"]').val('');
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
}

function fBtnInserirGrupo(event) {
    if (event.handler !== true) {
        var nome = $('input[name="txtNomeGrupo"]').val();
        var grupo = $('#slcTipoGrupo').val();
        if (validaNovoGrupo(nome, grupo) === true) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "nome=" + nome + "&grupo=" + grupo + "&tipo=inserir_grupo",
                beforeSend: function() {
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        $('input[name="txtNomeGrupo"]').val('');
                        $('#slcTipoGrupo').val(0);
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
}

function fBtnModify(event) {
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
}

function fBtnNovoAdmin(event) {
    if (event.handler !== true) {
        var dados = {};
        var ldap = $('#chkLDAP').prop('checked');
        var username = $('#txtLoginNovoAdmin').val();
        if (ldap === true) {
            if (validaNovoAdminLDAP(username) === true) {
                dados[0] = {};
                dados[0].username = username;
                dados[0].ldap = ldap;
            } else {
                $('.error').show().html('<span id="error">' + data + '</span>');
                $("body").animate({
                    scrollTop: 0
                });
            }
        } else {
            var nome = $('#txtNomeNovoAdmin').val();
            var pass = $('#txtPassNovoAdmin').val();
            var pass_conf = $('#txtPassRepNovoAdmin').val();
            var email = $('#txtEmailNovoAdmin').val();
            if (validaNovoAdminNLDAP(username, nome, pass, pass_conf, email) === true) {
                dados[0] = {};
                dados[0].username = username;
                dados[0].nome = nome;
                dados[0].pass = pass;
                dados[0].pass_conf = pass_conf;
                dados[0].email = email;
                dados[0].ldap = ldap;
            } else {
                $('.error').show().html('<span id="error">' + data + '</span>');
                $("body").animate({
                    scrollTop: 0
                });
            }
        }
        if (Object.size(dados) > 0) {
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=novo_admin";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#divSwitchLDAP').hide();
                    $('#divNovoAdmin').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#divSwitchLDAP').show();
                    $('#divNovoAdmin').show();
                    if (dados.sucesso === true) {
                        emptyTable('#tblAdministradores');
                        $.each(dados.dados_in, function() {
                            $('#tblAdministradores').append('<tr>' +
                                '<td style="padding: 6px;">' + this.login + '</td>' +
                                '<td style="padding: 6px;">' + this.nome_user + '</td>' +
                                '<td style="padding: 6px;">' + this.date + '</td>' +
                                '</tr>');
                        });
                        $('#txtLoginNovoAdmin').val('');
                        $('#txtNomeNovoAdmin').val('');
                        $('#txtPassNovoAdmin').val('');
                        $('#txtPassRepNovoAdmin').val('');
                        $('#txtEmailNovoAdmin').val('');
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

function fBtnRemEmpresa(event) {
    if (event.handler !== true) {
        var dados = {};
        $.each($('#tblEmpresasGeral').find('input[type=checkbox].chk'), function(key, value) {
            if (key > 0) {
                if ($(this).prop('checked') === true) {
                    dados[key] = {};
                    dados[key].id = $(this).closest('tr').find('input[name="chkEmpresa"]').val();
                }
            }
        });
        if (Object.size(dados) > 0) {
            if (confirm('Deseja mesmo remover as linhas selecionadas?')) {
                var dataString = "dados=" + JSON.stringify(dados) + "&tipo=del_empresas";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function() {
                        showLoading();
                        $('#tblEmpresasGeral').hide();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                emptyTable('#tblEmpresasGeral');
                                $.each(dados.dados_in, function() {
                                    $('#tblEmpresasGeral').append('<tr>' +
                                        '<td class="transparent">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkEmpresa_' + this.id_empresa + '" name="chkEmpresa" type="checkbox" class="chk" value="' + this.id_empresa + '">' +
                                        '<label for="chkEmpresa" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + this.id_empresa + '">' +
                                        '</div>' +
                                        '</td>' +
                                        '<td>' + this.empresa + '</td>' +
                                        '<td>' + this.nipc + '</td>' +
                                        '<td style="text-align: left;">' + this.morada + '</td>' +
                                        '<td>' + this.nome + '</td>' +
                                        '</tr>');
                                });
                                $('#tblEmpresasVazia').hide();
                                $('#btnRemEmpresa').hide();
                                $('#tblEmpresasGeral').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                                $('#tblEmpresasGeral').show();
                            } else {
                                emptyTable('#tblEmpresasGeral');
                                $('#tblEmpresasGeral').hide();
                                $('#btnRemEmpresa').hide();
                                $('#slcFiltrarGrupo').val(0);
                                $('#tblEmpresasVazia').show();
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

function fBtnUpdateData(event) {
    if (event.handler !== true) {
        var id_cal = $('#frmDetalhesCal').find('#hddIdCal').val();
        var id_grupo = $('#frmDetalhesCal').find('#slcGrupoEditCal').val();
        var mes = $('#frmDetalhesCal').find('#slcMesEditCal').val();
        var ano_val = $('#frmDetalhesCal').find('#slcAnoEditCal option:selected').val();
        var ano = $('#frmDetalhesCal').find('#slcAnoEditCal option:selected').text();
        var data_i = $('#frmDetalhesCal').find('input[name="txtDatai"]').val();
        var data_f = $('#frmDetalhesCal').find('input[name="txtDataf"]').val();
        var cor = $('.colorpicker_hex').children('input').val();
        var date = new Date();
        if (validaFormCalendario(id_grupo, mes, ano_val, date, data_i, data_f, cor) === true) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_cal=" + id_cal + "&id_grupo=" + id_grupo + "&mes=" + mes + "&ano=" + ano + "&data_i=" + data_i + "&data_f=" + data_f + "&cor=" + cor + "&tipo=update_data",
                beforeSend: function() {
                    $('#calendario_detalhes').closest('.linha').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#calendario_detalhes').closest('.linha').show();
                    if (dados.sucesso === true) {
                        $('#frmDetalhesCal').closest('.linha').hide();
                        $('#tblCalendarioGeral').closest('.linha').show();
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
}

function fBtnVoltarCal(event) {
    if (event.handler !== true) {
        $('#frmDetalhesCal').closest('.linha').hide();
        $('#tblCalendarioGeral').closest('.linha').show();
        event.handler = true;
    }
    return false;
}

function fChkAllEmpresas(event) {
    if (event.handler !== true) {
        if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
            $('#tblEmpresasGeral').find('input[class="chk"]').prop('checked', false);
            $('#btnRemEmpresa').hide();
        } else {
            $('#tblEmpresasGeral').find('input[class="chk"]').prop('checked', true);
        }
        event.handler = true;
    }
    return false;
}

function fChkLdap(event) {
    if (event.handler !== true) {
        if ($(this).prop('checked') === true) {
            $('#txtNomeNovoAdmin').closest('.linha').fadeOut();
            $('#txtPassNovoAdmin').closest('.linha').fadeOut();
            $('#txtPassRepNovoAdmin').closest('.linha').fadeOut();
            $('#txtEmailNovoAdmin').closest('.linha').fadeOut();
        } else {
            $('#txtNomeNovoAdmin').closest('.linha').fadeIn();
            $('#txtPassNovoAdmin').closest('.linha').fadeIn();
            $('#txtPassRepNovoAdmin').closest('.linha').fadeIn();
            $('#txtEmailNovoAdmin').closest('.linha').fadeIn();
        }
        event.handler = true;
    }
}

function fDivImgRemData(event) {
    if (event.handler !== true) {
		$('#btnVoltarCal').click();
		if (confirm('Deseja mesmo remover esta linha?')) {
            var id_cal = $(this).closest('td').children('#hddIdCal').val();
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_cal=" + id_cal + "&tipo=apagar_data",
                beforeSend: function() {
                    // $('#frmDetalhesCal').closest('.linha').hide();
					$('#tblCalendarioGeral').closest('.linha').hide();
					showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#tblCalendarioGeral').closest('.linha').show();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            emptyTable('#tblCalendarioGeral');
                            $.each(dados.dados_in, function() {
                                $('#tblCalendarioGeral').append('<tr>' +
                                    '<td>' + this.nome_grupo + '</td>' +
                                    '<td>' + this.mes + '</td>' +
                                    '<td>' + this.ano + '</td>' +
                                    '<td class="width5 iconwrapper">' +
                                    '<input id="hddIdCal" name="hddIdCal" type="hidden" value="' + this.id_cal + '">' +
                                    '<div id="divImgVerCal_' + this.id_cal + '" name="divImgVerCal" class="novolabelicon icon-info"></div>' +
                                    '</td>' +
                                    '<td class="width5 iconwrapper">' +
                                    '<input id="hddIdCal" name="hddIdCal" type="hidden" value="' + this.id_cal + '">' +
                                    '<div id="divImgRemData_' + this.id_cal + '" name="divImgRemData" class="novolabelicon icon-garbage rem_linha"></div>' +
                                    '</td>' +
                                    '</tr>');
                            });
                        } else {
                            $('#tblCalendarioGeral').append('<tr>' +
                                '<td>Não existe um calendário virtual definido</td>' +
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

function fDivImgVerCal(event) {
    if (event.handler !== true) {
        var id_cal = $(this).closest('td').children('#hddIdCal').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_cal=" + id_cal + "&tipo=dados_calendario",
            beforeSend: function() {
                showLoading();
                $('#tblCalendarioGeral').closest('.linha').hide();
            },
            success: function(dados) {
                hideLoading();
                $('#frmDetalhesCal').closest('.linha').show();
                if (dados.sucesso === true) {
                    $('.chosenSelect').chosen('destroy');
                    $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});
                    $('#frmDetalhesCal').find('#hddIdCal').val(dados.id_cal);
                    $('#frmDetalhesCal').find('select[name="slcGrupoEditCal"]').val(dados.id_grupo);
                    $('#frmDetalhesCal').find('select[name="slcMesEditCal"]').val(dados.mes);
                    emptySelect("#slcAnoEditCal");
                    $('#frmDetalhesCal').find('select[name="slcAnoEditCal"]').append('<option value="1">' + (dados.ano - 2) + '</option>' +
                        '<option value="2">' + (dados.ano - 1) + '</option>' +
                        '<option value="3" selected="selected">' + dados.ano + '</option>' +
                        '<option value="4">' + (dados.ano + 1) + '</option>' +
                        '<option value="5">' + (dados.ano + 2) + '</option>');
                    $('#frmDetalhesCal').find('input[name="txtDatai"]').val(dados.data_inicio + " " + dados.hora_inicio);
                    $('#frmDetalhesCal').find('input[name="txtDataf"]').val(dados.data_fim + " " + dados.hora_fim);
                    $('#frmDetalhesCal').find('.pickerCor').html('<div style="background-color: ' + dados.cor + '"></div>');
                    $('.pickerCor').ColorPickerSetColor(dados.cor);
                    $('#frmDetalhesCal').find('select[name="slcGrupoEditCal"]').trigger("chosen:updated");
                    $('#frmDetalhesCal').find('select[name="slcMesEditCal"]').trigger("chosen:updated");
                    $('#frmDetalhesCal').find('select[name="slcAnoEditCal"]').trigger("chosen:updated");
                    $('html,body').animate({
                        scrollTop: $("#frmDetalhesCal").offset().top
                    }, 'slow');
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fDivImgVerEmpresa(event) {
    if (event.handler !== true) {
        var id = $(this).closest('td').children('#hddIdEmpresa').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id=" + id + "&tipo=dados_empresas",
            beforeSend: function() {
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#frmDadosEmpresa').show();
                if (dados.sucesso === true) {
                    $('#frmDadosEmpresa').find('#hddIdEmpresaDEmp').val(dados.id_empresa);
                    $('#frmDadosEmpresa').find('#txtNissDEmp').val(dados.niss);
                    $('#frmDadosEmpresa').find('#txtNipcDEmp').val(dados.nipc);
                    $('#frmDadosEmpresa').find('#txtNomeDEmp').val(dados.nome);
                    $('#frmDadosEmpresa').find('#txtTipoDEmp').val(dados.tipo);
                    $('#frmDadosEmpresa').find('#txtDesignacaoDEmp').val(dados.designacao);
                    $('#frmDadosEmpresa').find('#txtMoradaDEmp').val(dados.morada);
                    $('#frmDadosEmpresa').find('#txtCodPostalDEmp').val(dados.cod_postal);
                    $('#frmDadosEmpresa').find('#txtLocalidadeDEmp').val(dados.localidade);
                    $('#frmDadosEmpresa').find('#txtPaisDEmp').val(dados.pais);
                    $('#frmDadosEmpresa').find('#txtEmailDEmp').val(dados.email);
                    $('#frmDadosEmpresa').find('#txtGrupoDEmp').val(dados.grupo);
                    
					// $('#frmDadosEmpresa').find('label[for="txtUtilizadorDEmp"]').closest('.linha10').find('.dir80').empty();
					$('#frmDadosEmpresa').find('label[for="txtUtilizadorDEmp"]').closest('.linha10').find('.width80').empty();
                    
					$('#frmDadosEmpresa').find('.extra').each(function() {
                        $(this).remove();
                    });
                    $.each(dados.dados_u, function(i, item) {
                        if (i == "0") {
                            $('#frmDadosEmpresa').find('label[for="txtUtilizadorDEmp"]').closest('.linha10').find('.width80').append('<div class="inputarea left width190">' +
                                '<input id="txtUtilizadorDEmp_' + i + '" name="txtUtilizadorDEmp" type="text" readonly="readonly" value="' + this.nome_user + '">' +
                                '</div>');
                        } else {
                            $('#frmDadosEmpresa').find('label[for="txtUtilizadorDEmp"]').closest('.linha10').append('<div class="width20 left extra"></div>' +
                                '<div class="width80 left extra">' +
                                '<div class="inputarea left width190">' +
                                '<input id="txtUtilizadorDEmp_' + i + '" name="txtUtilizadorDEmp" type="text" readonly="readonly" value="' + this.nome_user + '">' +
                                '</div>' +
                                '</div>');
                        }
                    });
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fDivImgVerUser(event) {
    if (event.handler !== true) {
        var id = $(this).closest('td').children('#hddIdUser').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id=" + id + "&tipo=dados_user",
            beforeSend: function() {
                $('#frmDadosUsers').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#frmDadosUsers').find('input[name="hddIdUserFrm"]').val(dados.id_user);
                $('#frmDadosUsers').find('input[name="txtLoginUser"]').val(dados.login);
                $('#frmDadosUsers').find('input[name="txtNomeEmpresa"]').val(dados.nome_empresa);
                $('#frmDadosUsers').find('input[name="txtPassword"]').val('');
                $('#frmDadosUsers').find('input[name="txtConfPassword"]').val('');
                $('#frmDadosUsers').show();
            }
        });
        event.handler = true;
    }
    return false;
}

function fSlcGrupoDefCal(event) {
    if (event.handler !== true) {
        var id_grupo = $(this).val();
        var events = {
            url: 'functions/funcoes_geral.php',
            type: 'POST',
            data: {
                id_grupo: id_grupo,
                tipo: "eventos"
            }
        };
        $('#divCalendarioInicio').fullCalendar('removeEventSource', events);
        $('#divCalendarioInicio').fullCalendar('addEventSource', events);
        $('#divCalendarioInicio').fullCalendar('refetchEvents');
        event.handler = true;
    }
    return false;
}

function fSlcFiltrarGrupo(event) {
    if (event.handler !== true) {
        var id_grupo = $(this).val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_grupo=" + id_grupo + "&tipo=filtrar_empresas",
            beforeSend: function() {
                showLoading();
                $('#tblEmpresasGeral').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    emptyTable('#tblEmpresasGeral');
                    $.each(dados.dados_in, function() {
                        $('#tblEmpresasGeral').append('<tr>' +
                            '<td style="background-color: transparent; cursor: pointer;">' +
                            '<div class="checkbox">' +
                            '<input id="chkEmpresa_' + this.id_empresa + '" name="chkEmpresa" type="checkbox" class="chk" value="' + this.id_empresa + '">' +
                            '<label for="chkEmpresa" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                            '<input id="hddIdEmpresa" name="hddIdEmpresa" type="hidden" value="' + this.id_empresa + '">' +
                            '</div>' +
                            '</td>' +
                            '<td>' + this.empresa + '</td>' +
                            '<td>' + this.nipc + '</td>' +
                            '<td style="text-align: left;">' + this.morada + '</td>' +
                            '<td>' + this.nome + '</td>' +
                            '</tr>');
                    });
                    $('#tblEmpresasVazia').hide();
                    $('#btnRemEmpresa').hide();
                    $('#tblEmpresasGeral').find('#chkAllEmpresas').closest('.checkbox').find('input').prop('checked', false);
                    $('#tblEmpresasGeral').show();
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

// 20190226 - Funções para gerir tarefas apresentadas no Calendário
function loadTasks() {
    $.getScriptOnce("js/functions/validacaoCentroDados.js");
    hideError();
    hideLoading();
	
    $('#frmDetalhesCalTask').closest('.linha').hide();
	$('#tblCalendTasksGeralVazia').show();
    if ($('#tblCalendTasksGeral tr').length > 1) {
        $('#tblCalendTasksGeralVazia').hide();
        $('#tblCalendTasksGeral').show();
    } else {
        $('#tblCalendTasksGeral').hide();
        $('#tblCalendTasksGeralVazia').show();
    }
    
    /* * /
    $('.datetimepicker_ini').datetimepicker({
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
        // timepicker: true,
        timepicker: false,
        format: 'd-m-Y 00:00:00'
    });
    //
    $('.datetimepicker_fim').datetimepicker({
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
        // timepicker: true,
        timepicker: false,
        format: 'd-m-Y 23:59:59'
    });
    /* */
    
    $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});
    $(document).on('click', '#btnSaveTask', fBtnSaveTask);
    $(document).on('click', '#btnUpdateTaskData', fBtnUpdateTaskData);
    $(document).on('click', '#btnVoltarTaskCal', fBtnVoltarTaskCal);
    $(document).on('click', 'div[name="divImgRemTaskData"]', fDivImgRemTaskData);
    $(document).on('click', 'div[name="divImgVerTaskCal"]', fDivImgVerTaskCal);
}

function fBtnSaveTask() {
    if (event.handler !== true) {
        var id_grupo = $('#frmNewTask').find('#slcGrupoDefTask').val();
        var task_desc = $('#frmNewTask').find('#txtDescTasks').val();
        // var data_i = $('#frmNewTask').find('input[name="txtDataI"]').val();
        // var data_f = $('#frmNewTask').find('input[name="txtDataF"]').val();
        var dia_i = $('#frmNewTask').find('#slcDiaIniDefCalTask').val();
        var mes_i = $('#frmNewTask').find('#slcMesIniDefCalTask').val();
        var dia_f = $('#frmNewTask').find('#slcDiaFimDefCalTask').val();
        var mes_f = $('#frmNewTask').find('#slcMesFimDefCalTask').val();
        
        if (mes_f < mes_i || (mes_i == mes_f && dia_i > dia_f)) {
            $('.error').show().html('<span id="error">Intoduza um intervalo de datas válido!</span>');
            $("body").animate({
                scrollTop: 0
            });
        } else {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_grupo=" + id_grupo + "&desc=" + task_desc + "&dia_i=" + dia_i + "&mes_i=" + mes_i + "&dia_f=" + dia_f + "&mes_f=" + mes_f + "&tipo=guardar_task",
                beforeSend: function() {
                    $('#frmNewTask').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmNewTask').show();
                    if (dados.sucesso === true) {
                        $('#slcGrupoDefTask').val(0).trigger("chosen:updated");
                        $('#txtDesctasks').val('');
                        // $('input[name="txtDataI"]').val('');
                        // $('input[name="txtDataF"]').val('');
                        $('#slcDiaIniDefCalTask').val(0).trigger("chosen:updated");
                        $('#slcMesIniDefCalTask').val(0).trigger("chosen:updated");
                        $('#slcDiaFimDefCalTask').val(0).trigger("chosen:updated");
                        $('#slcMesFimDefCalTask').val(0).trigger("chosen:updated");
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

function fDivImgRemTaskData(event) {
    if (event.handler !== true) {
        if (confirm('Deseja mesmo remover esta linha?')) {
            var id_cal = $(this).closest('td').children('#hddIdCal').val();
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_cal=" + id_cal + "&tipo=apagar_task",
                beforeSend: function() {
                    $('#tblCalendTasksGeral').closest('.linha').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            emptyTable('#tblCalendTasksGeral');
                            $.each(dados.dados_in, function() {
                                $('#tblCalendTasksGeral').append('<tr>' +
                                    '<td>' + this.nome_grupo + '</td>' +
                                    '<td>' + this.dia_v_ini + '</td>' +
                                    '<td>' + this.mes_v_ini + '</td>' +
                                    '<td>' + this.dia_v_fim + '</td>' +
                                    '<td>' + this.mes_v_fim + '</td>' +
                                    '<td class="width5 iconwrapper">' +
                                    '<input id="hddIdCal" name="hddIdCal" type="hidden" value="' + this.id + '">' +
                                    '<div id="divImgVerTaskCal_' + this.id + '" name="divImgVerTaskCal" class="novolabelicon icon-info"></div>' +
                                    '</td>' +
                                    '<td class="width5 iconwrapper">' +
                                    '<input id="hddIdCal" name="hddIdCal" type="hidden" value="' + this.id + '">' +
                                    '<div id="divImgRemTaskData_' + this.id + '" name="divImgRemTaskData" class="novolabelicon icon-garbage rem_linha"></div>' +
                                    '</td>' +
                                    '</tr>');
                            });
                            $('#tblCalendTasksGeral').closest('.linha').show();
                            $('#frmDetalhesCalTask').closest('.linha').hide();
                            // $('#btnVoltarCal').click();
                        } else {
                            $('#tblCalendTasksGeral').append('<tr>' +
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

function fDivImgVerTaskCal(event) {
    if (event.handler !== true) {
        var id_cal = $(this).closest('td').children('#hddIdCal').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_cal=" + id_cal + "&tipo=dados_calendario_task",
            beforeSend: function() {
                showLoading();
                $('#tblCalendTasksGeral').closest('.linha').hide();
            },
            success: function(dados) {
                hideLoading();
                $('#tblCalendTasksGeral').closest('.linha').show();
                if (dados.sucesso === true) {
                    $('.chosenSelect').chosen('destroy');
                    $('.chosenSelect').chosen({no_results_text: 'Sem resultados!'});
                    $('#frmDetalhesCalTask').find('#hddIdCal').val(id_cal);
                    $('#frmDetalhesCalTask').find('select[name="slcGrupoEditCal"]').val(dados.id_grupo);
                    $('#frmDetalhesCalTask').find('#txtDescTasks').val(dados.desc);
                    $('#frmDetalhesCalTask').find('select[name="slcMesIniDefCalTask"]').val(dados.mes_v_ini);
                    $('#frmDetalhesCalTask').find('select[name="slcDiaIniDefCalTask"]').val(dados.dia_v_ini);
                    $('#frmDetalhesCalTask').find('select[name="slcMesFimDefCalTask"]').val(dados.mes_v_fim);
                    $('#frmDetalhesCalTask').find('select[name="slcDiaFimDefCalTask"]').val(dados.dia_v_fim);
                    
                    $('#frmDetalhesCalTask').find('select[name="slcGrupoEditCal"]').trigger("chosen:updated");
                    $('#frmDetalhesCalTask').find('select[name="slcMesIniDefCalTask"]').trigger("chosen:updated");
                    $('#frmDetalhesCalTask').find('select[name="slcDiaIniDefCalTask"]').trigger("chosen:updated");
                    $('#frmDetalhesCalTask').find('select[name="slcMesFimDefCalTask"]').trigger("chosen:updated");
                    $('#frmDetalhesCalTask').find('select[name="slcDiaFimDefCalTask"]').trigger("chosen:updated");
                    $('html,body').animate({
                        scrollTop: $("#frmDetalhesCalTask").offset().top
                    }, 'slow');
                }
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnUpdateTaskData(event) {
    if (event.handler !== true) {
        var id_cal = $('#frmDetalhesCalTask').find('#hddIdCal').val();
        var id_grupo = $('#frmDetalhesCalTask').find('#slcGrupoEditCal option:selected').val();
        var nm_grupo = $('#frmDetalhesCalTask').find('#slcGrupoEditCal option:selected').text();
        var desc = $('#frmDetalhesCalTask').find('#txtDescTasks').val();
        var mes_v_ini = $('#frmDetalhesCalTask').find('#slcMesIniDefCalTask option:selected').val();
        var mes_v_inm = $('#frmDetalhesCalTask').find('#slcMesIniDefCalTask option:selected').text();
        var dia_v_ini = $('#frmDetalhesCalTask').find('#slcDiaIniDefCalTask option:selected').val();
        var mes_v_fim = $('#frmDetalhesCalTask').find('#slcMesFimDefCalTask option:selected').val();
        var mes_v_fnm = $('#frmDetalhesCalTask').find('#slcMesFimDefCalTask option:selected').text()
        var dia_v_fim = $('#frmDetalhesCalTask').find('#slcDiaFimDefCalTask option:selected').val();
        
        if (mes_v_fim < mes_v_ini || (mes_v_ini == mes_v_fim && dia_v_ini > dia_v_fim)) {
            $('.error').show().html('<span id="error">Intoduza um intervalo de datas válido!</span>');
            $("body").animate({
                scrollTop: 0
            });
        } else if (confirm('Deseja mesmo alterar esta tarefa?')) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_cal=" + id_cal + "&id_grupo=" + id_grupo + "&desc=" + desc + "&mes_v_ini=" + mes_v_ini + "&dia_v_ini=" + dia_v_ini + "&mes_v_fim=" + mes_v_fim + "&dia_v_fim=" + dia_v_fim + "&tipo=update_task",
                beforeSend: function() {
                    $('#tblCalendTasksGeral').closest('.linha').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    // $('#tblCalendTasksGeral').closest('.linha').show();
                    if (dados.sucesso === true) {
                        $('#frmDetalhesCalTask').closest('.linha').hide();
                        $('#tblCalendTasksGeral').closest('.linha').show();
                        
                        $('#tblCalendTasksGeral tr:not(:first)').each(function () {
                            var id = $(this).find('#hddIdCal').val();
                            // var id = $(this).children('td').eq(5).children('#hddIdCal').val();
                            if (id == id_cal) {
                                $(this).children('td').eq(0).text(nm_grupo);
                                $(this).children('td').eq(1).text(dia_v_ini);
                                $(this).children('td').eq(2).text(mes_v_inm);
                                $(this).children('td').eq(3).text(dia_v_fim);
                                $(this).children('td').eq(4).text(mes_v_fnm);
                            }
                        });
                        
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

function fBtnVoltarTaskCal(event) {
    if (event.handler !== true) {
        $('#frmDetalhesCalTask').closest('.linha').hide();
        $('#tblCalendTasksGeral').closest('.linha').show();
        event.handler = true;
    }
    return false;
}