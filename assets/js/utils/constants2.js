"use strict";

import { validerPrenom, validerNom, validerEmail, validerMotDePasse, logout } from "./fonction.js";

//#region field
export const NOM = 'nom';
export const PRENOM = 'prenom';
export const EMAIL = 'email';
export const MOT_DE_PASSE = 'motDePasse';
export const TOKEN = 'token';
//#endregion

//#region state
export const SUCCESS = 'success';
export const ERREUR = 'erreur';
//#endregion

//#region validation
export const REGEX_VALIDER_EMAIL = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
export const MOT_DE_PASSE_MIN_LONGUEUR = 5;
export const NOM_MIN_LONGUEUR = 2;
export const PRENOM_MIN_LONGUEUR = 2;
export const MATCH_FIELD_FUNCTION = [
    { [EMAIL]: validerEmail },
    { [MOT_DE_PASSE]: validerMotDePasse },
    { [PRENOM]: validerPrenom },
    { [NOM]: validerNom },
];
//#endregion

//#region Server
export const SERVICE_URL = "http://localhost/ventRush/api/"
export const SERVICE_UTILISATEUR = "user.php";
export const SERVICE_CONNEXION = "connection";
export const SERVICE_EVENEMENT = "ad";
export const SERVICE_EVENEMENTS = "ads";

export const NAVIGATION = [
    { name: 'Créer un compte', href: 'register.html', needLogin: false },
    { name: 'Se connecter', href: 'login.html', needLogin: false },
    { name: 'Se déconnecter', href: null, needLogin: true, onclick: logout },
    { name: 'Voir les événement', href: 'ads.html', needLogin: false, accessAnywhere: true },
    { name: 'Supprimer une événement', href: 'create_ad.html', needLogin: true },
    
];