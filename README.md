# Sistema CRUD de Clientes com PHP, MySQL e CI/CD na AWS EC2

[![Status do CI/CD](https://github.com/carlosxns/meu-projeto-uern-dwn-u3/actions/workflows/ci.yml/badge.svg)](https://github.com/carlosxns/meu-projeto-uern-dwn-u3/actions)

Este projeto é uma aplicação web CRUD (Create, Read, Update, Delete) completa para gerenciamento de clientes. Foi construída utilizando uma pilha LEMP (Linux, Nginx, MySQL, PHP) e implantada em uma instância AWS EC2.

O projeto inclui uma arquitetura de backend profissional (Padrão de Repositório) e é totalmente automatizado com um pipeline de CI/CD usando GitHub Actions, que inclui testes de unidade (Jest) e testes de integração (PHPUnit) com um banco de dados MySQL de teste.

## ✨ Funcionalidades

* **Create (Criar):** Cadastro de novos clientes com validação.
* **Read (Ler):** Listagem de todos os clientes com:
    * **Busca** por nome.
    * **Paginação** (10 clientes por página).
* **Update (Atualizar):** Edição dos dados de um cliente existente.
* **Delete (Excluir):** Remoção de clientes (de forma segura, via `POST`).

## 🛠️ Tecnologias Utilizadas

* **Infraestrutura:** AWS EC2 (Ubuntu)
* **Servidor Web:** Nginx
* **Backend:** PHP 8.3 (com PHP-FPM)
* **Banco de Dados:** MySQL 8.0
* **Testes Backend:** PHPUnit (para Testes de Integração)
* **Testes Frontend:** Jest (para Testes de Unidade de formatação)
* **CI/CD:** GitHub Actions
* **Gerenciamento de Dependências:** Composer (PHP) e NPM (Node.js)

## 🚀 Como Executar (Desenvolvimento Local)

Para executar este projeto em uma máquina local para desenvolvimento:

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/](https://github.com/)carlosxns/meu-projeto-uern-dwn-u3.git
    cd meu-projeto-crud-php
    ```
2.  **Instale as dependências:**
    ```bash
    composer install
    npm install
    ```
3.  **Configure o Banco de Dados:**
    * Crie dois bancos de dados MySQL locais: `banco_de_dados_principal` e `banco_de_dados_teste`.
    * Crie um usuário (ex: `admin_app`) e dê a ele permissões sobre ambos os bancos.
4.  **Crie as Tabelas:**
    * Execute o script `tabela_clientes.sql` em ambos os bancos:
    ```bash
    mysql -u admin_app -p banco_de_dados_principal < tabela_clientes.sql
    mysql -u admin_app -p banco_de_dados_teste < tabela_clientes.sql
    ```
5.  **Ajuste as Senhas:**
    * Edite todos os arquivos `.php` na raiz e os testes (`tests/backend/`) para usar a senha do seu banco de dados local.
6.  **Execute os Testes:**
    ```bash
    ./vendor/bin/phpunit
    npm test
    ```
7.  **Inicie o Servidor:**
    * Use o servidor embutido do PHP (ou configure um ambiente Nginx/Apache local):
    ```bash
    php -S localhost:8000
    ```

## ☁️ Configuração do Servidor de Produção (EC2)

Este projeto está rodando em uma instância AWS EC2 com a seguinte configuração:

1.  **Instalação da Pilha LEMP:**
    ```bash
    sudo apt install nginx php8.3-fpm php8.3-mysql mysql-server git composer nodejs
    ```
2.  **Configuração do Nginx:**
    * O arquivo `/etc/nginx/sites-available/default` foi editado para:
        * Adicionar `index.php` à diretiva `index`.
        * Descomentar o bloco `location ~ \.php$` para passar scripts ao PHP-FPM:
        ```nginx
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        ```
3.  **Permissões:**
    * O diretório do projeto (`/var/www/html`) teve sua propriedade alterada para o usuário do servidor web para permitir o `git pull` e uploads:
    ```bash
    sudo chown -R www-data:www-data /var/www/html
    ```

## 🤖 Pipeline de CI/CD (GitHub Actions)

O pipeline de implantação automática está definido em `.github/workflows/ci.yml`.

### Job 1: `build-and-test`
1.  Inicia um serviço de contêiner `mysql:8.0` para os testes.
2.  Cria o banco `banco_de_dados_teste` e executa `tabela_clientes.sql` nele.
3.  Configura o PHP 8.3 e o Node.js 18.
4.  Instala as dependências (Composer e NPM).
5.  Executa os testes `PHPUnit` (contra o banco de teste) e `Jest`.

### Job 2: `deploy-para-ec2`
1.  Este *job* só é executado se `build-and-test` passar e o *push* for no *branch* `main`.
2.  Ele se conecta à instância EC2 via SSH (usando `appleboy/ssh-action`).
3.  Executa os seguintes comandos no servidor:
    ```bash
    cd /var/www/html
    git pull origin main
    composer install --no-dev --optimize-autoloader
    sudo chown -R www-data:www-data /var/www/html
    sudo systemctl restart nginx
    ```

### ❗ Instruções de Configuração (Secrets)

Para que o *deploy* automático funcione, os seguintes **Secrets** devem ser configurados no repositório do GitHub (em `Settings > Secrets and variables > Actions`):

* `EC2_HOST`: O endereço IP público da instância EC2.
* `EC2_USER`: O nome de usuário para a conexão SSH (ex: `ubuntu`).
* `EC2_SSH_KEY`: A chave SSH privada (em formato **PEM**) para autenticar na instância.
