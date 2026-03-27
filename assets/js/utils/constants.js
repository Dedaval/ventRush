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
export const SERVICE_URL = "https://devmob.ictge.ch/~kramer/other/api/"
// export const SERVICE_URL = "https://devmob.ictge.ch/~allegra/small_ads/";
export const SERVICE_USER = "user";
export const SERVICE_CONNECTION = "connection";
export const SERVICE_AD = "ad";
export const SERVICE_ADS = "ads";

export const NAVIGATION = [
    { name: 'Créer un compte', href: 'register.html', needLogin: false },
    { name: 'Se connecter', href: 'login.html', needLogin: false },
    { name: 'Se déconnecter', href: null, needLogin: true, onclick: logout },
    { name: 'Voir les annonces', href: 'ads.html', needLogin: false, accessAnywhere: true },
    { name: 'Créer une annonce', href: 'create_ad.html', needLogin: true },
    
];