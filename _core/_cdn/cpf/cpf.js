$(document).ready(function() {

    function validarCPF(cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');

        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
            return false;
        }

        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        let digito1 = (resto >= 10) ? 0 : resto;

        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = 11 - (soma % 11);
        let digito2 = (resto >= 10) ? 0 : resto;

        return digito1 === parseInt(cpf.charAt(9)) && digito2 === parseInt(cpf.charAt(10));
    }

    $("input[name=cpf]").blur(function() {
        let cpf = $(this).val();
        let $campo = $(this);

        if (cpf !== "") {
            if (validarCPF(cpf)) {
                $campo.removeClass("input-error");
            } else {
                $campo.addClass("input-error");
                $campo.focus();
            }
        } else {
            $campo.removeClass("input-error");
        }
    });

});
