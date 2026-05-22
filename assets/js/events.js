"use strict";

import {
    DESCRIPTION,
    TITLE,
    SERVICE_EVENEMENTS,
} from "./utils/constants.js";
import { sendData, supprimerAnnonce, toJson } from "./utils/function.js";

const adsList = document.querySelector("#ads-list");
const messageError = document.querySelector(".messageError");

const modal = document.createElement("div");
modal.innerHTML = `
  <div id="participants-backdrop"
       style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1040"
       aria-hidden="true"></div>
  <div id="participants-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title"
       style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
              z-index:1050;background:#fff;border-radius:.5rem;padding:1.5rem;
              min-width:320px;max-width:480px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.2)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
      <h5 id="modal-title" style="margin:0">Participants</h5>
      <button id="modal-close" aria-label="Fermer"
              style="background:none;border:none;font-size:1.4rem;cursor:pointer;line-height:1">&times;</button>
    </div>
    <ul id="modal-list" style="list-style:none;padding:0;margin:0;max-height:300px;overflow-y:auto"></ul>
  </div>`;
document.body.append(modal);

const backdrop = document.getElementById("participants-backdrop");
const modalEl = document.getElementById("participants-modal");
const modalList = document.getElementById("modal-list");
const modalClose = document.getElementById("modal-close");

function openModal(participants) {
    modalList.innerHTML = "";
    if (!participants.length) {
        const li = document.createElement("li");
        li.style.color = "#888";
        li.textContent = "Aucun participant pour le moment.";
        modalList.append(li);
    } else {
        participants.forEach(p => {
            const li = document.createElement("li");
            li.style.cssText = "padding:.4rem 0;border-bottom:1px solid #f0f0f0";
            li.textContent = `${p.prenom} ${p.nom}`;
            modalList.append(li);
        });
    }
    backdrop.style.display = "block";
    modalEl.style.display = "block";
    modalClose.focus();
}

function closeModal() {
    backdrop.style.display = "none";
    modalEl.style.display = "none";
}

modalClose.addEventListener("click", closeModal);
backdrop.addEventListener("click", closeModal);
document.addEventListener("keydown", e => { if (e.key === "Escape") closeModal(); });

function createDisplayAdCard(ad) {
    const idAd = ad.idAd;
    const title = ad?.[TITLE] ?? "Sans titre";
    const description = ad?.[DESCRIPTION] ?? "Aucune description";
    const date = ad.date ? new Date(ad.date).toLocaleDateString("fr-CH") : "";
    const maxUsers = ad.nbMaxUtilisateurs;
    const editable = ad.editable ?? false;
    const participant = ad.participant ?? false;

    // ── Conteneur
    const col = document.createElement("div");
    col.className = "col-md-6 col-lg-4 mb-4";

    const card = document.createElement("div");
    card.className = "card h-100";

    // ── Corps
    const cardBody = document.createElement("div");
    cardBody.className = "card-body";

    const h5 = document.createElement("h5");
    h5.className = "card-title";
    h5.textContent = title;

    const pDesc = document.createElement("p");
    pDesc.className = "card-text text-muted";
    pDesc.textContent = description;

    const pMeta = document.createElement("p");
    pMeta.className = "card-text";
    pMeta.innerHTML =
        `<small class="text-muted">📅 ${date}` +
        (maxUsers ? `&nbsp;&nbsp;👥 max ${maxUsers}` : "") +
        `</small>`;

    cardBody.append(h5, pDesc, pMeta);

    // ── Pied
    const footer = document.createElement("div");
    footer.className = "card-footer d-flex align-items-center justify-content-between gap-2 flex-wrap";

    // Bouton « Voir participants »
    const viewBtn = document.createElement("button");
    viewBtn.type = "button";
    viewBtn.className = "btn btn-sm btn-outline-info";
    viewBtn.textContent = "Voir les participants";
    viewBtn.addEventListener("click", async () => {
        viewBtn.disabled = true;
        viewBtn.textContent = "Chargement…";
        try {
            const res = await sendData(`inscriptionEvenement.php?id=${idAd}`, "GET");
            if (res?.ok) {
                const data = await toJson(res);
                openModal(data.participants ?? []);
            } else {
                alert("Impossible de récupérer les participants.");
            }
        } finally {
            viewBtn.disabled = false;
            viewBtn.textContent = "Voir les participants";
        }
    });

    // Bouton « S'inscrire »
    const inscriptionBtn = document.createElement("button");
    inscriptionBtn.type = "button";
    inscriptionBtn.className = "btn btn-sm btn-primary";

    if (participant) {
        inscriptionBtn.textContent = "Inscrit ✓";
        inscriptionBtn.disabled = true;
    } else {
        inscriptionBtn.textContent = "S'inscrire";
        inscriptionBtn.addEventListener("click", async () => {
            inscriptionBtn.disabled = true;
            inscriptionBtn.textContent = "Inscription…";
            const res = await sendData("inscriptionEvenement.php", "POST", {
                evenements_id: idAd
            });
            if (res?.ok) {
                inscriptionBtn.textContent = "Inscrit ✓";
            } else {
                const body = await res?.json().catch(() => null);
                const msg = body?.errors?.[0]?.message ?? "Erreur inconnue";
                if (msg === "already.registered") {
                    inscriptionBtn.textContent = "Déjà inscrit";
                } else if (msg === "event.full") {
                    inscriptionBtn.textContent = "Complet";
                    alert("Cet événement est complet.");
                } else {
                    alert("Erreur lors de l'inscription : " + msg);
                    inscriptionBtn.disabled = false;
                    inscriptionBtn.textContent = "S'inscrire";
                }
            }
        });
    }

    // Boutons édition / suppression (propriétaire seulement)
    const btnGroup = document.createElement("div");
    btnGroup.className = "d-flex gap-2 ms-auto";

    if (editable) {
        const editBtn = document.createElement("button");
        editBtn.type = "button";
        editBtn.className = "btn btn-sm btn-outline-secondary";
        editBtn.title = "Modifier";
        editBtn.dataset.adId = idAd;
        editBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
            viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-9.5 9.5L3 14
                     l.646-2.646 9.5-9.5zM11.207 2 4 9.207V12h2.793L14 4.793 11.207 2z"/></svg>`;
        editBtn.addEventListener("click", () => {
            window.location.href = `./creerEvenement.html?id=${idAd}`;
        });

        const delBtn = document.createElement("button");
        delBtn.type = "button";
        delBtn.className = "btn btn-sm btn-outline-danger text-danger";
        delBtn.title = "Supprimer";
        delBtn.dataset.adId = idAd;
        delBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
            viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm4.5.5v6
                     a.5.5 0 0 1-1 0V6a.5.5 0 0 1 1 0z"/>
            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1
                     0-2H5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h2.5a1 1 0 0 1 1 1zM4 4v9a1 1 0 0 0 1
                     1h6a1 1 0 0 0 1-1V4H4z"/></svg>`;
        delBtn.addEventListener("click", () => supprimerAnnonce(idAd));

        btnGroup.append(editBtn, delBtn);
    }

    footer.append(viewBtn, inscriptionBtn, btnGroup);
    card.append(cardBody, footer);
    col.append(card);

    return col;
}

// ─── Chargement ──────────────────────────────────────────────────
async function displayAds() {
    const result = await sendData(SERVICE_EVENEMENTS, "GET");
    if (!result?.ok) {
        messageError.style.display = "block";
        return;
    }

    const allAds = await toJson(result);

    if (!Array.isArray(allAds) || allAds.length === 0) {
        messageError.style.display = "block";
        messageError.textContent = "Aucun événement disponible";
        return;
    }

    allAds.forEach(ad => adsList.append(createDisplayAdCard(ad)));
}

displayAds();