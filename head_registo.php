<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-01 14:45:22
*/

$datetime = new DateTime();
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0">
    <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/chosen.css">
    <link rel="stylesheet" href="css/registo.css">
    <link rel="icon" href="favicon.ico">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="js/jquery.inputmask-3.x/jquery.inputmask.js"></script>
    <script src="js/jquery.inputmask-3.x/jquery.inputmask.numeric.extensions.js"></script>
    <script src="js/functions.js"></script>
    <script src="js/chosen.jquery.min.js"></script>
    <script src="js/registo_jquery.js"></script>
    
    <script src="https://www.google.com/recaptcha/api.js"></script>
    
    <!--[if lte IE 7]><link rel="stylesheet... />
        .outer {
            display: inline;
            top: 0;
        }

        .middle {
            display: inline;
            top: 50%;
            position: relative;
        }

        .inner {
            display: inline;
            top: -50%;
            position: relative;
        }
    <![endif]-->
</head>