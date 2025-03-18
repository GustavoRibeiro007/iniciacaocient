function mostrarCampos() {
    const numParticipantes = document.getElementById('num-participantes').value;
    const camposContainer = document.getElementById('participant-fields');
    camposContainer.innerHTML = '';  // Limpar campos anteriores

    if (numParticipantes == 0) return;

    for (let i = 1; i <= numParticipantes; i++) {
        const participanteDiv = document.createElement('div');
        participanteDiv.classList.add('participant');
        
        // Criando o contêiner para Nome e RG (div com display flex)
        const nomeDiv = document.createElement('div');
        const rgDiv = document.createElement('div');

        // Nome
        const nomeLabel = document.createElement('label');
        nomeLabel.setAttribute('for', `nome-${i}`);
        nomeLabel.textContent = `Nome do Participante ${i}:`;
        nomeDiv.appendChild(nomeLabel);

        const nomeInput = document.createElement('input');
        nomeInput.type = 'text';
        nomeInput.id = `nome-${i}`;
        nomeInput.placeholder = `Nome do Participante ${i}`;
        nomeDiv.appendChild(nomeInput);

        // RG
        const rgLabel = document.createElement('label');
        rgLabel.setAttribute('for', `rg-${i}`);
        rgLabel.textContent = `RG do Participante ${i}:`;
        rgDiv.appendChild(rgLabel);
        
        const rgInput = document.createElement('input');
        rgInput.type = 'text';
        rgInput.id = `rg-${i}`;
        rgInput.placeholder = `RG do Participante ${i}`;
        rgDiv.appendChild(rgInput);

        // Adicionando nomeDiv e rgDiv ao participanteDiv
        participanteDiv.appendChild(nomeDiv);
        participanteDiv.appendChild(rgDiv);

        camposContainer.appendChild(participanteDiv);
    }

  
}
