"use strict";

import { checkFields, displayMessageErrorApi, getAd, resetMessage, sendData, toJson } from "./utils/function.js";
import { DATE, DESCRIPTION, NB_MAX_PARTIPANT, PRICE, SERVICE_EVENEMENT, TITLE, TOKEN } from "./utils/constants.js";

const btnSubmit = document.querySelector('#submit');
const params = new URLSearchParams(window.location.search);
const id = params.get("id");

document.addEventListener("DOMContentLoaded", async () => {
    const nom = document.querySelector('#evenement-nom');;
    const description = document.querySelector('#evenement-description');
    const date = document.querySelector('#evenement-date');
    const titre = document.querySelector('.titre');


    if (id === null)
        return;
    titre.textContent = "Modifier un evenement";
    btnSubmit.textContent = "Modifier";

    const ad = await getAd(id);
    nom.value = ad.titre;
    description.value = ad.description;
    date.value = ad.price.amount;
});

btnSubmit?.addEventListener("click", async (e) => {
    e.preventDefault();

    const nom = document.querySelector('#evenement-nom').value.trim() || null;;
    const description = document.querySelector('#evenement-description').value.trim() || null;
    const date = document.querySelector('#evenement-date').value.trim() || null;
    const nbMaxParticipant = document.querySelector('#evenement-nbMaxParticipant').value.trim() || null;

    const nomError = document.querySelector('#error-evenement-nom');
    const descriptionError = document.querySelector('#error-evenement-description');
    const dateError = document.querySelector('#error-evenement-date');
    const nbMaxParticipantError = document.querySelector('#error-evenement-nbMaxParticipant');

    const fields = {
        [TITLE]: nom,
        [DESCRIPTION]: description,
        [DATE]: date,
        [NB_MAX_PARTIPANT]: nbMaxParticipant,
    };

    const fieldsError = {
        [TITLE]: nomError,
        [DESCRIPTION]: descriptionError,
        [DATE]: dateError,
        [NB_MAX_PARTIPANT]: nbMaxParticipantError,
    };

    resetMessage(fieldsError);
    if (!checkFields(fields, fieldsError))
        return;

    const data = {
        [TITLE]: fields[TITLE],
        [DESCRIPTION]: fields[DESCRIPTION],
        [DATE]: fields[DATE],
        [NB_MAX_PARTIPANT]: parseFloat(fields[NB_MAX_PARTIPANT]),

    };
    let result;
    if (id !== null){
        result = await sendData(SERVICE_EVENEMENT, "PUT", data, id);
    }
    else 
        console.log(data);
        result = await sendData(SERVICE_EVENEMENT, "POST", data);
    const json = await toJson(result);
    if (result.ok) {
        window.location.href = 'index.html';
    }
    if (Array.isArray(json)) {
        for (const element of json) {
            if (element.field === TITLE) {
                nomError.style.display = "block";
                nomError.textContent = displayMessageErrorApi(element.message);
            }
            if (element.field === DESCRIPTION) {
                descriptionError.style.display = "block";
                descriptionError.textContent = displayMessageErrorApi(element.message);
            }
        }
    } else if (json.message) {
        console.error(json.message);
    }

});
