<?php
/*
 * @Author: Ricardo Órfão
 * @Date:   2014-05-04 13:22:07
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-18 15:46:06
 */

include('./conf/check_admin.php');
include_once('./conf/common.php');
include_once('./functions/functions.php');

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id INNER JOIN utilizador u ON ent.id=u.id_entidade WHERE u.id=:id_utilizador LIMIT 1");
$query_moeda->execute(array(':id_utilizador' => $_SESSION['id_utilizador']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_validar_insc = $connection->prepare("SELECT ent.valido FROM utilizador u INNER JOIN entidade ent ON u.id_entidade=ent.id WHERE u.id=:id");
$query_validar_insc->execute(array(':id' => $_SESSION['id_utilizador']));
$linha_insc = $query_validar_insc->fetch(PDO::FETCH_ASSOC);

if ($linha_insc['valido'] != '0') {
    $checked = "checked";
} else {
    $checked = "";
}

//Numero de alertas de hoje
$query_alertas_hoje = $connection->prepare("SELECT * FROM alerta a WHERE DATE(date_reg)=CURDATE()");
$query_alertas_hoje->execute();
$query_alertas_hoje_num = $query_alertas_hoje->rowCount();

//Numero de alertas de ontem
$query_alertas_ontem = $connection->prepare("SELECT * FROM alerta a WHERE DATE(date_reg)=DATE_ADD(CURDATE(), INTERVAL -1 DAY)");
$query_alertas_ontem->execute();
$query_alertas_ontem_num= $query_alertas_ontem->rowCount();

//Numero de alertas de outros dias
$query_alertas_outros = $connection->prepare("SELECT * FROM alerta a WHERE DATE(date_reg)<>CURDATE() AND DATE(date_reg)<>DATE_ADD(CURDATE(), INTERVAL -1 DAY)");
$query_alertas_outros->execute();
$query_alertas_outros_num = $query_alertas_outros->rowCount();
?>
<!doctype html>
<html lang="pt-pt">
    <?php include_once('head_admin.php'); ?>
    <body>
        <div class="content left">
            <header>
                <div class="panel" style="height: 40px; width: 12%; position: absolute">
                    <a href="pag_admin.php" style="display: inline-block; margin-left: 2%; padding-top: 4%">
                        <span class="icon-home"></span>
                    </a>
                    <a href="?lingua=pt" id="pt" style="display: inline-block">
                        <img src="images/pt.gif" alt="" width="35" height="25" style="margin-left: 45%; padding-bottom: 40%">
                    </a>
                    <a href="?lingua=en" id="en" style="display: inline-block">
                        <img src="images/en.jpg" alt="" width="35" height="25" style="padding-bottom: 40%; margin-left: 55%">
                    </a>
                </div>
                <div>
                    <img id="logo_simemp" src="./images/logo_med.png">
                </div>
                <div class="panel">
                    <a href='terminar_sessao.php'>
                        <span class="icon-off"></span>
                    </a>
                </div>
            </header>
            <div id="var_content" class="var_content left">
                <?php if ($_SESSION['admin'] == "1") { ?>
                    <div class="divSwitchInscricoes">
                        <div class="celula">
                            <label for="chkInscValidas">Inscrições</label>
                        </div>
                        <div class="celula">
                            <div class="onoffswitch">
                                <input id="chkInscValidas" name="chkInscValidas" type="checkbox" class="onoffswitch-checkbox" <?php echo $checked; ?>>
                                <label class="onoffswitch-label" for="chkInscValidas">
                                    <div class="onoffswitch-inner"></div>
                                    <div class="onoffswitch-switch"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php } else if ($_SESSION['admin'] == "0") { //alertas notificações ?>
                <div id="center_div">
                            <div id="dados_alerta">
                                <div class="linha" style="margin-bottom: 10px;">
                                    <h3 style="font-size: 1.5em; font-weight: bold;">Alertas </h3>
                                </div>
                                <div class="linha" style="margin-bottom: 2px;">
                                    <div class="linha" style="margin-bottom: 5px;">
                                        <a href="#"  style="text-decoration: none; color: black;"><span style="cursor: pointer;">Alertas de hoje (<?php echo $query_alertas_hoje_num; ?>)</span></a>
                                    </div>
                                    <div class="linha" style="margin-bottom: 5px;">
                                        <a href="#"  style="text-decoration: none; color: black;"><span style="cursor: pointer;">Alertas de ontem (<?php echo $query_alertas_ontem_num; ?>)</span></a>
                                    </div>
                                    
                                    <div class="linha" style="margin-bottom: 5px;">
                                        <a href="#"  style="text-decoration: none; color: black;"><span style="cursor: pointer;">Alertas outros dias (<?php echo $query_alertas_outros_num; ?>)</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php }?>
                <div id="chartdiv" style="width: 100%; min-height: 400px"></div>
            </div>
        </div>
        <div id="menu">
            <nav>
                <h2><i class="fa fa-reorder"></i>Menu</h2>
                <ul>
                    <li>
                        <a href="#"><?php echo $lingua['DATA_CENTRAL']; ?></a>
                        <h2><?php echo $lingua['DATA_CENTRAL']; ?></h2>
                        <ul>
                            <?php if ($_SESSION['admin'] == "0") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['CMPNIES']; ?></a>
                                    <h2><?php echo $lingua['CMPNIES']; ?></h2>
                                    <ul>
                                        <li><a href="del_empresas"><?php echo $lingua['DEL'].' '.lcfirst($lingua['CMPNIES']); ?></a></li>
                                        <li><a href="v_empresas"><?php echo $lingua['EDT'].' '.lcfirst($lingua['CMPNY']); ?></a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#"><?php echo $lingua['GRP'].'s'; ?></a>
                                    <h2><?php echo $lingua['GRP'].'s'; ?></h2>
                                    <ul>
                                        <li><a href="grupo_afet_pag"><?php echo $lingua['AFFTTION']; ?></a></li>
                                        <li><a href="grupo_edit_pag"><?php echo $lingua['EDT']; ?></a></li>
                                        <li><a href="grupo_novo_pag"><?php echo $lingua['NEW']; ?></a></li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($_SESSION['admin'] == "1") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['ACTS']; ?></a>
                                    <h2><?php echo $lingua['ACTS']; ?></h2>
                                    <ul>
                                        <li><a href="e_atividade"><?php echo $lingua['EDT']; ?></a></li>
                                        <li><a href="n_atividade"><?php echo $lingua['NEW']; ?></a></li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="#"><?php echo $lingua['USR']; ?></a>
                                <h2><?php echo $lingua['USR']; ?></h2>
                                <ul>
                                    <?php if ($_SESSION['admin'] == "0") { ?>
                                        <li><a href="user_afet_pag"><?php echo $lingua['AFFTTION']; ?></a></li>
                                    <?php } ?>
                                    <li><a href="v_users"><?php echo $lingua['SEE'].'/'.$lingua['EDT']; ?></a></li>
                                    <?php if ($_SESSION['admin'] == "1") { ?>
                                        <li><a href="novo_admin"><?php echo $lingua['NEW'].' '.lcfirst($lingua['ADM']); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php if ($_SESSION['admin'] == "0") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['V_CLND']; ?></a>
                                    <h2><?php echo $lingua['V_CLND']; ?></h2>
                                    <ul>
                                        <li><a href="def_cal"><?php echo $lingua['DEF'].' '.lcfirst($lingua['CLND']); ?></a></li>
                                        <li><a href="e_cal"><?php echo $lingua['EDT'].' '.lcfirst($lingua['CLND']); ?></a></li>
                                        <!-- <li><a href="imp_cal">Importar calendário</a></li> -->
                                    </ul>
                                </li>
								
								<li>
                                    <a href="#"><?php echo $lingua['CLND']; ?> de tarefas</a>
                                    <h2><?php echo $lingua['CLND']; ?> de tarefas</h2>
                                    <ul>
                                        <li><a href="def_tasks_cal"><?php echo $lingua['DEF'].' '.lcfirst($lingua['CLND']); ?></a></li>
                                        <li><a href="e_tasks_cal"><?php echo $lingua['EDT'].' '.lcfirst($lingua['CLND']); ?></a></li>
                                        <!-- <li><a href="imp_cal">Importar calendário</a></li> -->
                                    </ul>
                                </li>
								
                            <?php } ?>
                            <?php if ($_SESSION['ldap'] == "0") { ?>
                                <li>
                                    <a href="mod_pass"><?php echo $lingua['CH_PASS']; ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><?php echo $lingua['PUBLIC_CENTRAL']; ?></a>
                        <h2><?php echo $lingua['PUBLIC_CENTRAL']; ?></h2>
                        <ul>
                            <?php if ($_SESSION['admin'] == "0") { ?>
                                <li><a href="v_entregas"><?php echo $lingua['V_DELIVERY']; ?></a></li>
                            <?php } ?>
                            <?php if ($_SESSION['admin'] == "1") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['TYPE'].'s '.$lingua['OF'].' '.lcfirst($lingua['DLVR']); ?></a>
                                    <h2><?php echo $lingua['TYPE'].'s '.$lingua['OF'].' '.lcfirst($lingua['DLVR']); ?></h2>
                                    <ul>
                                        <li><a href="e_tipos"><?php echo $lingua['EDT']; ?></a></li>
                                        <li><a href="n_tipo"><?php echo $lingua['NEW']; ?></a></li>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>

                    <?php if ($_SESSION['admin'] == "0") { ?>
                        <li>
                            <a href="#"><?php echo $lingua['FINANCIAL_CORE']; ?></a>
                            <h2><?php echo $lingua['FINANCIAL_CORE']; ?></h2>
                            <ul>
                                <li><a href="emprestimos"><?php echo $lingua['LNS']; ?></a></li>
                                <li><a href="extratos"><?php echo $lingua['STATEMENT']; ?></a></li>
                                <li><a href="locacoes"><?php echo $lingua['LSNG']; ?></a></li>
                                <li><a href="taxas"><?php echo $lingua['RLS']; ?></a></li>
                                <li><a href="titulos_banc"><?php echo $lingua['TIT']; ?></a></li>
                                <li><a href="calc_acoes">Ranking <?php echo $lingua['OF'].' '.lcfirst($lingua['TIT']); ?></a></li>
                                <li><a href="outras_op"><?php echo $lingua['O_OPERA']; ?></a></li>
                                <li><a href="alertas"><?php echo $lingua['ALERT'] ?></a></li> <!--meti esta-->
                            </ul>
                        </li>
                    <?php } ?>

                    <li>
                        <a href="#"><?php echo $lingua['ORDER']; ?></a>
                        <h2><?php echo $lingua['ORDER']; ?></h2>
                        <ul>
                            <li>
                                <a href="#"><?php echo $lingua['CATS']; ?></a>
                                <h2><?php echo $lingua['CATS']; ?></h2>
                                <ul>
                                    <?php if ($_SESSION['admin'] == "1") { ?>
                                        <li><a href="add_familias"><?php echo $lingua['ADD'].' '.lcfirst($lingua['CAT']); ?></a></li>
                                        <li><a href="asc_familias"><?php echo $lingua['AFFT'].' '.lcfirst($lingua['CATS']); ?></a></li>
                                        <li><a href="e_familias"><?php echo $lingua['EDT'].' '.lcfirst($lingua['CAT']); ?></a></li>
                                    <?php } ?>
                                    <li><a href="afet_familias"><?php echo $lingua['SEE'].' '.lcfirst($lingua['AFFTTION']).' '.$lingua['OF'].' '.lcfirst($lingua['CATS']); ?></a></li>
                                </ul>
                            </li>
                            <?php if ($_SESSION['admin'] == "1") { ?>
                                <li>
                                    <a href="#"><?php echo $lingua['PROD'].'s'; ?></a>
                                    <h2><?php echo $lingua['PROD'].'s'; ?></h2>
                                    <ul>
                                        <li><a href="add_produtos"><?php echo $lingua['ADD'].' '.lcfirst($lingua['PROD']); ?></a></li>
                                        <li><a href="e_produtos"><?php echo $lingua['EDT'].' '.lcfirst($lingua['PROD']); ?></a></li>
                                        <li><a href="afet_produtos"><?php echo $lingua['AFFT'].' '.lcfirst($lingua['PROD']).' '.$lingua['TO'].' '.lcfirst($lingua['SUPPLIER']); ?></a></li>
                                        <li>
                                            <a href="#"><?php echo $lingua['DSC'].'s'; ?></a>
                                            <h2><?php echo $lingua['DSC'].'s'; ?></h2>
                                            <ul>
                                                <li><a href="add_desc"><?php echo $lingua['ADD']; ?></a></li>
                                                <li><a href="edit_desc"><?php echo $lingua['EDT']; ?></a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <li><a href="v_faturas"><?php echo $lingua['SEE'].' '.lcfirst($lingua['FAT']).'s'; ?></a></li>
                        </ul>
                    </li>

                    <?php if ($_SESSION['admin'] == "0") { ?>
                        <li>
                            <a href="pag_email_admin.php"><?php echo $lingua['EMAIL']; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
    </body>
</html>