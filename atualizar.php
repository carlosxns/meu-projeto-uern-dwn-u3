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

// 3. VERIFICAR SE O MÉTODO É POST E SE TEM UM ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {

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
        // 5. Pega o ID e chama o método de atualização
        // A lógica de "senha vazia" já está dentro do repositório!
        $id_cliente = (int)$_POST['id'];
        
        // Passamos o array $_POST inteiro para o método
        $sucesso = $repositorio->atualizarCliente($id_cliente, $_POST);

        if ($sucesso) {
            // Sucesso! Redireciona de volta para a lista
            header("Location: consultar.php");
            exit;
        } else {
            echo "Erro ao atualizar o registro.";
        }

    } catch (Exception $e) {
        echo "Erro fatal ao atualizar: " . $e->getMessage();
    }
    
    // 6. Fecha a conexão
    $conn->close();

} else {
    // Se alguém tentar acessar o .php diretamente ou sem um ID
    echo "Acesso inválido. Por favor, preencha o formulário de edição.";
    echo "<a href='consultar.php'>Voltar para a lista</a>";
}
?>
