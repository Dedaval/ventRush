"use strict";

import { EMAIL, ERROR, PASSWORD, SERVICE_CONNECTION, SERVICE_URL, SERVICE_USER, TOKEN } from "./utils/constants.js";
import { checkFields, displayMessageErrorApi, resetMessage, sendData, toJson } from "./utils/function.js";

const registerButton = document.querySelector('#inscription-bouton');
registerButton?.addEventListener('click', async (e) => {
    e.preventDefault();
    const nom = document.querySelector('#inscription-nom').value;
    const prenom = document.querySelector('#inscription-prenom').value;
    const email = document.querySelector('#inscription-email').value;
    const password = document.querySelector('#inscription-motDePasse').value;
    const champErreurNom = document.querySelector('#erreur-inscription-nom');
    const champErreurPrenom = document.querySelector('#erreur-inscription-prenom');
    const fieldErrorEmail = document.querySelector('#erreur-inscription-email');
    const fieldErrorPassword = document.querySelector('#erreur-inscription-motDePasse');

    const fields = {
        nom: nom,
        prenom: prenom,
        email: email,
        password: password,
    };

    const fieldsError = {
        nom: champErreurNom,
        prenom: champErreurPrenom,
        email: fieldErrorEmail,
        password: fieldErrorPassword,
    };
    console.log(fields)
    resetMessage(fieldErrorEmail, fieldErrorPassword);
    if (!checkFields(fields, fieldsError))
        return;
    const result = await sendData(SERVICE_USER, "POST", fields);
    const json = await toJson(result);
    console.log(json);
    console.log(result);
    if (result.ok) {
        if (!json.token) {
            console.log('Error, ' + 'API haven\'t send a token');
            return;
        }
        localStorage.setItem(TOKEN, json.token);
        window.location.href = 'index.html';
        return;
    }
    if (Array.isArray(json)) {
        for (const element of json) {
            if (element.field === EMAIL) {
                fieldErrorEmail.style.display = "block";
                fieldErrorEmail.textContent = displayMessageErrorApi(element.message);
                continue;

            }
            if (element.field === PASSWORD) {
                fieldErrorPassword.style.display = "block";
                fieldErrorPassword.textContent = displayMessageErrorApi(element.message);
                continue;
            }
        }
    } else {
        console.error(json.message);
    }


});

const loginButton = document.querySelector('#login-button');
loginButton?.addEventListener('click', async (e) => {
    e.preventDefault();
    const email = document.querySelector('#login-email').value;
    const password = document.querySelector('#login-password').value;
    const fieldErrorEmail = document.querySelector('#error-login-email');
    const fieldErrorPassword = document.querySelector('#error-login-password');
    const fieldError = document.querySelector('#error-login');

    const fields = {
        email: email,
        password: password,
    };

    const fieldsError = {
        email: fieldErrorEmail,
        password: fieldErrorPassword,
        none: fieldError,
    };

    resetMessage(fieldsError);
    if (!checkFields(fields, fieldsError))
        return;

    const result = await sendData(SERVICE_CONNECTION, "POST", fields);
    const json = await toJson(result);
    if (result.ok) {
        if (!json.token) {
            console.log('Error, ' + 'API haven\'t send a token');
            return;
        }
        localStorage.setItem(TOKEN, json.token);
        window.location.href = 'index.html';
        return;
    }
    fieldError.style.display = "block";
    fieldError.textContent = displayMessageErrorApi(json.message);

});