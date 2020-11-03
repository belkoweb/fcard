$(document).ready(function () {

    // method for mail verification
    $.validator.addMethod("mailverified", function (value, element, params) {
        let pattern = new RegExp(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/);
        return pattern.test(value);
    }, "Veuillez saisir une adresse mail valide");

    // main method of jQuery validation plugin
    $('#form-registration').validate({
        rules: {
            "registration[email]": {
                required: true,
                mailverified: true, // replace mail property
                maxlength: 100
            },
            "registration[pseudo]": {
                required: true,
                maxlength: 100
            },
            "registration[password]": {
                required: true,
                maxlength: 100,
                minlength: 10
            },
            "registration[confirm_password]": {
                required: true,
                maxlength: 100,
                minlength: 10,
                equalTo: "#registration_password"
            }
        },
        messages: {
            "registration[email]": {
                required: "Veuillez saisir votre adresse mail",
                email: "Veuillez saisir une adresse mail valide",
                mailverified: "Veuillez saisir une adresse mail valide", // replace mail property
                maxlength: "Veuillez saisir une adresse mail valide"
            },
            "registration[pseudo]": {
                required: "Veuillez saisir votre pseudo",
                maxlength: "Veuillez saisir un pseudo moins long"
            },
            "registration[password]": {
                required: "Veuillez saisir votre mot de passe",
                maxlength: "Veuillez saisir un mot de passe moins long",
                minlength: "Votre mot de passe doit faire plus de 10 caractères"
            },
            "registration[confirm_password]": {
                required: "Veuillez confirmer votre mot de passe",
                maxlength: "Veuillez saisir un mot de passe moins long",
                minlength: "Votre mot de passe doit faire plus de 10 caractères",
                equalTo: "Veuillez saisir le même mot de passe"
            }
        }
    });
    
});