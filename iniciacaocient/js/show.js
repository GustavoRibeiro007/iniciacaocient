function mostrarParticipantes() {
    const quantidade = document.getElementById("quantParticipantes").value;
    let participantesDiv = document.getElementById("participantes");
    participantesDiv.innerHTML = ""; // Limpa os campos existentes

    // Cria campos de participante de acordo com a quantidade selecionada
    for (let i = 1; i <= quantidade; i++) {
        participantesDiv.innerHTML += `
            <label for="participanteNome${i}">Participante ${i} (Nome e RA):</label><br>
            <input type="text" id="participanteNome${i}" name="participanteNome${i}" placeholder="Nome" required><br>
            <input type="text" id="participanteRA${i}" name="participanteRA${i}" placeholder="RA" required><br><br>
        `;
    }
}

function mostrarGitHub() {
    const githubDiv = document.getElementById("github");
    const alunoTI = document.getElementById("alunoTI").checked;

    if (alunoTI) {
        githubDiv.style.display = "block";
    } else {
        githubDiv.style.display = "none";
    }
}