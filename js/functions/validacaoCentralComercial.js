/*
 * @Author: Ricardo Órfão
 * @Date:   2014-07-31 16:17:23
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-08-04 08:44:31
 */

function validaAddProd(id_categoria, id_subcat, id_familia, nome, fornecedor, preco, iva) {
    valido = true;
    if (id_categoria == '' || id_categoria == 0) {
        valido = false;
        data = "Escolha uma categoria";
        return data;
    }
    if (id_subcat == '' || id_subcat == 0) {
        valido = false;
        data = "Escolha uma subcategoria";
        return data;
    }
    if (id_familia == '' || id_familia == 0) {
        valido = false;
        data = "Escolha uma família";
        return data;
    }
    if (nome == "") {
        valido = false;
        data = "Escolha um nome";
        return data;
    }
    if (fornecedor == "" || fornecedor == 0) {
        valido = false;
        data = "Escolha um fornecedor";
        return data;
    }
    if (preco == "" || preco == 0) {
        valido = false;
        data = "Escolha um preço";
        return data;
    }
    if (iva == "" || iva == 0) {
        valido = false;
        data = "Escolha uma taxa de IVA";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaCategoria(categoria) {
    valido = true;
    if (categoria === 0) {
        valido = false;
        data = "Tem de inserir um nome";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaFamilia(categoria, subcategoria, nome) {
    valido = true;
    if (categoria === 0) {
        valido = false;
        data = "Escolha uma categoria";
        return data;
    }
    if (subcategoria === 0) {
        valido = false;
        data = "Escolha uma subcategoria";
        return data;
    }
    if (nome === 0) {
        valido = false;
        data = "Tem de inserir um nome";
        return data;
    }
    if (valido === true) {
        return true;
    }
}

function validaSubcategoria(categoria, nome) {
    valido = true;
    if (categoria === 0) {
        valido = false;
        data = "Escolha uma categoria";
        return data;
    }
    if (nome === 0) {
        valido = false;
        data = "Tem de inserir um nome";
        return data;
    }
    if (valido === true) {
        return true;
    }
}