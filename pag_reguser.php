<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-30 17:14:20
*/

include('./conf/checkregisto.php');

function carregar_grupo($connection) {
    // $query_grupo = $connection->prepare("SELECT DISTINCT g.id, g.nome FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN entidade ent ON ent.id=u.id_entidade WHERE ent.id=:id_entidade");
    // $query_grupo = $connection->prepare("SELECT q1.id, q2.nome, q2.estado FROM (SELECT DISTINCT g.id FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN entidade ent ON ent.id=u.id_entidade WHERE ent.id=:id_entidade ORDER BY g.id) AS q1 INNER JOIN (SELECT DISTINCT g.id, g.nome, eg.estado, eg.date_reg FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN entidade ent ON ent.id=u.id_entidade WHERE ent.id=:id_entidade1 ORDER BY g.id ASC, eg.date_reg DESC) AS q2 ON q1.id=q2.id GROUP BY q1.id ASC ORDER BY q2.nome ASC");
	
	$query_grupo = $connection->prepare("SELECT q1.id, q2.nome, q2.estado FROM (SELECT DISTINCT g.id FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN entidade ent ON ent.id=u.id_entidade WHERE ent.id=:id_entidade) AS q1 LEFT JOIN (SELECT DISTINCT g.id, g.nome, eg.estado, eg.date_reg FROM grupo g INNER JOIN estado_grupo eg ON g.id=eg.id_grupo INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN entidade ent ON ent.id=u.id_entidade WHERE ent.id=:id_entidade1) AS q2 ON q1.id=q2.id ORDER BY q2.nome ASC, q2.date_reg DESC"); //pedente
	$query_grupo->execute(array(':id_entidade' => $_SESSION['id_entidade'], 'id_entidade1' => $_SESSION['id_entidade']));
    return $query_grupo;
}

$datetime = new DateTime();
?>
<!doctype html>
<html lang="pt-pt">
    <?php include_once('head_registo.php'); ?>
    <body>
        <div class="outer">
            <div class="middle">
                <div class="inner">
                    <form id="frmRegisto" name="frmRegisto">
                        <div class="title">
                            <h1>Registo de utilizador</h1>
                        </div>
                        <div class="niveis big">
                            <div class="divActiva left"><div class="nivelactiveicon icon-user"></div></div>
                            <div class="divInactiva left"><div class="nivelinactiveicon icon-company"></div></div>
                        </div>
                        <div class="content left">
                            <div class="linha left">
                                <div class="iconwrapper left">
                                    <div class="novolabelicon normal icon-user"></div>
                                </div>
                                <div class="inputarea_col left small">
                                    <input id="txtUsername" name="txtUsername" type="text" class="width100" placeholder="Login">
                                </div>
                            </div>
                            <div class="linha left">
                                <div class="iconwrapper left">
                                    <div class="novolabelicon normal icon-pass"></div>
                                </div>
                                <div class="inputarea_col left small">
                                    <input id="txtPassword" name="txtPassword" type="password" class="width100" placeholder="Palavra-passe">
                                </div>
                            </div>
                            <?php if ($_SESSION['ldap'] == "0") { ?>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-user"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <input id="txtCompleteName" name="txtCompleteName" type="text" class="width100" placeholder="Nome completo">
                                    </div>
                                </div>
                                <div class="linha left">
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-pass"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <input id="txtConfPassword" name="txtConfPassword" type="password" class="width100" placeholder="Confirmação de palavra-passe">
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="linha left">
                                <?php if ($_SESSION['ldap'] == "0") { ?>
                                    <div class="iconwrapper left">
                                        <div class="novolabelicon normal icon-mail"></div>
                                    </div>
                                    <div class="inputarea_col left small">
                                        <input id="txtEmail" name="txtEmail" type="text" class="width100" placeholder="Correio eletrónico">
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="linha left">
                                <div class="iconwrapper left">
                                    <div class="novolabelicon normal icon-group"></div>
                                </div>
                                <select id="slcGrupoReg" name="slcGrupoReg" class="chosen-select" data-placeholder="Grupo">
                                    <option selected="selected" value="0"></option>
                                    <?php
                                    $grupo = carregar_grupo($connection);
									$id_grupo = "";
                                    while ($row = $grupo->fetch(PDO::FETCH_ASSOC)) { 
										if ($id_grupo != $row['id'] && $row['estado'] == '1') { ?>
											<option value="<?php echo $row['id']; ?>"><?php echo $row['nome']; ?></option>
                                    <?php } 
									$id_grupo = $row['id'];
									} ?>
									
                                </select>
                            </div>
                            
                            <!-- NoCAPTCHA reCAPTCHA -->
                            <div class="linha left" style="margin-left: 55%;">
                                <!--<div class="g-recaptcha" data-sitekey="6Leo8BATAAAAAEfI-EnQN-psHKB-irCxDjQl2Ts9"></div>--> <!--estava esta (para funcionar no site)-->
                                <div class="g-recaptcha" data-sitekey="6LcME8wUAAAAANvZUU37W4W4NEADvn-Hf7U7wHcf"></div> <!--meti esta(para funcionar em localhost)-->
                            </div>
                            
                            <div class="botoes left">
                                <button id="btnRegUser" name="btnRegUser" class="botao icon-ok">Registar</button>
                                <button id="btnUserSair" name="btnUserSair" class="botaoIcon icon-sair"></button>
                            </div>
                        </div>
                    </form>
                    <div class="error left"></div>
                </div>
            </div>
        </div>
    </body>
</html>