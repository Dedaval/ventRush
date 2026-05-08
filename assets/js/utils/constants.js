"use strict";

import { validateAmount, validateDate,validateCurrency, validateEmail, validatePassword, validateTitle, validateDescription, logout } from "./function.js     ";

//#region field
export const EMAIL = 'email';
export const PASSWORD = 'password';
export const TOKEN = 'token';
export const TITLE = 'title';
export const DESCRIPTION = 'description';
export const PRICE = 'price';
export const AMOUNT = 'amount';
export const CURRENCY = 'currency';
export const DATE = 'date';
export const NB_MAX_PARTIPANT = 'nbMaxUtilisateurs';
//#endregion

//#region state
export const SUCCESS = 'success';
export const ERROR = 'error';
//#endregion

//#region validation
export const REGEX_VALIDATE_EMAIL = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
export const PASSWORD_MIN_LENGTH = 5;
export const TITLE_MIN_LENGTH = 2;
export const AMOUNT_MIN_VALUE = 0;
export const MATCH_FIELD_FUNCTION = [
    { [EMAIL]: validateEmail },
    { [PASSWORD]: validatePassword },
    { [TITLE]: validateTitle },
    { [DESCRIPTION]: validateDescription },
    { [AMOUNT]: validateAmount },
    { [CURRENCY]: validateCurrency },
    { [DATE]: validateDate},
];
//#endregion

//#region Server
export const SERVICE_URL = "http://localhost/ventRush/api/"
export const SERVICE_USER = "user.php";
export const SERVICE_CONNECTION = "connection.php";
export const SERVICE_EVENEMENT = "evenement.php";
export const SERVICE_EVENEMENTS = "evenements.php";

export const NAVIGATION = [
    { name: 'Créer un compte', href: 'inscription.html', needLogin: false, id: "btnSignIn" },
    { name: 'Se connecter', href: 'login.html', needLogin: false, id: "btnLogin" },
    { name: 'Se déconnecter', href: null, needLogin: true, onclick: logout, id: "btnLogout" },
    { name: 'Voir les événements', href: 'evenements.html', needLogin: false, accessAnywhere: true, id: "btnAd" },
    { name: 'Créer un événements', href: 'creerEvenement.html', needLogin: true, id: "btnCreateAd" },
    
];
