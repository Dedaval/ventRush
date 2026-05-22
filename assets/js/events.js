"use strict";
const inscriptionBtn = document.createElement("button");
inscriptionBtn.type = "button";
inscriptionBtn.className = "btn btn-sm btn-primary";
inscriptionBtn.textContent = "S'inscrire";

inscriptionBtn.addEventListener('click', async () => {
    const result = await sendData('inscription.php', 'POST', {
        evenements_id: evenement.id
    });

    if (result?.ok) {
        inscriptionBtn.disabled = true;
        inscriptionBtn.textContent = "Inscrit";
    } else {
        alert("Erreur lors de l'inscription.");
    }
});


import { AMOUNT, CURRENCY, DESCRIPTION, PRICE, SERVICE_EVENEMENT, SERVICE_EVENEMENTS, TITLE } from "./utils/constants.js";
import { sendData, supprimerAnnonce, toJson } from "./utils/function.js";

const adsList = document.querySelector("#ads-list");
const messageError = document.querySelector(".messageError");

function createDisplayAdCard(ad) {
    const idAd = ad.idAd;
    const title = ad?.[TITLE] ?? "Sans titre";
    const description = ad?.[DESCRIPTION] ?? "Aucune description";
    const amount = ad?.[PRICE]?.[AMOUNT] ?? "-";
    const currency = ad?.[PRICE]?.[CURRENCY] ?? "";
    const editable = ad.editable ?? false;
    // Col wrapper
    const col = document.createElement("div");
    col.className = "col-md-6 col-lg-4 mb-4";

    // Card
    const card = document.createElement("div");
    card.className = "card h-100";

    // Card body
    const cardBody = document.createElement("div");
    cardBody.className = "card-body";

    const h5 = document.createElement("h5");
    h5.className = "card-title";
    h5.textContent = title;

    const p = document.createElement("p");
    p.className = "card-text";
    p.textContent = description;

    cardBody.append(h5, p);

    // Card footer
    const footer = document.createElement("div");
    footer.className = "card-footer d-flex align-items-center justify-content-between";

    const strong = document.createElement("strong");
    strong.textContent = `${amount} ${currency}`;

    // Buttons container
    const btnGroup = document.createElement("div");
    btnGroup.className = "d-flex gap-2";

    // Edit button
    const editBtn = document.createElement("button");
    editBtn.type = "button";
    editBtn.className = "btn btn-sm btn-outline-secondary ad-edit-btn";
    editBtn.title = "Modifier";
    if (idAd) editBtn.dataset.adId = idAd;
    if (editable === false) editBtn.disabled = true;

    // Simple SVG for edit (pencil)
    editBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-9.5 9.5L3 14l.646-2.646 9.5-9.5zM11.207 2 4 9.207V12h2.793L14 4.793 11.207 2z"/>
        </svg>
    `;
    editBtn.addEventListener('click', () => {
        window.location.href = `../views/create_ad.html?id=${idAd}`;
    });

    // Delete button
    const delBtn = document.createElement("button");
    delBtn.type = "button";
    delBtn.className = "btn btn-sm btn-outline-danger ad-delete-btn text-danger";
    delBtn.title = "Supprimer";
    if (idAd) delBtn.dataset.adId = idAd;
    if (editable === false) delBtn.disabled = true;

    // Simple SVG for delete (trash)
    delBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm4.5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 1 0z"/>
            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2H5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h2.5a1 1 0 0 1 1 1zM4 4v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4H4z"/>
        </svg>
    `;
    delBtn.addEventListener('click', () => {
        supprimerAnnonce(idAd);
    });

    btnGroup.append(editBtn, delBtn);
    if (editable)
        footer.append(btnGroup);
    footer.append(strong);

    card.append(cardBody, footer);
    col.append(card);

    return col;
}

async function displayAds() {

    const result = await sendData(SERVICE_EVENEMENTS, "GET");
    if (result === undefined || !result.ok) {
        messageError.style.display = "block";
        console.log(result);
        return;
    }

    const allAds = await toJson(result);
    console.log(allAds)

    if (!Array.isArray(allAds) || allAds.length === 0) {
        messageError.style.display = "block";
        messageError.textContent = 'Aucun evenement disponible';
        return;
    }

    allAds.forEach(ad => {
        const adCard = createDisplayAdCard(ad);
        adsList.append(adCard);
    });
}

displayAds();