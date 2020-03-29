/*
 * @Author: Ricardo Órfão
 * @Date:   2014-08-04 18:19:56
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-08-05 11:36:01
 */

function validaOp(valor, descricao, destinatario) {
    valido = true;
    if (valor === 0 || !/^([1-9]{1}[0-9]{0,}([,.][0-9]{1,2})?|0([,.][0-9]{0,2}))$/.test(valor)) {
        valido = false;
        data = "Insira um valor";
        return data;
    }
    if (descricao === 0) {
        valido = false;
        data = "Insira uma descrição";
        return data;
    }
    if (destinatario === "") {
        valido = false;
        data = "Insira um destinatário";
        return data;
    }
    if (valido === true) {
        return true;
    }
}