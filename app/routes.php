<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Classes\Models\Manifestacao;
use PDO\conexao\Conexao;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

include_once __DIR__ . '/../conexao/Conexao.php';

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $db = $this->get('db');
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/api/v1', function ($group) {
        $conexao = new Conexao();
        $conn = $conexao->Conectar();

        $group->get('/manifestacoes', function ($request, $response) use ($conn) {
            // Executar a consulta
            $query = "SELECT * FROM tb_manifestacoes";
            $manifestacoes = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

            // Retornar os dados como JSON
            $response->getBody()->write(json_encode($manifestacoes));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/manifestacoes/{id}', function ($request, $response, array $args) use ($conn) {
            $id = intval($args['id']); // Obter o ID da entidade a partir dos parâmetros da URL
            // Executar a consulta
            $query = "SELECT m.*, u.nome_usuario, u.sobrenome_usuario, u.cpf_usuario, u.email_usuario, tm.nome_tipo_manifestacao FROM tb_manifestacoes m JOIN tb_usuario u ON m.id_usuario_manifestacao = u.id_usuario JOIN tb_tipo_manifestacoes tm ON m.id_tipo_manifestacao = tm.id_tipo_manifestacao WHERE m.id_entidade_manifestacao = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $manifestacao = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($manifestacao)) {
                $response->getBody()->write(json_encode($manifestacao));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('Entidade Não Cadastrada.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->get('/manifestacoes_anonimas/{id}', function ($request, $response, array $args) use ($conn) {
            $id = intval($args['id']); // Obter o ID da entidade a partir dos parâmetros da URL
            // Executar a consulta
            $query = "SELECT m.*, tm.nome_tipo_manifestacao FROM tb_manifestacoes m JOIN tb_tipo_manifestacoes tm ON m.id_tipo_manifestacao = tm.id_tipo_manifestacao WHERE m.id_entidade_manifestacao = :id AND m.id_usuario_manifestacao IN (SELECT id_usuario FROM tb_usuario WHERE cpf_usuario = 0)";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $manifestacao = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($manifestacao)) {
                $response->getBody()->write(json_encode($manifestacao));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('Entidade Não Cadastrada.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->post('/manifestacao_anonima', function ($request, $response) use ($conn) {
            $body = $request->getParsedBody();
            $args = $body;
            //print_r($args); // Debug: Exibir o conteúdo do corpo da requisição
            // $response->getBody()->write(json_encode($args));
            // return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
            

            if (isset($args['protocolo'])) {
                $protocolo = $args['protocolo']; // Obter o protocolo a partir do corpo da requisição

                // Executar a consulta
                // $query = "SELECT m.*, tm.nome_tipo_manifestacao FROM tb_manifestacoes m JOIN tb_tipo_manifestacoes tm ON m.id_tipo_manifestacao = tm.id_tipo_manifestacao WHERE m.protocolo_manifestacao = :protocolo";
                $query = "SELECT * FROM tb_manifestacoes WHERE protocolo_manifestacao = :protocolo";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':protocolo', $protocolo);
                $stmt->execute();
                $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($manifestacao) {
                    // Codificar os dados em JSON
                    $dados = json_encode($manifestacao);

                    // // Construir a URL base manualmente
                    $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                    if ($request->getUri()->getPort()) {
                    $baseUrl .= ':' . $request->getUri()->getPort();
                    }

                    // Construir a URL de redirecionamento
                    $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_anonima_template.php?dados=' . urlencode($dados);

                    // Redirecionar para a página PHP
                    return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302) // Redirecionamento temporário
                    ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0') // Desabilitar cache
                    ->withHeader('Pragma', 'no-cache'); // Compatibilidade com HTTP/1.0

            } else {


                $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                // Construir a URL de redirecionamento
                $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_anonima_template.php?dados=';

                // Redirecionar para a página PHP
                return $response
                ->withHeader('Location', $url)
                ->withStatus(302); // Redirecionamento temporário
            }
            } else {
                $response->getBody()->write('Protocolo não informado.');
                return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->post('/consulta_manifestacao', function ($request, $response) use ($conn) {
            $body = $request->getParsedBody();
            $args = $body;

            if (isset($args['protocolo'])) {
                $protocolo = $args['protocolo']; // Obter o protocolo a partir do corpo da requisição

                // Executar a consulta
                // $query = "SELECT m.*, tm.nome_tipo_manifestacao FROM tb_manifestacoes m JOIN tb_tipo_manifestacoes tm ON m.id_tipo_manifestacao = tm.id_tipo_manifestacao WHERE m.protocolo_manifestacao = :protocolo";
                $query = "SELECT * FROM tb_manifestacoes WHERE protocolo_manifestacao = :protocolo";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':protocolo', $protocolo);
                $stmt->execute();
                $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($manifestacao) {
                    // Codificar os dados em JSON
                    $dados = json_encode($manifestacao);

                    // // Construir a URL base manualmente
                    $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                    if ($request->getUri()->getPort()) {
                    $baseUrl .= ':' . $request->getUri()->getPort();
                    }

                    // Construir a URL de redirecionamento
                    $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_template.php?dados=' . urlencode($dados);

                    // Redirecionar para a página PHP
                    return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302) // Redirecionamento temporário
                    ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0') // Desabilitar cache
                    ->withHeader('Pragma', 'no-cache'); // Compatibilidade com HTTP/1.0

                } else {

                    $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                    // Construir a URL de redirecionamento
                    $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_anonima_template.php?dados=';

                    // Redirecionar para a página PHP
                    return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302); // Redirecionamento temporário
                }
            } else {
                $response->getBody()->write('Protocolo não informado.');
                return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->get('/entidades', function ($request, $response) use ($conn) {
            $query = "SELECT * FROM tb_entidades";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $entidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($entidades)) {
                $response->getBody()->write(json_encode($entidades));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('Entidade Não Cadastrada.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->get('/entidades/{id}', function ($request, $response, array $args) use ($conn) {
            $id = intval($args['id']); // Obter o ID da entidade a partir dos parâmetros da URL
            // Executar a consulta
            $query = "SELECT * FROM tb_entidades WHERE id_entidade = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $entidades = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($entidades)) {
                $response->getBody()->write(json_encode($entidades));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('Entidade Não Cadastrada.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->post('/inserir_entidade', function ($request, $response) use ($conn) {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response->getBody()->write('Método não permitido.');
                return $response->withStatus(405)->withHeader('Content-Type', 'text/plain');
            } else {
                $body = $request->getBody()->getContents();
                $args = json_decode($body, true);

                if (!empty($args)) {
                    try {
                        $entidade = $args;
                        $query = "INSERT INTO tb_entidades (nome_entidade, email_entidade, telefone_entidade, id_portal_entidade) VALUES (:nome_entidade, :email_entidade, :telefone_entidade, :id_portal_entidade)";
                        $stmt = $conn->prepare($query);
                        $stmt->bindValue(':nome_entidade', $entidade['nome_entidade'], PDO::PARAM_STR);
                        $stmt->bindValue(':email_entidade', $entidade['email_entidade'], PDO::PARAM_STR);
                        $stmt->bindValue(':telefone_entidade', $entidade['telefone_entidade'], PDO::PARAM_STR);
                        $stmt->bindValue(':id_portal_entidade', $entidade['id_portal_entidade'], PDO::PARAM_INT);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $response->getBody()->write('Entidade inserida com sucesso!');
                            return $response->withStatus(201)->withHeader('Content-Type', 'text/plain');
                        } else {
                            $response->getBody()->write('Erro ao inserir entidade.');
                            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                        }
                    } catch (Exception $e) {
                        $response->getBody()->write('Erro ao decodificar JSON: ' . $e->getMessage());
                        return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
                    }
                } else {
                    $response->getBody()->write('Entidade não informada.');
                    return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
                }
            }
        });

        $group->get('/usuarios', function ($request, $response) use ($conn) {
            $query = "SELECT * FROM tb_usuario";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($usuarios)) {
                $response->getBody()->write(json_encode($usuarios));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('Usuário Não Cadastrado.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });
    });
};
