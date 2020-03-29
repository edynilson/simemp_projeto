<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-08 16:07:41
*/
?>
<?php
$datetime = new DateTime();
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0">
    <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700&subset=latin,cyrillic-ext,latin-ext,cyrillic">
    <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/jquery.multilevelpushmenu.css">
    <link rel="stylesheet" href="css/jquery.datetimepicker.css">
    <link rel="icon" href="favicon.ico">
	<!-- Slide --> <link rel="stylesheet" href="css/blueimp-gallery.min.css">
	
    <!-- <script src="http://modernizr.com/downloads/modernizr-latest.js"></script> -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="js/jquery.multilevelpushmenu.min.js"></script>
    <script src="js/functions.js"></script>
    <script src="js/banco.js"></script>
    <script src="js/jquery.inputmask-3.x/jquery.inputmask.js"></script>
    <script src="js/jquery.inputmask-3.x/jquery.inputmask.numeric.extensions.js"></script>
    <script src="js/jquery.windowmsg-1.0.js"></script>
    <script src="js/jquery.datetimepicker.js"></script>
    <!-- <script src="js/jquery.jqpagination.min.js"></script> -->
    <script src="ckeditor/ckeditor.js"></script>
    <script src="ckeditor/adapters/jquery.js"></script>
    <script src="js/jquery.quicksearch.js"></script>
    
	<!-- Grágico cotação ações -->
	<script src="http://www.amcharts.com/lib/3/amcharts.js"></script>
    <script src="http://www.amcharts.com/lib/3/serial.js"></script>
    <script src="http://www.amcharts.com/lib/3/themes/light.js"></script>
    <script src="http://www.amcharts.com/lib/3/amstock.js"></script>
	<!-- -->
	<!-- Slide --> <script src="js/blueimp-gallery.min.js"></script>
	
	<!-- Notifications -->
	<script src="bar_notifications/jquery-notify.js"></script>    
    <link href="bar_notifications/notify.css" rel="stylesheet">
    <link href="bar_notifications/notifysimemp.css" rel="stylesheet">
	<!-- -->
	
	<script>
            /*localhost*/
        var domain = "localhost";
        var redirect = "http://simemp.estig.ipb.pt/pag_user.php";
        if ((location.hostname != domain) && (location.hostname != "www." + domain))
        {
            location.href = redirect;
        }
        /*        Servidor!!! */
//         var domain = "simemp.ipb.pt";
//         var redirect = "http://simemp.ipb.pt/pag_user.php";
//         if ((location.hostname != domain) && (location.hostname != "http://" + domain))
//         {
//         location.href = redirect;
//         }
    </script>
</head>
