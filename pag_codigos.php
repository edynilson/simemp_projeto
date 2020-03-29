<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-23 11:59:13
*/

if ($_GET['tipo'] == "admin") {
    include('./conf/check_admin.php');
} else if ($_GET['tipo'] == "user") {
    include('./conf/check.php');
}

$query_rubrica = $connection->prepare("SELECT cod.rubrica, cod.designacao FROM codigo cod");
$query_rubrica->execute();
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Destinatário</title>
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/base.css">
        <link rel="stylesheet" href="css/codigos.css">
        <link rel="icon" href="favicon.ico">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="js/jquery.quicksearch.js"></script>
        <script>
            $(document).ready(function() {
                $('input#divProcRubrica').quicksearch('#tblRubrica .tbody', {
                    noResults: '#noresults',
                    stripeRows: ['odd', 'even'],
                    loader: 'span.loading',
                    show: function() {
                        this.style.display = "";
                        $(this).closest('#tblRubrica').find('tr').eq(0).show();
                        $(this).show();
                    },
                    hide: function() {
                        this.style.display = "none";
                        $(this).hide();
                        if ($(this).closest('#tblRubrica').find('.tbody').filter(':visible').length == 0) {
                            $(this).closest('#tblRubrica').find('tr').eq(0).hide();
                        }
                    }
                });
            });
        </script>
    </head>
    <body>
        <div class="var_content">
            <div class="linha left">
                <h3 class="left">Códigos</h3>
                <div id="divProcRubrica" name="divProcRubrica" class="inputarea">
                    <input id="divProcRubrica" name="divProcRubrica" type="text" class="procura left editableText" placeholder="Pesquise aqui">
                    <div class="iconwrapper left">
                        <div class="novolabelicon icon-lupa"></div>
                    </div>
                </div>
            </div>
            <table id="tblRubrica" name="tblRubrica" class="tabela">
                <tr>
                    <td class="td5">Rubrica</td>
                    <td class="td95">Designação</td>
                </tr>
                <?php while ($linha_rubrica = $query_rubrica->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr class="tbody">
                        <td class="td5" style="padding: 4px;"><?php echo $linha_rubrica['rubrica']; ?></td>
                        <td class="td95" style="padding: 4px;"><?php echo $linha_rubrica['designacao']; ?></td>
                    </tr>
                <?php } ?>
                <tr id="noresults">
                    <td colspan="5" style="background-color: #2b6db9; color: #fff;">Não existem resultados</td>
                </tr>
            </table>
        </div>
    </body>
</html>