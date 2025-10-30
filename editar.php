<?php
// 1. Inclui o autoloader do Composer
require 'vendor/autoload.php';

// 2. Define a classe que vamos usar
use App\ClienteRepository;

/* ================================= 
   CONFIGURAÇÃO DO BANCO DE DADOS
   ================================= */
$servername = "localhost";
$username = "admin_app";
$password = "ADM@ptr05"; // Lembre-se de trocar pela sua senha real
$dbname = "banco_de_dados_principal"; // O nome do seu banco de produção

// Variável para armazenar os dados do cliente
$cliente = null;
$id_cliente = null;

// 3. VERIFICAR SE O ID FOI PASSADO
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_cliente = (int)$_GET['id'];

    // Conectar ao banco
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    /* ================================= 
       LÓGICA DA APLICAÇÃO (Refatorada)
       ================================= */
    
    // 4. Instancia o Repositório
    $repositorio = new ClienteRepository($conn);

    try {
        // 5. Busca o cliente pelo ID
        $cliente = $repositorio->buscarPorId($id_cliente);

    } catch (Exception $e) {
        // Trata erros de banco
        die("Erro ao buscar cliente: " . $e->getMessage());
    }
    
    $conn->close();

} else {
    die("Nenhum ID de cliente fornecido.");
}

// 6. Se o cliente não foi encontrado no banco
if ($cliente === null) {
    die("Cliente com ID $id_cliente não encontrado.");
}

// Se chegamos aqui, $cliente é um array com todos os dados.
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <main>
      <h1>Editar Cliente: <?php echo htmlspecialchars($cliente['nome']); ?></h1>
      <a href="consultar.php" class="btn-primary">Voltar para a Lista</a>

      <form
        id="cadastroForm"
        action="atualizar.php"
        method="post"
      >
        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>" />

        <fieldset>
          <legend>Dados Pessoais:</legend>
          <div>
            <label for="nome">Nome:</label>
            <input
              type="text"
              id="nome"
              name="nome"
              required
              value="<?php echo htmlspecialchars($cliente['nome']); ?>"
            />

            <label for="email">Email:</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              required 
              value="<?php echo htmlspecialchars($cliente['email']); ?>"
            />

            <label for="cpf">CPF:</label>
            <input
              type="text"
              id="cpf"
              name="cpf"
              required
              maxlength="14"
              value="<?php echo htmlspecialchars($cliente['cpf']); ?>"
            />

            <label for="telefone">Telefone:</label>
            <input
              type="tel"
              id="telefone"
              name="telefone"
              required
              maxlength="15"
              value="<?php echo htmlspecialchars($cliente['telefone']); ?>"
            />

            <label for="usuario">Usuário:</label>
            <input
              type="text"
              id="usuario"
              name="usuario"
              required
              value="<?php echo htmlspecialchars($cliente['usuario']); ?>"
            />

            <label for="senha">Senha:</label>
            <input
              type="password"
              id="senha"
              name="senha"
              placeholder="Deixe em branco para não alterar a senha"
            />

            <label for="idade">Idade:</label>
            <input
              type="number"
              id="idade"
              name="idade"
              required
              min="1"
              max="100"
              value="<?php echo htmlspecialchars($cliente['idade']); ?>"
            />

            <label for="datanasc">Data de Nascimento:</label>
            <input 
              type="date" 
              id="datanasc" 
              name="datanasc" 
              value="<?php echo htmlspecialchars($cliente['data_nascimento']); ?>"
            />

            <p>
              Sexo:
              <select name="sexo" id="sexo">
                <option value="selecione1">Selecione</option>
                <option value="masculino" <?php if($cliente['sexo'] == 'masculino') echo 'selected'; ?>>Masculino</option>
                <option value="feminino" <?php if($cliente['sexo'] == 'feminino') echo 'selected'; ?>>Feminino</option>
              </select>
            </p>
            <p>
              Estado Civil:
              <select name="estadocivil" id="estadocivil">
                <option value="selecione2">Selecione</option>
                <option value="solteiro" <?php if($cliente['estado_civil'] == 'solteiro') echo 'selected'; ?>>Solteiro</option>
                <option value="casado" <?php if($cliente['estado_civil'] == 'casado') echo 'selected'; ?>>Casado</option>
                <option value="divorciado" <?php if($cliente['estado_civil'] == 'divorciado') echo 'selected'; ?>>Divorciado</option>
              </select>
            </p>
            <label for="pais">País:</label>
            <input type="text" id="pais" name="pais" value="<?php echo htmlspecialchars($cliente['pais']); ?>" readonly />
          </div>
        </fieldset>

        <fieldset>
          <legend>Endereço de Entrega:</legend>
          <div>
            <label for="cep">CEP:</label>
            <input
              type="text"
              id="cep"
              name="cep"
              required
              maxlength="9"
              value="<?php echo htmlspecialchars($cliente['cep']); ?>"
            />

            <p>
              Tipo de logradouro:
              <select name="tipologradouro" id="tipologradouro">
                <option value="selecione3">Selecione</option>
                <option value="rua" <?php if($cliente['tipo_logradouro'] == 'rua') echo 'selected'; ?>>Rua</option>
                <option value="avenida" <?php if($cliente['tipo_logradouro'] == 'avenida') echo 'selected'; ?>>Avenida</option>
              </select>
            </p>

            <label for="logradouro">Logradouro:</label>
            <input 
              type="text" 
              id="logradouro" 
              name="logradouro" 
              required 
              value="<?php echo htmlspecialchars($cliente['logradouro']); ?>"
            />

            <label for="bairro">Bairro:</label>
            <input 
              type="text" 
              id="bairro" 
              name="bairro" 
              required 
              value="<?php echo htmlspecialchars($cliente['bairro']); ?>"
            />

            <label for="cidade">Cidade:</label>
            <input 
              type="text" 
              id="cidade" 
              name="cidade" 
              required 
              value="<?php echo htmlspecialchars($cliente['cidade']); ?>"
            />

            <label for="estado">Estado:</label>
            <input 
              type="text" 
              id="estado" 
              name="estado" 
              required 
              value="<?php echo htmlspecialchars($cliente['estado']); ?>"
            />
          </div>
        </fieldset>

        <button type="submit">Atualizar</button>
        <input type="reset" value="Limpar Alterações" />
      </form>
    </main>

    <script>
      document.getElementById("cpf").addEventListener("input", (e) => {
        let value = e.target.value.replace(/\D/g, "");
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        e.target.value = value;
      });

      document.getElementById("telefone").addEventListener("input", (e) => {
        let value = e.target.value.replace(/\D/g, "");
        value = value.replace(/^(\d{2})(\d)/, "($1) $2");
        value = value.replace(/(\d{5})(\d{4})$/, "$1-$2");
        e.target.value = value;
      });

      document.getElementById("cep").addEventListener("input", (e) => {
        let value = e.target.value.replace(/\D/g, "");
        value = value.replace(/^(\d{5})(\d)/, "$1-$2");
        e.target.value = value;
      });
    </script>
  </body>
</html>
