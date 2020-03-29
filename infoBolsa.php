<?php

include('./conf/check.php');
include_once('./conf/common.php');
include_once('head_infoBolsa.php');

include_once('./phpfastcache/src/autoload.php');
use phpFastCache\CacheManager;
CacheManager::setDefaultConfig([
  'path' => '/tmp',
  'securityKey' => 'SimEmp'
]);


function carrega_cotacao_cache() {
    $cache = CacheManager::getInstance('files');
    
    /* ABSOLETO
    $arr_dados = $cache->get('nome_acoes');
    echo $arr_dados;
    */
    
    /* */
    // print "Path: " . $cache->getPath() . "\n";
    // print "Cache stats: \n"; print_r($cache->driver_stats());

    $key1 = 'cotacao_acoes';
	// $key1 = 'archive_cotacoes';
    $CachedString1 = $cache->getItem($key1);
    $arr_dados = $CachedString1->get();

    if ($arr_dados != null) {
        echo '<div class="linha">';
        echo '<table class="tabela">';
        echo '<tr>';
		echo '<td style="padding: 4px;">#</td>';
        echo '<td style="padding: 4px;">ID Ação</td>';
        echo '<td style="padding: 4px;">Nome Bolsa</td>';
        echo '<td style="padding: 4px;">Nome</td>';
        echo '<td style="padding: 4px;">Empresa</td>';
        echo '<td style="padding: 4px;">Último preço</td>';
        echo '<td style="padding: 4px;">Variação</td>';
        echo '<td style="padding: 4px;">Abertura</td>';
        echo '<td style="padding: 4px;">Min / Máx</td>';
		echo '<td style="padding: 4px;">Volume</td>';
        echo '<td style="padding: 4px;">Hora</td>';
        echo '</tr>';

        foreach ($arr_dados as $value) {
            echo '<tr>';
			echo '<td>' . ++$key . '</td>';
            echo '<td>' . $value['id_acao'] . '</td>';
            echo '<td>' . $value['nome_bolsa'] . '</td>';
            echo '<td>' . $value['nome_acao'] . '</td>';
            echo '<td>' . $value['nome_empresa'] . '</td>';
            echo '<td>' . $value['last_trade_price'] . '</td>';
            echo '<td>' . $value['change'] . '</td>';
            echo '<td>' . $value['open'] . '</td>';
            echo '<td>' . $value['days_low'] . '/' . $value['days_high'] . '</td>';
			echo '<td>' . $value['volume'] . '</td>';
            echo '<td>' . $value['last_trade_time'] . '</td>';
            echo '</tr>';
        }
		
        echo '</table>';
        echo '</div>';
    } else {
        echo "ERRO: Cache vazia!";
    }
    /* */
}

function carrega_cache_info_acoes() {
    $cache = CacheManager::getInstance('files');
    
    /* */
    // print "Path: " . $cache->getPath() . "\n";
    // print "Cache stats: \n"; print_r($cache->driver_stats());

    $key1 = 'cache_info_acoes';
    $CachedString1 = $cache->getItem($key1);
    $arr_dados = $CachedString1->get();

    if ($arr_dados != null) {
        echo '<div class="linha">';
        echo '<table class="tabela">';
        echo '<tr>';
        echo '<td style="padding: 4px;">ID País</td>';
        echo '<td style="padding: 4px;">País</td>';
        echo '<td style="padding: 4px;">Cód. Moeda</td>';
        echo '<td style="padding: 4px;">ID Bolsa</td>';
        echo '<td style="padding: 4px;">Nome Bolsa</td>';
        echo '<td style="padding: 4px;">ID Ação</td>';
        echo '<td style="padding: 4px;">Nome</td>';
        echo '<td style="padding: 4px;">Empresa</td>';
        echo '</tr>';

        foreach ($arr_dados as $pos => $value) {
            echo '<tr>';
            echo '<td>' . $value['id_pais'] . '</td>';
            echo '<td>' . $value['nome_pais'] . '</td>';
            echo '<td>' . $value['moeda'] . '</td>';
            echo '<td>' . $value['id_bolsa'] . '</td>';
            echo '<td>' . $value['nome_bolsa'] . '</td>';
            echo '<td>' . $value['id_acao'] . '</td>';
            echo '<td>' . $pos . '</td>';
            echo '<td>' . $value['nome_empresa'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    } else {
        echo "ERRO: Cache vazia!";
    }
    /* */
}

/*
function carrega_cache_preco_alvo() {
    $cache = phpFastCache();
    
    print "Path: " . $cache->getPath() . "\n";
    // print "Cache stats: \n"; print_r($cache->driver_stats());

    $arr_dados = $cache->get('cache_preco_alvo');

    if ($arr_dados != null) {
        echo '<div class="linha">';
        echo '<table class="tabela">';
        echo '<tr>';
        echo '<td style="padding: 4px;">ID</td>';
        echo '<td style="padding: 4px;">ID Ação</td>';
        echo '<td style="padding: 4px;">ID Empresa</td>';
        echo '<td style="padding: 4px;">Qtds</td>';
        echo '<td style="padding: 4px;">Preço alvo</td>';
        echo '<td style="padding: 4px;">IS</td>';
        echo '<td style="padding: 4px;">Encargos</td>';
        echo '<td style="padding: 4px;">Tipo</td>';
        echo '<td style="padding: 4px;">Parent</td>';
        echo '<td style="padding: 4px;">Estado</td>';
        echo '<td style="padding: 4px;">Data virt.</td>';
        echo '<td style="padding: 4px;">Data real</td>';
        echo '</tr>';

        foreach ($arr_dados as $value) {
            if ($value['active'] == '0') $estado = 'Inativo'; else $estado = 'Activo';
            echo '<tr>';
            echo '<td>' . $value['id_preco_alvo'] . '</td>';
            echo '<td>' . $value['id_acao'] . '</td>';
            echo '<td>' . $value['id_empresa'] . '</td>';
            echo '<td>' . $value['qtd'] . '</td>';
            echo '<td>' . $value['preco_alvo'] . '</td>';
            echo '<td>' . $value['is'] . '</td>';
            echo '<td>' . $value['encargos'] . '</td>';
            echo '<td>' . $value['tipo'] . '</td>';
            echo '<td>' . $value['parent'] . '</td>';
            echo '<td>' . $estado . '</td>';
            echo '<td>' . $value['data_limite_virtual'] . '</td>';
            echo '<td>' . $value['data_limite_real'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    } else {
        echo "ERRO: Cache vazia!";
    }
}
/* */

function run() {
    carrega_cotacao_cache();
    // carrega_cache_info_acoes();
    // carrega_cache_preco_alvo();
}

run();



