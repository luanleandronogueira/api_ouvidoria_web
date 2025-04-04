# API Ouvidoria Web

Este é um projeto de API para gerenciar manifestações e entidades de uma ouvidoria. A API foi desenvolvida utilizando o framework **Slim 4** e segue boas práticas de desenvolvimento.

## 🚀 Funcionalidades

- Gerenciamento de manifestações:
  - Listar todas as manifestações.
  - Buscar manifestação por ID.
- Gerenciamento de entidades:
  - Listar todas as entidades.
  - Buscar entidade por ID.
  - Inserir uma nova entidade.

## 🛠️ Tecnologias Utilizadas

- **PHP** (versão 7.4 ou superior)
- **Slim Framework 4**
- **MySQL** (banco de dados)
- **Composer** (gerenciador de dependências)
- **Postman** (para testes de API)

## 📂 Estrutura do Projeto

```plaintext
c:\xampp\htdocs\api_ouvidoria_web
├── app
│   ├── middleware.php       # Configuração de middlewares
│   ├── routes.php           # Definição das rotas da API
│   ├── settings.php         # Configurações globais (banco de dados, logger, etc.)
├── conexao
│   ├── Conexao.php          # Classe para gerenciar a conexão com o banco de dados
├── public
│   ├── index.php            # Arquivo principal para inicializar a aplicação
├── logs                     # Diretório para logs da aplicação
├── [composer.json](http://_vscodecontentref_/1)            # Configuração do Composer