<?php

$datetime = new DateTime();
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0">
        <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="icon" href="favicon.ico">
    </head>
    <body>
        <div id="container">
            <div id="body">
                <div id="logo">
                    <img src="./images/logo.png">
                </div>
                <div style="text-align:center; margin: 0 auto;">
                    <!-- <a href="/../simempacoes/index3.php"><img src="images/manutencao.png"></a>
                    <a href="index3.php"><img src="images/manutencao.png"></a> -->
                    
                    <div style="display: inline-block; margin-left: auto; margin-right: auto;">
                        <a href="index1.php">
                            <img src="images/contab.png">
                        </a>
                    </div>
                    
                    <div style="display: inline-block; margin-left: 20px; margin-right: 20px;">
                        <a href="index1.php">
                            <img src="images/bolsa.png">
                        </a>
                    </div>
                    
                    <div style="display: inline-block; margin-left: auto; margin-right: auto;">
                        <!-- <a href="http://193.136.195.9/index.php?r=user-management%2Fauth%2Flogin"> -->
						<a href="http://simempgestao.ipb.pt">
                            <img src="images/gestao.png">
                        </a>
                    </div>
                    
                    <div style="display: inline-block; margin-left: 20px; margin-right: 20px;">
                        <a href="http://simempavaliacao.ipb.pt">
                            <img src="images/student_2.png">
                        </a>
                    </div>
                </div>
            </div>
            <div id="footer">
                <p>
                    SimEmp - Copyright © <?php echo $datetime->format('Y'); ?>
                    <!--geral@simemp.pt-->
                </p>
            </div>
        </div>
    </body>
</html>