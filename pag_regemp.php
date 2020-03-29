<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-30 17:20:14
*/

include('./conf/checkregisto.php');

$query_atividade = $connection->prepare("SELECT id, designacao, capital_social_monetario FROM atividade ORDER BY designacao");
$query_atividade->execute();

$query_banco = $connection->prepare("SELECT b.id, b.nome FROM banco b INNER JOIN entidade_banco eb ON b.id=eb.id_banco INNER JOIN entidade ent ON eb.id_entidade=ent.id WHERE ent.id=:id_entidade LIMIT 1");
$query_banco->execute(array(':id_entidade' => $_SESSION['id_entidade']));
$linha_banco = $query_banco->fetch(PDO::FETCH_ASSOC);

$query_moeda = $connection->prepare("SELECT mo.simbolo FROM moeda mo INNER JOIN entidade ent ON ent.id_moeda=mo.id WHERE ent.id=:id_entidade LIMIT 1");
$query_moeda->execute(array(':id_entidade' => $_SESSION['id_entidade']));
$linha_moeda = $query_moeda->fetch(PDO::FETCH_ASSOC);

$query_empresa = $connection->prepare("SELECT DISTINCT emp.id_empresa, emp.nome FROM empresa emp INNER JOIN utilizador u ON emp.id_empresa=u.id_empresa INNER JOIN entidade ent ON u.id_entidade=ent.id WHERE emp.ativo='1' AND emp.id_empresa <> 1 && emp.id_empresa <> 2 && emp.id_empresa <> 3 && ent.id=:id_entidade");
$query_empresa->execute(array(':id_entidade' => $_SESSION['id_entidade']));

$query_tipo = $connection->prepare("SELECT id, tipo FROM tipo_empresa");
$query_tipo->execute();

$query_tipo_grupo = $connection->prepare("SELECT tg.designacao FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo = tg.id WHERE g.id = :id_grupo");
$query_tipo_grupo->execute(array(':id_grupo' => $_SESSION['id_grupo']));
$linha_tipo_grupo = $query_tipo_grupo->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-pt">
    <?php include_once('head_registo.php'); ?>
    <body>
        <div class="outer">
            <div class="middle">
                <div class="inner">
                    <div class="title">
                        <h1>Registo de Empresa</h1>
                    </div>
                    <div class="niveis big">
                        <div class="divInactiva left"><div class="nivelinactiveicon icon-user"></div></div>
                        <div class="divActiva left"><div class="nivelactiveicon icon-company"></div></div>
                    </div>
                    <div id="divTipoRegEmp" class="tipo_empresa">
                        <div class="tipo left nova_emp">
                            <label for="radNovaEmp"></label>
                            <input id="radNovaEmp" name="empresa" type="radio" checked="checked" value="divNovaEnt">
                            <img src="images/newcomp.jpg" alt="Nova empresa">
                        </div>
						<div class="tipo left emp_exist">
							<!-- Limitar o registo a empresas existentes só para Entidade IPB (solução provisória) -->
							<?php if ($_SESSION['id_entidade'] == 1) { ?>
								<label for="radEmpExi"></label>
								<input id="radEmpExi" name="empresa" type="radio" value="divJuntarEnt">
							<?php } ?>
							<!-- -->
							<img src="images/existcomp_uns.jpg" alt="Empresa existente">
						</div>
                    </div>
                    <div class="content left">
                        <div id="divNovaEnt" class="desc">
                            <form id="frmRegistoEmpresa" name="frmRegistoEmpresa">
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon small" style="font-family: arial;font-weight: bold;">NISS</div>
                                    </div>
                                    <div class="inputarea_alone left small">
                                        <span id="txtNNiss" name="txtNNiss" class="textoReadonly width100">Nº de Identif. de Segurança Social</span>
                                    </div>
                                    <button id="btnGenNISS" class="botaoIcon" type="button">Gerar</button>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon small" style="font-family: arial;font-weight: bold;">NIPC</div>
                                    </div>
                                    <div class="inputarea_alone left small">
                                        <span id="txtNNipc" name="txtNNipc" class="textoReadonly width100">Nº de Identif. de Pessoa Coletiva</span>
                                    </div>
                                    <button id="btnGenNIPC" class="botaoIcon" type="button">Gerar</button>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-tag"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <input id="txtNNomeEmp" name="txtNNomeEmp" type="text" class="width100" placeholder="Designação">
                                    </div>
                                </div>
                                <div class="linha special left" style="height: 38px">&nbsp;</div>
                                <div class="linha left">
                                    <select id="slcNTipoEmpresa" name="slcNTipoEmpresa" class="chosen-select" data-placeholder="Tipo de empresa">
                                        <option value="0" selected="selected"></option>
                                        <?php while ($linha_tipo = $query_tipo->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option value=<?php echo $linha_tipo['id'] ?>><?php echo $linha_tipo["tipo"] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="linha left">
                                    <select id="slcNAtividadeEmp" name="slcNAtividadeEmp" class="chosen-select" data-placeholder="Atividade">
                                        <option value="0" selected="selected"></option>
                                        <?php
                                        if($linha_tipo_grupo['designacao'] == "Bolsa")
                                            $query_atividade2 = $connection->prepare("SELECT id, designacao, capital_social_monetario FROM atividade WHERE designacao = 'Bolsa'");
                                        else
                                            $query_atividade2 = $connection->prepare("SELECT id, designacao, capital_social_monetario FROM atividade ORDER BY designacao");
                                        
                                        $query_atividade2->execute();
                                        while ($linha_atividade2 = $query_atividade2->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <option value=<?php echo $linha_atividade2['id'] ?>><?php echo $linha_atividade2['designacao'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="linha left money">
                                    <div class="inputarea_col left middle small">
                                        <input id="txtNCapSocM" name="txtNCapSocM" type="text" class="dinheiro" placeholder="Capital social monetário">
                                        <div class="mnyLabel right">
                                            <span name="txtMoeda" class="textoReadonly width100"><?php echo $linha_moeda['simbolo'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="linha left money">
                                    <div class="inputarea_col left middle small">
                                        <input id="txtNCapSocE" name="txtNCapSocE" type="text" class="dinheiro" placeholder="Capital social em espécie">
                                        <div class="mnyLabel right">
                                            <span name="txtMoeda" class="textoReadonly width100"><?php echo $linha_moeda['simbolo'] ?></span>
                                        </div>
                                    </div>
                                    <input id="hddNCapSocO" name="hddNCapSocO" type="hidden" value="">
                                </div>
                                <div class="linha width100 left">
                                    <div class="iconwrapper iconwrappergrande left">
                                        <div class="novolabelicon normal icon-morada"></div>
                                    </div>
                                    <div class="inputarea_big left small">
                                        <input id="txtNMoradaEmp" name="txtNMoradaEmp" type="text" class="width100" placeholder="Morada">
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-local"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <input id="txtNCodPostal" name="txtNCodPostal" type="text" class="width100" placeholder="Código Postal">
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <input id="txtNLocalidade" name="txtNLocalidade" type="text" class="width100" placeholder="Localidade">
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-mail"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <span id="txtNEmailEmp" name="txtNEmailEmp" class="textoReadonly width100">Correio eletrónico</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <!-- Não faz mto sentido ser possivel escolher o pais, visto que a moeda é determinada pela entidade. Poderá gerar conflitos com a nova funcionalidade "produtos estrangeiros".
                                        Por exemplo, uma empresa de EUA, pertencente a uma entidade q negoceia em €, qdo compra produtos dos EUA, o valor será convertido de € para $, o que é incorrecto. -->
                                        <input id="txtNPais" name="txtNPais" type="text" class="width100" placeholder="País">
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-banco"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <span class="textoReadonly width100"><?php echo $linha_banco['nome']; ?></span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <?php
                                        $query_grupo = $connection->prepare("SELECT g.nome FROM grupo g WHERE g.id=:id_grupo");
                                        $query_grupo->execute(array(':id_grupo' => $_SESSION['id_grupo']));
                                        $linha_grupo = $query_grupo->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <span class="textoReadonly width100"><?php echo $linha_grupo['nome'] ?></span>
                                        <input id="hddIdGrupo" name="hddIdGrupo" type="hidden" value="<?php echo $_SESSION['id_grupo'] ?>">
                                    </div>
                                </div>
                            </form>
                            <div class="botoes left">
                                <button id="btnRegEmp" name="btnRegEmp" type="submit" class="botao icon-ok">Registar</button>
                                <button id="btnSairReg" name="btnSairReg" class="botaoIcon icon-sair sair"></button>
                            </div>
                        </div>
                        <div id="divJuntarEnt" class="desc">
                            <form id="frmJuntarEmpresa" name="frmJuntarEmpresa">
                                <div class="linha left">
                                    <select id="txtNomeJEmp" name="txtNomeJEmp" class="chosen-select" data-placeholder="Empresa">
                                        <option value="0" selected="selected"></option>
                                        <?php
                                        while ($linha_empresa = $query_empresa->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <option value=<?php echo $linha_empresa['id_empresa'] ?>><?php echo $linha_empresa['nome'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="linha special left" style="height: 38px">&nbsp;</div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon small" style="font-family: arial;font-weight: bold;">NISS</div>
                                    </div>
                                    <div class="inputarea_col wbutton left small">
                                        <span id="txtJNiss" name="txtJNiss" class="textoReadonly width100">Nº de Identif. de Segurança Social</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon small" style="font-family: arial;font-weight: bold;">NIPC</div>
                                    </div>
                                    <div class="inputarea_col wbutton left small">
                                        <span id="txtJNipc" name="txtJNipc" class="textoReadonly width100">Nº de Identif. de Pessoa Coletiva</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJTipo" name="txtJTipo" class="textoReadonly width100">Tipo de empresa</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJAtividade" name="txtJAtividade" class="textoReadonly width100">Atividade</span>
                                    </div>
                                </div>
                                <div class="linha left money">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJCapSocM" name="txtJCapSocM" class="textoReadonly dinheiro">Capital social monetário</span>
                                        <div class="mnyLabel right">
                                            <span name="txtMoeda" class="textoReadonly width100"><?php echo $linha_moeda['simbolo'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="linha left money">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJCapSocE" name="txtJCapSocE" class="textoReadonly dinheiro">Capital social em espécie</span>
                                        <div class="mnyLabel right">
                                            <span name="txtMoeda" class="textoReadonly width100"><?php echo $linha_moeda['simbolo'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="linha width100 left">
                                    <div class="iconwrapper iconwrappergrande left">
                                        <div class="novolabelicon normal icon-morada"></div>
                                    </div>
                                    <div class="inputarea_big left small">
                                        <span id="txtJMorada" name="txtJMorada" class="textoReadonly width100">Morada</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-local"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <span id="txtJCodPostal" name="txtJCodPostal" class="textoReadonly width100">Código postal</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJLocalidade" name="txtJLocalidade" class="textoReadonly width100">Localidade</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-mail"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <span id="txtJEmail" name="txtJEmail" class="textoReadonly width100">Correio eletrónico</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJPais" name="txtJPais" class="textoReadonly width100">País</span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-banco"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <span id="txtJBanco" name="txtJBanco" class="textoReadonly width100"><?php echo $linha_banco['nome']; ?></span>
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="inputarea_col left middle small">
                                        <span id="txtJGrupo" name="txtJGrupo" class="textoReadonly width100">Grupo</span>
                                    </div>
                                </div>
                            </form>
                            <div class="botoes left">
                                <button id="btnJuntarEmp" name="btnJuntarEmp"class="botao icon-ok">Registar</button>
                                <button id="btnSairReg" name="btnSairReg" class="botaoIcon icon-sair sair"></button>
                            </div>
                        </div>
                    </div>
                    <div id="error" class="error left"></div>
                </div>
            </div>
        </div>
    </body>
</html>