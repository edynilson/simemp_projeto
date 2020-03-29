<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:07
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-09-22 16:16:33
*/
include_once('./conf/common.php');

$datetime = new DateTime();

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == "user") {
    header('location:pag_user.php');
} elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] == "admin") {
    header('location:pag_admin.php');
}
?>
<!doctype html>
<html lang="pt-pt">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>SimEmp <?php echo $datetime->format('Y'); ?></title>
        <link rel="stylesheet" href="css/normalize.css" media="screen">
        <link rel="stylesheet" href="css/index.css" media="screen">
        <link rel="icon" href="favicon.ico">
        <script src="js/respond.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script type="text\/javascript" src="js\/jquery.min.js"><\/script>')</script>
        <script src="js/functions.js"></script>
        <script src="js/prefixfree.min.js"></script>
        <script src="js/login_form.js"></script>
    </head>
    <body>
        <section>
            <div class="row middle-row">
                <div class="cell middle-cell">
                    <div class="main-content">
                        <header>
                            <img src="images/logo.png" alt="Logo">
                            <!-- --> <div class="tblVideo">
                                <div class="tblCell">
                                    <img class="player" src="images/play_button.png" alt="Player">
                                </div>
                                <div class="tblCell">
                                    <a href="#openModal" class="modalLink">Tutorial</a>
                                </div>
                            </div> <!-- -->
                        </header>
                        <div id="modal">
                            <div id="openModal" class="window">
                                <div class="contents">
                                    <video controls poster="images/logo.png">
                                        <source src="video/Tutorial.mp4" type="video/mp4">
                                        <source src="video/Tutorial.webm" type="video/webm">
                                        <source src="video/Tutorial.ogg" type="video/ogg">
                                    </video>
                                    <a href="#" class="close">X</a>
                                </div>
                            </div>
                        </div>
                        <form id="frmLogin" name="frmLogin" class="frmLogin">
                            <div class="table">
                                <div class="tr">
                                    <span class="th"><input id="txtUsernameLogin" name="txtUsernameLogin" type="text" class="txtUsernameLogin small" placeholder="<?php echo $lingua['USERNAME']; ?>"></span>
                                </div>
                                <div class="tr">
                                    <span class="td"><input id="txtPasswordLogin" name="txtPasswordLogin" type="password" class="txtPasswordLogin small" placeholder="<?php echo $lingua['PASSWORD']; ?>"></span>
                                </div>
                            </div>
                            <p id="warning_caps" style="color:red; display: none;">WARNING! Caps lock is ON.</p>
                            <div id="erro-tri" class="erro-tri"></div>
                            <div id="error" class="error normal"></div>
                            <button id="btnLogin" name="btnLogin" type="submit" class="btnInicio normal btnLogin"><?php echo $lingua['LOGIN']; ?></button>
                            <button id="btnRegister" name="btnRegister" type="submit" class="btnInicio normal btnRegister"><?php echo $lingua['REGISTER']; ?></button>
                        </form>
                    </div>
                    <div class="push"></div>
                </div>
            </div>
        </section>
        <footer>
            <p class="cell-footer small">&copy; Copyright <?php echo $datetime->format('Y'); ?> por SimEmp. Todos os direitos reservados.</p>
        </footer>
        
        <script>
var input = document.getElementById("txtPasswordLogin");
var warning_caps = document.getElementById("warning_caps");
input.addEventListener("keyup", function(event) {

if (event.getModifierState("CapsLock")) {
    warning_caps.style.display = "block";
  } else {
    warning_caps.style.display = "none"
  }
});

$( "#txtPasswordLogin" ).select(function(event) {// se clickar no tab
  if (event.getModifierState("CapsLock")) {
    warning_caps.style.display = "block";
  } else {
    warning_caps.style.display = "none"
  }
});

//$( "#txtPasswordLogin" ).click(function(event) {// se clickar no input
//  if (event.getModifierState("CapsLock")) {
//    warning_caps.style.display = "block";
//  } else {
//    warning_caps.style.display = "none"
//  }
//});
</script>
    </body>
</html>