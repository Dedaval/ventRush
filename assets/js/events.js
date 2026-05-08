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