<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo
* @Last Modified time: 2014-07-26 17:19:24
*/
?>
<?php

/*
    Created on : 29/Abr/2014, 19:06:13
    Author     : Ricardo Órfão
*/
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
                <div style="margin: 0 auto; width: 385px;">
                    <img src="images/manutencao.png">
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