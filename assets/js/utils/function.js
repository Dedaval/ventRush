"use strict";
//#region Imports



import { AMOUNT, AMOUNT_MIN_VALUE, CURRENCY, EMAIL, MATCH_FIELD_FUNCTION, PASSWORD, PASSWORD_MIN_LENGTH, SERVICE_URL, SERVICE_USER, TITLE, TITLE_MIN_LENGTH, REGEX_VALIDATE_EMAIL, TOKEN, NAVIGATION, SERVICE_CONNECTION } from "./constants.js";
//#endregion

//#region Validate fields
export function validateEmail(email) {
	if (!email)
		return false;
    return REGEX_VALIDATE_EMAIL.test(email.toLowerCase());
}

export function validatePassword(password){
    if (!password)
        return false;
    return password.length >= PASSWORD_MIN_LENGTH;
}

export function validateTitle(title){
    if (!title)
        return false;
    return title.length >= TITLE_MIN_LENGTH;
}

export function validateDescription(description){
    return true
}

export function validateAmount(amount){
    if (!amount)
        return false;
    return amount.length >= AMOUNT_MIN_VALUE;
}
export function validateCurrency(currency){
    if (!currency)
        return false;
    currency = currency.toLowerCase();
    return Object.values(Currency).includes(currency);
}
//#endregion

export function checkFields(fields, fieldsError) {
    let error = false;

    MATCH_FIELD_FUNCTION.forEach(element => {
        const key = Object.keys(element)[0];
        const validateFn = element[key];

        const value = fields[key];
        const errorField = fieldsError[key];

        if (!validateFn(value)) {
            if (errorField) {
                errorField.style.display = "block";
                errorField.textContent = `The field ${key} is invalid`;
                error = true;
            }
            
        } else {
            if (errorField) {
                errorField.style.display = "none";
                errorField.textContent = '';
            }
        }
    });

    return !error;
}
export function resetMessage(...fields){
    for (const field of fields){
        if (field.style !== undefined)
        field.style.display = "none";
        field.textContent = null;
    }
}


export async function sendData(service, method, data = null){

    let dataJson = null;
    if (data){
        dataJson = JSON.stringify(data);
    }
    const token = getToken();
    try{
        const properties = {
            method: method,
            headers: { 
                "Content-Type": "application/json",
            },
        }

        if (token){
            properties.headers["Authorization"] = `Token ${token}`;
        }
        if (dataJson){
            properties.body = dataJson;
        }
        
        const response = await fetch(SERVICE_URL + service, properties);
        return await (response);
    }
    catch(error){
        console.error("Error fetch," + error);
    }
}
export async function toJson(response){
    try{
        return await response.json()
    }
    catch(error){
        console.error("Error JSON," + error);
    }
}

export function getToken(){
    return localStorage.getItem(TOKEN);
}

export function isLogin(){
    const token = getToken();
    return token != null;
}

export function createNavigation(container, navigation, needLogin){
    for (const object of navigation) {
        if (object.needLogin == needLogin || object.accessAnywhere){
            const a = document.createElement('a');
            a.className = 'nav-link';

            if (object.href !== null)
                a.href = object.href;

            a.textContent = object.name;
            a.onclick = object.onclick || null;

            container.appendChild(a);
        }       
    }
}
export async function logout(){
    const result = await sendData(SERVICE_CONNECTION, "DELETE" );
    if (!result.ok) {
        const message = await toJson(result);    
        console.log(message);
        return;
    }
    localStorage.removeItem(TOKEN);
    window.location.href = 'index.html';       
}
export function displayMessageErrorApi(messageError) {
    const  formatted = messageError.replace(/\./g, " ");
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
}