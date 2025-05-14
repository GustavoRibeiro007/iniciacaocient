document.addEventListener("DOMContentLoaded", function () {
  //para mostrar os campos especificos por usuario
  const selectTipo = document.getElementById("tipoUsuario");

  if (selectTipo) {
    selectTipo.addEventListener("change", function () {
      const tipo = selectTipo.value;

      //esconde todos os campos específicos
      document.querySelectorAll(".campos-especificos").forEach(div => {
        div.style.display = "none";
      });

      //mostra o campo específico correspondente ao tipo de usuário
      const bloco = document.getElementById("campos-" + tipo);
      if (bloco) {
        bloco.style.display = "block";
      }
    });
  }

  //botão do usuario
  const formCadastroUsuario = document.getElementById("formCadastroUsuario");

  if (formCadastroUsuario) {
    formCadastroUsuario.addEventListener("submit", function (e) {
      e.preventDefault();

      const tipoUsuario = document.getElementById("tipoUsuario").value;
      const nome = document.getElementById("nome").value;

      let camposEspecificos = {};
      if (tipoUsuario === "avaliador") {
        camposEspecificos.cpf = document.getElementById("nome").value;
        camposEspecificos.endereco = document.getElementById("telefone").value;
      } else if (tipoUsuario === "professor") {
         camposEspecificos.cpf = document.getElementById("nome").value;
        camposEspecificos.matricula = document.getElementById("n-matricula").value;
        camposEspecificos.setor = document.getElementById("telefone").value;
      } else if (tipoUsuario === "coordenador") {
        camposEspecificos.cpf = document.getElementById("nome").value;
        camposEspecificos.nivelAcesso = document.getElementById("email").value;
      }

    });
  }
});

 const projetos = [
      {
        id: 1,
        titulo: "Sistema de Gestão Escolar",
        curso: "DSM",
        aluno: "João Silva",
        orientador: "Prof. Carlos",
        status: "pendente",
        resumo: "Um sistema web para gerenciar uma escola.",
        arquivo: "arquivo1.pdf"
      },
      {
        id: 2,
        titulo: "App de Saúde",
        curso: "Gestao",
        aluno: "Maria Oliveira",
        orientador: "Prof. Ana",
        status: "aprovado",
        resumo: "Aplicativo que monitora sinais vitais em tempo real.",
        arquivo: "arquivo2.pdf"
      }
    ];

    function carregarProjetos(lista) {
      const tbody = document.querySelector("#tabelaProjetos tbody");
      tbody.innerHTML = "";
      lista.forEach(p => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${p.titulo}</td>
          <td>${p.curso}</td>
          <td>${p.aluno}</td>
          <td>${p.status}</td>
          <td><button onclick='verDetalhes(${JSON.stringify(p)})'>Ver detalhes</button></td>
        `;
        tbody.appendChild(tr);
      });
    }

    function aplicarFiltros() {
      const curso = document.getElementById("filtroCurso").value;
      const status = document.getElementById("filtroCategoria").value;

      const filtrados = projetos.filter(p => {
        return (curso === "" || p.curso === curso) &&
               (status === "" || p.status === status);
      });

      carregarProjetos(filtrados);
    }

    function verDetalhes(projeto) {
      document.getElementById("detTitulo").innerText = projeto.titulo;
      document.getElementById("detResumo").innerText = projeto.resumo;
      document.getElementById("detAluno").innerText = projeto.aluno;
      document.getElementById("detOrientador").innerText = projeto.orientador;
      document.getElementById("detStatus").innerText = projeto.status;
      document.getElementById("detArquivo").href = projeto.arquivo;

      document.getElementById("overlay").style.display = "block";
      document.getElementById("detalhesModal").style.display = "block";
    }

    function fecharModal() {
      document.getElementById("overlay").style.display = "none";
      document.getElementById("detalhesModal").style.display = "none";
    }

    function exportarProjetosPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.autoTable({ html: '#tabelaProjetos' });
      doc.save('projetos.pdf');
    }

    function exportarProjetosCSV() {
  const table = document.querySelector('#tabelaProjetos');
  let csv = '';
  
  //loop pelas linhas da tabela
  for (const row of table.rows) {
    const cols = Array.from(row.cells).map(col => {
      // trata casos de dados com vírgulas, aspas e quebras de linha
      let colText = col.innerText.trim();
      colText = `"${colText.replace(/"/g, '""')}"`; // Escapa aspas duplas
      return colText;
    });
    csv += cols.join(',') + '\n';
  }

  //criação do Blob com o conteúdo CSV
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "projetos.csv";
  link.click();
}
    //inicializa a tabela ao carregar a página
    carregarProjetos(projetos);
