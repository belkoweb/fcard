$(document).ready(function () {

    // method for mail verification
    $.validator.addMethod("mailverified", function (value, element, params) {
        let pattern = new RegExp(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/);
        return pattern.test(value);
    }, "Veuillez saisir une adresse mail valide");

    // main method of jQuery validation plugin
    $('#form-login').validate({
        rules: {
            _username: {
                required: true,
                mailverified: true, // replace mail property
                maxlength: 100
            },
            _password: {
                required: true,
                maxlength: 100
            }
        },
        messages: {
            _username: {
                required: "Veuillez saisir votre adresse mail",
                email: "Veuillez saisir une adresse mail valide",
                mailverified: "Veuillez saisir une adresse mail valide", // replace mail property
                maxlength: "Veuillez saisir une adresse mail valide"
            },
            _password: {
                required: "Veuillez saisir votre mot de passe",
                maxlength: "Veuillez saisir un mot de passe moins long"
            }
        }
    });

});