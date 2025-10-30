<?php
// Declara o namespace de testes
namespace Tests\Backend;

// Importa as classes que vamos usar
use PHPUnit\Framework\TestCase;
use App\ClienteRepository; // A classe que estamos testando
use mysqli; // A classe de conexão do PHP

/**
 * Teste de Integração para a classe ClienteRepository.
 *
 * @covers App\ClienteRepository
 *
 * IMPORTANTE: Este teste requer uma conexão real
 * com o banco de dados de TESTE (banco_de_dados_teste).
 */
class ClienteRepositoryTest extends TestCase
{
    /** @var mysqli A conexão com o banco de dados de teste */
    private $conn;

    /** @var ClienteRepository A instância do repositório que vamos testar */
    private $repository;

    /**
     * Este método é executado ANTES de CADA teste (ex: testPodeCriarCliente).
     * Sua função é garantir um ambiente limpo.
     */
    protected function setUp(): void
    {
        // 1. Conecta ao banco de TESTE usando Variáveis de Ambiente
        //    Isso nos permite usar credenciais diferentes no CI e localmente.
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'admin_app';
        $pass = getenv('DB_PASS') ?: 'ADM@ptr05';
        $db   = getenv('DB_NAME') ?: 'banco_de_dados_teste'; // <--- NOME DO SEU BANCO DE TESTE
        $port = getenv('DB_PORT') ?: 3306;

        $this->conn = new mysqli($host, $user, $pass, $db, $port);

        if ($this->conn->connect_error) {
            $this->fail("Falha ao conectar no banco de dados de TESTE ($host:$port): " . $this->conn->connect_error);
        }

        // 2. LIMPA a tabela 'clientes' para garantir que o teste seja isolado
        $this->conn->query("TRUNCATE TABLE clientes");

        // 3. Cria uma nova instância do repositório para este teste
        $this->repository = new ClienteRepository($this->conn);
    }

    /**
     * Este método é executado DEPOIS de CADA teste.
     * Sua função é limpar os recursos (como a conexão).
     */
    protected function tearDown(): void
    {
        // Fecha a conexão com o banco de dados
        $this->conn->close();
    }

    /**
     * Um array de dados de cliente de exemplo para usarmos nos testes.
     * Corresponde ao seu formulário
     * @return array
     */
    private function getDadosClienteExemplo(): array
    {
        return [
            'nome' => 'Cliente Teste',
            'email' => 'teste@exemplo.com',
            'cpf' => '123.456.789-00',
            'telefone' => '(84) 99999-8888',
            'usuario' => 'clienteteste',
            'senha' => 'SenhaForte@123',
            'idade' => 30,
            'datanasc' => '1990-01-01',
            'sexo' => 'masculino',
            'estadocivil' => 'solteiro',
            'pais' => 'Brasil',
            'cep' => '59300-000',
            'tipologradouro' => 'rua',
            'logradouro' => 'Rua do Teste',
            'bairro' => 'Centro',
            'cidade' => 'Caicó',
            'estado' => 'RN',
            'interesse' => ['informatica', 'games'], // Testando o array de interesses
            'inforcomple' => 'Info teste'
        ];
    }

    // ===================================
    // OS TESTES DE FATO
    // ===================================

    public function testPodeCriarCliente()
    {
        $dados = $this->getDadosClienteExemplo();

        // 1. Ação (Act)
        $sucesso = $this->repository->criarCliente($dados);

        // 2. Afirmação (Assert)
        $this->assertTrue($sucesso, "Repositório falhou ao criar cliente.");

        // 3. Verificação (Busca o cliente salvo para ver se os dados batem)
        $cliente_salvo = $this->repository->buscarPorId(1); // O primeiro ID é 1

        $this->assertNotNull($cliente_salvo, "Cliente criado não foi encontrado no banco.");
        $this->assertEquals('Cliente Teste', $cliente_salvo['nome']);
        $this->assertEquals('teste@exemplo.com', $cliente_salvo['email']);
        $this->assertEquals('informatica, games', $cliente_salvo['interesses']); // Verifica se o implode funcionou
    }

    public function testPodeExcluirCliente()
    {
        // 1. Preparação (Arrange)
        $dados = $this->getDadosClienteExemplo();
        $this->repository->criarCliente($dados);

        // 2. Ação (Act)
        $sucesso = $this->repository->excluirCliente(1); // Exclui o ID 1

        // 3. Afirmação (Assert)
        $this->assertTrue($sucesso, "Repositório falhou ao excluir cliente.");

        // 4. Verificação (Tenta buscar o cliente excluído)
        $cliente_excluido = $this->repository->buscarPorId(1);
        $this->assertNull($cliente_excluido, "Cliente não foi realmente excluído do banco.");
    }

    public function testPodeAtualizarClienteSemMudarSenha()
    {
        // 1. Preparação (Arrange)
        $dados = $this->getDadosClienteExemplo();
        $this->repository->criarCliente($dados);
        $cliente_original = $this->repository->buscarPorId(1);
        $hash_senha_original = $cliente_original['senha']; // Salva o hash da senha original

        // 2. Ação (Act)
        $dados_atualizados = $this->getDadosClienteExemplo(); // Pega dados base
        $dados_atualizados['nome'] = 'Nome Alterado'; // Muda o nome
        $dados_atualizados['senha'] = ''; // Deixa a senha EM BRANCO

        $this->repository->atualizarCliente(1, $dados_atualizados);

        // 3. Afirmação (Assert)
        $cliente_atualizado = $this->repository->buscarPorId(1);
        $this->assertEquals('Nome Alterado', $cliente_atualizado['nome']);
        $this->assertEquals($hash_senha_original, $cliente_atualizado['senha'], "A senha foi alterada indevidamente.");
    }

    public function testBuscaPaginacaoEContagemFunciona()
    {
        // 1. Preparação (Arrange)
        // Cria 15 clientes: 5 "Anas" e 10 "Brunos"
        $dados_ana = $this->getDadosClienteExemplo();
        $dados_ana['nome'] = 'Ana Silva';
        $dados_ana['email'] = 'ana@exemplo.com';

        $dados_bruno = $this->getDadosClienteExemplo();
        $dados_bruno['nome'] = 'Bruno Costa';
        $dados_bruno['email'] = 'bruno@exemplo.com';

        for ($i=0; $i<5; $i++) $this->repository->criarCliente($dados_ana);
        for ($i=0; $i<10; $i++) $this->repository->criarCliente($dados_bruno);

        // 2. Ação e Afirmação (Testa a contagem total)
        $total = $this->repository->contarTodosComFiltro("");
        $this->assertEquals(15, $total, "Contagem total de clientes está errada.");

        // 3. Ação e Afirmação (Testa a contagem com filtro)
        $total_ana = $this->repository->contarTodosComFiltro("Ana");
        $this->assertEquals(5, $total_ana, "Contagem com filtro 'Ana' está errada.");

        // 4. Ação e Afirmação (Testa a paginação - Página 1)
        // (10 por página, offset 0)
        $pagina1 = $this->repository->buscarComFiltroEPaginacao("", 10, 0);
        $this->assertCount(10, $pagina1, "Paginação da Página 1 falhou.");

        // 5. Ação e Afirmação (Testa a paginação - Página 2)
        // (10 por página, offset 10)
        $pagina2 = $this->repository->buscarComFiltroEPaginacao("", 10, 10);
        $this->assertCount(5, $pagina2, "Paginação da Página 2 falhou.");
    }
}
