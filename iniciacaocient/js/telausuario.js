document.addEventListener("DOMContentLoaded", function () {
  // Para mostrar os campos específicos por usuário
  const selectTipo = document.getElementById("tipoUsuario");
  const formCadastroUsuario = document.getElementById("formCadastroUsuario");
  const botaoSubmit = document.getElementById("botao-submit");
  const camposAvaliador = document.getElementById('campos-avaliador');
  const camposProfessor = document.getElementById('campos-professor');
  const camposCoordenador = document.getElementById('campos-coordenador');

  if (selectTipo) {
    selectTipo.addEventListener("change", function () {
      const tipo = selectTipo.value;

      // Esconde todos os campos específicos e limpa os valores
      camposAvaliador.style.display = 'none';
      camposProfessor.style.display = 'none';
      camposCoordenador.style.display = 'none';
      document.querySelectorAll(".campos-especificos").forEach(div => {
        div.style.display = "none";
        // Limpa os campos do div
        div.querySelectorAll('input').forEach(input => {
          input.value = '';
        });
      });

      // Esconde o botão de submit se nenhum tipo for selecionado
      if (!tipo) {
        botaoSubmit.style.display = "none";
        return;
      }

      // Mostra o campo específico correspondente ao tipo de usuário
      if (tipo === 'avaliador') {
        camposAvaliador.style.display = 'block';
      } else if (tipo === 'professor') {
        camposProfessor.style.display = 'block';
      } else if (tipo === 'coordenador') {
        camposCoordenador.style.display = 'block';
      }
      botaoSubmit.style.display = "block";
    });
  }

  // Formulário de cadastro de usuário
  if (formCadastroUsuario) {
    formCadastroUsuario.addEventListener("submit", async function (e) {
      e.preventDefault();

      const tipoUsuario = document.getElementById("tipoUsuario").value;
      if (!tipoUsuario) {
        alert('Por favor, selecione um tipo de usuário.');
        return;
      }

      const formData = new FormData(this);

      try {
        const response = await fetch('../php/usuarios.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.sucesso) {
          alert('Usuário cadastrado com sucesso!');
          // Reseta o formulário
          this.reset();
          // Esconde todos os campos específicos
          document.querySelectorAll(".campos-especificos").forEach(div => {
            div.style.display = "none";
          });
          // Esconde o botão de submit
          botaoSubmit.style.display = "none";
          selectTipo.dispatchEvent(new Event('change')); // Reseta a exibição dos campos
        } else {
          alert('Erro ao cadastrar usuário: ' + data.erro);
        }
      } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao enviar dados: ' + error.message);
      }
    });
  }
});
