/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:10
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-28 18:45:47
*/

$(document).ready(function() {
    heartbeat();
    setInterval(heartbeat, 60000);
    updateRelogio();
    setInterval(function(){updateRelogio()}, 1000);
    hideLoading();
    hideError();
    $('#divPagEncomenda').hide();

    if ($('#tblProdutos tr').length > 1) {
        $('#tblProdVazia').hide();
        $('#tblProdutos').show();
    } else {
        $('#tblProdutos').hide();
        $('#tblProdVazia').show();
    }
    
    if ($('#tblCarrinho tr').length > 0) {
        $('#tblCarrVazio').hide();
        $('#btnVerEncomenda').closest('.linha').show();
        $('#tblCarrinho').show();
    } else {
        $('#tblCarrinho').hide();
        $('#btnVerEncomenda').closest('.linha').hide();
        $('#tblCarrVazio').show();
    }
    $(document).on('mousedown', fEsconderErro);
    
    /* Search de Produtos com plugin "quicksearch" (ABSOLETO) * /
    var qs = $('input#txtProcProd').quicksearch('#tblProdutos .tbody', {
        noResults: '.noresults',
        stripeRows: ['odd', 'even'],
        loader: 'span.loading',
        show: function() {
            this.style.display = "";
            $(this).closest('#tblProdutos').find('tr').eq(0).show();
        },
        hide: function() {
            this.style.display = "none";
            if ($(this).closest('#tblProdutos').find('.tbody').filter(':visible').length == "0")
                $(this).closest('#tblProdutos').find('tr').eq(0).hide();
        }
    });
    /* */
    var qs = "";
   
    /* Search com clique em botão de "lupa". Alteraçao para "elminar" lentidao */
    $(document).on('click', '.icon-lupa', function(event) {
        if (event.handler !== true) {
            var nome = $('#txtProcProd').val();
            var dataString = "nome=" + nome + "&tipo=produtos_search";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblProdutos').hide();
                    $('#tblProdVazia').hide();
                    showLoading();
                },
                success: function(dados) {
                    emptyTable('#tblProdutos');
                    
                    if (dados.vazio === false) {
                        $('#slcCategoria').val(0);
                        $('#slcSubcategoria').val(0);
                        $('#slcFamilia').val(0);
                        
                        $.each(dados.dados_in, function (i, item) {
                            var tx_iva = dados.dados_in[i].taxa > 0 ? number_format(dados.dados_in[i].taxa, 0, ',', '.') + '%' : '-';
                            var tx_irc = dados.dados_in[i].taxa < 0 ? number_format(Math.abs(dados.dados_in[i].taxa), 0, ',', '.') + '%' : '-';
                            var preco_un = dados.dados_in[i].taxa < 0 ? number_format(dados.dados_in[i].preco_un, 2, ',', '.') : number_format(dados.dados_in[i].preco, 2, ',', '.');
                            $('#tblProdutos').append('<tr class="tbody">' +
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
                        $('#tblProdutos').find('tr').eq(0).show();
                        
                    } else {
                        $('#tblProdutos').find('tr').eq(0).hide();
                        $('#tblProdutos').append('<tr><td class="noresults" colspan="5">Não existem resultados</td></tr>');
                        
                    }
                    $('#tblProdutos').show();
                }
            });
            
            event.handler = true;
        }
    });
    
    $(document).on('change', '#slcCategoria', function(event) {
        if (event.handler !== true) {
            var id = $(this).val();
            var dataString = "id=" + id + "&nivel=0" + "&tipo=produtos_dados";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblProdutos').hide();
                    $('#tblProdVazia').hide();
                    showLoading();
                },
                success: function(dados) {
                    if (dados.sucesso === true) {
                        povTblProd('tblProdutos', dados, 'slcSubcategoria', id, qs);
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({scrollTop: 0});
                    }
                }
            });
            event.handler = true;
        }
    });

    $(document).on('change', '#slcSubcategoria', function(event) {
        if (event.handler !== true) {
            var cat = $('#slcCategoria').val();
            var id = $(this).val();
            var dataString = "id=" + id + "&cat=" + cat + "&nivel=1" + "&tipo=produtos_dados";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblProdutos').hide();
                    $('#tblProdVazia').hide();
                    showLoading();
                },
                success: function(dados) {
                    if (dados.sucesso === true) {
                        povTblProd('tblProdutos', dados, 'slcFamilia', id, qs);
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({scrollTop: 0});
                    }
                }
            });
            event.handler = true;
        }
    });

    $(document).on('change', '#slcFamilia', function(event) {
        if (event.handler !== true) {
            var cat = $('#slcSubcategoria').val();
            var id = $(this).val();
            var dataString = "id=" + id + "&cat=" + cat + "&nivel=2" + "&tipo=produtos_dados";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                data: dataString,
                dataType: "json",
                beforeSend: function() {
                    $('#tblProdutos').hide();
                    $('#tblProdVazia').hide();
                    showLoading();
                },
                success: function(dados) {
                    if (dados.sucesso === true) {
                        povTblProd('tblProdutos', dados, '', id, qs);
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({scrollTop: 0});
                    }
                }
            });
            event.handler = true;
        }
    });

    $(document).on('click', '.add_carrinho', function(event) {
        if (event.handler !== true) {
            var id_produto = $(this).children('#hddIdProd').val();
            var str = $(this).closest('tr').find('.preco').html().split(' ');
            var preco = formatValor(str[0]);
            var id_fornecedor = $(this).closest('tr').find('#hddIdFornecedor').val();
            var taxa_iva = $(this).closest('tr').find('#hddTaxaIva').val();
            var dataString = "id_produto=" + id_produto + "&preco=" + preco + "&id_fornecedor=" + id_fornecedor + "&taxa_iva=" + taxa_iva + "&tipo=add";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                data: dataString,
                dataType: "json",
                beforeSend: function() {
                    $('#tblProdutos').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#tblProdutos').show();
                    if (dados.sucesso === true) {
                        $('#tblCarrinho').empty();
                        $.each(dados.dados_in, function(i, item) {
                            $('#tblCarrinho').append('<tr>' +
                                    '<td style="width: 65%;">' + dados.dados_in[i].nome + '</td>' +
                                    '<td style="width: 35%; text-align: right; padding-right: 5px;">' + number_format(dados.dados_in[i].valor, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                    '</tr>');
                        });
                        $('#tblCarrVazio').hide();
                        $('#btnVerEncomenda').closest('.linha').show();
                        $('#tblCarrinho').show();
                    }
                    
                     else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({scrollTop: 0});
                    }
                    
                }
            });
            event.handler = true;
        }
    });

    $(document).on('click', '#btnVerEncomenda', function(event) {
        if (event.handler !== true) {
            $('#divPagProdutos').hide();
            $('#divPagEncomenda').show();
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                data: "tipo=get_carrinho",
                dataType: "json",
                beforeSend: function() {
                    $('#tblCarrDetailVazio').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        povTblProdDetail(dados);
                        $('#tblCarrDetailVazio').hide();
                        $('#tblCarrDetail').show();
                        $('#divTotalEncomenda').closest('.linha').show();
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

    $(document).on('click', '#btnVoltar', function(event) {
        if (event.handler !== true) {
            $('#divPagEncomenda').hide();
            $('#divPagProdutos').show();
            event.handler = true;
        }
        return false;
    });

    $(document).on('click', '.rem_linha', function(event) {
        if (event.handler !== true) {
            if (confirm('Deseja mesmo remover esta linha?')) {
                var id_p_add = $(this).children('#hddProdCarr').val();
                var dataString = "id_produto=" + id_p_add + "&tipo=rem_linha";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_users.php",
                    data: dataString,
                    dataType: "json",
                    beforeSend: function() {
                        $('#tblCarrinho').hide();
                        showLoading();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                povTblProdDetail(dados);
                            } else {
                                $('#tblCarrDetail').hide();
                                $('#divTotalEncomenda').closest('.linha').hide();
                                $('#tblCarrinho').hide();
                                $('#btnVerEncomenda').closest('.linha').hide();
                                $('#tblCarrDetailVazio').show();
                                $('#tblCarrVazio').show();
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
    });

    $(document).on('click', '.limpar_carrinho', function(event) {
        if (event.handler !== true) {
            if (confirm('Deseja mesmo limpar o carrinho?')) {
                var dataString = "tipo=esvaziar_carrinho";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_users.php",
                    data: dataString,
                    dataType: "json",
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            hideLoading();
                            emptyTable('#tblCarrDetail');
                            $('#tblCarrDetail').hide();
                            $('#divTotalEncomenda').closest('.linha').hide();
                            $('#tblCarrinho').hide();
                            $('#btnVerEncomenda').closest('.linha').hide();
                            $('#tblCarrDetailVazio').show();
                            $('#tblCarrVazio').show();
                        }
                    }
                });
            }
            event.handler = true;
        }
    });

    $(document).on('click', '.editableText', function(event) {
        if (event.handler !== true) {
            var id_p_add = $(this).closest("tr").find('#hddProdCarr').val();
            var t=$(this).closest("tr").find('#txtPonderacao');
            var e=$(this).closest("tr").find('#txtQuantEnc');
            var input = $(this);
            input.attr('readonly', false);
            var dataString;
            input.focusout(function() {
                input.attr('readonly', true);
                var quant = input.val();
                var a=t.val();
                var b=e.val();
                input.val(quant);
                dataString = "ponderacao=" + formatValor(a) + "&quantidade=" + formatValor(b) + "&id_produto=" + id_p_add + "&tipo=act_qtd";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_users.php",
                    data: dataString,
                    dataType: "json",
                    beforeSend: function() {
                        $('#divCarrDetail').closest('.linha').hide();
                        $('#btnVoltar').closest('.linha').hide();
                        showLoading();
                    },
                    success: function(dados) {
                        hideLoading();
                        $('#divCarrDetail').closest('.linha').show();
                        $('#btnVoltar').closest('.linha').show();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                povTblProdDetail(dados);
                            } else {
                                $('#tblCarrDetail').hide();
                                $('#divTotalEncomenda').closest('.linha').hide();
                                $('#tblCarrinho').hide();
                                $('#btnVerEncomenda').closest('.linha').hide();
                                $('#tblCarrDetailVazio').show();
                                $('#tblCarrVazio').show();
                            }
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({scrollTop: 0});
                        }
                    }
                });
            });
            input.keydown(function(event) {
                if (event.which == 13) {
                    input.attr('readonly', true);
                    var quant = input.val();
                    var a=t.val();
                    var b=e.val();
                    input.val(quant);
//                    input.attr('readonly', true);
//                    var quant = input.val();
//                    input.val(quant);
                    dataString = "ponderacao=" + formatValor(a) + "&quantidade=" + formatValor(b) + "&id_produto=" + id_p_add + "&tipo=act_qtd";
                    $.ajax({
                        type: "POST",
                        url: "functions/funcoes_users.php",
                        data: dataString,
                        dataType: "json",
                        beforeSend: function() {
                            $('#divCarrDetail').closest('.linha').hide();
                            $('#btnVoltar').closest('.linha').hide();
                            showLoading();
                        },
                        success: function(dados) {
                            hideLoading();
                            $('#divCarrDetail').closest('.linha').show();
                            $('#btnVoltar').closest('.linha').show();
                            if (dados.sucesso === true) {
                                if (dados.vazio === false) {
                                    povTblProdDetail(dados);
                                } else {
                                    $('#tblCarrDetail').hide();
                                    $('#divTotalEncomenda').closest('.linha').hide();
                                    $('#tblCarrinho').hide();
                                    $('#btnVerEncomenda').closest('.linha').hide();
                                    $('#tblCarrDetailVazio').show();
                                    $('#tblCarrVazio').show();
                                }
                            } else {
                                $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                                $("body").animate({scrollTop: 0});
                            }
                        }
                    });
                }
            });
            event.handler = true;
        }
        return false;
    });

    $(document).on('click', '#btnEncomendar', function(event) {
        if (event.handler !== true) {
            var f_array = [];
            var dados = {};
            dados.fornecedor = {};
            dados.data = getVirtualDate();
            $("#tblCarrDetail tr").each(function(key, value) {
                if (key > 0) {
                    var id_fornecedor = $(this).find('#hddIdFornecedor').val();
                    if ($.inArray(id_fornecedor, f_array) == -1) {
                        f_array.push(id_fornecedor);
                        dados.fornecedor[f_array.length - 1] = {};
                        dados.fornecedor[f_array.length - 1].produtos = {};
                    }
                }
            });
            for (var i = 0; i < f_array.length; i++) {
                var total_iva = 0;
                var total_irc = 0;
                var total_desconto = 0;
                var total = 0;
                var key = f_array[i];
                $("#tblCarrDetail tr").each(function(index) {
                    if (index > 0) {
                        var id_fornecedor_in = $(this).find('#hddIdFornecedor').val();
                        var desconto = $(this).find('#hddTaxaDesc').val();
                        dados.fornecedor[i].id_fornecedor = key;
                        if (key == id_fornecedor_in) {
                            var id_produto = $(this).find('#hddIdProduto').val();
                            var id_prod_carr = $(this).find('#hddProdCarr').val();
                            var preco = parseFloat(formatValor($(this).children().eq(4).text()));
                            var quantidade = parseFloat(formatValor($(this).find('#txtQuantEnc').val()));
                            var ponderacao=parseFloat(formatValor($(this).find('#txtPonderacao').val()));
                            var valor_ini = (preco * quantidade)*ponderacao;
                            var valor_base = parseFloat(valor_ini.toFixed(2));
                            var desc_ini = valor_base * (desconto / 100);
                            var desc = parseFloat(desc_ini.toFixed(2));
                            var iva_ini = (valor_base - desc) * ($(this).find('#hddTaxaIva').val() / 100);
                            var iva = parseFloat(iva_ini.toFixed(2));
                            var irc_ini = (valor_base - desc) * ($(this).find('#hddTaxaIrc').val() / 100);
                            var irc = parseFloat(irc_ini.toFixed(2));
                            var valor = valor_base - desc + iva - irc;
                            total_iva += iva;
                            total_irc += irc;
                            total_desconto += desc;
                            total += valor;
                            dados.fornecedor[i].produtos[index] = {};
                            dados.fornecedor[i].produtos[index].id_produto = id_produto;
                            dados.fornecedor[i].produtos[index].id_prod_carr = id_prod_carr;
                            dados.fornecedor[i].produtos[index].preco = preco*ponderacao;
                            dados.fornecedor[i].produtos[index].quantidade = quantidade;
                            dados.fornecedor[i].produtos[index].iva = iva;
                            dados.fornecedor[i].produtos[index].irc = irc;
                            dados.fornecedor[i].produtos[index].valor = valor;
                            //dados.fornecedor[i].produtos[index].desconto = desc;
                            dados.fornecedor[i].produtos[index].desconto = desc;
                            dados.fornecedor[i].total_iva = total_iva;
                            dados.fornecedor[i].total_irc = total_irc;
                            dados.fornecedor[i].total_desconto = total_desconto;
                            dados.fornecedor[i].total = total;
                        }
                    }
                });
            }
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=encomendar";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                data: dataString,
                dataType: "json",
                beforeSend: function() {
                    $('#divCarrDetail').closest('.linha').hide();
                    $('#btnVoltar').closest('.linha').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#divCarrDetail').closest('.linha').show();
                    $('#btnVoltar').closest('.linha').show();
                    if (dados.sucesso === true) {
                        $.each(dados.dados_in, function (i, item) {
                            var id = dados.dados_in[i].id;
                            var pais = dados.dados_in[i].pais;
                            $('#btnVoltar').click();
                            var win = window.open("./impressao/fatura.php?id=" + id + "&p=" + pais);
                            win.focus();
                        });
                    }
                    $('#divPagEncomenda').hide();
                    $('#tblCarrinho').hide();
                    $('#btnVerEncomenda').closest('.linha').hide();
                    $('#divPagProdutos').show();
                    $('#tblCarrVazio').show();
                }
            });
            event.handler = true;
        }
    });
    
    //--
    $(document).on('change', '#slcPaisProduto', function(event) {
        if (event.handler !== true) {
            var id_pais = $('#slcPaisProduto').val();
            var dataString = "pais=" + id_pais + "&tipo=carregar_produto_pais";
            
            $.ajax({
                type: "POST",
                url: "functions/funcoes_users.php",
                data: dataString,
                dataType: "json",
                success: function(dados) {
                    emptyTable('#tblProdutos');
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            $('#tblProdVazia').hide();
                            $('#tblProdutos').show();
                            $.each(dados.dados_in, function() {
                                $('#tblProdutos').append('<tr class="tbody">' +
                                    '<td style="text-align: left; padding: 1%;">' + this.nome_produto +
                                        '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '">' +
                                        '<input id="hddTaxaIva" name="hddTaxaIva" type="hidden" value="' + this.taxa_iva + '">' + 
                                    '</td>' +
                                    '<td style="text-align: left; padding: 1%;">' + this.nome_fornecedor + '</td>' +
                                    '<td class="preco" style="padding: 1%;">' + number_format(this.preco, 2, ",", ".") + ' ' + this.simbolo_moeda + '</td>' +
                                    '<td style="padding: 1%;">' + number_format(this.taxa_iva, 0, ",", ".") + '% </td>' +
                                    '<td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">' + 
                                        '<div id="btnAddCarr" name="btnAddCarr" class="labelicon icon-carrinho add_carrinho" style="margin: 0;">' + 
                                            '<input id="hddIdProd" name="hddIdProd" type="hidden" value=' + this.id_produto + '>' + 
                                        '</div>' + 
                                    '</td>' + 
                                '</tr>');
                            });
                        }
                        else {
                            $('#tblProdutos').hide();
                            $('#tblProdVazia').show();
                        }
                    }
                }
            });
            
            event.handler = true;
        }
    });
    //--
});