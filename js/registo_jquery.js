/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:10
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-10-01 18:53:59
*/

$(document).ready(function() {
    
    //$('#captcha').hide();
    
    $('.chosen-select').chosen({no_results_text: 'Sem resultados!'});

    var dataStore = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_registo.php",
            data: "tipo=checkLDAP",
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

    function checkLDAP() {
        return dataStore.getJson().ldap;
    }

    hideError();
    $(document).on('mousedown', fEsconderErro);
    $("div.desc").hide();
    $("#divNovaEnt").show();
    $('#txtNCapSocM').prop('readonly', true);
    $('#txtNCapSocE').prop('readonly', true);
    floatMask();
    $(document).on('click', 'input[name$="empresa"]', function(event) {
        if (event.handler !== true) {
            var nome = $(this).val();
            $("div.desc").hide();
            $("#" + nome).show();
            hideError();
            if($('#radNovaEmp').prop('checked') === true) {
                $('.nova_emp').css({"background-color": "#1B1B1B"});
                $('.emp_exist').css({"background-color": "#3F3F3F"});
                $('.nova_emp').children('img').attr('src', "images/newcomp.jpg");
                $('.emp_exist').children('img').attr('src', "images/existcomp_uns.jpg");
            } else {
                $('.nova_emp').css({"background-color": "#3F3F3F"});
                $('.emp_exist').css({"background-color": "#1B1B1B"});
                $('.nova_emp').children('img').attr('src', "images/newcomp_uns.jpg");
                $('.emp_exist').children('img').attr('src', "images/existcomp.jpg");
            }
            event.handler = true;
        }
    });

    $(document).on('click', '#btnRegUser', function(event) {
        if (event.handler !== true) {
            var val = true;
            var login = $('#txtUsername').val();
            var password = $('#txtPassword').val();
            var grupo = $('#slcGrupoReg').val();
            var dataString = "";
            var recaptcha = $('textarea#g-recaptcha-response').val();
            
            if (checkLDAP() === true) {
                if ((validaUserLDAP(login, password, grupo)) !== true) {
                    $('.error').show().html('<span id="error">' + data + '</span>');
                    $("body").animate({scrollTop: $(document).height() - $(window).height()});
                    val = false;
                } else {
                    dataString = "&username=" + login + "&password=" + password + "&grupo=" + grupo + "&g-recaptcha-response=" + recaptcha + "&modo=ldap" + "&tipo=reg_user";
                }
            } else {
                var nome_completo = $('#txtCompleteName').val();
                var conf_pass = $('#txtConfPassword').val();
                var email = $('#txtEmail').val();
                if ((validaUserNLDAP(nome_completo, login, password, conf_pass, email, grupo)) !== true) {
                    $('.error').show().html('<span id="error">' + data + '</span>');
                    $("body").animate({scrollTop: $(document).height() - $(window).height()});
                    val = false;
                } else {
                    dataString = "name=" + nome_completo + "&username=" + login + "&password=" + password + "&pass_conf=" + conf_pass + "&email=" + email + "&grupo=" + grupo + "&g-recaptcha-response=" + recaptcha + "&modo=nldap" + "&tipo=reg_user";
                }
            }
            if (val === true) {
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_registo.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            window.location.href = dados.pagina;
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({scrollTop: $(document).height() - $(window).height()});
                        }
                    }
                });
            }
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnRegEmp', function(event) {
        if (event.handler !== true) {
            var niss = $('#txtNNiss').text();
            var nipc = $('#txtNNipc').text();
            var nome = $('#txtNNomeEmp').val();
            var tipo = $('#slcNTipoEmpresa').val();
            var atividade = $('#slcNAtividadeEmp').val();
            var cap_soc_m = formatValor($('#txtNCapSocM').val());
            var cap_soc_e;
            if($('#txtNCapSocE').val() != "") {
                cap_soc_e = formatValor($('#txtNCapSocE').val());
            } else {
                cap_soc_e = 0;
            }
            var cap_soc_o = $('#hddNCapSocO').val();
            var morada = $('#txtNMoradaEmp').val();
            var cod_postal = $('#txtNCodPostal').val();
            var localidade = $('#txtNLocalidade').val();
            var email = $('#txtNEmailEmp').text();
            var pais = $('#txtNPais').val();
            var grupo = $('#hddIdGrupo').val();
            if ((validaForm(niss, nipc, nome, tipo, atividade, cap_soc_m, cap_soc_e, cap_soc_o, morada, cod_postal, localidade, pais, grupo)) === true) {
                var dataString = "niss=" + niss + "&nipc=" + nipc + "&nome=" + nome + "&tipo_emp=" + tipo + "&atividade=" + atividade + "&cap_soc_m=" + cap_soc_m + "&cap_soc_e=" + cap_soc_e + "&morada=" + morada + "&cod_postal=" + cod_postal + "&localidade=" + localidade + "&email=" + email + "&pais=" + pais + "&grupo=" + grupo + "&tipo=reg_empresa";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_registo.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            window.location.href = 'terminar_sessao.php';
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({scrollTop: $(document).height() - $(window).height()});
                        }
                    }
                });
            } else {
                $('.error').show().html('<span id="error">' + data + '</span>');
                $("body").animate({scrollTop: $(document).height() - $(window).height()});
            }
            event.handler = true;
        }
        return false;
    });

    $(document).on('change', '#txtNomeJEmp', function(event) {
        if (event.handler !== true) {
            var id = $(this).val();
            if (id == "0") {
                $('#txtJNiss').text("Nº de Identif. de Segurança Social");
                $('#txtJNipc').text("Nº de Identif. de Pessoa Coletiva");
                $('#txtJTipo').text("Tipo de empresa");
                $('#txtJAtividade').text("Atividade");
                $('#txtJCapSocM').text("Capital social monetário");
                $('#txtJCapSocE').text("Capital social em espécie");
                $('#txtJMorada').text("Morada");
                $('#txtJCodPostal').text("Código postal");
                $('#txtJLocalidade').text("Localidade");
                $('#txtJEmail').text("Correio eletrónico");
                $('#txtJPais').text("País");
                $('#txtJGrupo').text("Grupo");
            } else {
                var dataString = "id=" + id + "&tipo=dados_empresa";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_registo.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            $('#txtJNiss').text(dados.niss);
                            $('#txtJNipc').text(dados.nipc);
                            $('#txtJTipo').text(dados.tipo);
                            $('#txtJAtividade').text(dados.atividade);
                            $('#txtJCapSocM').text(dados.cap_soc_m);
                            $('#txtJCapSocE').text(dados.cap_soc_e);
                            $('#txtJMorada').text(dados.morada);
                            $('#txtJCodPostal').text(dados.cod_postal);
                            $('#txtJLocalidade').text(dados.localidade);
                            $('#txtJPais').text(dados.pais);
                            $('#txtJEmail').text(dados.email);
                            $('#txtJBanco').text(dados.banco);
                            $('#txtJGrupo').text(dados.grupo);
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({scrollTop: $(document).height() - $(window).height()});
                        }
                    }
                });
            }
            event.handler = true;
        }
        return false;
    });

    $(document).on('change', '#slcNAtividadeEmp', function(event) {
        if (event.handler !== true) {
            var id = $(this).val();
            if (id == "0") {
                $('#txtNCapSocM').prop('readonly', true).val('');
                $('#txtNCapSocE').prop('readonly', true).val('');
            } else {
                var dataString = "id=" + id + "&tipo=dados_atividade";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_registo.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            $('#txtNCapSocM').prop('readonly', false).val(number_format(dados.cap_soc, 2, ',', '.'));
                            $('#txtNCapSocE').prop('readonly', false).val('');
                            $('#hddNCapSocO').val(dados.cap_soc);
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({scrollTop: $(document).height() - $(window).height()});
                        }
                    }
                });
            }
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnJuntarEmp', function(event) {
        if (event.handler !== true) {
            var nome = $('#txtNomeJEmp').val();
            if (nome !== "") {
                var dataString = "nome=" + nome + "&tipo=juntar_empresa";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_registo.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            window.location.href = 'terminar_sessao.php';
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({scrollTop: $(document).height() - $(window).height()});
                        }
                    }
                });
            } else {
                $('.error').show().html('<span id="error">Escolha uma empresa</span>');
                $("body").animate({scrollTop: $(document).height() - $(window).height()});
            }
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnSairReg', function(event) {
        if (event.handler !== true) {
            var dataString = "tipo=reg_sair";
            $.ajax({
                async: false,
                type: "POST",
                url: "functions/funcoes_registo.php",
                data: dataString,
                dataType: "json",
                success: function(dados) {
                    if (dados.sucesso === true) {
                        window.location.href = 'terminar_sessao.php';
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({scrollTop: $(document).height() - $(window).height()});
                    }
                }
            });
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnUserSair', function(event) {
        if (event.handler !== true) {
            window.location.href="terminar_sessao.php";
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnGenNISS', function(event) {
        if (event.handler !== true) {
            var dataString = "tipo=gera_niss";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_registo.php",
                data: dataString,
                dataType: "json",
                success: function(dados) {
                    if (dados.sucesso === true) {
                        $('#txtNNiss').text(dados.niss);
                    }
                }
            });
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnGenNIPC', function(event) {
        if (event.handler !== true) {
            var dataString = "tipo=gera_nipc";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_registo.php",
                data: dataString,
                dataType: "json",
                success: function(dados) {
                    if (dados.sucesso === true) {
                        $('#txtNNipc').text(dados.nipc);
                    }
                }
            });
            event.handler = true;
        }
        return false;
    });
    $(document).on('keyup', '#txtNNomeEmp', function(e) {
        var nome = $('#txtNNomeEmp').val().replace(/\s+|,.+/g, '').toLowerCase();
        if (nome.length > 0) {
            $('#txtNEmailEmp').text(nome + "@simemp.pt");
        } else {
            $('#txtNEmailEmp').text("Correio eletrónico");
        }
    });
});