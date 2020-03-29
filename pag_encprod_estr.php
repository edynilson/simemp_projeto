<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-19 19:47:04
*/

include('./conf/check.php');
include_once('./conf/common.php');


$query_pais_fornecedores = $connection->prepare("SELECT p.id_pais, p.nome_pais FROM pais p ORDER by p.nome_pais");
$query_pais_fornecedores->execute();

// $query_produto = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT rp.valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, (@preco_un * (1 + @taxa/100)) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais c ON pf.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.nome_pais='Portugal' ORDER BY s.nome");
$query_produto = $connection->prepare("SELECT s.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev, s.nome, @preco_un := preco AS preco_un, @taxa := (SELECT IF(r.nome_regra LIKE '% IRC', -rp.valor, rp.valor) AS valor FROM produto p INNER JOIN regra_produto rp ON p.id=rp.id_produto INNER JOIN regra r ON rp.id_regra=r.id_regra WHERE p.id=s.id ORDER BY data DESC LIMIT 1) AS taxa, round((@preco_un * (1 + @taxa/100)), 2) AS total, m.simbolo AS simbolo_moeda, descricao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia fa1 ON af.id_familia=fa1.id INNER JOIN familia fa2 ON fa1.id=fa2.parent INNER JOIN familia fa3 ON fa2.id=fa3.parent INNER JOIN produto s ON fa3.id=s.familia INNER JOIN fp_stock fp ON fp.id_produto=s.id INNER JOIN fornecedor f ON fp.id_fornecedor=f.id INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais c ON pf.id_pais=c.id_pais INNER JOIN moeda m ON c.id_moeda=m.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.nome_pais='Portugal' ORDER BY s.nome");
$query_produto->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$num_produto = $query_produto->rowCount();

$query_produto_add = $connection->prepare("SELECT c.id AS id_item_add, p.id AS id_produto, f.id AS id_fornecedor, f.nome_abrev AS nome_fornecedor, p.nome, c.preco, c.quantidade, c.iva AS taxa, c.valor, m.simbolo AS simbolo_moeda FROM carrinho c INNER JOIN produto p ON p.id=c.item_adicionado INNER JOIN fornecedor f ON f.id=c.id_fornecedor INNER JOIN pais_fornecedor pf ON f.id=pf.id_fornecedor INNER JOIN pais pp ON pf.id_pais=pp.id_pais INNER JOIN moeda m ON pp.id_moeda=m.id WHERE id_empresa=:id_empresa ORDER BY c.id");
$query_produto_add->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$num_produto_add = $query_produto_add->rowCount();

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_categorias = $connection->prepare("SELECT f.id, f.designacao FROM empresa emp INNER JOIN atividade a ON emp.atividade=a.id INNER JOIN atividade_familia af ON af.id_atividade=a.id INNER JOIN familia f ON f.id=af.id_familia WHERE emp.ativo='1' AND f.parent IS NULL AND emp.id_empresa=:id_empresa");
$query_categorias->execute(array(':id_empresa' => $_SESSION['id_empresa']));

$query_desconto = $connection->prepare("SELECT re.valor, re.simbolo FROM regra_empresa re INNER JOIN regra r ON re.id_regra=r.id_regra INNER JOIN empresa emp ON emp.id_empresa=re.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND r.nome_regra=:nome_regra ORDER BY date(re.date_reg) DESC, time(re.date_reg) DESC LIMIT 1");
$query_desconto->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':nome_regra' => "Taxa de desconto por defeito"));
$linha_desconto = $query_desconto->fetch(PDO::FETCH_ASSOC);

$datetime = new DateTime();
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0">
        <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="js/carrinho.js"></script>
        <script src="js/functions.js"></script>
        <script src="js/jquery.quicksearch.js"></script>
        <script src="js/jquery.inputmask-3.x/jquery.inputmask.js"></script>
        <script src="js/jquery.inputmask-3.x/jquery.inputmask.numeric.extensions.js"></script>
        <link rel="stylesheet" href="css/style.css">
        <link rel="icon" href="favicon.ico">
    </head>
    <body id="normal">
        <header>
            <div id="header_1">
                <section class="left-column">
                    <div id="panel_home" class="panel">
                        <a href="pag_user.php">
                            <div class="icon-home"></div>
                        </a>
                    </div>
                </section>
                <section class="center-column">
                    <div class="esq40" style="margin-top: 1.5%; margin-bottom: 1.5%;">
                        <img id="logo_simemp" src="./images/logo_med.png" style="margin-top: 0;">
                    </div>
                    <div class="dir60" style="margin-top: 1.5%; margin-bottom: 1.5%;">
                        <label id="txtDataVirtual" class="labelNormal" style="float: none; font-family: helvetica-light; font-size: 100%; color: #fff; font-weight: bold;"></label>
                    </div>
                </section>
                <section class="right-column">
                    <div id="panel_logout" class="panel">
                        <a href="terminar_sessao.php">
                            <div class="icon-off"></div>
                        </a>
                    </div>
                </section>
            </div>
        </header>
        <section id="conteudo">
            <div id="divEstruturaPag">
                <div id="divPagProdutos">
                    <div id="divPagProdutosEsq">
                        <div class="linha">
                            <div class="left-column" style="width: 40%;">
                                <h3> Fazer encomenda </h3>
                            </div>
                            <!-- A ser eliminado após upgrade para plugin "notify.js" -->
                            <div class="error"></div>
                            <!-- -->
                        </div>
                        <table id="tblProdutos" name="tblProdutos" class="tabela">
                            <tr>
                                <td class="td40">Nome</td>
                                <td class="td25">Fornecedor</td>
                                <td class="td20">Preço un.</td>
                                <td class="td5">IVA</td>
                                <td class="td5">IRC</td>
                                <td class="td5" style="background-color: transparent;">&nbsp;</td>
                            </tr>
                            <?php while ($linha_itens = $query_produto->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr class="tbody">
                                    <td style="text-align: left; padding: 1%;">
                                        <?php echo $linha_itens['nome']; ?>
                                        <input id="hddIdFornecedor" type="hidden" value="<?php echo $linha_itens['id_fornecedor']; ?>">
                                        <input id="hddTaxaIva" type="hidden" value="<?php echo $linha_itens['taxa']; ?>">
                                    </td>
                                    <td style="text-align: left; padding: 1%;"><?php echo $linha_itens['nome_abrev']; ?></td>
                                    <td class="preco" style="padding: 1%;"><?php echo number_format($linha_itens['preco_un'], 2, ',', '.'); ?> <?php echo $linha_itens['simbolo_moeda']; ?></td>
                                    <td style="padding: 1%;"><?php echo $linha_itens['taxa'] > 0 ? number_format($linha_itens['taxa'], 0, ',', '.').'%' : '-'; ?></td>
                                    <td style="padding: 1%;"><?php echo $linha_itens['taxa'] < 0 ? number_format(abs($linha_itens['taxa']), 0, ',', '.').'%' : '-'; ?></td>
                                    <td style="background-color: #77a4d7; padding: 1px; cursor: pointer;">
                                        <div id="btnAddCarr" name="btnAddCarr" class="labelicon icon-carrinho add_carrinho" style="margin: 0;">
                                            <input id="hddIdProd" name="hddIdProd" type="hidden" value="<?php echo $linha_itens['id_produto']; ?>">
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td class="noresults" colspan="5">Não existem resultados</td>
                            </tr>
                        </table>
                        <table id="tblProdVazia" name="tblProdVazia" class="tabela">
                            <tr>
                                <td>Não existem itens para mostrar neste momento. Por favor, verifique novamente mais tarde. Obrigado.</td>
                            </tr>
                        </table>
                    </div>
                    <div id="divPagProdutosDir">
                        <div id="divPesqProd" name="divPesqProd" class="barPesqGr">
                            <input id="txtProcProd" name="txtProcProd" type="text" placeholder="Pesquise aqui">
                            <label class="icon-lupa" style="cursor: pointer"></label>
                        </div>
                        <div id="divCatProd">
                            <div class="linha10">
                                <label for="slcPaisProduto">País</label>
                                <div class="inputarea_col1">
                                    <div class="styled-select">
                                        <select id="slcPaisProduto" name="slcPaisProduto" size="1" class="select">
                                            <?php while ($linha = $query_pais_fornecedores->fetch(PDO::FETCH_ASSOC)) {
                                                if ($linha['nome_pais'] == 'Portugal') { ?>
                                                    <option value="<?php echo $linha['id_pais']; ?>" selected="selected"><?php echo $linha['nome_pais']; ?></option>
                                                <?php } else { ?>
                                                    <option value="<?php echo $linha['id_pais']; ?>"><?php echo $linha['nome_pais']; ?></option>
                                                <?php } 
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="linha10">
                                <label for="slcCategoria">Categoria</label>
                                <div class="inputarea_col1">
                                    <div class="styled-select">
                                        <select id="slcCategoria" name="slcCategoria" size="1" class="select">
                                            <option value="0" selected="selected">- Categoria -</option>
                                            <?php while ($linha_categorias = $query_categorias->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <option value="<?php echo $linha_categorias['id']; ?>"><?php echo $linha_categorias['designacao']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="linha10">
                                <label for="slcSubcategoria">Subcategoria</label>
                                <div class="inputarea_col1">
                                    <div class="styled-select">
                                        <select id="slcSubcategoria" name="slcSubcategoria" size="1" class="select">
                                            <option value="0" selected="selected">- Subcategoria -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="linha10">
                                <label for="slcFamilia">Família</label>
                                <div class="inputarea_col1">
                                    <div class="styled-select">
                                        <select id="slcFamilia" name="slcFamilia" size="1" class="select">
                                            <option value="0" selected="selected">- Família -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="divCarrinho">
                            <div class="linha" style="margin-bottom: 10px;">
                                <h4>Carrinho de compras</h4>
                            </div>
                            <table id="tblCarrinho" name="tblCarrinho" style="width: 100%;">
                                <?php while ($linha_produto_add = $query_produto_add->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td style="width: 65%;"><?php echo $linha_produto_add['nome']; ?></td>
                                        <td style="width: 35%; text-align: right; padding-right: 5px;"><?php echo number_format($linha_produto_add['preco'] * $linha_produto_add['quantidade'] + ($linha_produto_add['preco'] * $linha_produto_add['quantidade'] * ($linha_produto_add['taxa'] / 100)), 2, ',', '.'); ?> <?php echo $linha_produto_add['simbolo_moeda']; ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                            <div class="linha" style="text-align: center;">
                                <button id="btnVerEncomenda" class="btn btn-3 btn-3a icon-carrinho" style="float: none; margin-top: 10px;">Ver encomenda</button>
                            </div>
                            <table id="tblCarrVazio" name="tblCarrVazio" class="tabela">
                                <tr>
                                    <td>O seu carrinho de compras está vazio.</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="divPagEncomenda">
                    <div class="linha">
                        <button id="btnVoltar" name="btnVoltar" class="btnNoIco voltarDir">Voltar</button>
                    </div>
                    <div class="linha">
                        <div id="divCarrDetail" name="divCarrDetail">
                            <table id="tblCarrDetail" name="tblCarrDetail" class="tabela">
                                <tr>
                                    <td class="td5">Nº</td>
                                    <td class="td35">Nome do item</td>
                                    <td class="td25">Fornecedor</td>
                                    <td class="td10">Ponderação</td>
                                    <td class="td10">Preço</td>
                                    <td class="td10">Qtd</td>
                                    <td class="td10">Valor</td>
                                    <td class="td5" style="background-color: transparent;">&nbsp;</td>
                                </tr>
                            </table>
                            <table id="tblCarrDetailVazio" name="tblCarrDetailVazio" class="tabela">
                                <tr>
                                    <td>Não existem itens para mostrar neste momento. Por favor, verifique novamente mais tarde. Obrigado.</td>
                                </tr>
                            </table>
                            <div class="linha" style="margin-top: 20px;">
                                <div id="divTotalEncomenda" name="divTotalEncomenda" align="left">
                                    <input id="txtTotSDesc" name="txtTotSDesc" type="text" class="total_campo" readonly="readonly" value="">
                                    <input id="txtDesconto" name="txtDesconto" type="text" class="total_campo" readonly="readonly" value="">
                                    <input id="txtIva" name="txtIva" type="text" class="total_campo" readonly="readonly" value="">
                                    <input id="txtIrc" name="txtIrc" type="text" class="total_campo" readonly="readonly" value="">
                                    <input id="txtSoma" name="txtSoma" type="text" class="total_campo tot" readonly="readonly" value="">
                                </div>
                                <button id="btnEncomendar" name="btnEncomendar" class="btn btn-3 btn-3a icon-carrinho">Encomendar</button>
                                <button id="carrinho_botoes" class="botaoG icon-garbage limpar_carrinho"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>