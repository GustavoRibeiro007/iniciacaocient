document.addEventListener("DOMContentLoaded", function () {
  const selectTipo = document.getElementById("tipoUsuario");
  const formCadastroUsuario = document.getElementById("formCadastroUsuario");
  const botaoSubmit = document.getElementById("botao-submit");
  const camposAvaliador = document.getElementById('campos-avaliador');
  const camposProfessor = document.getElementById('campos-professor');
  const camposCoordenador = document.getElementById('campos-coordenador');
  const mensagemUsuario = document.getElementById('mensagem-usuario');

  if (selectTipo) {
    selectTipo.addEventListener("change", function () {
      const tipo = selectTipo.value;
      camposAvaliador.style.display = 'none';
      camposProfessor.style.display = 'none';
      camposCoordenador.style.display = 'none';
      document.querySelectorAll(".campos-especificos").forEach(div => {
        div.style.display = "none";
        div.querySelectorAll('input').forEach(input => { input.value = ''; });
      });
      if (!tipo) {
        botaoSubmit.style.display = "none";
        return;
      }
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

  if (formCadastroUsuario) {
    formCadastroUsuario.addEventListener("submit", async function (e) {
      e.preventDefault();
      mensagemUsuario.textContent = '';
      const tipoUsuario = document.getElementById("tipoUsuario").value;
      if (!tipoUsuario) {
        mensagemUsuario.textContent = 'Por favor, selecione um tipo de usuário.';
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
          mensagemUsuario.style.color = 'green';
          mensagemUsuario.textContent = 'Usuário cadastrado com sucesso!';
          this.reset();
          document.querySelectorAll(".campos-especificos").forEach(div => {
            div.style.display = "none";
          });
          botaoSubmit.style.display = "none";
          selectTipo.dispatchEvent(new Event('change'));
        } else {
          mensagemUsuario.style.color = 'red';
          mensagemUsuario.textContent = 'Erro ao cadastrar usuário: ' + data.erro;
        }
      } catch (error) {
        mensagemUsuario.style.color = 'red';
        mensagemUsuario.textContent = 'Erro ao enviar dados: ' + error.message;
      }
    });
  }
});
