<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-07-01 18:21:04
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-27 16:27:08
*/

include('../conf/check_pastas.php');
include_once('functions.php');

if ($_POST['tipo'] == "ler_mensagem") {
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
        $query_up_email = $connection->prepare("UPDATE correio SET lido=:lido WHERE id=:id AND prop=:prop");
        $query_up_email->execute(array(':lido' => 1, ':id' => $_POST['id'], ':prop' => $id_empresa));

        $query_mail = $connection->prepare("SELECT co.id, emp1.id_empresa AS id_remetente, emp1.nome AS de, emp2.id_empresa AS id_destinatario, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND  co.id=:id ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
        $query_mail->execute(array(':id' => $_POST['id']));
        $linha_mail = $query_mail->fetch(PDO::FETCH_ASSOC);
    } elseif ($_SESSION['tipo'] == "admin") {
        $query_up_email = $connection->prepare("UPDATE correio SET lido=:lido WHERE id=:id AND (prop=:prop1 OR prop=:prop2 OR prop=:prop3)");
        $query_up_email->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':lido' => 1, ':id' => $_POST['id']));

        $query_mail = $connection->prepare("SELECT co.id, emp1.id_empresa AS id_remetente, emp1.nome AS de, emp2.id_empresa AS id_destinatario, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.id=:id AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
        $query_mail->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':id' => $_POST['id']));
        $linha_mail = $query_mail->fetch(PDO::FETCH_ASSOC);
    }
    $arr = array('sucesso' => true, 'id' => $linha_mail['id'], 'id_remetente' => $linha_mail['id_remetente'], 'remetente' => $linha_mail['de'], 'id_destinatario' => $linha_mail['id_destinatario'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo']);
    logClicks($connection, "113");
} elseif ($_POST['tipo'] == "apagar_mensagem") {
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
        if ($_POST['flag'] == 1) {
            $query_up_email = $connection->prepare("UPDATE correio SET lixo=:lixo WHERE id=:id AND prop=:prop");
            $query_up_email->execute(array(':lixo' => 1, ':id' => $_POST['id'], ':prop' => $id_empresa));
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND  co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_recebido->execute(array(':id_empresa' => $id_empresa, ':para' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 2) {
            $query_up_email = $connection->prepare("UPDATE correio SET lixo=:lixo WHERE id=:id");
            $query_up_email->execute(array(':lixo' => 1, ':id' => $_POST['id']));
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND  co.de=:de AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_enviado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 3) {
            $query_up_email = $connection->prepare("UPDATE correio SET lixo=:lixo, eliminado=:elim WHERE id=:id");
            $query_up_email->execute(array(':lixo' => 1, ':elim' => 1, ':id' => $_POST['id']));
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa em2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de OR co.para=:para) AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_eliminado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':para' => $id_empresa, ':lixo' => 1, ':elim' => 0));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        }
    } elseif ($_SESSION['tipo'] == "admin") {
        if ($_POST['flag'] == 1) {
            $query_up_email = $connection->prepare("UPDATE correio SET lixo=:lixo WHERE id=:id AND (prop=:prop1 OR prop=:prop2 OR prop=:prop3)");
            $query_up_email->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':lixo' => 1, ':id' => $_POST['id']));
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.para=:para1 OR co.para=:para2 OR co.para=:para3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_recebido->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':para1' => 1, ':para2' => 2, ':para3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 2) {
            $query_up_email = $connection->prepare("UPDATE correio SET lixo=:lixo WHERE id=:id AND (prop=:prop1 OR prop=:prop2 OR prop=:prop3)");
            $query_up_email->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':lixo' => 1, ':id' => $_POST['id']));
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de1 OR co.de=:de2 OR co.de=:de3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_enviado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':de2' => 2, ':de3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 3) {
            $query_up_email = $connection->prepare("UPDATE correio SET lixo=:lixo, eliminado=:elim WHERE id=:id AND (prop=:prop1 OR prop=:prop2 OR prop=:prop3)");
            $query_up_email->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':lixo' => 1, ':elim' => 1, ':id' => $_POST['id']));
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND ((co.de=:de1 OR co.para=:para1) OR (co.de=:de2 OR co.para=:para2) OR (co.de=:de3 OR co.para=:para3)) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_eliminado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':para1' => 1, ':de2' => 2, ':para2' => 2, ':de3' => 3, ':para3' => 3, ':lixo' => 1, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        }
    }
    logClicks($connection, "111");
} elseif ($_POST['tipo'] == "mensagens") {
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
        if ($_POST['flag'] == 1) {
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_recebido->execute(array(':id_empresa' => $id_empresa, ':para' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 2) {
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.de=:de AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_enviado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 3) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de OR co.para=:para) AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_eliminado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':para' => $id_empresa, ':lixo' => 1, ':elim' => 0));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        }
    } elseif ($_SESSION['tipo'] == "admin") {
        if ($_POST['flag'] == 1) {
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.para=:para1 OR co.para=:para2 OR co.para=:para3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_recebido->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':para1' => 1, ':para2' => 2, ':para3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 2) {
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de1 OR co.de=:de2 OR co.de=:de3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_enviado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':de2' => 2, ':de3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($_POST['flag'] == 3) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND ((co.de=:de1 OR co.para=:para1) OR (co.de=:de2 OR co.para=:para2) OR (co.de=:de3 OR co.para=:para3)) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_eliminado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':para1' => 1, ':de2' => 2, ':para2' => 2, ':de3' => 3, ':para3' => 3, ':lixo' => 1, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        }
    }
    logClicks($connection, "109");
} elseif ($_POST['tipo'] == "enviar_mensagem") {
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
        if (isset($_FILES["fileAnexar"])) {
			if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $_FILES["fileAnexar"]["name"])) {
				if ($_FILES['fileAnexar']['type'] == 'application/pdf') {
					if (($_FILES["fileAnexar"]["size"] < 1048576)) {
						if ($_FILES["fileAnexar"]["error"] <= 0) {
							$extension = end(explode(".", $_FILES["fileAnexar"]["name"]));
							$filename_tmp = str_replace(' ', '_', $_POST['txtPath']);
							$filename = str_replace('.' . $extension, '', $filename_tmp);
							date_default_timezone_set('Europe/London');
							$data = date('d/m/o H:i:s');
							$condicoes = array("/", ":", " ");
							$data_final = str_replace($condicoes, "", $data);

							$nome_ficheiro = $filename . "_" . $data_final . "_" . $id_empresa;
							$nome_disco = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $filename) . "_" . $data_final . "_" . $id_empresa;

							$path = "../anexos_emails/";
							if (!file_exists($path . $nome_ficheiro . "." . $extension)) {
								// move_uploaded_file($_FILES["fileAnexar"]["tmp_name"], $path . $nome_disco . "." . $extension);
								if (move_uploaded_file($_FILES["fileAnexar"]["tmp_name"], $path . $nome_disco . "." . $extension)) {
									$query_email_e = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
									$query_email_e->execute(array(':prop' => $id_empresa, ':de' => $id_empresa, ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
									$id_email = $connection->lastInsertId();
									$query_ins_anexo = $connection->prepare("INSERT INTO anexo (anexo) VALUES (:anexo)");
									$query_ins_anexo->execute(array(':anexo' => substr($path, 3) . $nome_ficheiro . "." . $extension));
									$id_anexo = $connection->lastInsertId();
									$query_nn = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
									$query_nn->execute(array(':id_anexo' => $id_anexo, ':id_email' => $id_email, ':data' => date('Y-m-d H:i:s')));
									$query_email_s = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
									$query_email_s->execute(array(':prop' => $_POST['destinatario'], ':de' => $id_empresa, ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
									$id_email2 = $connection->lastInsertId();
									$query_nn2 = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
									$query_nn2->execute(array(':id_anexo' => $id_anexo, ':id_email' => $id_email2, ':data' => date('Y-m-d H:i:s')));
									$arr = array('sucesso' => true);
								} else {
									// console.log(print_r($_FILES));
									$arr = array('sucesso' => false, 'mensagem' => 'Ocorreu um erro no carregamento do ficheiro');
								}
							} else {
								$query_sel_email = $connection->prepare("SELECT a.id FROM anexo a WHERE a.anexo=:anexo");
								$query_sel_email->execute(array(':anexo' => substr($path, 3) . $nome_ficheiro . '.' . $extension));
								$linha_sel_email = $query_sel_email->fetch(PDO::FETCH_ASSOC);
								$query_email_e = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
								$query_email_e->execute(array(':prop' => $id_empresa, ':de' => $id_empresa, ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
								$id_email = $connection->lastInsertId();
								$query_nn = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
								$query_nn->execute(array(':id_anexo' => $linha_sel_email['id'], ':id_email' => $id_email, ':data' => date('Y-m-d H:i:s')));
								$query_email_s = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
								$query_email_s->execute(array(':prop' => $_POST['destinatario'], ':de' => $id_empresa, ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
								$id_email2 = $connection->lastInsertId();
								$query_nn2 = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
								$query_nn2->execute(array(':id_anexo' => $id_anexo, ':id_email' => $id_email2, ':data' => date('Y-m-d H:i:s')));
								$arr = array('sucesso' => true);
							}
						} else {
							$arr = array('sucesso' => false, 'mensagem' => "Ocorreu um erro no carregamento do ficheiro");
						}
					} else {
						$arr = array('sucesso' => false, 'mensagem' => "O ficheiro ultrapassa o tamanho máximo permitido: 2MB");
					}
				} else {
					$arr = array('sucesso' => false, 'mensagem' => "O formato do ficheiro deve ser .pdf");
				}
			} else {
                $arr = array('sucesso' => false, 'mensagem' => "O nome do ficheiro não deve conter caractéres especiais");
            }
			
        } else {
            $query_email_e = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
            $query_email_e->execute(array(':prop' => $id_empresa, ':de' => $id_empresa, ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
            $query_email_s = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
            $query_email_s->execute(array(':prop' => $_POST['destinatario'], ':de' => $id_empresa, ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
            $arr = array('sucesso' => true);
        }
    } elseif ($_SESSION['tipo'] == "admin") {
        if (isset($_FILES["fileAnexar"])) {
			if ($_FILES['file_upload']['type'] != 'application/pdf') {
				if (($_FILES["fileAnexar"]["size"] < 10485760)) {
					if ($_FILES["fileAnexar"]["error"] <= 0) {
						$extension = end(explode(".", $_FILES["fileAnexar"]["name"]));
						$filename_tmp = str_replace(' ', '_', $_POST['txtPath']);
						$filename = str_replace('.' . $extension, '', $filename_tmp);
						date_default_timezone_set('Europe/London');
						$data = date('d/m/o H:i:s');
						$condicoes = array("/", ":", " ");
						$data_final = str_replace($condicoes, "", $data);
						
						$nome_ficheiro = $filename . "_" . $data_final . "_" . $_POST['remetente'];
						$nome_disco = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $filename) . "_" . $data_final . "_" . $_POST['remetente'];
						
						$path = "../anexos_emails/";
						if (!file_exists($path . $nome_ficheiro . "." . $extension)) {
							if (move_uploaded_file($_FILES["fileAnexar"]["tmp_name"], $path . $nome_disco . "." . $extension)) {
								$query_email_e = $connection->prepare("INSERT INTO correio (prop, admin, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :admin, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
								$query_email_e->execute(array(':prop' => $_POST['remetente'], ':admin' => $_SESSION['id_utilizador'], ':de' => $_POST['remetente'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
								$id_email = $connection->lastInsertId();
								$query_ins_anexo = $connection->prepare("INSERT INTO anexo (anexo) VALUES (:anexo)");
								$query_ins_anexo->execute(array(':anexo' => substr($path, 3) . $nome_ficheiro . "." . $extension));
								$id_anexo = $connection->lastInsertId();
								$query_nn = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
								$query_nn->execute(array(':id_anexo' => $id_anexo, ':id_email' => $id_email, ':data' => date('Y-m-d H:i:s')));
								$query_email_s = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
								$query_email_s->execute(array(':prop' => $_POST['destinatario'], ':de' => $_POST['remetente'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
								$id_email2 = $connection->lastInsertId();
								$query_nn2 = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
								$query_nn2->execute(array(':id_anexo' => $id_anexo, ':id_email' => $id_email2, ':data' => date('Y-m-d H:i:s')));
								$arr = array('sucesso' => true);
							} else {
								$arr = array('sucesso' => false, 'mensagem' => 'Ocorreu um erro no carregamento do ficheiro');
							}
						} else {
							$query_sel_email = $connection->prepare("SELECT a.id FROM anexo a WHERE a.anexo=:anexo");
							$query_sel_email->execute(array(':anexo' => substr($path, 3) . $nome_ficheiro . '.' . $extension));
							$linha_sel_email = $query_sel_email->fetch(PDO::FETCH_ASSOC);
							$query_email_e = $connection->prepare("INSERT INTO correio (prop, admin, de, para, assunto, mensagem, `data`, lido, lixo, eliminado) VALUES (:prop, :admin, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
							$query_email_e->execute(array(':prop' => $_POST['remetente'], ':admin' => $_SESSION['id_utilizador'], ':de' => $_POST['remetente'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
							$id_email = $connection->lastInsertId();
							$query_nn = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
							$query_nn->execute(array(':id_anexo' => $linha_sel_email['id'], ':id_email' => $id_email, ':data' => date('Y-m-d H:i:s')));
							$query_email_s = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, `data`, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
							$query_email_s->execute(array(':prop' => $_POST['destinatario'], ':de' => $_POST['remetente'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
							$id_email2 = $connection->lastInsertId();
							$query_nn2 = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
							$query_nn2->execute(array(':id_anexo' => $linha_sel_email['id'], ':id_email' => $id_email2, ':data' => date('Y-m-d H:i:s')));
							$arr = array('sucesso' => true);
						}
					} else {
						$arr = array('sucesso' => false, 'mensagem' => "Ocorreu um erro no carregamento do ficheiro");
					}
				} else {
					$arr = array('sucesso' => false, 'mensagem' => "O ficheiro ultrapassa o tamanho máximo permitido: 2MB");
				}
			} else {
				$arr = array('sucesso' => false, 'mensagem' => "O tipo de ficheiro não é suportado");
			}
        } else {
            $query_email_e = $connection->prepare("INSERT INTO correio (prop, admin, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :admin, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
            $query_email_e->execute(array(':prop' => $_POST['remetente'], ':admin' => $_SESSION['id_utilizador'], ':de' => $_POST['remetente'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
            $query_email_s = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, data, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
            $query_email_s->execute(array(':prop' => $_POST['destinatario'], ':de' => $_POST['remetente'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
            $arr = array('sucesso' => true);
        }
    }
    logClicks($connection, "112");
} elseif ($_POST['tipo'] == "reencaminhar") {
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
        $query_sel_email = $connection->prepare("SELECT co.de, co.mensagem, a.`anexo` FROM correio co LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE co.id=:id AND co.prop=:prop");
        $query_sel_email->execute(array(':prop' => $id_empresa, ':id' => $_POST['id_mail']));
        $linha = $query_sel_email->fetch(PDO::FETCH_ASSOC);
        $query_fw = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, `data`, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
        $query_fw->execute(array(':prop' => $id_empresa, ':de' => $linha['de'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
        $id_email = $connection->lastInsertId();
        $query_fw2 = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, `data`, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
        $query_fw2->execute(array(':prop' => $_POST['destinatario'], ':de' => $linha['de'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
        $id_email2 = $connection->lastInsertId();
        if ($linha['anexo'] != null) {
            $query_sel_anexo = $connection->prepare("SELECT a.id FROM anexo a WHERE a.anexo=:anexo");
            $query_sel_anexo->execute(array(':anexo' => $linha['anexo']));
            $linha_anexo = $query_sel_anexo->fetch(PDO::FETCH_ASSOC);
            $query_nn = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
            $query_nn->execute(array(':id_anexo' => $linha_anexo['id'], ':id_email' => $id_email, ':data' => date('Y-m-d H:i:s')));
            $query_nn2 = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
            $query_nn2->execute(array(':id_anexo' => $linha_anexo['id'], ':id_email' => $id_email2, ':data' => date('Y-m-d H:i:s')));
        }
        $arr = array('sucesso' => true);
    } elseif ($_SESSION['tipo'] == "admin") {
        $query_sel_email = $connection->prepare("SELECT co.de, co.mensagem, a.`anexo` FROM correio co LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE co.id=:id");
        $query_sel_email->execute(array(':id' => $_POST['id_mail']));
        $linha = $query_sel_email->fetch(PDO::FETCH_ASSOC);
        $query_fw = $connection->prepare("INSERT INTO correio (prop, admin, de, para, assunto, mensagem, `data`, lido, lixo, eliminado) VALUES (:prop, :admin, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
        $query_fw->execute(array(':prop' => $linha['de'], ':admin' => $_SESSION['id_utilizador'], ':de' => $linha['de'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 1, ':lixo' => 0, ':elim' => 0));
        $id_email = $connection->lastInsertId();
        $query_fw2 = $connection->prepare("INSERT INTO correio (prop, de, para, assunto, mensagem, `data`, lido, lixo, eliminado) VALUES (:prop, :de, :para, :assunto, :mens, :data, :lido, :lixo, :elim)");
        $query_fw2->execute(array(':prop' => $_POST['destinatario'], ':de' => $linha['de'], ':para' => $_POST['destinatario'], ':assunto' => $_POST['assunto'], ':mens' => $_POST['mensagem'], ':data' => date('Y-m-d H:i:s', strtotime($_POST['data_virtual'])), ':lido' => 0, ':lixo' => 0, ':elim' => 0));
        $id_email2 = $connection->lastInsertId();
        if ($linha['anexo'] != null) {
            $query_sel_anexo = $connection->prepare("SELECT a.id FROM anexo a WHERE a.anexo=:anexo");
            $query_sel_anexo->execute(array(':anexo' => $linha['anexo']));
            $linha_anexo = $query_sel_anexo->fetch(PDO::FETCH_ASSOC);
            $query_nn = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
            $query_nn->execute(array(':id_anexo' => $linha_anexo['id'], ':id_email' => $id_email, ':data' => date('Y-m-d H:i:s')));
            $query_nn2 = $connection->prepare("INSERT INTO anexo_email (id_anexo, id_email, data) VALUES (:id_anexo, :id_email, :data)");
            $query_nn2->execute(array(':id_anexo' => $linha_anexo['id'], ':id_email' => $id_email2, ':data' => date('Y-m-d H:i:s')));
        }
        $arr = array('sucesso' => true);
    }
    logClicks($connection, "114");
} elseif ($_POST['tipo'] == "filtrar_emails") {
    if ($_SESSION['tipo'] == "user") {
        $id_empresa = $_SESSION['id_empresa'];
        $id_filtro = $_POST['id_filtro'];
        $flag = $_POST['flag'];
        if ($flag == 1 && ($id_filtro == 0 || $id_filtro == 2)) {
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_recebido->execute(array(':id_empresa' => $id_empresa, ':para' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 1 && $id_filtro == 1) {
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.para=:para AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY de ASC");
            $query_email_recebido->execute(array(':id_empresa' => $id_empresa, ':para' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 2 && ($id_filtro == 0 || $id_filtro == 2)) {
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.de=:de AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_enviado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 2 && $id_filtro == 1) {
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND co.de=:de AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY para ASC");
            $query_email_enviado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':lixo' => 0, ':elim' => 0));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 3 && ($id_filtro == 0 || $id_filtro == 3)) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de OR co.para=:para) AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_eliminado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':para' => $id_empresa, ':lixo' => 1, ':elim' => 0));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 3 && $id_filtro == 1) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de OR co.para=:para) AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY de DESC");
            $query_email_eliminado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':para' => $id_empresa, ':lixo' => 1, ':elim' => 0));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 3 && $id_filtro == 2) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de OR co.para=:para) AND co.lixo=:lixo AND co.eliminado=:elim AND co.prop=:id_empresa ORDER BY para DESC");
            $query_email_eliminado->execute(array(':id_empresa' => $id_empresa, ':de' => $id_empresa, ':para' => $id_empresa, ':lixo' => 1, ':elim' => 0));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        }
    } elseif ($_SESSION['tipo'] == "admin") {
        $id_filtro = $_POST['id_filtro'];
        $flag = $_POST['flag'];
        if ($flag == 1 && ($id_filtro == 0 || $id_filtro == 2)) {
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.para=:para1 OR co.para=:para2 OR co.para=:para3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_recebido->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':para1' => 1, ':para2' => 2, ':para3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 1 && $id_filtro == 1) {
            $query_email_recebido = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND  (co.para=:para1 OR co.para=:para2 OR co.para=:para3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY de ASC");
            $query_email_recebido->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':para1' => 1, ':para2' => 2, ':para3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_recebido->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_recebido->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 2 && ($id_filtro == 0 || $id_filtro == 2)) {
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de1 OR co.de=:de2 OR co.de=:de3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_enviado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':de2' => 2, ':de3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 2 && $id_filtro == 1) {
            $query_email_enviado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND (co.de=:de1 OR co.de=:de2 OR co.de=:de3) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY para ASC");
            $query_email_enviado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':de2' => 2, ':de3' => 3, ':lixo' => 0, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_enviado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_enviado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 3 && ($id_filtro == 0 || $id_filtro == 3)) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND ((co.de=:de1 OR co.para=:para1) OR (co.de=:de2 OR co.para=:para2) OR (co.de=:de3 OR co.para=:para3)) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY date(co.`data`) ASC, time(co.`data`) DESC");
            $query_email_eliminado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':para1' => 1, ':de2' => 2, ':para2' => 2, ':de3' => 3, ':para3' => 3, ':lixo' => 1, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 3 && $id_filtro == 1) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND ((co.de=:de1 OR co.para=:para1) OR (co.de=:de2 OR co.para=:para2) OR (co.de=:de3 OR co.para=:para3)) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY de ASC");
            $query_email_eliminado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':para1' => 1, ':de2' => 2, ':para2' => 2, ':de3' => 3, ':para3' => 3, ':lixo' => 1, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        } elseif ($flag == 3 && $id_filtro == 2) {
            $query_email_eliminado = $connection->prepare("SELECT co.id, emp1.nome AS de, emp2.nome AS para, co.assunto, co.mensagem, date_format(co.`data`,'%d-%m-%Y %h:%i:%s') AS `data`, a.`anexo`, co.lido FROM correio co INNER JOIN empresa emp1 ON co.de=emp1.id_empresa INNER JOIN empresa emp2 ON co.para=emp2.id_empresa LEFT JOIN anexo_email ae ON co.id=ae.id_email LEFT JOIN anexo a ON ae.id_anexo=a.id INNER JOIN utilizador u ON co.admin=u.id WHERE emp1.ativo='1' AND emp2.ativo='1' AND ((co.de=:de1 OR co.para=:para1) OR (co.de=:de2 OR co.para=:para2) OR (co.de=:de3 OR co.para=:para3)) AND co.lixo=:lixo AND co.eliminado=:elim AND (co.prop=:prop1 OR co.prop=:prop2 OR co.prop=:prop3) AND u.id=:id_utilizador ORDER BY para ASC");
            $query_email_eliminado->execute(array(':prop1' => 1, ':prop2' => 2, ':prop3' => 3, ':de1' => 1, ':para1' => 1, ':de2' => 2, ':para2' => 2, ':de3' => 3, ':para3' => 3, ':lixo' => 1, ':elim' => 0, ':id_utilizador' => $_SESSION['id_utilizador']));
            $linhas = $query_email_eliminado->rowCount();
            if ($linhas > 0) {
                while ($linha_mail = $query_email_eliminado->fetch(PDO::FETCH_ASSOC)) {
                    $arr_dados[] = array('id' => $linha_mail['id'], 'remetente' => $linha_mail['de'], 'destinatario' => $linha_mail['para'], 'assunto' => $linha_mail['assunto'], 'mensagem' => $linha_mail['mensagem'], 'data' => $linha_mail['data'], 'anexo' => $linha_mail['anexo'], 'lido' => $linha_mail['lido']);
                }
                $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
            } else {
                $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem mensagens");
            }
        }
    }
    logClicks($connection, "110");
} elseif ($_POST['tipo'] == "empresas_grupo") {
    if ($_SESSION['tipo'] == "admin") {
        if ($_POST['id'] == 0) {
            $query_grupos = $connection->prepare("SELECT emp.id_empresa AS id_empresa, g.id AS id_grupo, emp.nome AS nome_empresa, num_conta, g.nome AS nome_grupo FROM empresa emp INNER JOIN conta c ON emp.id_empresa=c.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND emp.id_empresa<>:cc AND emp.id_empresa<>:cf AND emp.id_empresa<>:cp AND u.tipo=:tipo AND u.id=:id_utilizador ORDER BY nome_empresa");
            $query_grupos->execute(array(':cc' => 1, ':cf' => 2, ':cp' => 3, ':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador']));
            
        } else {
            $query_grupos = $connection->prepare("SELECT emp.id_empresa AS id_empresa, g.id AS id_grupo, emp.nome AS nome_empresa, num_conta, g.nome AS nome_grupo FROM empresa emp INNER JOIN conta c ON emp.id_empresa=c.id_empresa INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id WHERE emp.ativo='1' AND emp.id_empresa<>:cc AND emp.id_empresa<>:cf AND emp.id_empresa<>:cp AND u.tipo=:tipo AND u.id=:id_utilizador AND g.id=:id_grupo ORDER BY nome_empresa");
            $query_grupos->execute(array(':cc' => 1, ':cf' => 2, ':cp' => 3, ':tipo' => "admin", ':id_utilizador' => $_SESSION['id_utilizador'], ':id_grupo' => $_POST['id']));
            
        }
    } elseif ($_SESSION['tipo'] == "user") {
        if ($_SESSION['tipo_grupo'] == "Bolsa") {
            if ($_POST['id'] == 0) {
                $query_grupos = $connection->prepare("SELECT emp.id_empresa AS id_empresa, emp.nome AS nome_empresa FROM empresa emp LEFT JOIN grupo g ON emp.id_grupo=g.id LEFT JOIN utilizador u ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND emp.id_empresa<>:id_empresa1 AND emp.id_empresa<>:cc AND emp.id_empresa<>:cp AND (g.id=(SELECT emp.id_grupo FROM empresa emp WHERE emp.ativo='1' AND emp.id_empresa=:id_empresa2) OR g.id IS NULL) ORDER BY emp.nome");
                $query_grupos->execute(array(':id_empresa1' => $_SESSION['id_empresa'], ':cc' => "1", ':cp' => "3", ':id_empresa2' => $_SESSION['id_empresa']));
				
            } else {
                $query_grupos = $connection->prepare("SELECT emp.id_empresa AS id_empresa, emp.nome AS nome_empresa FROM empresa emp LEFT JOIN grupo g ON emp.id_grupo=g.id LEFT JOIN utilizador u ON emp.id_empresa=u.id_empresa WHERE emp.ativo='1' AND emp.id_empresa<>:id_empresa1 AND emp.id_empresa<>:cc AND emp.id_empresa<>:cp AND (g.id=:id_grupo OR g.id IS NULL) ORDER BY emp.nome");
                $query_grupos->execute(array(':id_empresa1' => $_SESSION['id_empresa'], ':cc' => "1", ':cp' => "3", ':id_grupo' => $_POST['id']));
                
            }
        } else {
            if ($_POST['id'] == 0) {
                //-- Todas as empresas, dos grupos, do tipo NORMAL, ATIVOS, de QUALQUER entidade
                $query_grupos = $connection->prepare("SELECT grupos.id AS id_grupo, grupos.nome AS nome_grupo, grupos.entidade_grupo, em.id_empresa, em.nome, atv.designacao AS nome_atv FROM (SELECT * FROM (SELECT g.* FROM grupo g INNER JOIN tipo_grupo tg ON g.id_tipo=tg.id WHERE tg.designacao LIKE '%Normal%') AS ent_grupos LEFT JOIN (SELECT eg.id_grupo, eg.estado, e.nome AS entidade_grupo FROM estado_grupo eg INNER JOIN utilizador admin ON eg.id_user=admin.id INNER JOIN entidade e ON admin.id_entidade=e.id ORDER BY eg.id_grupo ASC, eg.date_reg DESC) AS estado_gr ON ent_grupos.id=estado_gr.id_grupo GROUP BY ent_grupos.id) AS grupos LEFT JOIN empresa em ON grupos.id_grupo=em.id_grupo INNER JOIN atividade atv ON em.atividade=atv.id WHERE grupos.estado='1' AND em.ativo='1' AND em.id_empresa<>:id_empresa ORDER BY em.nome ASC");//pedente
                $query_grupos->execute(array(':id_empresa' => $_SESSION['id_empresa']));
                
            } else {
                $query_grupos = $connection->prepare("SELECT e.nome AS entidade_grupo, emp.id_empresa AS id_empresa, emp.nome AS nome_empresa, atv.designacao AS nome_atv FROM empresa emp INNER JOIN atividade atv ON emp.atividade=atv.id INNER JOIN grupo g ON emp.id_grupo=g.id INNER JOIN user_grupo ug ON g.id=ug.id_grupo INNER JOIN utilizador u ON ug.id_user=u.id INNER JOIN entidade e ON u.id_entidade=e.id WHERE g.id=:id_grupo AND emp.ativo='1' AND emp.id_empresa<>:id_empresa ORDER BY emp.nome ASC");
                $query_grupos->execute(array(':id_grupo' => $_POST['id'], ':id_empresa' => $_SESSION['id_empresa']));
            }
        }
    }
	
	$num_dados = $query_grupos->rowCount();
    if ($num_dados > 0) {
        for ($i = 0; $i < $num_dados; $i++) {
            $linha_dados = $query_grupos->fetch(PDO::FETCH_ASSOC);
            $arr_dados[] = array('entidade_grupo' => $linha_dados['entidade_grupo'], 'id_empresa' => $linha_dados['id_empresa'], 'nome' => $linha_dados['nome_empresa'], 'nome_atv' => $linha_dados['nome_atv']);
        }
        $arr = array('sucesso' => true, 'vazio' => false, 'dados_in' => $arr_dados);
    } else {
        $arr = array('sucesso' => true, 'vazio' => true, 'mensagem' => "Não existem empresas para o grupo selecionado");
    }
    logClicks($connection, "115");
}

echo json_encode($arr);
$connection = null;
