<?php
// Define o namespace que configuramos no composer.json
namespace App; 

// Importa a classe mysqli para usarmos type-hinting (boa prática)
use mysqli;

/**
 * Classe Repositório para gerenciar todas as operações
 * de banco de dados para a entidade 'Cliente'.
 */
class ClienteRepository
{
    /**
     * @var mysqli A conexão com o banco de dados.
     */
    private $conn;

    /**
     * O Construtor recebe a conexão mysqli e a armazena.
     * Isso é chamado de "Injeção de Dependência".
     *
     * @param mysqli $conn A conexão com o banco de dados.
     */
    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Busca um único cliente pelo seu ID.
     * (Usado em editar.php)
     *
     * @param int $id O ID do cliente.
     * @return array|null Os dados do cliente ou null se não for encontrado.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return null; // Retorna nulo se não encontrar
    }

    /**
     * Conta o número total de clientes, opcionalmente com filtro.
     * (Usado em consultar.php para a paginação)
     *
     * @param string $termo_busca O termo para filtrar por nome.
     * @return int O total de registros.
     */
    public function contarTodosComFiltro(string $termo_busca): int
    {
        $sql_where = "";
        if (!empty($termo_busca)) {
            // Usamos real_escape_string para segurança na cláusula LIKE
            $termo_seguro = $this->conn->real_escape_string($termo_busca);
            $sql_where = " WHERE nome LIKE '%$termo_seguro%'";
        }

        $sql_total = "SELECT COUNT(*) AS total FROM clientes" . $sql_where;
        $result = $this->conn->query($sql_total);
        return $result->fetch_assoc()['total'];
    }

    /**
     * Busca clientes com filtro e paginação.
     * (Usado em consultar.php para exibir a tabela)
     *
     * @param string $termo_busca O termo para filtrar por nome.
     * @param int $limit O número de registros por página.
     * @param int $offset O ponto de início da busca.
     * @return array Uma lista de clientes.
     */
    public function buscarComFiltroEPaginacao(string $termo_busca, int $limit, int $offset): array
    {
        $sql_where = "";
        if (!empty($termo_busca)) {
            $termo_seguro = $this->conn->real_escape_string($termo_busca);
            $sql_where = " WHERE nome LIKE ?";
            $param_busca = "%" . $termo_seguro . "%";
        }

        $sql = "SELECT id, nome, email, telefone FROM clientes" 
             . $sql_where 
             . " ORDER BY nome ASC LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);

        // O bind_param muda dependendo se temos uma busca ou não
        if (!empty($termo_busca)) {
            // "sii" = string (busca), integer (limit), integer (offset)
            $stmt->bind_param("sii", $param_busca, $limit, $offset);
        } else {
            // "ii" = integer (limit), integer (offset)
            $stmt->bind_param("ii", $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC); // Retorna todos os resultados como um array
    }

    /**
     * Cria um novo cliente no banco de dados.
     * (Usado em cadastraCliente.php)
     *
     * @param array $dados Os dados do formulário (ex: $_POST).
     * @return bool True se foi bem-sucedido, false se não.
     */
    public function criarCliente(array $dados): bool
    {
        // Hasheia a senha
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);

        // Pega os interesses do array e junta em uma string
        $interesses = isset($dados['interesse']) ? implode(", ", $dados['interesse']) : '';

        $sql = "INSERT INTO clientes (
                    nome, email, cpf, telefone, usuario, senha, idade, data_nascimento, 
                    sexo, estado_civil, pais, cep, tipo_logradouro, logradouro, 
                    bairro, cidade, estado, interesses, info_complementares
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?
                )";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssissssssssssss",
            $dados['nome'], $dados['email'], $dados['cpf'], $dados['telefone'], $dados['usuario'],
            $senha_hash, // Usa a senha hasheada
            $dados['idade'], $dados['datanasc'], $dados['sexo'], $dados['estadocivil'],
            $dados['pais'], $dados['cep'], $dados['tipologradouro'], $dados['logradouro'],
            $dados['bairro'], $dados['cidade'], $dados['estado'], 
            $interesses, // Usa a string de interesses
            $dados['inforcomple']
        );

        return $stmt->execute();
    }

    /**
     * Atualiza um cliente existente.
     * (Usado em atualizar.php)
     *
     * @param int $id O ID do cliente a ser atualizado.
     * @param array $dados Os dados do formulário (ex: $_POST).
     * @return bool True se foi bem-sucedido, false se não.
     */
    public function atualizarCliente(int $id, array $dados): bool
    {
        // Lógica da senha: só atualiza se uma nova senha for fornecida
        if (!empty($dados['senha'])) {
            $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);

            $sql = "UPDATE clientes SET 
                        nome = ?, email = ?, cpf = ?, telefone = ?, usuario = ?, 
                        idade = ?, data_nascimento = ?, sexo = ?, estado_civil = ?, 
                        pais = ?, cep = ?, tipo_logradouro = ?, logradouro = ?, 
                        bairro = ?, cidade = ?, estado = ?, senha = ?
                    WHERE id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssssisssssssssssi",
                $dados['nome'], $dados['email'], $dados['cpf'], $dados['telefone'], $dados['usuario'],
                $dados['idade'], $dados['datanasc'], $dados['sexo'], $dados['estadocivil'],
                $dados['pais'], $dados['cep'], $dados['tipologradouro'], $dados['logradouro'],
                $dados['bairro'], $dados['cidade'], $dados['estado'],
                $senha_hash, // A nova senha
                $id
            );

        } else {
            // Se a senha estiver em branco, NÃO a inclua no UPDATE
            $sql = "UPDATE clientes SET 
                        nome = ?, email = ?, cpf = ?, telefone = ?, usuario = ?, 
                        idade = ?, data_nascimento = ?, sexo = ?, estado_civil = ?, 
                        pais = ?, cep = ?, tipo_logradouro = ?, logradouro = ?, 
                        bairro = ?, cidade = ?, estado = ?
                    WHERE id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssssissssssssssi",
                $dados['nome'], $dados['email'], $dados['cpf'], $dados['telefone'], $dados['usuario'],
                $dados['idade'], $dados['datanasc'], $dados['sexo'], $dados['estadocivil'],
                $dados['pais'], $dados['cep'], $dados['tipologradouro'], $dados['logradouro'],
                $dados['bairro'], $dados['cidade'], $dados['estado'],
                $id
            );
        }

        return $stmt->execute();
    }

    /**
     * Exclui um cliente do banco de dados.
     * (Usado em excluir.php)
     *
     * @param int $id O ID do cliente a ser excluído.
     * @return bool True se foi bem-sucedido, false se não.
     */
    public function excluirCliente(int $id): bool
    {
        $sql = "DELETE FROM clientes WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
