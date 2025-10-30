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

// 3. Verifica se é um POST e se o ID foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && !empty($_POST['id'])) {

    // Conecta ao banco
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
        // 5. Chama o método para excluir
        $id_para_excluir = (int)$_POST['id'];
        $repositorio->excluirCliente($id_para_excluir);

    } catch (Exception $e) {
        // (Opcional) Em um sistema real, você logaria o erro
        // error_log($e->getMessage());
        // Por enquanto, apenas falhamos silenciosamente e redirecionamos
    }

    // 6. Fecha a conexão
    $conn->close();
}

// 7. Redireciona de volta para a lista EM QUALQUER CASO
// (Seja sucesso, falha, ou acesso indevido)
header("Location: consultar.php");
exit;
?>
