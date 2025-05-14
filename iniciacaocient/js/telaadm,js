/*alternar visibilidade dos submenus*/
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
