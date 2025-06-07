/*alternar visibilidade dos submenus*/
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.has-submenu').forEach(item => {
    item.addEventListener('click', function (e) {
      e.stopPropagation();

      /*fecha todos os outros submenus*/
      document.querySelectorAll('.submenu').forEach(sub => {
        if (sub !== this.querySelector('.submenu')) {
          sub.style.display = 'none';
        }
      });

      /*alternar visibilidade do submenu clicado*/
      const submenu = this.querySelector('.submenu');
      submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
    });
  });

  /*fechar submenu ao clicar fora da sidebar*/
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.sidebar')) {
      document.querySelectorAll('.submenu').forEach(sub => {
        sub.style.display = 'none';
      });
    }
  });

  /*mostrar tela de cadastro de curso*/
  document.getElementById('menuCadastrarCurso').addEventListener('click', function (e) {
    e.stopPropagation();

    document.getElementById('conteudoPrincipal').style.display = 'none';
    document.getElementById('telaCadastroCurso').style.display = 'block';

    /*echa submenu depois do clique*/
    document.querySelectorAll('.submenu').forEach(sub => sub.style.display = 'none');
  });

  /*lógica do formulário de curso*/
  document.getElementById('cursoForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const nomeCurso = document.getElementById('nomeCurso').value;
    const duracao = document.getElementById('duracao').value;
    const professores = document.getElementById('professores').value;
    const materias = document.getElementById('materias').value;

    console.log("Curso Cadastrado:", {
      nomeCurso,
      duracao,
      professores,
      materias
    });

    alert("Curso cadastrado com sucesso!");

    this.reset();

    /*voltar para dashboard*/
    document.getElementById('telaCadastroCurso').style.display = 'none';
    document.getElementById('conteudoPrincipal').style.display = 'grid';
  });

  /*carregar usuários na inicialização*/
  carregarUsuarios();
  carregarProjetos();
  carregarAgendamentos();
});

function adicionarProfessor() {
  const container = document.getElementById('professoresContainer');

  const novaLinha = document.createElement('div');
  novaLinha.className = 'professor-field';

  novaLinha.innerHTML = `
    <input type="text" name="professores[]" placeholder="Nome do professor" required>
    <input type="text" name="ids_professores[]" placeholder="Nº de identificação" required>
    <button type="button" class="btn-remove-professor" onclick="removerProfessor(this)">Remover</button>
  `;

  container.appendChild(novaLinha);
}

function removerProfessor(botao) {
  const linha = botao.parentElement;
  linha.remove();
}

function adicionarMateria() {
  const container = document.getElementById('materiasContainer');

  const novaLinha = document.createElement('div');
  novaLinha.className = 'materia-field';

  novaLinha.innerHTML = `
    <input type="text" name="materias[]" placeholder="Nome da matéria" required>
    <input type="text" name="ids_materias[]" placeholder="Carga horária" required>
    <button type="button" class="btn-remove-materia" onclick="removerMateria(this)">Remover</button>
  `;

  container.appendChild(novaLinha);
}

function removerMateria(botao) {
  const linha = botao.parentElement;
  linha.remove();
}

/*gerar codigo do curso*/
function gerarCodigoCurso() {
  const prefixo = "CUR";
  const ano = new Date().getFullYear();
  const random = Math.floor(1000 + Math.random() * 9000); 
  return `${prefixo}-${ano}-${random}`; 
}

window.addEventListener("DOMContentLoaded", function () {
  const campoCodigo = document.getElementById("codigo");
  if (campoCodigo) {
    campoCodigo.value = gerarCodigoCurso();
  }
});

/*botão liga desliga*/
function ativarCurso(checkbox) {
  if (checkbox.checked) {
    alert("Curso ativado!");
  } else {
    alert("Curso desativado!");
  }
}

/*atualizar status*/
function atualizarStatus() {
  const checkbox = document.getElementById("toggleStatus");
  const statusTexto = document.getElementById("statusTexto");

  if (checkbox.checked) {
    statusTexto.textContent = "Status: Ativo";
  } else {
    statusTexto.textContent = "Status: Inativo";
  }
}

/*funções tela usuario*/
document.addEventListener("DOMContentLoaded", function () {
  const selectTipo = document.getElementById("tipoUsuario");

  selectTipo.addEventListener("change", function () {
    const tipo = selectTipo.value;

    document.querySelectorAll(".campos-especificos").forEach(div => {
      div.style.display = "none";
    });

    const bloco = document.getElementById("campos-" + tipo);
    if (bloco) {
      bloco.style.display = "block";
    }
  });
});

function abrirModalDetalhes(codigo, titulo, curso, orientador, resumo, arquivo, status) {
  document.getElementById('detalheCodigo').innerText = codigo;
  document.getElementById('detalheTitulo').innerText = titulo;
  document.getElementById('detalheCurso').innerText = curso;
  document.getElementById('detalheOrientador').innerText = orientador;
  document.getElementById('detalheResumo').innerText = resumo;
  document.getElementById('detalheArquivo').href = arquivo;
  document.getElementById('detalheArquivo').innerText = arquivo;
  document.getElementById('detalheStatus').innerText = status;

  document.getElementById('modalDetalhes').style.display = 'block';
}

function fecharModal() {
  document.getElementById('modalDetalhes').style.display = 'none';
}

/*fecha modal ao clicar fora*/
window.onclick = function(event) {
  const modal = document.getElementById('modalDetalhes');
  if (event.target === modal) {
    modal.style.display = "none";
  }
}

// Funções de Usuário
async function carregarUsuarios() {
  try {
    const response = await fetch('../php/usuarios.php?acao=listar');
    const data = await response.json();

    if (data.sucesso) {
      const tbody = document.querySelector("#tabelaUsuarios tbody");
      if (tbody) {
        tbody.innerHTML = "";
        data.usuarios.forEach(u => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${u.Nome}</td>
            <td>${u.Tipo_Usuario}</td>
            <td>${u.Email || '-'}</td>
            <td>${u.Telefone || '-'}</td>
            <td>
              <button onclick="editarUsuario(${u.ID})">Editar</button>
              <button onclick="excluirUsuario(${u.ID})">Excluir</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
    }
  } catch (error) {
    console.error('Erro ao carregar usuários:', error);
  }
}

async function editarUsuario(id) {
  try {
    const response = await fetch(`../php/usuarios.php?acao=buscar&id=${id}`);
    const data = await response.json();

    if (data.sucesso) {
      const usuario = data.usuario;
      document.getElementById('editId').value = usuario.ID;
      document.getElementById('editNome').value = usuario.Nome;
      document.getElementById('editTipoUsuario').value = usuario.Tipo_Usuario;
      document.getElementById('editEmail').value = usuario.Email || '';
      document.getElementById('editTelefone').value = usuario.Telefone || '';
      document.getElementById('editIdentificacao').value = usuario.Identificacao || '';

      document.getElementById('modalEditarUsuario').style.display = 'block';
    }
  } catch (error) {
    console.error('Erro ao buscar usuário:', error);
  }
}

async function salvarEdicaoUsuario() {
  const formData = new FormData();
  formData.append('acao', 'editar');
  formData.append('id', document.getElementById('editId').value);
  formData.append('nome', document.getElementById('editNome').value);
  formData.append('tipoUsuario', document.getElementById('editTipoUsuario').value);
  formData.append('email', document.getElementById('editEmail').value);
  formData.append('telefone', document.getElementById('editTelefone').value);
  formData.append('identificacao', document.getElementById('editIdentificacao').value);

  try {
    const response = await fetch('../php/usuarios.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();
    if (data.sucesso) {
      alert('Usuário atualizado com sucesso!');
      document.getElementById('modalEditarUsuario').style.display = 'none';
      carregarUsuarios();
    } else {
      alert('Erro ao atualizar usuário: ' + data.erro);
    }
  } catch (error) {
    console.error('Erro ao salvar edição:', error);
  }
}

async function excluirUsuario(id) {
  if (confirm('Tem certeza que deseja excluir este usuário?')) {
    try {
      const formData = new FormData();
      formData.append('acao', 'excluir');
      formData.append('id', id);

      const response = await fetch('../php/usuarios.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      if (data.sucesso) {
        alert('Usuário excluído com sucesso!');
        carregarUsuarios();
      } else {
        alert('Erro ao excluir usuário: ' + data.erro);
      }
    } catch (error) {
      console.error('Erro ao excluir usuário:', error);
    }
  }
}

// Funções de Projeto
async function carregarProjetos() {
  try {
    const response = await fetch('../php/projetos.php?acao=listar');
    const data = await response.json();

    if (data.sucesso) {
      const tbody = document.querySelector("#tabelaProjetos tbody");
      if (tbody) {
        tbody.innerHTML = "";
        data.projetos.forEach(p => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${p.Nome_Projeto}</td>
            <td>${p.Curso}</td>
            <td>${p.Orientador}</td>
            <td>${p.Data_Envio}</td>
            <td>
              <button onclick="verDetalhesProjeto(${p.ID})">Detalhes</button>
              <button onclick="excluirProjeto(${p.ID})">Excluir</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
    }
  } catch (error) {
    console.error('Erro ao carregar projetos:', error);
  }
}

async function verDetalhesProjeto(id) {
  try {
    const response = await fetch(`../php/projetos.php?acao=detalhes&id=${id}`);
    const data = await response.json();

    if (data.sucesso) {
      const p = data.projeto;
      document.getElementById('detalheCodigo').innerText = p.ID;
      document.getElementById('detalheTitulo').innerText = p.Nome_Projeto;
      document.getElementById('detalheCurso').innerText = p.Curso;
      document.getElementById('detalheOrientador').innerText = p.Orientador;
      document.getElementById('detalheResumo').innerText = p.Resumo;
      
      if (p.GitHub_Link) {
        document.getElementById('detalheGithub').href = p.GitHub_Link;
        document.getElementById('detalheGithub').style.display = 'block';
      } else {
        document.getElementById('detalheGithub').style.display = 'none';
      }

      // Lista de participantes
      const participantesHtml = p.participantes.map(part => `
        <li>
          <strong>${part.Nome}</strong><br>
          RA: ${part.RA}<br>
          Email: ${part.Email}
        </li>
      `).join('');
      document.getElementById('detalheParticipantes').innerHTML = participantesHtml;

      document.getElementById('modalDetalhes').style.display = 'block';
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes do projeto:', error);
  }
}

async function excluirProjeto(id) {
  if (confirm('Tem certeza que deseja excluir este projeto?')) {
    try {
      const formData = new FormData();
      formData.append('acao', 'excluir');
      formData.append('id', id);

      const response = await fetch('../php/projetos.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      if (data.sucesso) {
        alert('Projeto excluído com sucesso!');
        carregarProjetos();
      } else {
        alert('Erro ao excluir projeto: ' + data.erro);
      }
    } catch (error) {
      console.error('Erro ao excluir projeto:', error);
    }
  }
}

// Funções de Agendamento
async function carregarAgendamentos() {
  try {
    const response = await fetch('../php/agendamentos.php?acao=listar');
    const data = await response.json();

    if (data.sucesso) {
      const tbody = document.querySelector("#tabelaAgendamentos tbody");
      if (tbody) {
        tbody.innerHTML = "";
        data.agendamentos.forEach(a => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${a.Nome_Projeto}</td>
            <td>${a.Curso}</td>
            <td>${a.Data_Apresentacao}</td>
            <td>${a.Local}</td>
            <td>${a.Status}</td>
            <td>
              <button onclick="editarAgendamento(${a.ID})">Editar</button>
              <button onclick="cancelarAgendamento(${a.ID})">Cancelar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
    }
  } catch (error) {
    console.error('Erro ao carregar agendamentos:', error);
  }
}

async function agendarApresentacao(formularioId) {
  const formData = new FormData();
  formData.append('acao', 'agendar');
  formData.append('formulario_id', formularioId);
  formData.append('data_apresentacao', document.getElementById('dataApresentacao').value);
  formData.append('local', document.getElementById('localApresentacao').value);

  try {
    const response = await fetch('../php/agendamentos.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();
    if (data.sucesso) {
      alert('Apresentação agendada com sucesso!');
      document.getElementById('modalAgendamento').style.display = 'none';
      carregarAgendamentos();
    } else {
      alert('Erro ao agendar apresentação: ' + data.erro);
    }
  } catch (error) {
    console.error('Erro ao agendar apresentação:', error);
  }
}

async function cancelarAgendamento(id) {
  if (confirm('Tem certeza que deseja cancelar este agendamento?')) {
    try {
      const formData = new FormData();
      formData.append('acao', 'cancelar');
      formData.append('id', id);

      const response = await fetch('../php/agendamentos.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      if (data.sucesso) {
        alert('Agendamento cancelado com sucesso!');
        carregarAgendamentos();
      } else {
        alert('Erro ao cancelar agendamento: ' + data.erro);
      }
    } catch (error) {
      console.error('Erro ao cancelar agendamento:', error);
    }
  }
}

// Funções auxiliares
function fecharModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
  const modais = document.getElementsByClassName('modal');
  for (const modal of modais) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }
}

// Remova ou ajuste qualquer código que tente navegar por links ou recarregar a página.
// Todas as ações devem manipular o DOM dentro do #conteudo-dinamico da telaadm.html SPA.
