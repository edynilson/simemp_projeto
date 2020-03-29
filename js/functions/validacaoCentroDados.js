/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-22 15:05:44
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-08-19 10:29:18
 */

function validaFormCalendario(id_grupo, mes, ano_val, date, data_i, data_f, cor) {
    valido = true;
    if (id_grupo == 0 || id_grupo == '') {
        valido = false;
        data = "Escolha um grupo";
        return data;
    }
    if (mes == 0 || mes == '') {
        valido = false;
        data = "Escolha um mês";
        return data;
    }
    if (ano_val == 0 || ano_val == '') {
        valido = false;
        data = "Escolha um ano";
        return data;
    }
    if (data_i == 0 || data_i == '') {
        valido = false;
        data = "Escreva uma data inicial (" + (("0" + date.getDate()).slice(-2)) + "-" + (("0" + (date.getMonth() + 1)).slice(-2)) + "-" + date.getFullYear() + " 00:00:00)";
        return data;
    }
    if (data_f == 0 || data_f == '') {
        valido = false;
        data = "Escreva uma data final (" + (("0" + date.getDate()).slice(-2)) + "-" + (("0" + (date.getMonth() + 1)).slice(-2)) + "-" + date.getFullYear() + " 23:59:59)";
        return data;
    }
    if (cor == 0 || cor == '') {
        valido = false;
        data = "Escolha uma cor";
        return data;
    }
    if (valido == true) {
        return true;
    }
}

function validaNovoGrupo(nome, grupo) {
    valido = true;
    if (nome === 0) {
        valido = false;
        data = "Insira um nome válido";
        return data;
    }
    if (grupo === 0) {
        valido = false;
        data = "Escolha um grupo";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaAtividade(nome, cap_soc) {
    valido = true;
    if (nome === "") {
        valido = false;
        data = "Insira um nome";
        return data;
    }
    if (cap_soc === "" || !/^([1-9]{1}[0-9]{0,}([,.][0-9]{1,2})?|0([,.][0-9]{0,2}))$/.test(cap_soc)) {
        valido = false;
        data = "Insira um capital social";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaMudaPass(pass, conf_pass) {
    valido = true;
    if (pass !== conf_pass) {
        valido = false;
        data = "As palavras-passe não correspondem";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaNovoAdminLDAP(username) {
    valido = true;
    if (username === "") {
        valido = false;
        data = "Insira um nome de utilizador";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaNovoAdminNLDAP(username, nome, pass, pass_conf, email) {
    valido = true;
    if (username === "") {
        valido = false;
        data = "Insira um nome de utilizador";
        return data;
    }
    if (nome === "" || !/\s/.test(nome)) {
        valido = false;
        data = "Insira o nome completo";
        return data;
    }
    if (pass === "") {
        valido = false;
        data = "Insira uma palavra-passe";
        return data;
    }
    if (pass_conf === "") {
        valido = false;
        data = "Insira a confirmação da palavra-passe";
        return data;
    }
    if (pass !== pass_conf) {
        valido = false;
        data = "As palavras-passe não correspondem";
        return data;
    }
    if (email === "") {
        valido = false;
        data = "Insira um endereço de correio eletrónico";
        return data;
    }
    if (!/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/.test(email)) {
        data = "Insira um endereço de correio eletrónico válido";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaPass(pass_old, pass_new, conf_pass) {
    var valido = true;
    if (pass_old.length === 0) {
        data = "Escreva a palavra-passe antiga";
        valido = false;
        return data;
    }
    if (pass_new.length === 0) {
        data = "Escreva a palavra-passe nova";
        valido = false;
        return data;
    }
    if (conf_pass.length === 0) {
        data = "Escreva a confirmação da palavra-passe";
        valido = false;
        return data;
    }
    if (pass_new !== conf_pass) {
        data = "Palavras-passe não correspondem";
        valido = false;
        return data;
    }
    if (pass_new == pass_old) {
        data = "Palavra-passe nova tem de ser diferente da palavra-passe antiga";
        valido = false;
        return data;
    }
    if (valido === true) {
        return true;
    }
}