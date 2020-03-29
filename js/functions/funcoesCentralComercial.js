/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-31 15:06:16
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-29 19:21:28
 */

function carregaCategorias() {
    var dados_categorias = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "tipo=categorias",
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
    return dados_categorias;
}

function carregaFamilias(id) {
    var dados_familias = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id=" + id + "&tipo=familias",
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
    return dados_familias;
}

function carregaProdutos() {
    var dados_produtos = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "tipo=dados_produtos",
            async: false,
            success: function(dados) {
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        json = dados.dados_in;
                    }
                }
            }
        });
        return {
            getJson: function() {
                if (json)
                    return json;
            }
        };
    })();
    return dados_produtos;
}

function carregaSubCategorias(id) {
    var dados_subcategorias = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id=" + id + "&tipo=subcategorias",
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
    return dados_subcategorias;
}

function loadCategorias() {
    $.getScriptOnce("js/functions/validacaoCentralComercial.js");
    hideError();
    hideLoading();
    $('#frmCatAti').hide();
    $('#frmFamilia').hide();
    $('#frmSubcategoria').hide();
    $('#frmCatEmp').hide();
    $('.chosenSelect').chosen({
        allow_single_deselect: true,
        no_results_text: 'Sem resultados!'
    });
    $(document).on('click', '#btnGuardarCat', fBtnGuardarCat);
    $(document).on('click', '#btnGuardarCategoria', fBtnGuardarCategoria);
    $(document).on('click', '#btnGuardarFamilia', fBtnGuardarFamilia);
    $(document).on('click', '#btnGuardarSubcategoria', fBtnGuardarSubcategoria);
    $(document).on('change', '#slcAtividade', fSlcAtividade);
    $(document).on('change', '#slcCatAddFam', fSlcCatAddFam);
    $(document).on('change', '#slcCatAfetCat', fSlcCatAfetCat);
    $(document).on('change', '#slcCatAti', fSlcCatAti);
    $(document).on('change', '#slcCatEditCat', fSlcCatEditCat);
    $(document).on('change', '#slcEmpresa', fSlcEmpresa);
    $(document).on('change', '#slcSubcatEditCat', fSlcSubcatEditCat);
}

function loadFaturas() {
    hideError();
    hideLoading();
    $('#btnRemFatExt').hide();
    $('#divFatIntDetail').closest('.linha').hide();
    $('#divFatExtDetail').closest('.linha').hide();
    $('#slcFiltrarFatExt').closest('.linha').hide();
    $('#hideSlcFiltrarFatExt').hide();
    $('#tblDadosFatExt').hide();
    if ($('#tblDadosFatInt tr').length > 1) {
        $('#tblFatVazia').hide();
        $('#btnRemFatInt').hide();
        $('#hideSlcFiltrarFatGrupo').hide();
        $('#hideSlcFiltrarFatInt').hide();
        $('#btnRemFatInt').closest('.linha').show();
        $('#tblDadosFatInt').show();
    } else {
        $('#tblDadosFatInt').hide();
        $('#btnRemFatInt').closest('.linha').hide();
        $('#tblFatVazia').show();
    }
    $('.chosenSelect').chosen({
        allow_single_deselect: true,
        no_results_text: 'Sem resultados!'
    });
    $(document).on('click', 'div[name^="btnIDFactoring"]', fBtnIDFactoring);
    $(document).on('click', 'div[name="btnIDFatInt"]', fBtnIDFatInt);
    $(document).on('click', '#btnRemFatInt', fBtnRemFatInt);
    $(document).on('click', '#btnRemFatExt', fBtnRemFatExt);
    $(document).on('click', '#btnVoltarFatInt', fBtnVoltarFatInt);
    $(document).on('click', '#btnVoltarFatExt', fBtnVoltarFatExt);
    $(document).on('click', '#chkAllInt', fChkAllInt);
    $(document).on('click', '#chkAllExt', fChkAllExt);
    $(document).on('change', '#slcFiltrarFatGrupo', fSlcFiltrarFatGrupo);
    $(document).on('change', '#slcFiltrarFatInt', fSlcFiltrarFatInt);
    $(document).on('change', '#slcFiltrarFatExt', fSlcFiltrarFatExt);
    $(document).on('change', '#slcOrdenarFatInt', fSlcOrdenarFatInt);
    $(document).on('change', '#slcOrdenarFatExt', fSlcOrdenarFatExt);
}

function loadProdutos() {
    $.getScriptOnce("js/functions/validacaoCentralComercial.js");
    hideError();
    hideLoading();
    dynamicInput('tblProdutosAlt');
    floatMask();
    var qs = searchProd();
    $('.chosenSelect').chosen({
        allow_single_deselect: true,
        no_results_text: 'Sem resultados!'
    });
    $('.chosenTabelaSelect').chosen({
        no_results_text: 'Sem resultados!'
    });
    $(document).on('click', '#btnGuardarDadosProdutos', fBtnGuardarDadosProdutos);
    $(document).on('click', '#btnGuardarProduto', fBtnGuardarProduto);
    $(document).on('change', '#slcCatAddProd', fSlcCatAddProd);
    $(document).on('change', '#slcCategoria', {
        qs: qs
    }, fSlcCategoria);
    $(document).on('change', '#slcFamilia', {
        qs: qs
    }, fSlcFamilia);
    $(document).on('change', '#slcFornecedor', {
        qs: qs
    }, fSlcFornecedor);
    $(document).on('change', '#slcSubcatAddProd', fSlcSubcatAddProd);
    $(document).on('change', '#slcSubcategoria', {
        qs: qs
    }, fSlcSubcategoria);
    // carregaProdutosPaginacao(qs);
	
	//--
    $(document).on('change', '#slcTipoDesc', fSlcTipoDesc);
    $(document).on('change', '#slcPaisFornecedor', fSlcPaisFornecedor);
    $(document).on('change', '#slcPaisFornecedorAfet', fSlcPaisFornecedor);
    $(document).on('change', '#slcPaisFornecedorDesc', fSlcPaisFornecedor);
    // $(document).on('change', '#slcPaisFornecedorAfet', fSlcPaisFornecedorAfet);
    
    $(document).on('change', '#slcFornecedorProdutoDesc', fSlcFornecedorDesc);
    
    $(document).on('change', '#slcProdAfet', fSlcProdAfet);
    $(document).on('change', '#slcProdDesc', fSlcProdAfet);
    
    $(document).on('click', '#btnAfetProduto', fBtnAfetProduto);
    $(document).on('click', '#btnAddDescProd', fBtnAddDescProd);
    //--
    
    //-- Editar descontos
    $('#btnGuardarChgDesc').hide();
    if ($('#tblDescDetail tr').length > 1) {
        $('#tblDescDetailVazia').hide();
    } else {
        $('#tblDescDetail').hide();
		$('select[name="slcFornecDesc"]').closest('.linha10').hide();
    }
    
    $(document).on('change', 'select[name="slcFornecDesc"]', fSrchDescFornec);
    $(document).on('change', 'select[name="slcEstadoDesc"]', fSlcEstadoDesc);
    $(document).on('click', '#btnGuardarChgDesc', fBtnEditDesc);
    $(document).on('click', '.delDesc', fBtnDelDesc);
    //--
}

function searchProd() {
    var qs = $('input#txtProcProd').quicksearch('#tblProdutosAlt .tbody', {
        noResults: '#tblProdutosAltVazio',
        stripeRows: ['odd', 'even'],
        loader: 'div.loading',
        show: function() {
            this.style.display = "";
            $(this).closest('#tblProdutosAlt').find('tr').eq(0).show();
            $('#btnGuardarDadosProdutos').show();
            $('#tblProdutosAltVazio').hide();
        },
        hide: function() {
            this.style.display = "none";
            if ($(this).closest('#tblProdutosAlt').find('.tbody').filter(':visible').length == "0") {
                $(this).closest('#tblProdutosAlt').find('tr').eq(0).hide();
                $('#btnGuardarDadosProdutos').hide();
                $('#tblProdutosAltVazio').show();
            }
        }
    });
    return qs;
}

function fBtnGuardarCat(event) {
    if (event.handler !== true) {
        if (findDuplicates() === true) {
            var dados = {};
            var id_cat = $('#slcCatEditCat').val();
            var id_subcat = $('#slcSubcatEditCat').val();
            var dados_categ;
            if (id_cat == "0" && id_subcat == "0") {
                dados_categ = carregaCategorias();
            } else if (id_cat != "0" && id_subcat == "0") {
                dados_categ = carregaSubCategorias(id_cat);
            } else if (id_cat != "0" && id_subcat != "0") {
                dados_categ = carregaFamilias(id_subcat);
            }
            $('#tblCategorias tr').each(function(key, value) {
                if (key > 0) {
                    var id = $(this).find('#hddIdCat').val();
                    var nome = $(this).find('input[name="txtNomeDesignacao"]').val();
                    $.each(dados_categ.getJson(), function(i, item) {
                        if (validaCategoria(nome) === true) {
                            if (id == item.id && nome != item.desig) {
                                dados[key] = {};
                                dados[key].id = id;
                                dados[key].nome = nome;
                            }
                        } else {
                            $('.error').show().html('<span id="error">' + data + '</span>');
                            $("body").animate({
                                scrollTop: 0
                            });
                        }
                    });
                }
            });
            if (Object.size(dados) > 0) {
                var dataString = "dados=" + JSON.stringify(dados) + "&id_cat=" + id_cat + "&id_subcat=" + id_subcat + "&tipo=g_categorias";
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function() {
                        $('#divEditCat').hide();
                        showLoading();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (id_cat == "0" && id_subcat == "0") {
                                emptySelect("#slcCatEditCat");
                                $.each(dados.dados_cat, function(i, item) {
                                    $('#slcCatEditCat').append($('<option></option>').val(this.id).text(this.desig));
                                });
                            } else if (id_cat != "0" && id_subcat == "0") {
                                emptySelect("#slcSubcatEditCat");
                                $.each(dados.dados_subcat, function() {
                                    $('#slcSubcatEditCat').append($('<option></option>').val(this.id).text(this.desig));
                                });
                            }
                            $('#slcCatEditCat').val(id_cat);
                            $('#slcSubcatEditCat').val(id_subcat);
                        } else {
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({
                                scrollTop: 0
                            });
                        }
                        $('#divEditCat').show();
                    }
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

function fBtnGuardarCategoria(event) {
    if (event.handler !== true) {
        var nome = $('input[name="txtCategoria"]').val();
        if (validaCategoria(nome) === true) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "nome=" + nome + "&tipo=guardar_categoria",
                beforeSend: function() {
                    showLoading();
                    $('#frmCategoria').hide();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmCategoria').show();
                    if (dados.sucesso === true) {
                        $('#txtCategoria').val('');
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

function fBtnGuardarDadosProdutos(event) {
    if (event.handler !== true) {
        var dados = {};
        var dados_produtos = carregaProdutos();
        var id_fornecedor;
        var id_produto;
        $('#tblProdutosAlt tr').each(function(key, value) {
            if (key > 0) {
                if ($('input[name="txtNomeProduto"]').length == 0 && $('input[name="txtPrecoProd"]').length == 0) {
                    id_fornecedor = $(this).find('input[name="hddIdFornecedor"]').val();
                    var nome_fornecedor = $(this).find('input[name="txtNomeFornecedor"]').val();
                
                } else if ($('input[name="txtNomeFornecedor"]').length == 0 && $('input[name="txtPrecoProd"]').length == 0) {
                    id_produto = $(this).find('input[name="hddIdProd"]').val();
                    var nome_produto = $(this).find('input[name="txtNomeProduto"]').val();
                    var desc_produto = $(this).find('input[name="txtDescProd"]').val();
                    
                } else {
                    id_fornecedor = $(this).find('input[name="hddIdFornecedor"]').val();
                    id_produto = $(this).find('input[name="hddIdProd"]').val();
                    var preco = formatValor($(this).find('input[name="txtPrecoProd"]').val());
                    var id_regra = $(this).find('select[name="slcTaxa"]').val();
                }
                
                // alert(id_fornecedor);
                $.each(dados_produtos.getJson(), function(i, item) {
                    if (typeof nome_fornecedor !== 'undefined') { // Testa se variavel exite.
                        // console.log(item.id_fornecedor);
                        if ((id_fornecedor == item.id_fornecedor) && (nome_fornecedor != item.nome_abrev)) {
                            dados[key] = {};
                            dados[key].id_fornecedor = id_fornecedor;
                            dados[key].nome_fornecedor = nome_fornecedor;
                            dados[key].tipo_tipo = "nome_fornecedor";
                        }
                    } else if (typeof nome_produto !== 'undefined') {
                        // else if ((id_produto == item.id_produto) && (nome_produto != item.nome)) {
						if ((id_produto == item.id_produto) && (nome_produto != item.nome)) {
                            dados[key] = {};
                            dados[key].id_produto = id_produto;
                            dados[key].nome_produto = nome_produto;
                            dados[key].tipo_tipo = "nome_produto";
                        } else if ((id_produto == item.id_produto) && (desc_produto != item.descricao)) {
                            dados[key] = {};
                            dados[key].id_produto = id_produto;
                            dados[key].descricao = desc_produto;
                            dados[key].tipo_tipo = "descricao_produto";
                        } else if ((id_produto == item.id_produto) && (nome_produto != item.nome) && (desc_produto != item.descricao)) {
                            dados[key] = {};
                            dados[key].id_produto = id_produto;
                            dados[key].nome_produto = nome_produto;
                            dados[key].descricao = desc_produto;
                            dados[key].tipo_tipo = "nome_descricao";
                        }
                    } else if (typeof preco !== 'undefined') {
                        // else if ((id_fornecedor == item.id_fornecedor) && (id_produto == item.id_produto) && (preco != Math.round10(item.preco, -2)) && (id_regra == item.id_regra)) {
						if ((id_fornecedor == item.id_fornecedor) && (id_produto == item.id_produto) && (preco != Math.round10(item.preco, -2)) && (id_regra == item.id_regra)) {
                            dados[key] = {};
                            dados[key].id_fornecedor = id_fornecedor;
                            dados[key].id_regra = id_regra;
                            dados[key].id_produto = id_produto;
                            dados[key].preco = preco;
                            dados[key].tipo_tipo = "preco";
                        } else if ((id_fornecedor == item.id_fornecedor) && (id_produto == item.id_produto) && (preco == Math.round10(item.preco, -2)) && (id_regra != item.id_regra)) {
                            dados[key] = {};
                            dados[key].id_fornecedor = id_fornecedor;
                            dados[key].id_regra = id_regra;
                            dados[key].id_produto = id_produto;
                            dados[key].preco = preco;
                            dados[key].tipo_tipo = "taxa";
                        } else if ((id_fornecedor == item.id_fornecedor) && (id_produto == item.id_produto) && (id_regra != item.id_regra) && (preco != Math.round10(item.preco, -2))) {
                            dados[key] = {};
                            dados[key].id_fornecedor = id_fornecedor;
                            dados[key].id_regra = id_regra;
                            dados[key].id_produto = id_produto;
                            dados[key].preco = preco;
                            dados[key].tipo_tipo = "preco_taxa";
                        }
                    }
                    /*
                    else {
                        if ((id_fornecedor == item.id_fornecedor) && (nome_fornecedor != item.nome_abrev)) {
                            dados[key] = {};
                            dados[key].id_fornecedor = id_fornecedor;
                            dados[key].nome_fornecedor = nome_fornecedor;
                            dados[key].tipo_tipo = "nome_fornecedor";
                        } else if ((id_produto == item.id_produto) && (nome_produto != item.nome)) {
                            dados[key] = {};
                            dados[key].id_produto = id_produto;
                            dados[key].nome_produto = nome_produto;
                            dados[key].tipo_tipo = "nome_produto";
                        } else if ((id_produto == item.id_produto) && (desc_produto != item.descricao)) {
                            dados[key] = {};
                            dados[key].id_produto = id_produto;
                            dados[key].descricao = desc_produto;
                            dados[key].tipo_tipo = "descricao_produto";
                        } else if ((id_produto == item.id_produto) && (nome_produto != item.nome) && (desc_produto != item.descricao)) {
                            dados[key] = {};
                            dados[key].id_produto = id_produto;
                            dados[key].nome_produto = nome_produto;
                            dados[key].descricao = desc_produto;
                            dados[key].tipo_tipo = "nome_descricao";
                        }
                    }
                    */
                });
            }
        });
        if (Object.size(dados) > 0) {
            var dataString = "dados=" + JSON.stringify(dados) + "&tipo=g_preco_taxa";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#tblProdutosAlt').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#tblProdutosAlt').show();
                    if (dados.sucesso === true) {
                        //event.data.qs.cache();
                    } else {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
            
            $('#slcFornecedorProd').html('');
            var dados_atualizados = carregaProdutos();
            $.each(dados_atualizados.getJson(), function(j, itemj) {
                $('#slcFornecedorProd').append('<option value="' + itemj.id_fornecedor + '">' + itemj.nome_abrev + '</option>');
            });
            
        }
        //event.data.qs.unbind();
        event.handler = true;
    }
    return false;
}

function fBtnGuardarFamilia(event) {
    if (event.handler !== true) {
        var id_categoria = $('#slcCatAddFam').val();
        var id_subcat = $('#slcSubcatAddFam').val();
        var nome = $('input[name="txtFamilia"]').val();
        if (validaFamilia(id_categoria, id_subcat, nome) === true) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "nome=" + nome + "&id_subcat=" + id_subcat + "&tipo=guardar_familia",
                beforeSend: function() {
                    $('#frmFamilia').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmFamilia').show();
                    if (dados.sucesso === true) {
                        emptySelect("#slcCatAddFam");
                        emptySelect("#slcSubcatAddFam");
                        $.each(dados.dados_in, function() {
                            $('#slcCatAddFam').append($('<option></option>').val(this.id).text(this.desig));
                        });
                        $('#txtFamilia').val('');
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

function fBtnGuardarProduto(event) {
    if (event.handler !== true) {
        var id_categoria = $('#frmAddProduto').find('#slcCatAddProd').val();
        var id_subcat = $('#frmAddProduto').find('#slcSubcatAddProd').val();
        var id_familia = $('#frmAddProduto').find('#slcFamAddProd').val();
        var nome = $('#frmAddProduto').find('input[name="txtProduto"]').val();
        var descricao = $('#frmAddProduto').find('input[name="txtDescricao"]').val();
        var fornecedor = $('#frmAddProduto').find('#slcFornecedorProduto').val();
        var preco = formatValor($('#frmAddProduto').find('input[name="txtPreco"]').val());
        var iva = $('#frmAddProduto').find('#slcIVA').val();
        var data = validaAddProd(id_categoria, id_subcat, id_familia, nome, fornecedor, preco, iva);
		if (data === true) {
            var dataString = "nome=" + nome + "&id_familia=" + id_familia + "&descricao=" + descricao + "&id_fornecedor=" + fornecedor + "&preco=" + preco + "&iva=" + iva + "&tipo=guardar_produto";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    $('#frmAddProduto').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmAddProduto').show();
                    if (dados.sucesso === true) {
                        $('#frmAddProduto').find('#slcCatAddProd').val(0);
                        emptySelect("#slcSubcatAddProd");
                        emptySelect("#slcFamAddProd");
                        $('#frmAddProduto').find('input[name="txtProduto"]').val('');
                        $('#frmAddProduto').find('input[name="txtDescricao"]').val('');
                        
                        $('#frmAddProduto').find('#slcPaisFornecedor option:contains(Portugal)').prop("selected", true);
                        $('#frmAddProduto').find('#slcFornecedorProduto').val(0);
                        // $('#frmAddProduto').find('#slcFornecedor option').eq(0).prop("selected", true);
                        $('#frmAddProduto').find('input[name="txtPreco"]').val('');
                        // $('#frmAddProduto').find('#slcIVA option').val(0);
                        $('#frmAddProduto').find('#slcIVA option').eq(0).prop("selected", true);
                        
                        $('#frmAddProduto').find('.chosenSelect').trigger("chosen:updated");
						
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

function fBtnGuardarSubcategoria(event) {
    if (event.handler !== true) {
        var id_categoria = $('#slcCatAddSubcat').val();
        var nome = $('input[name="txtSubcategoria"]').val();
        if (validaSubcategoria(id_categoria, nome) === true) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "nome=" + nome + "&id_cat=" + id_categoria + "&tipo=guardar_subcategoria",
                beforeSend: function() {
                    showLoading();
                    $('#frmSubcategoria').hide();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmSubcategoria').show();
                    if (dados.sucesso === true) {
                        emptySelect("#slcCatAddSubcat");
                        $.each(dados.dados_in, function() {
                            $('#slcCatAddSubcat').append($('<option></option>').val(this.id).text(this.desig));
                        });
                        $('#txtSubcategoria').val('');
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

function fBtnIDFactoring(event) {
    if (event.handler !== true) {
        var id_factoring = $(this).closest('td').children('input[name="hddIDFactoring"]').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_factoring=" + id_factoring + "&tipo=ver_fatura_ext",
            beforeSend: function() {
                showLoading();
                $('#tblDadosFatExt').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    $('#divFatExtDetail').find('input[name="txtDataFactoring"]').val(dados.data_fact);
                    $('#divFatExtDetail').find('input[name="txtValorFactoring"]').val(number_format(dados.valor_fact, 2, ',', '.'));
                    $('#divFatExtDetail').find('input[name="txtTempoFactoring"]').val(dados.tempo);
                    $('#divFatExtDetail').find('input[name="txtRecursoFactoring"]').val(dados.recurso);
                    $('#divFatExtDetail').find('input[name="txtComissaoFactoring"]').val(number_format(dados.comissao_valor, 2, ',', '.'));
                    $('#divFatExtDetail').find('input[name="txtSeguroFactoring"]').val(number_format(dados.seguro_valor, 2, ',', '.'));
                    $('#divFatExtDetail').find('input[name="txtJurosFactoring"]').val(number_format(dados.juros_valor, 2, ',', '.'));
                    $('#divFatExtDetail').find('input[name="txtVRecebidoFactoring"]').val(number_format(dados.valor_recebido, 2, ',', '.'));
                    $('#divFatExtDetail').closest('.linha').show();
                } else {
                    $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                    $("body").animate({
                        scrollTop: 0
                    });
                }
                $('#slcFiltrarFatExt').closest('.linha').hide();
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnIDFatInt(event) {
    if (event.handler !== true) {
        var id_fatura = $(this).closest('tr').find('#hddIdFatura').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_fatura=" + id_fatura + "&tipo=ver_fatura_int",
            beforeSend: function() {
                showLoading();
                $('#tblDadosFatInt').hide();
            },
            success: function(dados) {
                hideLoading();
                $('#slcFiltrarFatInt').closest('.linha').hide();
                if (dados.sucesso === true) {
                    $('#divFatIntDetail').find('input[name="txtRef"]').val(dados.ref);
                    $('#divFatIntDetail').find('input[name="txtCliente"]').val(dados.nome);
                    $('#divFatIntDetail').find('input[name="txtData"]').val(dados.data);
                    $('#divFatIntDetail').find('input[name="txtDesconto"]').val(number_format(dados.desconto, 2, ',', '.'));
                    $('#divFatIntDetail').find('input[name="txtIva"]').val(number_format(dados.iva, 2, ',', '.'));
                    $('#divFatIntDetail').find('input[name="txtTotal"]').val(number_format(dados.total, 2, ',', '.'));
                    $('#divFatIntDetail').find('input[name="txtPago"]').val(dados.pago);
                    $('#divFatIntDetail').closest('.linha').show();
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

function fBtnRemFatInt(event) {
    if (event.handler !== true) {
        var dados = {};
        $.each($('#tblDadosFatInt').find('input[type=checkbox].chk'), function(key, value) {
            if (key != "0") {
                if ($(this).prop('checked') === true) {
                    dados[key] = {};
                    dados[key].id = $(this).closest('tr').find('input[name="chkFatura"]').val();
                }
            }
        });
        if (Object.size(dados) > 0) {
            var id_empresa = $('#slcFiltrarFatInt').val();
            var id_filtro = $('#slcOrdenarFatInt').val();
            var dataString = "dados=" + JSON.stringify(dados) + "&id_filtro=" + id_filtro + "&id_empresa=" + id_empresa + "&tipo=rem_fatura_int";
            if (confirm('Deseja mesmo remover as linhas selecionadas?')) {
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function() {
                        showLoading();
                        $('#tblDadosFatInt').hide();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                $('#tblFatVazia').hide();
                                emptyTable('#tblDadosFatInt');
                                $.each(dados.dados_in, function() {
                                    var html = '<tr>';
                                    if (this.pago == "0") {
                                        html += '<td class="transparent">' +
                                            '<div class="checkbox">' +
                                            '<input id="chkFatura_' + this.id + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id + '">' +
                                            '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                            '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '">' +
                                            '</div>' +
                                            '</td>';
                                    } else {
                                        html += '<td class="transparent"></td>';
                                    }
                                    html += '<td>' + this.ref + '</td>' +
                                        '<td>' + this.nome + '</td>' +
                                        '<td>' + this.data + '</td>' +
                                        '<td>' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                        '<td class="iconwrapper">' +
                                        '<div id="btnIDFatInt_' + this.id + '" name="btnIDFatInt" class="novolabelicon icon-info"></div>' +
                                        '</td>' +
                                        '</tr>';
                                    $('#tblDadosFatInt').append(html);
                                });
                                $('#tblDadosFatInt').append('<input id="hddFatInt" name="hddFatInt" type="hidden" value="1">');
                                emptySelect("#slcFiltrarFatInt");
                                $.each(dados.dados_fi, function() {
                                    if (this.id == id_empresa) {
                                        $('#slcFiltrarFatInt').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                                    } else {
                                        $('#slcFiltrarFatInt').append($('<option></option>').val(this.id).text(this.nome));
                                    }
                                });
                                $('#btnRemFatInt').closest('.linha').show();
                                $('#tblDadosFatInt').show();
                            } else {
                                emptyTable('#tblDadosFatInt');
                                $('#tblDadosFatInt').hide();
                                $('#btnRemFatInt').closest('.linha').hide();
                                $('#tblFatVazia').show();
                            }
                            $('#btnRemFatInt').hide();
                            $('#chkAllInt').prop('checked', false);
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

function fBtnRemFatExt(event) {
    if (event.handler !== true) {
        var dados = {};
        $.each($('#tblDadosFatExt').find('input[type=checkbox].chk'), function(key, value) {
            if (key != "0") {
                if ($(this).prop('checked') === true) {
                    dados[key] = {};
                    dados[key].id_fatura = $(this).closest('tr').find('input[name="chkFatura"]').val();
                }
            }
        });
        if (Object.size(dados) > 0) {
            var id_empresa = $('#slcFiltrarFatExt').val();
            var id_filtro = $('#slcOrdenarFatExt').val();
            var dataString = "dados=" + JSON.stringify(dados) + "&id_filtro=" + id_filtro + "&id_empresa=" + id_empresa + "&tipo=rem_fatura_ext";
            if (confirm('Deseja mesmo remover as linhas selecionadas?')) {
                $.ajax({
                    type: "POST",
                    url: "functions/funcoes_admin.php",
                    dataType: "json",
                    data: dataString,
                    beforeSend: function() {
                        showLoading();
                        $('#tblDadosFatExt').hide();
                    },
                    success: function(dados) {
                        hideLoading();
                        if (dados.sucesso === true) {
                            if (dados.vazio === false) {
                                $('#tblFatVazia').hide();
                                emptyTable('#tblDadosFatExt');
                                $.each(dados.dados_in, function() {
                                    var html = '<tr>';
                                    if (this.id_factoring === null) {
                                        html += '<td class="transparent">' +
                                            '<div class="checkbox">' +
                                            '<input id="chkFatura_' + this.id_fatura + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id_fatura + '">' +
                                            '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                            '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id_fatura + '">' +
                                            '</div>' +
                                            '</td>';
                                    } else {
                                        html += '<td class="transparent"></td>';
                                    }
                                    html += '<td>' + this.num_fatura + '</td>' +
                                        '<td>' + this.cliente + '</td>' +
                                        '<td>' + number_format(this.valor_fatura, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                        '<td>' + this.data_fatura + '</td>' +
                                        '<td>' + this.nome + '</td>';
                                    if (this.id_factoring !== null) {
                                        html += '<td class="iconwrapper">' +
                                            '<input name="hddIDFactoring" type="hidden" value="' + this.id_factoring + '">' +
                                            '<div name="btnIDFactoring_' + this.id_factoring + '" class="novolabelicon icon-info"></div>' +
                                            '</td>';
                                    }
                                    html += '</tr>';
                                    $('#tblDadosFatExt').append(html);
                                });
                                $('#tblDadosFatExt').append('<input id="hddFatExt" name="hddFatExt" type="hidden" value="1">');
                                emptySelect("#slcFiltrarFatExt");
                                $.each(dados.dados_fi, function() {
                                    if (this.id == id_empresa) {
                                        $('#slcFiltrarFatExt').append($('<option selected="selected"></option>').val(this.id).text(this.nome));
                                    } else {
                                        $('#slcFiltrarFatExt').append($('<option></option>').val(this.id).text(this.nome));
                                    }
                                });
                                $('#btnRemFatExt').closest('.linha').show();
                                $('#tblDadosFatExt').show();
                            } else {
                                emptyTable('#tblDadosFatExt');
                                $('#tblDadosFatExt').hide();
                                $('#btnRemFatExt').closest('.linha').hide();
                                $('#tblFatVazia').show();
                            }
                            $('#btnRemFatExt').hide();
                            $('#chkAllExt').prop('checked', false);
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

function fBtnVoltarFatInt(event) {
    if (event.handler !== true) {
        $('#divFatIntDetail').closest('.linha').hide();
        $('#slcFiltrarFatInt').closest('.linha').show();
        $('#tblDadosFatInt').show();
        event.handler = true;
    }
    return false;
}

function fBtnVoltarFatExt(event) {
    if (event.handler !== true) {
        $('#divFatExtDetail').closest('.linha').hide();
        $('#slcFiltrarFatExt').closest('.linha').show();
        $('#tblDadosFatExt').show();
        event.handler = true;
    }
    return false;
}

function fChkAllInt(event) {
    if (event.handler !== true) {
        if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
            $('#tblDadosFatInt').find('input[type=checkbox].chk').prop('checked', false);
            $('#btnRemFatInt').hide();
        } else {
            $('#tblDadosFatInt').find('input[type=checkbox].chk').prop('checked', true);
        }
        event.handler = true;
    }
    return false;
}

function fChkAllExt(event) {
    if (event.handler !== true) {
        if ($(this).closest('.checkbox').find('input').prop('checked') === true) {
            $('#tblDadosFatExt').find('input[type=checkbox].chk').prop('checked', false);
            $('#btnRemFatExt').hide();
        } else {
            $('#tblDadosFatExt').find('input[type=checkbox].chk').prop('checked', true);
        }
        event.handler = true;
    }
    return false;
}

function fSlcAtividade(event) {
    if (event.handler !== true) {
        var id_atividade = $(this).val();
        if (id_atividade == "0") {
            $.each($('#frmAtiCat').find('.chk'), function() {
                $(this).prop('checked', false);
            });
        } else {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_atividade=" + id_atividade + "&tipo=afet_ati_cat",
                beforeSend: function() {
                    $('#frmAtiCat').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            $.each($('#frmAtiCat').find('.chk'), function() {
                                $(this).prop('checked', false);
                            });
                            $.each(dados.dados_in, function(i, item) {
                                $.each($('#frmAtiCat').find('.chk'), function() {
                                    if ($(this).val() == dados.dados_in[i].id) {
                                        $(this).prop('checked', true);
                                    }
                                });
                            });
                            $('#frmAtiCat').show();
                        } else {
                            $('#frmAtiCat').show();
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
        event.handler = true;
    }
    return false;
}

function fSlcCatAddFam(event) {
    if (event.handler !== true) {
        var id_categoria = $(this).val();
        if (id_categoria > 0) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_categ=" + id_categoria + "&tipo=ver_categorias",
                beforeSend: function() {
                    showLoading();
                    $('#frmFamilia').hide();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmFamilia').show();
                    emptySelect("#slcSubcatAddFam");
                    if (dados.sucesso === true) {
                        $.each(dados.dados_in, function() {
                            $('#slcSubcatAddFam').append($('<option></option>').val(this.id).text(this.desig));
                        });
                    } else {
                        $('#slcSubcatAddFam').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                    }
                    $('#slcSubcatAddFam').trigger("chosen:updated");
                }
            });
        } else {
            emptySelect("#slcSubcatAddFam");
        }
        event.handler = true;
    }
    return false;
}

function fSlcCatAddProd(event) {
    if (event.handler !== true) {
        var id_categoria = $(this).val();
        emptySelect("#slcSubcatAddProd");
        emptySelect("#slcFamAddProd");
        if (id_categoria > 0) {
            var dataString = "id_categ=" + id_categoria + "&tipo=ver_categorias";
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: dataString,
                beforeSend: function() {
                    showLoading();
                    $('#frmAddProduto').hide();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmAddProduto').show();
                    if (dados.sucesso === true) {
                        $.each(dados.dados_in, function() {
                            $('#slcSubcatAddProd').append($('<option></option>').val(this.id).text(this.desig));
                        });
                    } else {
                        $('#slcSubcatAddProd').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                    }
                    $('#slcSubcatAddProd').trigger("chosen:updated");
                }
            });
        }
        event.handler = true;
    }
    return false;
}

function fSlcCatAfetCat(event) {
    if (event.handler !== true) {
        var id_categoria = $(this).val();
        if (id_categoria == "0") {
            $.each($('#frmCatEmp').find('.chk'), function() {
                $(this).prop('checked', false);
            });
        } else {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_categoria=" + id_categoria + "&tipo=afet_cat_emp",
                beforeSend: function() {
                    $('#frmCatEmp').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            $.each(dados.dados_in, function(i, item) {
                                $.each($('#frmCatEmp').find('.chk'), function() {
                                    if ($(this).val() == dados.dados_in[i].id) {
                                        $(this).prop('checked', true);
                                    }
                                });
                            });
                            $('#frmCatEmp').show();
                        } else {
                            $('#frmCatEmp').show();
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
        event.handler = true;
    }
    return false;
}

function fSlcCatAti(event) {
    if (event.handler !== true) {
        var id_categoria = $(this).val();
        if (id_categoria == "0") {
            $.each($('#frmCatAti').find('.chk'), function() {
                $(this).prop('checked', false);
            });
        } else {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_categoria=" + id_categoria + "&tipo=afet_cat_ati",
                beforeSend: function() {
                    $('#frmCatAti').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            $.each($('#frmCatAti').find('.chk'), function() {
                                $(this).prop('checked', false);
                            });
                            $.each(dados.dados_in, function(i, item) {
                                $.each($('#frmCatAti').find('.chk'), function() {
                                    if ($(this).val() == dados.dados_in[i].id) {
                                        $(this).prop('checked', true);
                                    }
                                });
                            });
                            $('#frmCatAti').show();
                        } else {
                            $('#frmCatAti').show();
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
        event.handler = true;
    }
    return false;
}

function fSlcCatEditCat(event) {
    if (event.handler !== true) {
        var id_categoria = $(this).val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_categ=" + id_categoria + "&tipo=ver_cat_edit",
            beforeSend: function() {
                showLoading();
                $('#divEditCat').hide();
            },
            success: function(dados) {
                hideLoading();
                emptySelect("#slcSubcatEditCat");
                if (dados.sucesso === true) {
                    emptyTable('#tblCategorias');
                    $.each(dados.dados_in, function(i, item) {
                        if (id_categoria > 0) {
                            $('#slcSubcatEditCat').append($('<option></option>').val(this.id).text(this.desig));
                        }
                        $('#tblCategorias').append('<tr>' +
                            '<td>' +
                            '<div class="inputareaTable">' +
                            '<input id="txtNomeDesignacao' + i + '" name="txtNomeDesignacao" type="text" class="editableText" readonly="readonly" value="' + this.desig + '">' +
                            '<input id="hddIdCat" name="hddIdCat" type="hidden" value="' + this.id + '">' +
                            '</div>' +
                            '</td>' +
                            '</tr>');
                    });
                } else {
                    $('#slcSubcatEditCat').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                }
                $('#divEditCat').show();
                $('#slcSubcatEditCat').trigger("chosen:updated");
            }
        });
        event.handler = true;
    }
    return false;
}

//-- Versão em desenvolvimento
function fSlcCategoria(event) {
    if (event.handler !== true) {
        var id = $(this).val();
        var dataString = 'id=' + id + '&nivel=0' + '&tipo=cat_prod';
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            beforeSend: function() {
                $('#tblProdutosAlt').hide();
                showLoading();
            },
			success: function(dados) {
				hideLoading();
                if (dados.sucesso === true) {
                    $('#slcFornecedor').val(0);
                    var opcoes = "";
                    $.each(dados.dados_taxa, function(i, item) {
                        opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                    });
                    if (dados.dados_in.vazio === true) {
                        $('#btnGuardarDadosProdutos').hide();
                    } else {
                        $('#btnGuardarDadosProdutos').show();
                        emptyTable('#tblProdutosAlt');
                        $.each(dados.dados_in, function() {
                            $('#tblProdutosAlt').append('<tr class="tbody">' +
                                '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                                '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                                '<td style="padding: 2px;">' + this.descricao + '</td>' +
                                '<td style="padding: 2px;">' +
                                '<div class="inputareaTable">' +
                                '<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
                                '<div class="mnyLabel right">' +
                                '<span>' + dados.moeda + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
                                '<td style="padding: 2px;">' +
                                '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                                '<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
                                opcoes +
                                '</select>' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblProdutosAlt tr').each(function(key, value) {
                            if (key > 0) {
                                $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                            }
                        });
                        dynamicInput('tblProdutosAlt');
                        floatMask();
                        $('#tblProdutosAlt').show();
                        event.data.qs.cache();
                    }
                    emptySelect("#slcSubcategoria");
                    emptySelect("#slcFamilia");
                    $.each(dados.dados_cat, function(i, item) {
                        if (dados.dados_cat[i].cat_vazia === true) {
                            if (id != "0") {
                                $('#slcSubcategoria').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                            }
                        } else {
                            $('#slcSubcategoria').append($('<option></option>').val(this.id).text(this.desig));
                        }
                    });
                    $('.chosenTabelaSelect').chosen('destroy');
                    $('.chosenTabelaSelect').chosen({
                        no_results_text: 'Sem resultados!'
                    });
                    $('#slcSubcategoria').trigger("chosen:updated");
                    $('#slcFornecedor').trigger("chosen:updated");
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

/* Versão atualmente online
function fSlcCategoria(event) {
    if (event.handler !== true) {
        var id = $(this).val();
        var dataString = 'id=' + id + '&nivel=0' + '&tipo=cat_prod';
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            success: function(dados) {
                if (dados.sucesso === true) {
                    $('#slcFornecedor').val(0);
                    var opcoes = "";
                    $.each(dados.dados_taxa, function(i, item) {
                        opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                    });
                    if (dados.dados_in.vazio === true) {
                        $('#btnGuardarDadosProdutos').hide();
                    } else {
                        $('#btnGuardarDadosProdutos').show();
                        emptyTable('#tblProdutosAlt');
                        $.each(dados.dados_in, function() {
                            $('#tblProdutosAlt').append('<tr class="tbody">' +
                                '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                                '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                                '<td style="padding: 2px;">' + this.descricao + '</td>' +
                                '<td style="padding: 2px;">' +
                                '<div class="inputareaTable">' +
                                '<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
                                '<div class="mnyLabel right">' +
                                '<span>' + dados.moeda + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
                                '<td style="padding: 2px;">' +
                                '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                                '<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
                                opcoes +
                                '</select>' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblProdutosAlt tr').each(function(key, value) {
                            if (key > 0) {
                                $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                            }
                        });
                        dynamicInput('tblProdutosAlt');
                        floatMask();
                        $('#tblProdutosAlt').show();
                        event.data.qs.cache();
                    }
                    emptySelect("#slcSubcategoria");
                    emptySelect("#slcFamilia");
                    $.each(dados.dados_cat, function(i, item) {
                        if (dados.dados_cat[i].cat_vazia === true) {
                            if (id != "0") {
                                $('#slcSubcategoria').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                            }
                        } else {
                            $('#slcSubcategoria').append($('<option></option>').val(this.id).text(this.desig));
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
*/

function fSlcEmpresa(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).val();
        if (id_empresa == "0") {
            $.each($('#frmEmpCat').find('.chk'), function() {
                $(this).prop('checked', false);
            });
        } else {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_empresa=" + id_empresa + "&tipo=afet_emp_cat",
                beforeSend: function() {
                    $('#frmEmpCat').hide();
                    showLoading();
                },
                success: function(dados) {
                    hideLoading();
                    if (dados.sucesso === true) {
                        if (dados.vazio === false) {
                            $.each(dados.dados_in, function(i, item) {
                                $.each($('#frmEmpCat').find('.chk'), function() {
                                    if ($(this).val() == dados.dados_in[i].id) {
                                        $(this).prop('checked', true);
                                    }
                                });
                            });
                            $('#frmEmpCat').show();
                        } else {
                            $('#frmEmpCat').show();
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
        event.handler = true;
    }
    return false;
}

function fSlcFamilia(event) {
    if (event.handler !== true) {
        var subcat = $('#slcSubcategoria').val();
        var id = $(this).val();
        var dataString = 'id=' + id + '&cat=' + subcat + '&nivel=2' + '&tipo=cat_prod';
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            beforeSend: function() {
                $('#tblProdutosAlt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.dados_in.vazio === true) {
                        $('#btnGuardarDadosProdutos').hide();
                        $('#tblProdutosAltVazio').show();
                    } else {
                        $('#btnGuardarDadosProdutos').show();
                        emptyTable('#tblProdutosAlt');
                        var opcoes = "";
                        $.each(dados.dados_taxa, function(i, item) {
                            opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                        });
                        $.each(dados.dados_in, function() {
                            $('#tblProdutosAlt').append('<tr class="tbody">' +
                                '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                                '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                                '<td style="padding: 2px;">' + this.descricao + '</td>' +
                                '<td style="padding: 2px;">' +
                                '<div class="inputareaTable">' +
                                '<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
                                '<div class="mnyLabel right">' +
                                '<span>' + dados.moeda + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
                                '<td style="padding: 2px;">' +
                                '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                                '<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
                                opcoes +
                                '</select>' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblProdutosAlt tr').each(function(key, value) {
                            if (key > 0) {
                                $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                            }
                        });
                        dynamicInput('tblProdutosAlt');
                        floatMask();
                        $('#tblProdutosAlt').show();
                        event.data.qs.cache();
                    }
                    $('.chosenTabelaSelect').chosen('destroy');
                    $('.chosenTabelaSelect').chosen({
                        no_results_text: 'Sem resultados!'
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

//
function fSlcFiltrarFatGrupo(event) {
    if (event.handler !== true) {
        var id_grupo = $(this).val();
        var id_empresa = $('#slcFiltrarFatInt').val();
        //if (id_grupo == 0) id_empresa = 0;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_grupo=" + id_grupo + "&id_empresa=" + id_empresa + "&tipo=fat_int_esp",
            beforeSend: function() {
                $('#btnRemFatInt').hide();
                $('#tblDadosFatInt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    emptyTable('#tblDadosFatInt');
                    if (dados.vazio === false) {
                        $('#tblFatVazia').hide();
                        $.each(dados.dados_in, function() {
                            var html = '<tr>';
                            if (dados.admin == "0") {
                                if (this.pago == "0") {
                                    html += '<td class="transparent">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkFatura_' + this.id + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id + '">' +
                                        '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '">' +
                                        '</div>' +
                                        '</td>';
                                } else {
                                    html += '<td class="transparent"></td>';
                                }
                            }
                            html += '<td>' + this.ref + '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '"></td>' +
                                '<td>' + this.nome + '</td>' +
                                '<td>' + this.data + '</td>' +
                                '<td>' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td class="iconwrapper">' +
                                '<div id="btnIDFatInt_' + this.id + '" name="btnIDFatInt" class="novolabelicon icon-info"></div>' +
                                '</td>' +
                                '</tr>';
                            $('#tblDadosFatInt').append(html);
                        });
                        $('#tblDadosFatInt').append('<input id="hddFatInt" name="hddFatInt" type="hidden" value="1">');
                        $('#btnRemFatInt').closest('.linha').show();
                        $('#chkAllInt').prop('checked', false);
                        $('#tblDadosFatInt').show();
                        //
                        if (id_grupo != 0) {
                            $('#slcFiltrarFatInt').empty();
                            $('#slcFiltrarFatInt').append('<option selected="selected" value="0"></option>');
                            $.each(dados.empresas, function() {
                                $('#slcFiltrarFatInt').append('<option value="' + this.id_empresa + '">' + this.nome_empresa + '</option>');
                            });
                            //$('#slcFiltrarFatInt').val('');
                            $('#slcFiltrarFatInt').trigger('chosen:updated');
                        }
                        //
                    } else {
                        $('#tblDadosFatInt').hide();
                        $('#tblFatVazia').show();
                        $('#slcFiltrarFatInt').empty();
                        $('#slcFiltrarFatInt').val('');
                        $('#slcFiltrarFatInt').trigger('chosen:updated');
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
//

function fSlcFiltrarFatInt(event) {
    if (event.handler !== true) {
        var id_grupo = $('#slcFiltrarFatGrupo').val();
        var id_empresa = $(this).val();
        var dataString = "id_grupo=" + id_grupo + "&id_empresa=" + id_empresa + "&tipo=fat_int_esp";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#btnRemFatInt').hide();
                $('#tblDadosFatInt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    emptyTable('#tblDadosFatInt');
                    if (dados.vazio === false) {
                        $('#tblFatVazia').hide();
                        $.each(dados.dados_in, function() {
                            var html = '<tr>';
                            if (dados.admin == "0") {
                                if (this.pago == "0") {
                                    html += '<td class="transparent">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkFatura_' + this.id + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id + '">' +
                                        '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '">' +
                                        '</div>' +
                                        '</td>';
                                } else {
                                    html += '<td class="transparent"></td>';
                                }
                            }
                            html += '<td>' + this.ref + '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '"></td>' +
                                '<td>' + this.nome + '</td>' +
                                '<td>' + this.data + '</td>' +
                                '<td>' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td class="iconwrapper">' +
                                '<div id="btnIDFatInt_' + this.id + '" name="btnIDFatInt" class="novolabelicon icon-info"></div>' +
                                '</td>' +
                                '</tr>';
                            $('#tblDadosFatInt').append(html);
                        });
                        $('#tblDadosFatInt').append('<input id="hddFatInt" name="hddFatInt" type="hidden" value="1">');
                        $('#btnRemFatInt').closest('.linha').show();
                        $('#chkAllInt').prop('checked', false);
                        $('#tblDadosFatInt').show();
                        /*
                        if (id_empresa != 0) {
                            $('#slcFiltrarFatGrupo').val(dados.grupos.id_grupo);
                            $('#slcFiltrarFatGrupo').trigger('chosen:updated');
                        }
                        */
                    } else {
                        $('#tblDadosFatInt').hide();
                        $('#tblFatVazia').show();
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

function fSlcFiltrarFatExt(event) {
    if (event.handler !== true) {
        var id_empresa = $(this).val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_empresa=" + id_empresa + "&tipo=fat_ext_esp",
            beforeSend: function() {
                $('#btnRemFatExt').hide();
                $('#tblDadosFatExt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    emptyTable('#tblDadosFatExt');
                    $.each(dados.dados_in, function() {
                        var html = '<tr>';
                        if (dados.admin == "0") {
                            if (this.id_factoring === null) {
                                html += '<td class="transparent">' +
                                    '<div class="checkbox">' +
                                    '<input id="chkFatura_' + this.id_fatura + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id_fatura + '">' +
                                    '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                    '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id_fatura + '">' +
                                    '</div>' +
                                    '</td>';
                            } else {
                                html += '<td class="transparent"></td>';
                            }
                        }
                        html += '<td>' + this.num_fatura + '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id_fatura + '"></td>' +
                            '<td>' + this.cliente + '</td>' +
                            '<td>' + number_format(this.valor_fatura, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                            '<td>' + this.data_fatura + '</td>' +
                            '<td>' + this.nome + '</td>';
                        if (this.id_factoring !== null) {
                            html += '<td class="iconwrapper">' +
                                '<input id="hddIDFactoring" name="hddIDFactoring" type="hidden" value="' + this.id_factoring + '">' +
                                '<div id="btnIDFactoring_' + this.id_factoring + '" name="btnIDFactoring" class="novolabelicon icon-info"></div>' +
                                '</td>';
                        }
                        html += '</tr>';
                        $('#tblDadosFatExt').append(html);
                    });
                    $('#tblDadosFatExt').append('<input id="hddFatExt" name="hddFatExt" type="hidden" value="1">');
                    $('#chkAllExt').prop('checked', false);
                    $('#tblDadosFatExt').show();
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

function fSlcFornecedor(event) {
    if (event.handler !== true) {
        var id_fornecedor = $(this).val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "limite=" + $('#slcPag').val() + "&id_fornecedor=" + id_fornecedor + "&tipo=filtrar_produtos",
            beforeSend: function() {
                $('#tblProdutosAlt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    $('#slcCategoria').val(0);
                    emptySelect("#slcSubcategoria");
                    emptySelect("#slcFamilia");
                    emptyTable('#tblProdutosAlt');
                    var opcoes = "";
                    $.each(dados.dados_taxa, function(i, item) {
                        opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                    });
                    $.each(dados.dados_in, function() {
                        if ($("#slcFornecedor").val() != 0) {
                            $('#tblProdutosAlt').append('<tr class="tbody">' +
                            //'<td style="padding: 2px;">' + this.nome_abrev + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                            '<td style="padding: 2px;">' +
                            '<div class="inputareaTable">' + 
                            '<input type="text" id="txtNomeFornecedor" name="txtNomeFornecedor" class="editableText" style="text-align: center" readonly="readonly" value="' + this.nome_abrev + '">' + 
                            '</div>' + 
                            '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '">' + 
                            '</td>' +
                    
                            '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                            '<td style="padding: 2px;">' + this.descricao + '</td>' +
                            '<td style="padding: 2px;">' + number_format(this.preco, 2, ",", ".") + ' ' + dados.moeda + '</td>' +
                            '<td style="padding: 2px;">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '"></td>' +
                            '</tr>');
                        }
                        else {
                            $('#tblProdutosAlt').append('<tr class="tbody">' +
                            '<td style="padding: 2px;">' + this.nome_abrev + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                            '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                            '<td style="padding: 2px;">' + this.descricao + '</td>' +
                            /*
							'<td style="padding: 2px;">' +
                            '<input type="text" name="txtPrecoProd" class="editableText acoes dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' + dados.moeda +
                            '</td>' +
							*/
							//-- PREÇO
							'<td style="padding: 2px;">' +
							'<div class="inputareaTable">' +
							'<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
							'<div class="mnyLabel right">' +
							'<span>' + dados.moeda + '</span>' +
							'</div>' +
							'</div>' +
							'</td>' +
							//--
                            
							/* REGRAS
							'<td style="padding: 2px;">' +
                            '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                            // '<div class="inputarea_col1" style="margin: 0 auto; height: 21px; width: 38%; float: none; background-color: transparent;">' +
							'<div class="inputarea_col1" style="margin: 0 auto; height: 21px; width: 50%; float: none; background-color: transparent;">' +
                            '<div class="styled-select" style="height: 21px;">' +
                            // '<select id="slcTaxa" name="slcTaxa" size="1" class="select" style="padding: 0 0 0 5%; border: 1px solid #236688; color: #000;">' +
							'<select id="slcTaxa" name="slcTaxa" size="1" class="chosenTabelaSelect" style="padding: 0 0 0 5%; border: 1px solid #236688; color: #000;">' +
                            opcoes +
                            '</select>' +
                            '</div>' +
                            '</div>' +
							'</td>' +
							*/
							'<td style="padding: 2px;">' +
							'<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
							'<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
							opcoes +
							'</select>' +
							'</td>' +
                            '</tr>');
                        }
                    });
                    $('#tblProdutosAlt tr').each(function(key, value) {
                        if (key > 0) {
                            $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                        }
                    });
                    dynamicInput('tblProdutosAlt');
                    floatMask();
                    $('#tblProdutosAlt').show();
                    event.data.qs.cache();
                    $('.chosenTabelaSelect').chosen('destroy');
                    $('.chosenTabelaSelect').chosen({
                        no_results_text: 'Sem resultados!'
                    });
                    $('#slcCategoria').trigger("chosen:updated");
                    $('#slcSubcategoria').trigger("chosen:updated");
                    $('#slcFamilia').trigger("chosen:updated");
                } else {
                    $('.error').show().html('<span id="error">Algo correu mal</span>');
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

//
$(document).on('keyup', '#tblProdutosAlt input[name="txtNomeFornecedor"]', function(event) {
    if (event.handler !== true) {
        var td = $(this).val();
        $('#tblProdutosAlt input[name="txtNomeFornecedor"]').each(function(){
           $(this).val(td);
        });
    }
    return false;
});
//

function fSlcOrdenarFatInt(event) {
    if (event.handler !== true) {
        var dataString = "id_filtro=" + $(this).val() + "&tipo=ordenar_fat_int";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#tblDadosFatInt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#btnRemFatInt').hide();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        $('.chosenSelect').chosen('destroy');
                        $('.chosenSelect').chosen({
                            allow_single_deselect: true,
                            no_results_text: 'Sem resultados!'
                        });
                        emptyTable('#tblDadosFatInt');
                        $.each(dados.dados_in, function() {
                            var html = '<tr>';
                            if (dados.admin == "0") {
                                if (this.pago == "0") {
                                    html += '<td class="transparent">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkFatura_' + this.id + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id + '">' +
                                        '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '">' +
                                        '</div>' +
                                        '</td>';
                                } else {
                                    html += '<td class="transparent"></td>';
                                }
                            }
                            html += '<td>' + this.ref + '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id + '"></td>' +
                                '<td>' + this.nome + '</td>' +
                                '<td>' + this.data + '</td>' +
                                '<td>' + number_format(this.total, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td class="iconwrapper">' +
                                '<div id="btnIDFatInt_' + this.id + '" name="btnIDFatInt" class="novolabelicon icon-info"></div>' +
                                '</td>' +
                                '</tr>';
                            $('#tblDadosFatInt').append(html);
                        });
                        $('#tblDadosFatInt').append('<input id="hddFatInt" name="hddFatInt" type="hidden" value="1">');
                        emptySelect("#slcFamilia");
                        emptySelect("#slcFiltrarFatInt");
                        $.each(dados.dados_fi, function() {
                            $('#slcFiltrarFatInt').append($('<option></option>').val(this.id).text(this.nome));
                        });
                        $('#slcFiltrarFatInt').val(0);
                        $('#chkAllInt').prop('checked', false);
                        $('#slcFiltrarFatInt').trigger("chosen:updated");
                        $('#btnRemFatInt').closest('.linha').show();
                        $('#tblDadosFatInt').show();
                    } else {
                        emptyTable('#tblDadosFatInt');
                        $('#tblDadosFatInt').hide();
                        $('#slcFiltrarFatInt').val(0);
                        $('#btnRemFatInt').closest('.linha').hide();
                        $('#tblFatVazia').show();
                    }
                }
            }
        });
        if ($(this).val() == "2") {
            $('#hideSlcFiltrarFatGrupo').show();
            $('#hideSlcFiltrarFatInt').show();
        } else {
            $('#hideSlcFiltrarFatGrupo').hide();
            $('#hideSlcFiltrarFatInt').hide();
        }
        event.handler = true;
    }
    return false;
}

function fSlcOrdenarFatExt(event) {
    if (event.handler !== true) {
        var dataString = "id_filtro=" + $(this).val() + "&tipo=ordenar_fat_ext";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: dataString,
            beforeSend: function() {
                $('#tblDadosFatExt').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#btnRemFatExt').hide();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        $('.chosenSelect').chosen('destroy');
                        $('.chosenSelect').chosen({
                            allow_single_deselect: true,
                            no_results_text: 'Sem resultados!'
                        });
                        $('#tblFatVazia').hide();
                        emptyTable('#tblDadosFatExt');
                        $.each(dados.dados_in, function() {
                            var html = '<tr>';
                            if (dados.admin == "0") {
                                if (this.id_factoring === null) {
                                    html += '<td class="transparent">' +
                                        '<div class="checkbox">' +
                                        '<input id="chkFatura_' + this.id_fatura + '" name="chkFatura" type="checkbox" class="chk" value="' + this.id_fatura + '">' +
                                        '<label for="chkFatura" class="label_chk" style="padding-left: 0;">&nbsp;</label>' +
                                        '<input id="hddIdFatura" name="hddIdFatura" type="hidden" value="' + this.id_fatura + '">' +
                                        '</div>' +
                                        '</td>';
                                } else {
                                    html += '<td class="transparent"></td>';
                                }
                            }
                            html += '<td>' + this.num_fatura + '</td>' +
                                '<td>' + this.cliente + '</td>' +
                                '<td>' + number_format(this.valor_fatura, 2, ',', '.') + ' ' + dados.moeda + '</td>' +
                                '<td>' + this.data_fatura + '</td>' +
                                '<td>' + this.nome + '</td>';
                            if (this.id_factoring !== null) {
                                html += '<td class="iconwrapper">' +
                                    '<input id="hddIDFactoring" name="hddIDFactoring" type="hidden" value="' + this.id_factoring + '">' +
                                    '<div id="btnIDFactoring_' + this.id_factoring + '" name="btnIDFactoring" class="novolabelicon icon-info"></div>' +
                                    '</td>';
                            }
                            html += '</tr>';
                            $('#tblDadosFatExt').append(html);
                        });
                        $('#tblDadosFatExt').append('<input id="hddFatExt" name="hddFatExt" type="hidden" value="1">');
                        emptySelect("#slcFiltrarFatExt");
                        $.each(dados.dados_fi, function() {
                            $('#slcFiltrarFatExt').append($('<option></option>').val(this.id).text(this.nome));
                        });
                        $('#slcFiltrarFatExt').val(0);
                        $('#chkAllExt').prop('checked', false);
                        $('#slcFiltrarFatExt').trigger("chosen:updated");
                        $('#btnRemFatExt').closest('.linha').show();
                        $('#tblDadosFatExt').show();
                    } else {
                        emptyTable('#tblDadosFatExt');
                        $('#tblDadosFatExt').hide();
                        $('#slcFiltrarFatExt').val(0);
                        $('#btnRemFatExt').closest('.linha').hide();
                        $('#tblFatVazia').show();
                    }
                }
            }
        });
        if ($(this).val() == "2") {
            $('#hideSlcFiltrarFatExt').show();
        } else {
            $('#hideSlcFiltrarFatExt').hide();
        }
        event.handler = true;
    }
    return false;
}

function fSlcSubcatAddProd(event) {
    if (event.handler !== true) {
        var id_subcategoria = $(this).val();
        if (id_subcategoria > 0) {
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "id_subcateg=" + id_subcategoria + "&tipo=ver_subcategorias",
                beforeSend: function() {
                    showLoading();
                    $('#frmAddProduto').hide();
                },
                success: function(dados) {
                    hideLoading();
                    $('#frmAddProduto').show();
                    var familia = $('#slcFamAddProd');
                    emptySelect("#slcFamAddProd");
                    if (dados.sucesso === true) {
                        $.each(dados.dados_in, function() {
                            familia.append($('<option></option>').val(this.id).text(this.desig));
                        });
                    } else {
                        familia.append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                    }
                    familia.trigger("chosen:updated");
                }
            });
        } else {
            emptySelect("#slcFamAddProd");
        }
        event.handler = true;
    }
    return false;
}

function fSlcSubcatEditCat(event) {
    if (event.handler !== true) {
        var id_subcategoria = $(this).val();
        var id_categoria = $('#slcCatEditCat').val();
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "id_subcateg=" + id_subcategoria + "&id_categoria=" + id_categoria + "&tipo=ver_subcat_edit",
            beforeSend: function() {
                showLoading();
                $('#divEditCat').hide();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    emptyTable('#tblCategorias');
                    $.each(dados.dados_in, function(i, item) {
                        $('#tblCategorias').append('<tr>' +
                            '<td>' +
                            '<div class="inputareaTable">' +
                            '<input id="txtNomeDesignacao' + i + '" name="txtNomeDesignacao" type="text" class="editableText" readonly="readonly" value="' + this.desig + '">' +
                            '<input id="hddIdCat" name="hddIdCat" type="hidden" value="' + this.id + '">' +
                            '</div>' +
                            '</td>' +
                            '</tr>');
                    });
                }
                $('#divEditCat').show();
            }
        });
        event.handler = true;
    }
    return false;
}

/* Versão em desenvolvimento
 * function fSlcSubcategoria(event) {
    if (event.handler !== true) {
        var cat = $('#slcCategoria').val();
        var id = $(this).val();
        var dataString = 'id=' + id + '&cat=' + cat + '&nivel=1' + '&tipo=cat_prod';
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            success: function(dados) {
                if (dados.sucesso === true) {
                    var opcoes = "";
                    $.each(dados.dados_taxa, function(i, item) {
                        opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                    });
                    if (dados.dados_in.vazio === true) {
                        emptySelect("#slcFamilia");
                        $('#btnGuardarDadosProdutos').hide();
                    } else {
                        $('#btnGuardarDadosProdutos').show();
                        emptyTable('#tblProdutosAlt');
                        $.each(dados.dados_in, function() {
                            $('#tblProdutosAlt').append('<tr class="tbody">' +
                                '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                                //
                                //'<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                                //'<td style="padding: 2px;">' + this.descricao + '</td>' +
                                //
                       
                                //
                                '<td style="padding: 2px;">' +
                                '<div class="inputareaTable">' + 
                                '<input type="text" name="txtNomeProduto" class="editableText" style="text-align: center" readonly="readonly" value="' + this.nome + '">' + 
                                '</div>' + 
                                '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '">' + 
                                '</td>' +

                                '<td style="padding: 2px;">' + 
                                '<div class="inputareaTable">' + 
                                '<input type="text" name="txtDescProd" class="editableText" style="text-align: center" readonly="readonly" value="' + this.descricao + '">' +
                                '</div>' + 
                                '</td>' + 
                                //
                       
                                '<td style="padding: 2px;">' +
                                '<div class="inputareaTable">' +
                                '<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
                                '<div class="mnyLabel right">' +
                                '<span>' + dados.moeda + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
                                '<td style="padding: 2px;">' +
                                '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                                '<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
                                opcoes +
                                '</select>' +
                                '</td>' +
                                '</tr>');
                        });
                        $('#tblProdutosAlt tr').each(function(key, value) {
                            if (key > 0) {
                                $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                            }
                        });
                        dynamicInput('tblProdutosAlt');
                        floatMask();
                        $('#tblProdutosAlt').show();
                        event.data.qs.cache();
                    }
                    emptySelect("#slcFamilia");
                    $.each(dados.dados_cat, function(i, item) {
                        if (dados.dados_cat[i].cat_vazia === true) {
                            if (id != "0") {
                                $('#slcFamilia').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                            }
                        } else {
                            $('#slcFamilia').append($('<option></option>').val(this.id).text(this.desig));
                        }
                    });
                    $('.chosenTabelaSelect').chosen('destroy');
                    $('.chosenTabelaSelect').chosen({
                        no_results_text: 'Sem resultados!'
                    });
                    $('#slcFamilia').trigger("chosen:updated");
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
}*/

// Versão atualizada
function fSlcSubcategoria(event) {
    if (event.handler !== true) {
        var cat = $('#slcCategoria').val();
        var id = $(this).val();
        var dataString = 'id=' + id + '&cat=' + cat + '&nivel=1' + '&tipo=cat_prod';
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            success: function(dados) {
                if (dados.sucesso === true) {
                    var opcoes = "";
                    $.each(dados.dados_taxa, function(i, item) {
                        opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                    });
                    if (dados.dados_in.vazio === true) {
                        emptySelect("#slcFamilia");
                        $('#btnGuardarDadosProdutos').hide();
                    } else {
                        $('#btnGuardarDadosProdutos').show();
                        emptyTable('#tblProdutosAlt');
                        $.each(dados.dados_in, function() {
                            if ($("#slcSubcategoria").val() != 0) {
                                $('#tblProdutosAlt').append('<tr class="tbody">' +
                                '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                                //'<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                                //'<td style="padding: 2px;">' + this.descricao + '</td>' +
                                
                                '<td style="padding: 2px;">' +
                                '<div class="inputareaTable">' + 
                                '<input type="text" id="txtNomeProduto" name="txtNomeProduto" class="editableText" style="text-align: center" readonly="readonly" value="' + this.nome + '">' + 
                                '</div>' + 
                                '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '">' + 
                                '</td>' +

                                '<td style="padding: 2px;">' + 
                                '<div class="inputareaTable">' + 
                                '<input type="text" id="txtDescProd" name="txtDescProd" class="editableText" style="text-align: center" readonly="readonly" value="' + this.descricao + '">' +
                                '</div>' + 
                                '</td>' +
                                '<td style="padding: 2px;">' + number_format(this.preco, 2, ",", ".") + ' ' + dados.moeda + '</td>' +
                                '<td style="padding: 2px;">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '"></td>' +
                                '</tr>');
                            }
                            else {
                                $('#tblProdutosAlt').append('<tr class="tbody">' +
                                '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                                '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                                '<td style="padding: 2px;">' + this.descricao + '</td>' +
                                /* PREÇO
								'<td style="padding: 2px;">' +
                                '<input type="text" name="txtPrecoProd" class="editableText acoes dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' + dados.moeda +
                                '</td>' +
								*/
								'<td style="padding: 2px;">' +
								'<div class="inputareaTable">' +
								'<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
								'<div class="mnyLabel right">' +
								'<span>' + dados.moeda + '</span>' +
								'</div>' +
								'</div>' +
								'</td>' +
                                
								/* REGRAS
								'<td style="padding: 2px;">' +
                                '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                                // '<div class="inputarea_col1" style="margin: 0 auto; height: 21px; width: 38%; float: none; background-color: transparent;">' +
								'<div class="inputarea_col1" style="margin: 0 auto; height: 21px; width: 50%; float: none; background-color: transparent;">' +
                                '<div class="styled-select" style="height: 21px;">' +
                                // '<select id="slcTaxa" name="slcTaxa" size="1" class="select" style="padding: 0 0 0 5%; border: 1px solid #236688; color: #000;">' +
								'<select id="slcTaxa" name="slcTaxa" size="1" class="chosenTabelaSelect" style="padding: 0 0 0 5%; border: 1px solid #236688; color: #000;">' +
                                opcoes +
                                '</select>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
								*/
								'<td style="padding: 2px;">' +
								'<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
								'<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
								opcoes +
								'</select>' +
								'</td>' +
                                '</tr>');
								
                                $('#tblProdutosAlt tr').each(function(key, value) {
                                    if (key > 0) {
                                        $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                                    }
                                });
                            }
                        });
                        $('#tblProdutosAlt tr').each(function(key, value) {
                            if (key > 0) {
                                $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                            }
                        });
                        dynamicInput('tblProdutosAlt');
                        floatMask();
						$('.chosenTabelaSelect').chosen('destroy');
						$('.chosenTabelaSelect').chosen({
							no_results_text: 'Sem resultados!'
						});
                        $('#tblProdutosAlt').show();
                        event.data.qs.cache();
                    }
                    emptySelect("#slcFamilia");
                    $.each(dados.dados_cat, function(i, item) {
                        if (dados.dados_cat[i].cat_vazia === true) {
                            if (id != "0") {
                                $('#slcFamilia').append($('<option selected="selected"></option>').val(this.id).text(this.desig));
                            }
                        } else {
                            $('#slcFamilia').append($('<option></option>').val(this.id).text(this.desig));
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
//

function carregaProdutosPaginacao(qs) {
    $.ajax({
        type: "POST",
        url: "functions/funcoes_admin.php",
        data: "pagina_atual=" + $('#pagAtual').val() + "&limite=" + $('#slcPag').val() + "&tipo=dados_produtos",
        dataType: "json",
        beforeSend: function() {
            $('#tblProdutosAlt').hide();
            $('#tblProdutosAltVazio').hide();
            showLoading();
        },
        success: function(dados) {
            hideLoading();
            if (dados.sucesso === true) {
                $('#slcFornecedor').val(0);
                var opcoes = "";
                $.each(dados.dados_taxa, function(i, item) {
                    opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
                });
                if (dados.dados_in.vazio === true) {
                    $('#btnGuardarDadosProdutos').hide();
                } else {
                    $('#btnGuardarDadosProdutos').show();
                    emptyTable('#tblProdutosAlt');
                    $.each(dados.dados_in, function() {
                        $('#tblProdutosAlt').append('<tr class="tbody">' +
                            '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
                            '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
                            '<td style="padding: 2px;">' + this.descricao + '</td>' +
                            '<td style="padding: 2px;">' +
                            '<div class="inputareaTable">' +
                            '<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
                            '<div class="mnyLabel right">' +
                            //'<span>' + dados.moeda + '</span>' +
                            '<span>' + this.moeda + '</span>' +
                            '</div>' +
                            '</div>' +
                            '</td>' +
                            '<td style="padding: 2px;">' +
                            '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
                            '<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
                            opcoes +
                            '</select>' +
                            '</td>' +
                            '</tr>');
                    });
                    $('#tblProdutosAlt tr').each(function(key, value) {
                        if (key > 0) {
                            $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
                        }
                    });
                    floatMask();
                    $('#tblProdutosAlt').show();
                    qs.cache();
                }
                emptySelect("#slcSubcategoria");
                emptySelect("#slcFamilia");
                $('.chosenTabelaSelect').chosen('destroy');
                $('.chosenTabelaSelect').chosen({
                    no_results_text: 'Sem resultados!'
                });
                $('#slcSubcategoria').trigger("chosen:updated");
                $('#slcFornecedor').trigger("chosen:updated");
                $('#pagAtual').val(1);
                $('#pagTotal').val(dados.paginas);
                $('#pagInput').val("1 de " + dados.paginas);
            } else {
                $('#tblProdutosAlt').hide();
                $('#tblProdutosAltVazia').show();
            }
        }
    });
}

/*function inputPagination(objeto) {
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
    nivel = "";
    if ($('#slcCategoria').val() != "0" && $('#slcSubcategoria').val() == "0" && $('#slcFamilia').val() == "0") {
        nivel = "0";
    } else if ($('#slcCategoria').val() != "0" && $('#slcSubcategoria').val() != "0" && $('#slcFamilia').val() == "0") {
        nivel = "1";
    } else if ($('#slcCategoria').val() != "0" && $('#slcSubcategoria').val() != "0" && $('#slcFamilia').val() != "0") {
        nivel = "2";
    }
    alert(nivel);
    // $.ajax({
    //     type: "POST",
    //     url: "functions/funcoes_admin.php",
    //     data: "id=" + $('#slcFornecedor').val() + "&nivel=" + nivel + "&pagina_atual=" + $('#pagAtual').val() + "&limite=" + $('#slcPag').val() + "&tipo=dados_produtos_limite",
    //     dataType: "json",
    //     beforeSend: function() {
    //         $('#tblProdutosAlt').hide();
    //         $('#tblProdutosAltVazio').hide();
    //         showLoading();
    //     },
    //     success: function(dados) {
    //         hideLoading();
    //         if (dados.sucesso === true) {
    //             emptyTable('#tblProdutosAlt');
    //             if (dados.vazio === false) {
    //                 $('#slcFornecedor').val(0);
    //                 var opcoes = "";
    //                 $.each(dados.dados_taxa, function(i, item) {
    //                     opcoes += '<option value="' + this.id_regra + '">' + number_format(this.valor, 0, ',', '.') + ' ' + this.simbolo + '</option>';
    //                 });
    //                 if (dados.linhas <= $('#slcPag').val()) {
    //                     $('.pagination').hide();
    //                 }
    //                 $.each(dados.dados_in, function() {
    //                     $('#tblProdutosAlt').append('<tr class="tbody">' +
    //                         '<td style="padding: 2px;">' + this.nome_fornecedor + '<input id="hddIdFornecedor" name="hddIdFornecedor" type="hidden" value="' + this.id_fornecedor + '"></td>' +
    //                         '<td style="padding: 2px;">' + this.nome + '<input id="hddIdProd" name="hddIdProd" type="hidden" value="' + this.id_produto + '"></td>' +
    //                         '<td style="padding: 2px;">' + this.descricao + '</td>' +
    //                         '<td style="padding: 2px;">' +
    //                         '<div class="inputareaTable">' +
    //                         '<input type="text" name="txtPrecoProd" class="editableText dinheiro dynamicInput" readonly="readonly" value="' + number_format(this.preco, 2, ",", ".") + '" >' +
    //                         '<div class="mnyLabel right">' +
    //                         '<span>' + dados.moeda + '</span>' +
    //                         '</div>' +
    //                         '</div>' +
    //                         '</td>' +
    //                         '<td style="padding: 2px;">' +
    //                         '<input id="hddIdRegra" name="hddIdRegra" type="hidden" value="' + this.id_regra + '">' +
    //                         '<select id="slcTaxa" name="slcTaxa" class="chosenTabelaSelect">' +
    //                         opcoes +
    //                         '</select>' +
    //                         '</td>' +
    //                         '</tr>');
    //                 });
    //                 $('#tblProdutosAlt tr').each(function(key, value) {
    //                     if (key > 0) {
    //                         $(this).find('select[name="slcTaxa"]').val($(this).find('#hddIdRegra').val());
    //                     }
    //                 });
    //                 floatMask();
    //                 $('#tblProdutosAlt').show();
    //                 emptySelect("#slcSubcategoria");
    //                 emptySelect("#slcFamilia");
    //                 $('.chosenTabelaSelect').chosen('destroy');
    //                 $('.chosenTabelaSelect').chosen({
    //                     no_results_text: 'Sem resultados!'
    //                 });
    //                 $('#slcSubcategoria').trigger("chosen:updated");
    //                 $('#slcFornecedor').trigger("chosen:updated");
    //                 $('#pagInput').val(dados.pag_inicial + " de " + dados.paginas);
    //                 $('#pagAtual').val(dados.pag_inicial);
    //                 $('#pagTotal').val(dados.paginas);
    //                 $('#pagLinhas').val(dados.linhas);
    //             } else {
    //                 $('#tblProdutosAlt').hide();
    //                 $('#tblProdutosAltVazio').show();
    //             }
    //         }
    //     }
    // });
}

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
        if (parseInt($('#pagAtual').val()) - 1 <= 0) {
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
        if (parseInt($('#pagAtual').val()) + 1 >= parseInt($('#pagTotal').val())) {
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
});*/

function fSlcPaisFornecedor(event) {
    if (event.handler !== true) {
        var id_pais = $(this).val();
        var dataString = 'id_pais=' + id_pais + "&tipo=ver_fornecedor_pais";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            success: function(dados) {
                if (dados.sucesso === true) {
                    $('select[name="slcFornecedorProduto"]').empty();
                    $('select[name="slcFornecedorProduto"]').append('<option selected="selected" value="0"></option>');
                    if (dados.vazio == false) {
                        $.each(dados.dados_in, function() {
                            $('select[name="slcFornecedorProduto"]').append($('<option></option>').val(this.id_fornecedor).text(this.nome_fornecedor));
                        });
                        //$('#txtPreco').find('.mnyLabel right').children('span').text(dados.moeda);
                        $('#simboloMoeda').text(dados.moeda);
                    }
                    $('select[name="slcFornecedorProduto"]').trigger("chosen:updated");
                }
            }
        });
        event.handler = true;
    }
    return false;
};

function fSlcProdAfet(event) {
    if (event.handler !== true) {
        var DOM_id = $(this).attr("id");
        var id_prod = $(this).val();
        var dataString = 'id=' + id_prod + '&tipo=info_prod_afet';
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            success: function(dados) {
                $('input[name="txtDescricaoProd"]').val(dados.descricao);
                $('input[name="txtCategoriaProd"]').val(dados.cat);
                $('input[name="txtSubcategoriaProd"]').val(dados.subcat);
                $('input[name="txtFamiliaProd"]').val(dados.fam);
                if (DOM_id === "slcProdAfet") $('input[name="txtIVAProd"]').val(dados.iva);
            }
        });
        event.handler = true;
    }
    return false;
}

function fBtnAfetProduto(event) {
    if (event.handler !== true) {
        var id_prod = $('#frmAfetProdFornec').find('#slcProdAfet').val();
        var id_fornec = $('#frmAfetProdFornec').find('#slcFornecedorProdutoAfet').val();
        var preco = formatValor($('#frmAfetProdFornec').find('input[name="txtPrecoProd"]').val());
        var dataString = 'id_prod=' + id_prod + "&id_fornec=" + id_fornec + "&preco=" + preco + "&tipo=afet_prod_fornec";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            beforeSend: function() {
                $('#frmAfetProdFornec').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#frmAfetProdFornec').show();
                if (dados.sucesso === true) {
                    // $('#frmAfetProdFornec').find('#slcProdAfet').val(0);
                    // $('#frmAfetProdFornec').find('#slcPaisFornecedorAfet').val(0);
                    $('#frmAfetProdFornec').find('#slcProdAfet option').eq(0).prop("selected", true);
                    $('#frmAfetProdFornec').find('input[name="txtDescricaoProd"]').val('');
                    $('#frmAfetProdFornec').find('input[name="txtCategoriaProd"]').val('');
                    $('#frmAfetProdFornec').find('input[name="txtSubcategoriaProd"]').val('');
                    $('#frmAfetProdFornec').find('input[name="txtFamiliaProd"]').val('');
                    $('#frmAfetProdFornec').find('#slcFornecedorProdutoAfet option').eq(0).prop("selected", true);
                    $('#frmAfetProdFornec').find('input[name="txtPrecoProd"]').val('');
                    $('#frmAfetProdFornec').find('input[name="txtIVAProd"]').val('');
                    
                    $('#slcProdAfet').trigger("chosen:updated");
                    $('#slcFornecedorProdutoAfet').trigger("chosen:updated");
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

function fSlcTipoDesc(event) {
    if (event.handler !== true) {
        if ($(this).val() == "comerc") {
            $('#frmAddDescProd').find('#divDescFinanc').hide();
        } else if ($(this).val() == "financ") {
            $('#frmAddDescProd').find('#divDescFinanc').show();
        }
        event.handler = true;
    }
    return false;
}

function fSlcFornecedorDesc(event) {
    if (event.handler !== true) {
        var id_fornec = $(this).val();
        var dataString = 'id_fornec=' + id_fornec + "&tipo=ver_produto_fornecedor";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            success: function(dados) {
                if (dados.sucesso === true) {
                    $('select[name="slcProd"]').empty();
                    $('select[name="slcProd"]').append('<option selected="selected" value="0"></option>');
                    if (dados.vazio == false) {
                        $.each(dados.dados_in, function() {
                            $('select[name="slcProd"]').append($('<option></option>').val(this.id_prod).text(this.nome_prod));
                        });
                        //$('#txtPreco').find('.mnyLabel right').children('span').text(dados.moeda);
                        $('#frmAddDescProd').find('input[name="txtDescricaoProd"]').val('');
                        $('#frmAddDescProd').find('input[name="txtCategoriaProd"]').val('');
                        $('#frmAddDescProd').find('input[name="txtSubcategoriaProd"]').val('');
                        $('#frmAddDescProd').find('input[name="txtFamiliaProd"]').val('');
                    }
                    else{
                        $('#frmAddDescProd').find('#slcProdDesc option').eq(0).prop("selected", true);
                        $('#frmAddDescProd').find('input[name="txtDescricaoProd"]').val('');
                        $('#frmAddDescProd').find('input[name="txtCategoriaProd"]').val('');
                        $('#frmAddDescProd').find('input[name="txtSubcategoriaProd"]').val('');
                        $('#frmAddDescProd').find('input[name="txtFamiliaProd"]').val('');
                        
                        if (parseInt(id_fornec) > 0) {
                            /* */
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            $("body").animate({
                                scrollTop: 0
                            });
                            /* */
                        }
                    }
                    $('select[name="slcProd"]').trigger("chosen:updated");
                }
            }
        });
        
        event.handler = true;
    }
    return false;
}

function fBtnAddDescProd(event) {
    if (event.handler !== true) {
        var tipo_desc = $('#slcTipoDesc').val();
        var id_fornec = $('#frmAddDescProd').find('#slcFornecedorProdutoDesc').val();
        var desc = formatValor($('#frmAddDescProd').find('input[name="txtDescProd"]').val());
        var dataString = "";
        
        if (tipo_desc == "financ") {
            var id_prod = $('#frmAddDescProd').find('#slcProdDesc').val();
            var prz_pag = formatValor($('#frmAddDescProd').find('input[name="txtPrzPagProd"]').val());
            dataString = "tipo_desc=" + tipo_desc + "&id_fornec=" + id_fornec + '&id_prod=' + id_prod + "&desc=" + desc + "&prz_pag=" + prz_pag + "&tipo=add_desc_prod";
        
        } else if (tipo_desc == "comerc") {
            dataString = "tipo_desc=" + tipo_desc + "&id_fornec=" + id_fornec + "&desc=" + desc + "&tipo=add_desc_prod";
        }
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            beforeSend: function() {
                $('#frmAddDescProd').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                $('#frmAddDescProd').show();
                if (dados.sucesso === true) {
                    // $('#frmAfetProdFornec').find('#slcProdAfet').val(0);
                    // $('#frmAfetProdFornec').find('#slcPaisFornecedorAfet').val(0);
                    $('#frmAddDescProd').find('#slcTipoDesc option').eq(0).prop("selected", true);
                    $('#frmAddDescProd').find('#slcProdDesc option').eq(0).prop("selected", true);
                    $('#frmAddDescProd').find('input[name="txtDescricaoProd"]').val('');
                    $('#frmAddDescProd').find('input[name="txtCategoriaProd"]').val('');
                    $('#frmAddDescProd').find('input[name="txtSubcategoriaProd"]').val('');
                    $('#frmAddDescProd').find('input[name="txtFamiliaProd"]').val('');
                    $('#frmAddDescProd').find('#slcFornecedorProdutoDesc option').eq(0).prop("selected", true);
                    $('#frmAddDescProd').find('input[name="txtDescProd"]').val('');
                    $('#frmAddDescProd').find('input[name="txtPrzPagProd"]').val('');
                    $('#frmAddDescProd').find('#divDescFinanc').show();
                    
                    $('#slcTipoDesc').trigger("chosen:updated");
                    $('#slcProdDesc').trigger("chosen:updated");
                    $('#slcFornecedorProdutoDesc').trigger("chosen:updated");
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

function fSlcEstadoDesc(event) {
    if (event.handler !== true) {
        $('#btnGuardarChgDesc').show();
        event.handler = true;
    }
    return false;
}

function fBtnDelDesc(event) {
    if (event.handler !== true) {
		if (confirm('Tem a certeza que pretende eliminar este desconto?')) {
			var id = $(this).children('#hddIdDesc').val();
			var dataString = 'id_desc=' + id + '&tipo=del_desc';
        
			$.ajax({
				type: "POST",
				url: "functions/funcoes_admin.php",
				data: dataString,
				dataType: "json",
				beforeSend: function() {
					$('#tblDescDetail').hide();
					showLoading();
				},
				success: function(dados) {
					hideLoading();
					if (dados.sucesso === true) {
						if (dados.vazio === false) {
							emptyTable('#tblDescDetail');
							$.each(dados.dados_in, function(i, item) {
								$('#tblDescDetail').append('<tr>' +
								'<td style="padding: 6px;">' + dados.dados_in[i].fornec + '</td>' +
								'<td style="padding: 6px;">' + dados.dados_in[i].nome_prod + '</td>' +
								'<td style="padding: 6px;">' + dados.dados_in[i].desc + '</td>' +
								'<td style="padding: 6px;">' + dados.dados_in[i].prz_pag + '</td>' +
								'<td style="padding: 6px;">' + 
									'<select id="slcEstadoDesc_' + dados.dados_in[i].id_desc + '" name="slcEstadoDesc" class="chosenTabelaSelect">' +
										'<option value="0"> Inativo </option>' +
										'<option value="1"> Ativo </option>' +
									'</select>' +
								'</td>' +
								'<td class="iconwrapper">' + 
									'<div name="btnIDDesc" class="novolabelicon icon-garbage delDesc">' +
										'<input id="hddIdDesc" name="hddIdDesc" type="hidden" value="' + dados.dados_in[i].id_desc + '">' +
									'</div>' + 
								'</td>' + 
								'</tr>');
								var divWidth = $('.chosenTabelaSelect').css('width');
								$('#slcEstadoDesc_' + dados.dados_in[i].id_desc).chosen({width: divWidth});
								$('#slcEstadoDesc_' + dados.dados_in[i].id_desc + ' option').eq(dados.dados_in[i].state).prop("selected", true);
								$('#slcEstadoDesc_' + dados.dados_in[i].id_desc).trigger("chosen:updated");
							});
							
                            $('#slcFornecDesc option').eq(0).prop("selected", true);
                            $('#slcFornecDesc').trigger("chosen:updated");
                            
							$('#btnGuardarChgDesc').hide();
							$('#tblDescDetail').show();
						} else if (dados.vazio === true) {
							$('#tblDescDetail').hide();
							$('#slcFornecDesc').hide();
							$('#tblDescDetailVazia').show();
						}
					}
				}
			});
		}
        event.handler = true;
    }
    return false;
}

function fBtnEditDesc(event) {
    if (event.handler !== true) {
        /* */
        var dados = {};
        $.each($('#tblDescDetail').find('input[name="hddIdDesc"]'), function(key, value) {
            var id = $(this).val();
            dados[key] = {};
            dados[key].id = id;
            dados[key].estado = $('#tblDescDetail').find('#slcEstadoDesc_' + id).val();
            // alert ('ID: ' + id + ' Estado: ' + dados[key].estado);
        });
        var dataString = "dados=" + JSON.stringify(dados) + "&tipo=edit_desc";
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            beforeSend: function() {
                $('#tblDescDetail').hide();
                showLoading();
            },
            success: function(dados) {
                if (dados.sucesso === true) {
                    hideLoading();
                    if (dados.vazio === false) {
                        emptyTable('#tblDescDetail');
                        $.each(dados.dados_in, function(i, item) {
                            $('#tblDescDetail').append('<tr>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].fornec + '</td>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].nome_prod + '</td>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].desc + '</td>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].prz_pag + '</td>' +
                            '<td style="padding: 6px;">' + 
                                '<select id="slcEstadoDesc_' + dados.dados_in[i].id_desc + '" name="slcEstadoDesc" class="chosenTabelaSelect">' +
                                    '<option value="0"> Inativo </option>' +
                                    '<option value="1"> Ativo </option>' +
                                '</select>' +
                            '</td>' +
                            '<td class="iconwrapper">' + 
                                '<div name="btnIDDesc" class="novolabelicon icon-garbage delDesc">' +
                                    '<input id="hddIdDesc" name="hddIdDesc" type="hidden" value="' + dados.dados_in[i].id_desc + '">' +
                                '</div>' + 
                            '</td>' + 
                            '</tr>');
                            var divWidth = $('.chosenTabelaSelect').css('width');
                            $('#slcEstadoDesc_' + dados.dados_in[i].id_desc).chosen({width: divWidth});
                            $('#slcEstadoDesc_' + dados.dados_in[i].id_desc + ' option').eq(dados.dados_in[i].state).prop("selected", true);
                            $('#slcEstadoDesc_' + dados.dados_in[i].id_desc).trigger("chosen:updated");
                        });
                        $('#btnGuardarChgDesc').hide();
                    } else if (dados.vazio === true) {
                        $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                    $('#tblDescDetail').show();
                }
            }
        });
        /* */
        event.handler = true;
    }
    return false;
}

function fSrchDescFornec(event) {
    if (event.handler !== true) {
        var id = $(this).val();
        var dataString = 'id_fornec=' + id + '&tipo=filtrar_desc_fornec';
        
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            data: dataString,
            dataType: "json",
            beforeSend: function() {
                $('#tblDescDetail').hide();
                showLoading();
            },
            success: function(dados) {
                hideLoading();
                if (dados.sucesso === true) {
                    if (dados.vazio === false) {
                        emptyTable('#tblDescDetail');
                        $.each(dados.dados_in, function(i, item) {
                            $('#tblDescDetail').append('<tr>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].fornec + '</td>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].nome_prod + '</td>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].desc + '</td>' +
                            '<td style="padding: 6px;">' + dados.dados_in[i].prz_pag + '</td>' +
                            '<td style="padding: 6px;">' + 
                                '<select id="slcEstadoDesc_' + dados.dados_in[i].id_desc + '" name="slcEstadoDesc" class="chosenTabelaSelect">' +
                                    '<option value="0"> Inativo </option>' +
                                    '<option value="1"> Ativo </option>' +
                                '</select>' +
                            '</td>' +
                            '<td class="iconwrapper">' + 
                                '<div name="btnIDDesc" class="novolabelicon icon-garbage delDesc">' +
                                    '<input id="hddIdDesc" name="hddIdDesc" type="hidden" value="' + dados.dados_in[i].id_desc + '">' +
                                '</div>' + 
                            '</td>' + 
                            '</tr>');
                            var divWidth = $('.chosenTabelaSelect').css('width');
                            $('#slcEstadoDesc_' + dados.dados_in[i].id_desc).chosen({width: divWidth});
                            $('#slcEstadoDesc_' + dados.dados_in[i].id_desc + ' option').eq(dados.dados_in[i].state).prop("selected", true);
                            $('#slcEstadoDesc_' + dados.dados_in[i].id_desc).trigger("chosen:updated");
                        });
                        $('#btnGuardarChgDesc').hide();
                        $('#tblDescDetailVazia').hide();
                        $('#tblDescDetail').show();
                    } else if (dados.vazio === true) {
                        $('#tblDescDetail').hide();
                        $('#slcFornecDesc').hide();
                        $('#tblDescDetailVazia').show();
                    }
                }
            }
        });
        
        event.handler = true;
    }
    return false;
}