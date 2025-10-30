<?php
// 1. Inclui o autoloader do Composer para carregar nossas classes
require 'vendor/autoload.php';

// 2. Define qual classe do namespace 'App' estamos usando
use App\ClienteRepository;

/* ================================= 
   CONFIGURAÇÃO DO BANCO DE DADOS
   ================================= */
$servername = "localhost";
$username = "admin_app";
$password = "ADM@ptr05"; // Lembre-se de trocar pela sua senha real
$dbname = "banco_de_dados_principal"; // <-- O nome do seu banco de produção

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

/* ================================= 
   LÓGICA DA APLICAÇÃO (Refatorada)
   ================================= */

// 3. Verifica se o método é POST (como no seu formulário)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Cria uma instância do nosso Repositório, passando a conexão
    $repositorio = new ClienteRepository($conn);

    try {
        // 5. Tenta criar o cliente (toda a lógica SQL está escondida no repositório)
        // Nós simplesmente passamos o array $_POST inteiro para ele
        $sucesso = $repositorio->criarCliente($_POST);

        if ($sucesso) {
            echo "<h1>Cadastro realizado com sucesso!</h1>";
            // Agora podemos redirecionar para a lista de consulta
            echo "<p>Você será redirecionado para a lista de clientes.</p>";
            echo "<a href='consultar.php'>Voltar para a lista</a>";

            // Adiciona um redirecionamento automático após 3 segundos
            header("refresh:3;url=consultar.php");
        } else {
            echo "<h1>Erro ao cadastrar.</h1>";
            echo "<p>Por favor, tente novamente.</p>";
            echo "<a href='index.html'>Tentar novamente</a>";
        }

    } catch (Exception $e) {
        // Captura qualquer erro de banco de dados
        echo "<h1>Erro ao cadastrar.</h1>";
        echo "<p>Erro: " . $e->getMessage() . "</p>";
        echo "<a href='index.html'>Tentar novamente</a>";
    }

    // 6. Fecha a conexão
    $conn->close();

} else {
    // Se alguém tentar acessar o .php diretamente
    echo "Acesso inválido. Por favor, preencha o formulário.";
}
?>
