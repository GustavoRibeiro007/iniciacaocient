document.addEventListener('DOMContentLoaded', function() {
    const formProjeto = document.getElementById('formProjeto');
    if (formProjeto) {
        formProjeto.addEventListener('submit', enviarProjeto);
    }
});

function mostrarCampos() {
    const numParticipantes = document.getElementById('num-participantes').value;
    const camposContainer = document.getElementById('participant-fields');
    camposContainer.innerHTML = '';  // Limpar campos anteriores

    if (numParticipantes == 0) return;

    for (let i = 1; i <= numParticipantes; i++) {
        const participanteDiv = document.createElement('div');
        participanteDiv.classList.add('participant');
        
        // Criando campos para Nome, RA e Email
        const nomeDiv = document.createElement('div');
        const raDiv = document.createElement('div');
        const emailDiv = document.createElement('div');

        // Nome
        const nomeLabel = document.createElement('label');
        nomeLabel.setAttribute('for', `nome-${i}`);
        nomeLabel.textContent = `Nome do Participante ${i}:`;
        nomeDiv.appendChild(nomeLabel);

        const nomeInput = document.createElement('input');
        nomeInput.type = 'text';
        nomeInput.id = `nome-${i}`;
        nomeInput.name = `nome-${i}`;
        nomeInput.placeholder = `Nome do Participante ${i}`;
        nomeInput.required = true;
        nomeDiv.appendChild(nomeInput);

        // RA
        const raLabel = document.createElement('label');
        raLabel.setAttribute('for', `ra-${i}`);
        raLabel.textContent = `RA do Participante ${i}:`;
        raDiv.appendChild(raLabel);
        
        const raInput = document.createElement('input');
        raInput.type = 'text';
        raInput.id = `ra-${i}`;
        raInput.name = `ra-${i}`;
        raInput.placeholder = `RA do Participante ${i}`;
        raInput.required = true;
        raDiv.appendChild(raInput);

        // Email
        const emailLabel = document.createElement('label');
        emailLabel.setAttribute('for', `email-${i}`);
        emailLabel.textContent = `Email do Participante ${i}:`;
        emailDiv.appendChild(emailLabel);
        
        const emailInput = document.createElement('input');
        emailInput.type = 'email';
        emailInput.id = `email-${i}`;
        emailInput.name = `email-${i}`;
        emailInput.placeholder = `Email do Participante ${i}`;
        emailInput.required = true;
        emailDiv.appendChild(emailInput);

        // Adicionando os campos ao participanteDiv
        participanteDiv.appendChild(nomeDiv);
        participanteDiv.appendChild(raDiv);
        participanteDiv.appendChild(emailDiv);

        camposContainer.appendChild(participanteDiv);
    }
}

async function enviarProjeto(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('acao', 'cadastrar');
    formData.append('nome_projeto', document.getElementById('nome-projeto').value);
    formData.append('quantidade_participantes', document.getElementById('num-participantes').value);
    formData.append('curso', document.getElementById('tipo-curso').value);
    formData.append('semestre', document.getElementById('semestre').value);
    formData.append('orientador', document.getElementById('orientador').value);
    formData.append('resumo', document.getElementById('resumo').value);
    formData.append('github_link', document.getElementById('repositorio').value);

    // Processar arquivo PDF
    const arquivoPDF = document.getElementById('arquivo-pdf').files[0];
    if (arquivoPDF) {
        const uploadFormData = new FormData();
        uploadFormData.append('acao', 'upload');
        uploadFormData.append('arquivo', arquivoPDF);
        uploadFormData.append('tipo', 'pdf');

        try {
            const uploadResponse = await fetch('../php/uploads.php', {
                method: 'POST',
                body: uploadFormData
            });

            const uploadData = await uploadResponse.json();
            if (uploadData.sucesso) {
                formData.append('caminho_pdf', uploadData.upload.caminho);
            } else {
                alert('Erro ao fazer upload do arquivo: ' + uploadData.erro);
                return;
            }
        } catch (error) {
            alert('Erro ao enviar arquivo: ' + error.message);
            return;
        }
    }

    // Coletar dados dos participantes
    const numParticipantes = document.getElementById('num-participantes').value;
    const participantes = [];
    
    for (let i = 1; i <= numParticipantes; i++) {
        participantes.push({
            nome: document.getElementById(`nome-${i}`).value,
            ra: document.getElementById(`ra-${i}`).value,
            email: document.getElementById(`email-${i}`).value
        });
    }
    
    formData.append('participantes', JSON.stringify(participantes));

    try {
        const response = await fetch('../php/projetos.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.sucesso) {
            alert('Projeto cadastrado com sucesso!');
            window.location.href = 'sucesso.html';
        } else {
            alert('Erro ao cadastrar projeto: ' + data.erro);
        }
    } catch (error) {
        alert('Erro ao enviar dados: ' + error.message);
    }
}
