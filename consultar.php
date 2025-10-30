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

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

/* ================================= 
   LÓGICA DE PAGINAÇÃO E BUSCA
   ================================= */

// 1. Definições de Paginação
$resultados_por_pagina = 10; // Quantos clientes exibir por página
$pagina_atual = (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $resultados_por_pagina;

// 2. Termo de Busca
$termo_busca = (isset($_GET['busca']) && !empty($_GET['busca'])) ? $_GET['busca'] : '';

/* ================================= 
   LÓGICA DA APLICAÇÃO (Refatorada)
   ================================= */

// 3. Instancia o Repositório
$repositorio = new ClienteRepository($conn);

// 4. Busca os dados usando o Repositório
// (Todo o SQL complexo está escondido aqui)
$total_registros = $repositorio->contarTodosComFiltro($termo_busca);
$total_paginas = ceil($total_registros / $resultados_por_pagina);
$clientes = $repositorio->buscarComFiltroEPaginacao($termo_busca, $resultados_por_pagina, $offset);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Consultar Clientes</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <main>
        <h1>Clientes Cadastrados</h1>

        <div class="navigation-links">
            <a href="index.html" class="btn-primary">Adicionar Novo Cliente</a>
        </div>

        <form action="consultar.php" method="GET" class="search-form">
            <input type="text" 
                   name="busca" 
                   placeholder="Buscar cliente por nome..." 
                   value="<?php echo htmlspecialchars($termo_busca); ?>" />
            <button type="submit" class="btn-primary">Buscar</button>
            <a href="consultar.php" class="btn-clear">Limpar</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 5. Usamos count() e foreach() no array '$clientes'
                if (count($clientes) > 0) {
                    foreach ($clientes as $row) {
                ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                        <td><?php echo htmlspecialchars($row["email"]); ?></td>
                        <td><?php echo htmlspecialchars($row["telefone"]); ?></td>

                        <td class="actions">
                            <a href="editar.php?id=<?php echo $row["id"]; ?>" class="btn-edit">Editar</a>

                            <form action="excluir.php" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $row["id"]; ?>" />
                                <button type="submit" 
                                        class="btn-delete" 
                                        onclick="return confirm('Tem certeza que deseja excluir este cliente?');">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php
                    } // Fim do foreach
                } else {
                    echo "<tr><td colspan='5'>Nenhum cliente encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="paginacao">
            <?php if($total_paginas > 1): ?>

                <?php if($pagina_atual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_atual - 1; ?>&busca=<?php echo urlencode($termo_busca); ?>">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>&busca=<?php echo urlencode($termo_busca); ?>"
                       class="<?php if($i == $pagina_atual) echo 'pagina-ativa'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_atual + 1; ?>&busca=<?php echo urlencode($termo_busca); ?>">Próxima &raquo;</a>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </main>
</body>
</html>
<?php
// 6. Fecha a conexão
$conn->close();
?>
