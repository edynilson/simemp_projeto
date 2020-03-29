<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-20 10:49:14
*/
include('./conf/check.php');
include_once('./conf/common.php');

$query_numconta = $connection->prepare("SELECT c.num_conta FROM banco b INNER JOIN conta c ON b.id=c.id_banco INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
$query_numconta->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$num_numconta = $query_numconta->rowCount();
$row_numconta = $query_numconta->fetch(PDO::FETCH_ASSOC);
if ($num_numconta > 0) {
    $num_conta = $row_numconta["num_conta"];
}

$query_movimento_saldo = $connection->prepare("SELECT m.saldo_controlo FROM conta c INNER JOIN movimento m ON c.id=m.id_conta INNER JOIN banco b ON c.id_banco=b.id INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa AND c.tipo_conta='ordem' ORDER BY m.id DESC, m.date_reg DESC LIMIT 1");
$query_movimento_saldo->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$linha_movimento_saldo = $query_movimento_saldo->fetch(PDO::FETCH_ASSOC);
if ($linha_movimento_saldo > 0) {
    $saldo = $linha_movimento_saldo['saldo_controlo'];
}

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_imagem = $connection->prepare("SELECT b.path_imagem FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN conta c ON c.id_empresa=emp.id_empresa INNER JOIN banco b ON c.id_banco=b.id WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa");
$query_imagem->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$linha_imagem = $query_imagem->fetch(PDO::FETCH_ASSOC);

$query_ver_contaP = $connection->prepare("SELECT c.id FROM conta c INNER JOIN empresa e ON c.id_empresa = e.id_empresa INNER JOIN utilizador u ON u.id_empresa = e.id_empresa WHERE e.ativo = '1' AND c.tipo_conta = 'prazo' AND u.id=:id_utilizador LIMIT 1");
$query_ver_contaP->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$ver_contaP = $query_ver_contaP->rowCount();

$query_ver_juros = $connection->prepare("SELECT c.id, j.id_juro FROM conta c INNER JOIN empresa e ON c.id_empresa = e.id_empresa INNER JOIN utilizador u ON u.id_empresa = e.id_empresa INNER JOIN juros_dp j ON c.id=j.id_conta WHERE e.ativo = '1' AND c.tipo_conta = 'prazo' AND j.data_lim_r>NOW() AND u.id=:id_utilizador ORDER BY j.data_lim_r DESC LIMIT 1");
$query_ver_juros->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$ver_juros = $query_ver_juros->rowCount();

//$query_last_dp = $connection->prepare("SELECT j.deposito FROM juros_dp j INNER JOIN conta c ON j.id_conta = c.id WHERE c.id_empresa = :id_empresa AND c.data_lim_r>NOW() ORDER BY j.deposito DESC LIMIT 1");
$query_last_dp = $connection->prepare("SELECT j.deposito FROM juros_dp j INNER JOIN conta c ON j.id_conta = c.id WHERE c.id_empresa = :id_empresa ORDER BY j.deposito DESC LIMIT 1");
$query_last_dp->execute(array(':id_empresa' => $_SESSION['id_empresa']));
$last_dp = $query_last_dp->fetch(PDO::FETCH_ASSOC);

$query_ver_pag = $connection->prepare("SELECT m.id FROM movimento m INNER JOIN conta c ON m.id_conta = c.id WHERE c.id_empresa = :id_empresa AND m.descricao = CONCAT('Transferência de depósito a prazo nº ', :last_dp)");
$query_ver_pag->execute(array(':id_empresa' => $_SESSION['id_empresa'], ':last_dp' => $last_dp['deposito']));
$ver_pag = $query_ver_pag->rowCount();
?>

<!doctype html>
<html lang="pt-pt">
    <?php include_once('head_user.php'); ?>
    <body id="normal">
        <div class="content">
            <header>
                <div id="header_1">
                    <section class="left-column">
                        <div id="panel_home" class="panel">
                            <a href="<?php
                            if ($_SESSION['tipo_grupo'] == "Bolsa")
                                echo "pag_banco.php";
                            else
                                echo "pag_user.php";
                            ?>">
                                <div class="icon-home"></div>
                            </a>
                        </div>
						<?php if ($_SESSION['tipo_grupo'] == "Bolsa") { ?>
							<!-- -- > <div id="header" class="panel">
								<a href="?lingua=pt" id="pt"><img src="images/pt.gif" alt="" width="35" height="25"></a>
								<a href="?lingua=en" id="en"><img src="images/en.jpg" alt="" width="35" height="25"></a>
							</div> <!-- -->
						<?php } ?>
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
                            <a href='terminar_sessao.php'><div class="icon-off"></div></a>
                        </div>
                    </section>
                </div>
                <div id="header_2">
                    <section class="left-column"><div class="logo_banco"><img src="<?php echo $linha_imagem['path_imagem']; ?>"></div></section>
                    <section class="center-column">&nbsp;</section>
                    <section class="right-column">
                        <div class="dados_conta">
                            <label for="num_conta"><?php echo $lingua['ACCOUNT']; ?></label>
                            <input name="num_conta" type="text" value='<?php echo $num_conta; ?>' readonly="readonly" class="inputDadosTrans">
                        </div>
                        <div class="dados_conta">
                            <label for="saldo"><?php echo $lingua['BALANCE']; ?></label>
                            <input name="saldo" type="text" value='<?php echo number_format($saldo, 2, ',', '.'); ?> <?php echo $linha_moeda['simbolo']; ?>' readonly="readonly" class="inputDadosTrans">
                        </div>
                    </section>
                </div>
            </header>
            <section id="conteudo">
                <div id="var_content"></div>
            </section>
        </div>
        <!-- Menu do banco -->
        <div id="menu">
            <nav>
                <h2><i class="fa fa-reorder"></i>Menu</h2>
                <ul>
                    <li>
                        <a href="#"><?php echo $lingua['EVERYDAY']; ?></a>
                        <h2><?php echo $lingua['EVERYDAY']; ?></h2>
                        <ul>
                            <li>
                                <a href="#"><?php echo $lingua['ACCOUNTS']; ?></a>
                                <h2><?php echo $lingua['ACCOUNTS']; ?></h2>
                                <ul>
                                    <!--<li><a href="movimentos"><?php echo $lingua['BALANCE_M']; ?></a></li>
                                    <!--<li><a href="#"><?php echo $lingua['STATEMENT']; ?></a></li>-->
                                    
                                    <li>
                                        <a href="#"><?php echo $lingua['ORDER_ACCOUNT'] ?></a>
                                        <h2><?php echo $lingua['ORDER_ACCOUNT'] ?></h2>
                                            <ul>
                                                <li><a href="nib"><?php echo $lingua['NIB']; ?></a></li>
                                                <li><a href="movimentos"><?php echo $lingua['BALANCE_M']; ?></a></li>
                                            </ul>
                                    </li>
                                    

                                    
                                    <!-- Durante periodo de testes de CP -->
                                    <?php //if ($_SESSION['id_utilizador']=="24") { ?>
                                    <!-- -->
                                    
                                    <li>
                                        <a href="#"><?php echo $lingua['DEPOSIT_ACCOUNT']; ?></a>
                                        <h2><?php echo $lingua['DEPOSIT_ACCOUNT']; ?></h2>
                                        <ul>
                                            <!-- Se não tiver nenhuma CP ou se tvr e já foi concluido, permite criar -->
                                            <?php if($ver_contaP == 0 || $ver_pag == 1) { ?>
                                                <li><a href="criarcontaprazo"><?php echo $lingua['CREATE']; echo " "; echo lcfirst($lingua['DEPOSIT_ACCOUNT']); ?></a></li>
                                            
                                            <!-- Se já houver CP e ainda não foi "fechada", permite consultar info -->
                                            <?php } elseif ($ver_contaP == 1 && $ver_pag == 0) { ?>
                                                <li><a href="nib_contaprazo"><?php echo $lingua['NIB']; ?></a></li>
                                                <li><a href="mov_contaprazo"><?php echo $lingua['BALANCE_M']; ?></a></li>
                                                <li><a href="plano_deposito_prazo"><?php echo $lingua['SAV_PROD']; ?></a></li>
                                            
                                            <!-- Se não houver juros para pagar e CP ainda não foi "fechada" permite Renovar/Terminar -->
                                            <?php if ($ver_juros == 0) { ?>
                                                <li>
                                                    <a href="#"><?php echo $lingua['RENOVATE']; echo "/"; echo $lingua['TERMINATE']; echo " "; echo lcfirst($lingua['DEPOSIT_ACCOUNT']); ?></a>
                                                    <h2><?php echo $lingua['RENOVATE']; echo "/"; echo $lingua['TERMINATE']; echo " "; echo lcfirst($lingua['DEPOSIT_ACCOUNT']); ?></h2>
                                                    <ul>
                                                        <li><a href="terminardp"><?php echo $lingua['TERMINATE']; ?></a></li>
                                                        <li><a href="renovardp"><?php echo $lingua['RENOVATE']; ?></a></li>
                                                    </ul>
                                                </li>
                                            <?php }
                                            } ?>
                                            <!-- // -->
                                        </ul>
                                    </li>
                                    
                                    <!-- Durante periodo de testes de CP -->
                                    <?php //} ?>
                                    <!-- -->
                                    

                                </ul>
                            </li>
                            <?php if ($_SESSION['tipo_grupo'] != "Bolsa") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['TRANSFERS']; ?></a>
                                    <h2><?php echo $lingua['TRANSFERS']; ?></h2>
                                    <ul>
                                        <li><a href="transferencia"><?php echo $lingua['B_TRANS']; ?></a></li>
                                        <!--<li><a href="#"><?php echo $lingua['A_B_TRANS']; ?></a></li>-->
                                        <!--<li><a href="#"><?php echo $lingua['V_S_TRANS']; ?></a></li>-->
                                        <li><a href="transf_receb"><?php echo $lingua['V_CREDITS']; ?></a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#"><?php echo $lingua['PAYMENTS']; ?></a>
                                    <h2><?php echo $lingua['PAYMENTS']; ?></h2>
                                    <ul>
                                        <li><a href="pag_prest"><?php echo $lingua['B_PAYMENTS']; ?></a></li>
                                        <li><a href="pag_div"><?php echo $lingua['M_PAYMENTS']; ?></a></li>
                                        <!--<li><a href="#"><?php echo $lingua['SCHE_OP']; ?></a></li>-->
                                        
										<!-- MOVED to pag_user.php
                                        <li>
                                            <a href="#"><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['TO']; echo " "; echo $lingua['SUPPLIER']; ?></a>
                                            <h2><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['TO']; echo " "; echo $lingua['SUPPLIER']; ?></h2>
                                            <ul>
                                                <li><a href="adiant_fornec"><?php echo $lingua['MAKE']; echo " "; echo lcfirst($lingua['ADVANCE']); ?></a></li>
                                                <li><a href="adiant_efet"><?php echo $lingua['ADVANCE']; echo " "; echo $lingua['MADE']; ?></a></li>
                                            </ul>
                                        </li>
										-->
                                    
                                    </ul>
                                </li>
                                
                                <!-- -->
                                <li>
                                    <a href="#"><?php echo $lingua['RECEIPTS']; ?></a>
                                    <h2><?php echo $lingua['RECEIPTS']; ?></h2>
                                    <ul>
                                        <li><a href="recebimentos"><?php echo $lingua['RECEIPTS']; ?></a></li>
                                        <li><a href="adiant_receb"><?php echo $lingua['ADVANCE']; echo " "; echo lcfirst($lingua['RECEIVED']); ?></a></li>
                                    </ul>
                                </li>
                                <!-- -->
                                
                            <?php } ?>
                        </ul>
                    </li>

                        <li>
                            <a href="#"><?php echo $lingua['CREDIT']; ?></a>
                            <h2><?php echo $lingua['CREDIT']; ?></h2>
                            <ul>
                                <li>
                                    <a href="#"><?php echo $lingua['CREDIT']; ?></a>
                                    <h2><?php echo $lingua['CREDIT']; ?></h2>
                                    <ul>
                                        <li><a href="credito"><?php echo $lingua['P_C_REQ']; ?></a></li>
                                        <li><a href="cons_cred"><?php echo $lingua['CHECK_CREDIT']; ?></a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#"><?php echo $lingua['LEASING']; ?></a>
                                    <h2><?php echo $lingua['LEASING']; ?></h2>
                                    <ul>
                                        <li><a href="leasing_pedido"><?php echo $lingua['L_REQ']; ?></a></li>
                                        <li><a href="cons_leasing"><?php echo $lingua['C_LEASING']; ?></a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#"><?php echo $lingua['FACTORING']; ?></a>
                                    <h2><?php echo $lingua['FACTORING']; ?></h2>
                                    <ul>
                                        <li><a href="novo_factoring"><?php echo $lingua['F_REQ']; ?></a></li>
                                        <li><a href="consulta_factoring"><?php echo $lingua['C_FACTORING']; ?></a></li>
                                    </ul>
                                </li>
                                
                                <!-- -->
                                <li>
                                    <a href="#"><?php echo $lingua['T_CREDIT']; ; ?></a>
                                    <h2><?php echo $lingua['T_CREDIT']; ?></h2>
                                    <ul>
                                        <li>
                                            <a href="#"><?php echo $lingua['B_DISC']; ?></a>
                                            <h2><?php echo $lingua['B_DISC']; ?></h2>
                                            <ul>
                                                <li><a href="desconto_letra"><?php echo $lingua['DRAWER']; ?></a></li>
                                                <li><a href="aceite_letra"><?php echo $lingua['ACEPTOR']; ?></a></li>
                                                <li><a href="carteira_letra"><?php echo $lingua['T_CREDIT_PORTFOLIO']; ?></a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <!-- -->
                                
                            </ul>
                        </li>
                    <li>
                        <a href="#"><?php echo "Exemplo"; ?></a>
                        <h2><?php echo "Exemplo"; ?></h2>
                        <ul>
                            <li>
                            <a href="exemplo"><?php echo "Exemplo 1"; ?></a>
                            </li>
                            </ul>

                            </li>

                    <li>
                        <a href="#"><?php echo $lingua['SAV_INV']; ?></a>
                        <h2><?php echo $lingua['SAV_INV']; ?></h2>
                        <ul>
                            <!--<li>
                                <a href="#"><?php echo $lingua['SAV_PROD']; ?></a>
                                <h2><?php echo $lingua['SAV_PROD']; ?></h2>
                                <ul>
                                    <li><a href="#"><?php echo $lingua['SAVINGS']; ?></a></li>
                                    <li><a href="#"><?php echo $lingua['M_SAVINGS']; ?></a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#"><?php echo $lingua['INVESTMENT']; ?></a>
                                <h2><?php echo $lingua['INVESTMENT']; ?></h2>
                                <ul>
                                    <li><a href="#"><?php echo $lingua['INVESTMENT']; ?></a></li>
                                </ul>
                            </li>-->
                            <li>
                                <a href="#"><?php echo $lingua['M_SHARES']; ?></a>
                                <h2><?php echo $lingua['M_SHARES']; ?></h2>
                                <ul>
                                    <li><a href="acoes"><?php echo $lingua['M_INFORMATION']; ?></a></li>
                                    <li><a href="carteira_titulos"><?php echo $lingua['P_VIEW']; ?></a></li>
									<!-- -->
                                    <li>
                                        <a href="#"><?php echo $lingua['SCHE_OP']; ?></a>
                                        <h2><?php echo $lingua['SCHE_OP']; ?></h2>
                                        <ul>
                                            <li><a href="compras_agendadas"><?php echo $lingua['SCH_B']; ?></a></li>
                                            <li><a href="vendas_agendadas"><?php echo $lingua['SCH_S']; ?></a></li>
                                        </ul>
                                    </li>
                                    <!-- -->
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <?php if ($_SESSION['tipo_grupo'] == "Bolsa") { ?>
                        <li><a href="email"><?php echo $lingua['EMAIL']; ?></a></li>
                        <li><a href="media">Media</a></li>
                    <?php } ?>
                    <!-- <li>
                        <a href="#"><?php echo $lingua['INSURANCE']; ?></a>
                        <h2><?php echo $lingua['INSURANCE']; ?></h2>
                        <ul>
                            <li><a href="#"><?php echo $lingua['V_INSURANCE']; ?></a></li>
                            <li><a href="#"><?php echo $lingua['L_INSURANCE']; ?></a></li>
                            <li><a href="#"><?php echo $lingua['P_INSURANCE']; ?></a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><?php echo $lingua['O_OPERA']; ?></a>
                        <h2><?php echo $lingua['O_OPERA']; ?></h2>
                        <ul>
                            <li><a href="#"><?php echo $lingua['REFUND']; ?></a></li>
                        </ul>
                    </li>-->
                </ul>
            </nav>
        </div>
        <!-- Fim do menu do banco -->
    </body>
</html>
<?php
$connection = null;
