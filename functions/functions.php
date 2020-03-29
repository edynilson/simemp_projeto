<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-02 18:38:18
*/

define('ENCRYPTION_KEY', 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282');

/*
include_once('../phpfastcache/phpfastcache.php');
phpFastCache::setup('path', '/tmp');
phpFastCache::setup('securityKey', 'SimEmp');
/* */

function ucwords_pt($str) {
    $return = '';
    $palavras = explode(" ", $str);
    foreach ($palavras as $palavra) {
        $tamanho_palavra = strlen($palavra);
        $primeiro_caracter = mb_substr($palavra, 0, 1, 'UTF-8');
        if ($primeiro_caracter == 'Ç' or $primeiro_caracter == 'ç') {
            $primeiro_caracter = 'Ç';
        } elseif ($primeiro_caracter == 'Á' or $primeiro_caracter == 'á') {
            $primeiro_caracter = 'Á';
        } elseif ($primeiro_caracter == 'É' or $primeiro_caracter == 'é') {
            $primeiro_caracter = 'É';
        } elseif ($primeiro_caracter == 'Í' or $primeiro_caracter == 'í') {
            $primeiro_caracter = 'Í';
        } elseif ($primeiro_caracter == 'Ó' or $primeiro_caracter == 'ó') {
            $primeiro_caracter = 'Ó';
        } elseif ($primeiro_caracter == 'Ú' or $primeiro_caracter == 'ú') {
            $primeiro_caracter = 'Ú';
        } elseif ($primeiro_caracter == 'À' or $primeiro_caracter == 'à') {
            $primeiro_caracter = 'À';
        } elseif ($primeiro_caracter == 'Â' or $primeiro_caracter == 'â') {
            $primeiro_caracter = 'Â';
        } elseif ($primeiro_caracter == 'Ê' or $primeiro_caracter == 'ê') {
            $primeiro_caracter = 'Ê';
        } elseif ($primeiro_caracter == 'Ô' or $primeiro_caracter == 'ô') {
            $primeiro_caracter = 'Ô';
        } elseif ($primeiro_caracter == 'Ã' or $primeiro_caracter == 'ã') {
            $primeiro_caracter = 'Ã';
        } elseif ($primeiro_caracter == 'Õ' or $primeiro_caracter == 'õ') {
            $primeiro_caracter = 'Õ';
        } else {
            $primeiro_caracter = strtoupper($primeiro_caracter);
        }
        $outras = mb_substr($palavra, 1, $tamanho_palavra, 'UTF-8');
        $return.=$primeiro_caracter . tudo_minusculas($outras) . ' ';
    }

    $resultado = trim(str_replace('  ', ' ', $return));
    return $resultado;
}

function tudo_minusculas($str) {

    $str = str_replace('Ç', 'ç', $str);
    $str = str_replace('Á', 'á', $str);
    $str = str_replace('É', 'é', $str);
    $str = str_replace('Í', 'í', $str);
    $str = str_replace('Ó', 'ó', $str);
    $str = str_replace('Ú', 'ú', $str);
    $str = str_replace('À', 'à', $str);
    $str = str_replace('Â', 'â', $str);
    $str = str_replace('Ê', 'ê', $str);
    $str = str_replace('Ô', 'ô', $str);
    $str = str_replace('Ã', 'ã', $str);
    $str = str_replace('Õ', 'õ', $str);
    $str = mb_strtolower($str, 'UTF-8');

    return $str;
}

function retiraEsp($str) {
    $str = str_replace('Á', 'a', $str);
    $str = str_replace('á', 'a', $str);
    $str = str_replace('Ã', 'a', $str);
    $str = str_replace('ã', 'a', $str);
    $str = str_replace('À', 'a', $str);
    $str = str_replace('à', 'a', $str);
    $str = str_replace('Â', 'a', $str);
    $str = str_replace('â', 'a', $str);
    $str = str_replace('É', 'e', $str);
    $str = str_replace('é', 'e', $str);
    $str = str_replace('Ê', 'e', $str);
    $str = str_replace('ê', 'e', $str);
    $str = str_replace('Í', 'i', $str);
    $str = str_replace('í', 'i', $str);
    $str = str_replace('Ó', 'o', $str);
    $str = str_replace('ó', 'o', $str);
    $str = str_replace('Ô', 'o', $str);
    $str = str_replace('ô', 'o', $str);
    $str = str_replace('Õ', 'o', $str);
    $str = str_replace('õ', 'o', $str);
    $str = str_replace('Ú', 'u', $str);
    $str = str_replace('ú', 'u', $str);
    $str = str_replace('Ç', 'c', $str);
    $str = str_replace('ç', 'c', $str);
    $str = strtolower($str);

    return $str;
    unset($str);
}

function gerarIBAN($country, $codigo_banco, $codigo_balcao) {
    $pais = $country;
    $cod_banco = $codigo_banco;
    $cod_balcao = $codigo_balcao;

    $Dig1 = rand(0, 9);
    $Dig2 = rand(0, 9);
    $Dig3 = rand(0, 9);
    $Dig4 = rand(0, 9);
    $Dig5 = rand(0, 9);
    $Dig6 = rand(0, 9);
    $Dig7 = rand(0, 9);
    $Dig8 = rand(0, 9);
    $Dig9 = rand(0, 9);
    $Dig10 = rand(0, 9);
    $Dig11 = rand(0, 9);
    if ($Dig1 >= 10 || $Dig2 >= 10 || $Dig3 >= 10 || $Dig4 >= 10 || $Dig5 >= 10 || $Dig6 >= 10 || $Dig7 >= 10 || $Dig8 >= 10 || $Dig9 >= 10 || $Dig10 >= 10 || $Dig11 >= 10) {
        $Dig1 = 0;
        $Dig2 = 0;
        $Dig3 = 0;
        $Dig4 = 0;
        $Dig5 = 0;
        $Dig6 = 0;
        $Dig7 = 0;
        $Dig8 = 0;
        $Dig9 = 0;
        $Dig10 = 0;
        $Dig11 = 0;
    }
    $num_conta = $Dig1 . $Dig2 . $Dig3 . $Dig4 . $Dig5 . $Dig6 . $Dig7 . $Dig8 . $Dig9 . $Dig10 . $Dig11;

    $check = "00";
    $num = $cod_banco . $cod_balcao . $num_conta . $check;
    $checkDigit = 98 - (bcmod($num, "97"));

    if ($checkDigit < 10)
        $checkDigit = "0" . $checkDigit;

    $nib = $cod_banco . $cod_balcao . "_" . $num_conta . "_" . $checkDigit;
    $iban = $pais . "_" . $nib;

    return $iban;
    unset($Dig1, $Dig2, $Dig3, $Dig4, $Dig5, $Dig6, $Dig7, $Dig8, $Dig9, $Dig10, $Dig11, $num_conta, $check, $num, $checkDigit, $nib, $iban);
}

function gerarNISS() {

    $Dig1 = "2";
    $Dig2 = rand(0, 9);
    $Dig3 = rand(0, 9);
    $Dig4 = rand(0, 9);
    $Dig5 = rand(0, 9);
    $Dig6 = rand(0, 9);
    $Dig7 = rand(0, 9);
    $Dig8 = rand(0, 9);
    $Dig9 = rand(0, 9);
    $Dig10 = rand(0, 9);
    if ($Dig1 >= 10 || $Dig2 >= 10 || $Dig3 >= 10 || $Dig4 >= 10 || $Dig5 >= 10 || $Dig6 >= 10 || $Dig7 >= 10 || $Dig8 >= 10 || $Dig9 >= 10 || $Dig10 >= 10) {
        $Dig1 = 0;
        $Dig2 = 0;
        $Dig3 = 0;
        $Dig4 = 0;
        $Dig5 = 0;
        $Dig6 = 0;
        $Dig7 = 0;
        $Dig8 = 0;
        $Dig9 = 0;
        $Dig10 = 0;
    }
    $num = $Dig1 . $Dig2 . $Dig3 . $Dig4 . $Dig5 . $Dig6 . $Dig7 . $Dig8 . $Dig9 . $Dig10;
    $soma = (($Dig1 * 29) + ($Dig2 * 23) + ($Dig3 * 19) + ($Dig4 * 17) + ($Dig5 * 13) + ($Dig6 * 11) + ($Dig7 * 7) + ($Dig8 * 5) + ($Dig9 * 3) + ($Dig10 * 2));

    $checkDigit = 9 - (bcmod($soma, "10"));
    if ($checkDigit >= 10)
        $checkDigit = 0;
    $niss = $num . $checkDigit;

    return $niss;
    unset($Dig1, $Dig2, $Dig3, $Dig4, $Dig5, $Dig6, $Dig7, $Dig8, $Dig9, $Dig10, $num, $soma, $checkDigit, $niss);
}

function gerarNIPC() {
    $Dig1 = "5";
    $Dig2 = rand(0, 9);
    $Dig3 = rand(0, 9);
    $Dig4 = rand(0, 9);
    $Dig5 = rand(0, 9);
    $Dig6 = rand(0, 9);
    $Dig7 = rand(0, 9);
    $Dig8 = rand(0, 9);

    if ($Dig1 >= 10 || $Dig2 >= 10 || $Dig3 >= 10 || $Dig4 >= 10 || $Dig5 >= 10 || $Dig6 >= 10 || $Dig7 >= 10 || $Dig8 >= 10) {
        $Dig1 = 0;
        $Dig2 = 0;
        $Dig3 = 0;
        $Dig4 = 0;
        $Dig5 = 0;
        $Dig6 = 0;
        $Dig7 = 0;
        $Dig8 = 0;
    }
    $num = $Dig1 . $Dig2 . $Dig3 . $Dig4 . $Dig5 . $Dig6 . $Dig7 . $Dig8;
    $soma = (($Dig1 * 9) + ($Dig2 * 8) + ($Dig3 * 7) + ($Dig4 * 6) + ($Dig5 * 5) + ($Dig6 * 4) + ($Dig7 * 3) + ($Dig8 * 2));

    $checkDigit = 11 - (bcmod($soma, "11"));
    if ($checkDigit >= 10)
        $checkDigit = 0;
    $nipc = $num . $checkDigit;

    return $nipc;
    unset($Dig1, $Dig2, $Dig3, $Dig4, $Dig5, $Dig6, $Dig7, $Dig8, $num, $soma, $checkDigit, $nipc);
}

function time_diff($dt1, $dt2) {
    $y1 = substr($dt1, 0, 4);
    $m1 = substr($dt1, 5, 2);
    $d1 = substr($dt1, 8, 2);
    $h1 = substr($dt1, 11, 2);
    $i1 = substr($dt1, 14, 2);
    $s1 = substr($dt1, 17, 2);

    $y2 = substr($dt2, 0, 4);
    $m2 = substr($dt2, 5, 2);
    $d2 = substr($dt2, 8, 2);
    $h2 = substr($dt2, 11, 2);
    $i2 = substr($dt2, 14, 2);
    $s2 = substr($dt2, 17, 2);

    $r1 = date('U', mktime($h1, $i1, $s1, $m1, $d1, (int) $y1));
    $r2 = date('U', mktime($h2, $i2, $s2, $m2, $d2, (int) $y2));
    return ($r1 - $r2);
    unset($y1, $m1, $d1, $h1, $i1, $s1, $y2, $m2, $d2, $h2, $i2, $s2, $r1, $r2);
}

function conv_mes($num) {
    switch ($num) {
        case 1:
            $mes = 'Janeiro';
            break;
        case 2:
            $mes = 'Fevereiro';
            break;
        case 3:
            $mes = 'Março';
            break;
        case 4:
            $mes = 'Abril';
            break;
        case 5:
            $mes = 'Maio';
            break;
        case 6:
            $mes = 'Junho';
            break;
        case 7:
            $mes = 'Julho';
            break;
        case 8:
            $mes = 'Agosto';
            break;
        case 9:
            $mes = 'Setembro';
            break;
        case 10:
            $mes = 'Outubro';
            break;
        case 11:
            $mes = 'Novembro';
            break;
        case 12:
            $mes = 'Dezembro';
            break;
    }
    return $mes;
}

function dataVirtual($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_real) {
    $agora = date("Y-m-d H:i:s", strtotime($data_real));
    $diferenca_datas = time_diff($data_fim, $data_inicio);
    $diff_agr = time_diff($agora, $data_inicio);
    $factor = $diff_agr / $diferenca_datas;
    $distancia = time_diff("$ano-$mes-$ultimo_dia 23:59:59", "$ano-$mes-$primeiro_dia 00:00:00");
    $tempo_referencia = strtotime("$ano-$mes-01 00:00:00");
    $data_virtual = ($factor * $distancia) + $tempo_referencia;
    $arr = date("Y-m-d H:i:s", $data_virtual);
    return $arr;
}

function dataReal($primeiro_dia, $ultimo_dia, $mes, $ano, $data_inicio, $data_fim, $data_virtual) {
    $agora_r = date("Y-m-d H:i:s", strtotime($data_virtual));
    $diferenca_datas_r = time_diff("$ano-$mes-$ultimo_dia 23:59:59", "$ano-$mes-$primeiro_dia 00:00:00");
    $diff_agr_r = time_diff($agora_r, "$ano-$mes-$primeiro_dia 00:00:00");
    $factor = $diff_agr_r / $diferenca_datas_r;
    $distancia_r = time_diff($data_fim, $data_inicio);
    $tempo_referencia_r = strtotime($data_inicio);
    $data_real = ($factor * $distancia_r) + $tempo_referencia_r;
    $arr_r = date("Y-m-d H:i:s", $data_real);
    return $arr_r;
}

function geraRef($data, $nome, $num_enc) {
    $vogais = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");
    $minhaString = str_replace($vogais, "", $nome);
    $result = mb_substr(strtoupper($minhaString), 0, 3);
    $ref = $result;
    $ref .= $data->format('YmdHis');
    $ref .= $num_enc;
    return $ref;
}

function carregar_cat($connection) {
    $query_categorias = $connection->prepare("SELECT id, designacao FROM familia WHERE parent IS NULL ORDER BY designacao");
    $query_categorias->execute();
    return $query_categorias;
}

function carregar_grupo($connection, $tipo, $id_user) {
    // $query_grupo = $connection->prepare("SELECT * FROM (SELECT * FROM (SELECT g.id, g.nome, tg.id AS id_tipo, tg.designacao AS tipo FROM utilizador u INNER JOIN user_grupo ug ON u.id=ug.id_user INNER JOIN grupo g ON ug.id_grupo=g.id INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE u.tipo=:tipo AND u.id=:id_utilizador) AS grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado FROM estado_grupo eg ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado ON grupos.id=estado.id_grupo GROUP BY estado.id_grupo ORDER BY grupos.nome ASC) AS grupos_active WHERE grupos_active.estado='1'");
    // $query_grupo->execute(array(':tipo' => $tipo, ':id_utilizador' => $id_user));
    
    $query_adm = $connection->prepare("SELECT admin FROM utilizador WHERE id=:id_utilizador LIMIT 1");
    $query_adm->execute(array(':id_utilizador' => $id_user));
    $reg_usr = $query_adm->fetch(PDO::FETCH_ASSOC);
    
    if ($reg_usr['admin'] == 0) {
//        $query_grupo = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, g.id_tipo, tp.designacao, last_estado_grupos.estado FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC");//Comentei esta pois estava a dar problemas na opção "Utilizadores"
        $query_grupo = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, g.id_tipo, tp.designacao, last_estado_grupos.estado FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id WHERE last_estado_grupos.estado='1' AND ug.id_user=:id_utilizador ORDER BY g.nome ASC"); //Meti esta com a query atualizada
        $query_grupo->execute(array(':id_utilizador' => $id_user));
        
    } elseif ($reg_usr['admin'] == 1) {
//        $query_grupo = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, g.id_tipo, tp.designacao, last_estado_grupos.estado FROM (SELECT * FROM (SELECT * FROM estado_grupo ORDER BY id_grupo ASC, date_reg DESC) AS estados GROUP BY estados.id_grupo) AS last_estado_grupos INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE last_estado_grupos.estado='1' AND u.parent=:id_utilizador ORDER BY g.nome ASC");//estava esta
        $query_grupo = $connection->prepare("SELECT last_estado_grupos.id_grupo, g.nome, g.id_tipo, tp.designacao, last_estado_grupos.estado FROM estado_grupo last_estado_grupos JOIN (SELECT id_grupo, MAX(date_reg) AS max_date FROM estado_grupo GROUP BY id_grupo )  t1 ON t1.id_grupo=last_estado_grupos.id_grupo AND t1.max_date=last_estado_grupos.date_reg INNER JOIN grupo g ON last_estado_grupos.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN tipo_grupo tp ON g.id_tipo=tp.id INNER JOIN utilizador u ON ug.id_user=u.id WHERE last_estado_grupos.estado='1' AND u.parent=:id_utilizador ORDER BY g.nome ASC");//meti esta
        $query_grupo->execute(array(':id_utilizador' => $id_user));
        
    } else {
        include('./terminar_sessao.php');
    }
    
    return $query_grupo;
}

//-- Carrega Utilizadores e Empresas associadas, apenas de GRUPOS ATIVOS
function carregar_users($connection, $tipo, $id_user) {
    /*
    $query_utilizadores = $connection->prepare("SELECT users.id, users.nome_user AS nome_user, users.id_empresa, users.nome, users.u_ldap FROM (SELECT DISTINCT u.id, u.tipo, g.id AS id_grupo FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON u.id=ug.id_user) AS adm INNER JOIN (SELECT u.id, u.login AS nome_user, u.u_ldap, emp.id_empresa, emp.nome, g.id AS id_grupo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id) AS users ON adm.id_grupo=users.id_grupo WHERE adm.tipo=:tipo AND adm.id=:id_utilizador ORDER BY users.nome_user");
    $query_utilizadores->execute(array(':tipo' => $tipo, ':id_utilizador' => $id_user));
    return $query_utilizadores;
    */
    
    $query_grps_active = carregar_grupo($connection, $tipo, $id_user);
    $grps_active = $query_grps_active->fetchAll();
    $id_grps_active = "";
    foreach ($grps_active AS $active) {
        $id_grps_active .= $id_grps_active == "" ? $active['id_grupo'] : ", ".$active['id_grupo'];
    }
    
    $query_utilizadores = $connection->prepare("SELECT users.id, users.nome_user AS nome_user, users.id_empresa, users.nome, users.u_ldap FROM (SELECT DISTINCT u.id, u.tipo, g.id AS id_grupo FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON u.id=ug.id_user) AS adm INNER JOIN (SELECT u.id, u.login AS nome_user, u.u_ldap, emp.id_empresa, emp.nome, g.id AS id_grupo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id) AS users ON adm.id_grupo=users.id_grupo WHERE adm.tipo=:tipo AND adm.id=:id_utilizador AND users.id_grupo IN ($id_grps_active) ORDER BY users.nome_user");
    $query_utilizadores->execute(array(':tipo' => $tipo, ':id_utilizador' => $id_user));
    return $query_utilizadores;
}

function carregar_atividade($connection) {
    $query_atividade = $connection->prepare("SELECT a.id, a.designacao, a.capital_social_monetario FROM atividade a ORDER BY designacao");
    $query_atividade->execute();
    return $query_atividade;
}

function carregar_empresa($connection, $tipo, $id_user) {
    $query_empresa = $connection->prepare("SELECT DISTINCT users.id_empresa, users.nome FROM (SELECT DISTINCT u.id, u.tipo, g.id AS id_grupo FROM grupo g INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON u.id=ug.id_user) AS adm INNER JOIN (SELECT emp.id_empresa, emp.nome, g.id AS id_grupo FROM utilizador u INNER JOIN empresa emp ON u.id_empresa=emp.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id WHERE emp.ativo='1') AS users ON adm.id_grupo=users.id_grupo WHERE adm.tipo=:tipo AND adm.id=:id_utilizador ORDER BY users.nome");
    $query_empresa->execute(array(':tipo' => $tipo, ':id_utilizador' => $id_user));
    return $query_empresa;
}

function carregar_taxa($connection, $criterio) {
    $query_taxa = $connection->prepare("SELECT r.id_regra, r.nome_regra, r.valor, r.simbolo FROM regra r WHERE r.nome_regra LIKE :criterio GROUP BY r.id_regra ORDER BY r.valor");
    $query_taxa->execute(array(':criterio' => $criterio));
    return $query_taxa;
}

function decode_ip($int_ip)
{
    $hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
    return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

function encode_ip($ip) {
    $d = explode('.', $ip);
    if (count($d) == 4)
        return sprintf('%02x%02x%02x%02x', $d[0], $d[1], $d[2], $d[3]);
    $d = explode(':', preg_replace('/(^:)|(:$)/', '', $ip));
    $res = '';
    foreach ($d as $x)
        $res .= sprintf('%0'. ($x == '' ? (9 - count($d)) * 4 : 4) .'s', $x);
    return $res;
}

function guacamole_url($base_url, $conn_id, $hostname, $protocol, $secret, $username, $password) {
    $guac_base_url = $base_url;
    $guac_conn_id = $conn_id;
    $guac_hostname = $hostname;
    $guac_protocol = $protocol;
    $guac_username = $username;
    $guac_password = $password;
    $guac_secret = $secret;
    $guac_port = '0';
    $timestamp = time() * 1000;

    if ($guac_protocol == 'rdp')
        $guac_port = '3389';
    if ($guac_protocol == 'vnc')
        $guac_port = '5900';
    if ($guac_protocol == 'ssh')
        $guac_port = '22';

    $signature_concatenate = $timestamp . $guac_protocol . 'username' . $guac_username . 'password' . $guac_password . 'hostname' . $guac_hostname . 'port' . $guac_port;
    $signature_encode = base64_encode(hash_hmac('sha1', $signature_concatenate, $guac_secret, 1));
    $guacamole_url = $guac_base_url . '?id=c/' . $guac_conn_id . '&guac.protocol=' . $guac_protocol . '&guac.hostname=' . $guac_hostname . '&guac.port=' . $guac_port . '&guac.username=' . $guac_username . '&guac.password=' . $guac_password . '&amp;timestamp=' . $timestamp . '&signature=' . $signature_encode;
    return $guacamole_url;
}

function mc_encrypt($encrypt, $key) {
    $encrypt = serialize($encrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*', $key);
    $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt . $mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passcrypt) . '|' . base64_encode($iv);
    return $encoded;
}

function mc_decrypt($decrypt, $key) {
    $decrypt = explode('|', $decrypt);
    $decoded = base64_decode($decrypt[0]);
    $iv = base64_decode($decrypt[1]);
    if (strlen($iv) !== mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
        return false;
    }
    $key = pack('H*', $key);
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if ($calcmac !== $mac) {
        return false;
    }
    $decrypted = unserialize($decrypted);
    return $decrypted;
}

function logClicks($connection, $id_operacao) {
    $query_op = $connection->prepare("INSERT INTO sessao_operacao (id_sessao, id_operacao, data) VALUES (:id_sessao, :id_operacao, NOW())");
    $query_op->execute(array(':id_sessao' => $_SESSION['sessao'], ':id_operacao' => $id_operacao));
}

function atualiza_saldo($connection, $id_conta, $tipo, $descricao, $description, $deb, $cre, $data_virt, $saldo_controlo, $saldo_contab, $saldo_disp, $valor) {
    $valor_f = number_format($valor, 2, '.', '');
    $deb_f = number_format($deb, 2, '.', '');
    $cre_f = number_format($cre, 2, '.', '');
    
    if ($cre_f > 0) {
        $saldo_controlo = number_format($saldo_controlo, 2, '.', '') + $valor_f;
        $saldo_contab = number_format($saldo_contab, 2, '.', '') + $valor_f;
        $saldo_disp = number_format($saldo_disp, 2, '.', '') + $valor_f;
    } else {
        $saldo_controlo = number_format($saldo_controlo, 2, '.', '') - $valor_f;
        $saldo_contab = number_format($saldo_contab, 2, '.', '') - $valor_f;
        $saldo_disp = number_format($saldo_disp, 2, '.', '') - $valor_f;
    }
    
    $query_insert_mov = $connection->prepare("INSERT INTO movimento (id_conta, data_op, tipo, descricao, description, debito, credito, saldo_controlo, saldo_contab, saldo_disp) VALUES (:id_conta, :data_virt, :tipo, :descricao, :description, :debito, :credito, :saldo_controlo, :saldo_contab, :saldo_disp)");
    $query_insert_mov->execute(array(':id_conta' => $id_conta, ':data_virt' => $data_virt, ':tipo' => $tipo, ':descricao' => $descricao, ':description' => $description, ':debito' => $deb_f, ':credito' => $cre_f, ':saldo_controlo' => $saldo_controlo, ':saldo_contab' => $saldo_contab, ':saldo_disp' => $saldo_disp));
}

/* Construir data_virtual apartir de info em cache, em vez de ligar à BD a cada segundo */
function data_virtual_db($connection) {
    // $cache = phpFastCache();
    $cache = phpFastCache\CacheManager::getInstance('files');
	
    $query_emp_grup = $connection->prepare("SELECT e.id_empresa, g.id FROM empresa e INNER JOIN grupo g ON e.id_grupo=g.id WHERE e.ativo='1' ORDER BY e.id_empresa ASC");
    $query_emp_grup->execute();
    while ($linha_emp_grup = $query_emp_grup->fetch(PDO::FETCH_ASSOC)) {
        $id_empresa = $linha_emp_grup['id_empresa'];
        $arr_dados_emp_grup[$id_empresa] = array('id_grupo' => $linha_emp_grup['id']);
    }
    // $cache->set('calend_emp_grup', $arr_dados_emp_grup, 86400); // 24hrs
    $key1 = 'calend_emp_grup';
    $cache->deleteItem($key1);
	$newCachedString1 = $cache->getItem($key1);
    $newCachedString1->set($arr_dados_emp_grup)->expiresAfter(86400);
    $cache->save($newCachedString1);

    $query_grup_cal = $connection->prepare("SELECT c.id_grupo, c.mes, c.ano, c.data_inicio, c.hora_inicio, c.data_fim, c.hora_fim FROM calendario c WHERE NOW() <= CONCAT(c.data_fim, ' ', c.hora_fim)");
    $query_grup_cal->execute();
    if ($query_grup_cal->rowCount() > 0) {
        while ($linha_grup_cal = $query_grup_cal->fetch(PDO::FETCH_ASSOC)) {
            $arr_dados_grup_cal[] = array('id_grupo' => $linha_grup_cal['id_grupo'], 'mes' => $linha_grup_cal['mes'], 'ano' => $linha_grup_cal['ano'], 'data_inicio' => $linha_grup_cal['data_inicio'], 'hora_inicio' => $linha_grup_cal['hora_inicio'], 'data_fim' => $linha_grup_cal['data_fim'], 'hora_fim' => $linha_grup_cal['hora_fim']);
        }
        // $cache->set('calend_grup', $arr_dados_grup_cal, 86400);
        $key2 = 'calend_grup';
		$cache->deleteItem($key2);
        $newCachedString2 = $cache->getItem($key2);
        $newCachedString2->set($arr_dados_grup_cal)->expiresAfter(86400);
        $cache->save($newCachedString2);
    }
	
	// phpFastCache\CacheManager::clearInstances();
	return $arr_dados_emp_grup;
}

function data_virtual_cache($connection, $id_empresa) {
    // $cache = phpFastCache();
    // $cache_info_emp = $cache->get('calend_emp_grup');
    // $cache_cal_grup = $cache->get('calend_grup');
    //
    
	$cache = phpFastCache\CacheManager::getInstance('files');
    $key1 = 'calend_emp_grup';
    $CachedString1 = $cache->getItem($key1);
    $cache_info_emp = $CachedString1->get();
    $key2 = 'calend_grup';
    $CachedString2 = $cache->getItem($key2);
    $cache_cal_grup = $CachedString2->get();
    
    $dateTime = new DateTime();
    $now = $dateTime->format('Y-m-d H:i:s');
    
    if ($cache_cal_grup == null) {
        // date_default_timezone_set('Europe/London');
        // $arr = array('sucesso' => true, 'mensagem' => date("m/d/Y H:i:s"));
        data_virtual_db($connection);
        data_virtual_cache($connection, $id_empresa);
        return false;
		
    } else {
        foreach ($cache_cal_grup as $value) {
            $inicio = date('Y-m-d H:i:s', strtotime("$value[data_inicio] $value[hora_inicio]"));
            $fim = date('Y-m-d H:i:s', strtotime("$value[data_fim] $value[hora_fim]"));
            
			//-- Se info de Empresa não existe no array que guarda Grupo da Empresa, executa função que atualiza array
			if ($cache_info_emp == null || array_key_exists($id_empresa, $cache_info_emp) == false) {
				$cache_info_emp = data_virtual_db($connection);
			}
			
            if ($value['id_grupo'] == $cache_info_emp[$id_empresa]['id_grupo'] && $now >= $inicio && $now <= $fim) {
                $mes = $value['mes'];
                $ano = $value['ano'];
                $primeiro_dia = date('d', mktime(0, 0, 0, $mes, 1, $ano));
                $ultimo_dia = date('t', mktime(0, 0, 0, $mes, 1, $ano));
                $data_inicio = $value['data_inicio'] . " " . $value['hora_inicio'];
                $data_fim = $value['data_fim'] . " " . $value['hora_fim'];
                $agora = date("Y-m-d H:i:s");
                $diferenca_datas = time_diff($data_fim, $data_inicio);
                $diff_agr = time_diff($agora, $data_inicio);
                $factor = $diff_agr / $diferenca_datas;
                $distancia = time_diff(date("Y-m-d H:i:s", strtotime("$ano-$mes-$ultimo_dia 23:59:59")), date("Y-m-d H:i:s", strtotime("$ano-$mes-$primeiro_dia 00:00:00")));
                $tempo_referencia = strtotime(date("$ano-$mes-01 00:00:00"));
                $data_virtual = ($factor * $distancia) + $tempo_referencia;
                $arr = array('sucesso' => true, 'mensagem' => date("m/d/Y H:i:s", $data_virtual));
                break;
            } else {
				date_default_timezone_set('Europe/London');
                $arr = array('sucesso' => true, 'mensagem' => date("m/d/Y H:i:s"));
            }
        }
    }
	
	// phpFastCache\CacheManager::clearInstances();
	return $arr;
}

/* FUNCTIONS TO ESCAPE OPERATOR AND EXECUTE QUERY WITH OPERATOR AS A VARIABLE. USED IN funcoes_admin.php  */
function getOperator($operator) {
   $allowed_ops = array('=', '<', '>');
   return in_array($operator, $allowed_ops) ? $operator : false;
}

function loadby($connection, $opr_r, $id_regra, $opr_emp, $id_empresa, $opr_gr, $id_grupo) { 
    if(getOperator($opr_r) && getOperator($opr_emp) && getOperator($opr_gr)) {
        $query_taxas = $connection->prepare("SELECT g.id, g.nome, emp.id_empresa, r.id_regra, emp.nome AS empresa, b.id AS id_banco, b.nome AS `banco`, r.nome_regra, date_format(re1.`data`, '%d-%m-%Y') AS `data`, re1.valor, re1.simbolo FROM empresa emp INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN regra_empresa re1 ON emp.id_empresa=re1.id_empresa LEFT JOIN regra_empresa re2 ON (re1.id_regra = re2.id_regra AND re1.id_empresa = re2.id_empresa AND re1.date_reg < re2.date_reg) INNER JOIN regra r ON re1.id_regra=r.id_regra INNER JOIN banco b ON b.id=re1.id_banco WHERE emp.ativo='1' AND re2.id_regra IS NULL AND re2.id_empresa IS NULL AND u.tipo=:tipo AND u.id=:id_utilizador AND r.id_regra ".$opr_r." :id_regra AND emp.id_empresa ".$opr_emp." :id_empresa AND g.id ".$opr_gr." :id_grupo ORDER BY emp.nome, r.nome_regra");
        $query_taxas->execute(array(':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_regra' => $id_regra, ':id_empresa' => $id_empresa, ':id_grupo' => $id_grupo));
        return $query_taxas;
    } else {
        return false;
    }
}
/* */