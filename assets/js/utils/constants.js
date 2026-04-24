"use strict";

import { validateAmount, validateCurrency, validateEmail, validatePassword, validateTitle, validateDescription, logout } from "./function.js     ";

//#region field
export const EMAIL = 'email';
export const PASSWORD = 'password';
export const TOKEN = 'token';
export const TITLE = 'title';
export const DESCRIPTION = 'description';
export const PRICE = 'price';
export const AMOUNT = 'amount';
export const CURRENCY = 'currency';
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
    { [CURRENCY]: validateCurrency }
];
//#endregion

//#region Server
export const SERVICE_URL = "http://localhost/ventRush/api/"
export const SERVICE_USER = "user.php";
export const SERVICE_CONNECTION = "connection.php";
export const SERVICE_AD = "ad";
export const SERVICE_ADS = "ads";

export const NAVIGATION = [
    { name: 'Créer un compte', href: 'register.html', needLogin: false, id: "btnSignIn" },
    { name: 'Se connecter', href: 'login.html', needLogin: false, id: "btnLogin" },
    { name: 'Se déconnecter', href: null, needLogin: true, onclick: logout, id: "btnLogout" },
    { name: 'Voir les événements', href: 'ads.html', needLogin: false, accessAnywhere: true, id: "btnAd" },
    { name: 'Créer une événements', href: 'create_ad.html', needLogin: true, id: "btnCreateAd" },
    
];
