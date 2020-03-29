/*
 * @Author: Ricardo Órfão
 * @Date:   2014-05-04 13:22:10
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-10-01 20:55:33
 */

$(document).ready(function() {
    hideError();
    $('.erro-tri').hide();
    $(document).on('mousedown', function(e) {
        if (e.which == 1) {
            hideError();
            $('.erro-tri').hide();
        }
    });


    var activeWindow;
    $(document).on('click', 'a.modalLink', function(e) {
        e.preventDefault();
        var id = $(this).attr('href');
        activeWindow = $(id)
            .css({
                'opacity': '0',
                'left': '50%',
                'top': '50%',
                'margin-left': -$(id).width() / 2,
                'margin-top': -$(id).height() / 2
            }).fadeTo(500, 1);
        $('#modal').append('<div class="blind"></div>')
            .find('.blind')
            .css('opacity', '0')
            .fadeTo(500, 0.9).on('click', function(e) {
                closeModal();
            });
    });
    $(document).on('click', 'a.close', function(e) {
        e.preventDefault();
        $("video").trigger("pause");
        $("video")[0].currentTime = 0;
        closeModal();
    });

/*<<<<<<< HEAD
//    $(window).resize(function() {
//        activeWindow.css('top', '-1000px').css('left', '-1000px');
//        activeWindow = $('#openModal').css({
//            'left': '50%',
//            'top': '50%',
//            'margin-left': -$('#openModal').width() / 2,
//            'margin-top': -$('#openModal').height() / 2
//        });
//    });
*/
    $(window).resize(function() {

        activeWindow = $('#openModal').css({
            'left': '50%',
            'top': '50%',
            'margin-left': -$('#openModal').width() / 2,
            'margin-top': -$('#openModal').height() / 2
        });
        activeWindow.css('top', '-1000px').css('left', '-1000px');
    });
//>>>>>>> 1a1693c088273fb822b9b171d75ed2df381c4462

    function closeModal() {
        activeWindow.fadeOut(250, function() {
            $("video").trigger("pause");
            $("video")[0].currentTime = 0;
            $(this).css('top', '-1000px').css('left', '-1000px');
        });
        $('.blind').fadeOut(250, function() {
            $(this).remove();
        });
    }

    $(document).on('click', '#btnLogin', function(event) {
        if (event.handler !== true) {
            var username = $('#txtUsernameLogin').val();
            var password = $('#txtPasswordLogin').val();
            if (validaLogin(username, password) === true) {
                var dataString = "username=" + username + "&password=" + password;
                $.ajax({
                    type: "POST",
                    url: "login.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            window.location.href = dados.pagina;
                        } else {
                            $('.erro-tri').show();
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');
                            /*$.notify.create(dados.mensagem, {sticky: true, type: 'warning', style: 'bar', adjustContent: true});
                            $("#").notify("show");*/
                        }
                    }
                });
            } else {
                $('.erro-tri').show();
                $('.error').show().html('<span id="error">' + data + '</span>');
                /*$.notify.create(data, {sticky: true, type: 'warning', style: 'bar', adjustContent: true});
                $('#').notify("show");*/
            }
            event.handler = true;
        }
        return false;
    });
    $(document).on('click', '#btnRegister', function(event) {
        if (event.handler !== true) {
            var username = $('#txtUsernameLogin').val();
            var password = $('#txtPasswordLogin').val();
            if (validaLogin(username, password) === true) {
                var dataString = "username=" + username + "&password=" + password;
                $.ajax({
                    type: "POST",
                    url: "registo.php",
                    data: dataString,
                    dataType: "json",
                    success: function(dados) {
                        if (dados.sucesso === true) {
                            window.location.href = dados.pagina;
                        } else {
                            $('.erro-tri').show();
                            $('.error').show().html('<span id="error">' + dados.mensagem + '</span>');                            
                            /*$.notify.create(dados.mensagem, {sticky: true, type: 'warning', style: 'bar', adjustContent: true});
                            $("#").notify("show");*/
                        }
                    }
                });
            } else {
                $('.erro-tri').show();
                $('.error').show().html('<span id="error">' + data + '</span>');
                /*$.notify.create(data, {sticky: true, type: 'warning', style: 'bar', adjustContent: true});
                $('#').notify("show");*/
            }
            event.handler = true;
        }
        return false;
    });
    /*$(document).on("blur", "input", function() {
            if ($(this).val() === "") {
                $.notifybox.defaults({style: "simemp", className: "warn", position: "right", autoHide: true});
                $(this).notifybox("Preenchimento \n\Obrigatório");
            }
        });*/

});
  