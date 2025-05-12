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
            $query = "SELECT m.*, u.nome_usuario, u.sobrenome_usuario, u.cpf_usuario, u.email_usuario, tm.nome_tipo_manifestacao 
                      FROM tb_manifestacoes m 
                      JOIN tb_usuario u ON m.id_usuario_manifestacao = u.id_usuario 
                      JOIN tb_tipo_manifestacoes tm ON m.id_tipo_manifestacao = tm.id_tipo_manifestacao 
                      JOIN tb_entidades e ON m.id_entidade_manifestacao = e.id_entidade 
                      WHERE e.id_portal_entidade = :id";
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
                $query = "SELECT m.*, u.nome_usuario, u.sobrenome_usuario, u.cpf_usuario, u.email_usuario FROM tb_manifestacoes m JOIN tb_usuario u ON m.id_usuario_manifestacao = u.id_usuario WHERE m.protocolo_manifestacao = :protocolo";
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

        $group->post('/consulta_manifestacao', function ($request, $response) use ($conn) {
            $body = $request->getParsedBody();
            $args = $body;

            if (isset($args['protocolo'])) {
                $protocolo = $args['protocolo']; // Obter o protocolo a partir do corpo da requisição

                // Executar a consulta
                $query = "SELECT m.*, u.nome_usuario, u.sobrenome_usuario, u.cpf_usuario, u.email_usuario FROM tb_manifestacoes m JOIN tb_usuario u ON m.id_usuario_manifestacao = u.id_usuario WHERE m.protocolo_manifestacao = :protocolo";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':protocolo', $protocolo);
                $stmt->execute();
                $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($manifestacao) {
                    // coleta dados
                    $queryRespostas = "SELECT * FROM tb_respostas_manifestacoes WHERE id_manifestacao = :id_manifestacao";
                    $stmtRespostas = $conn->prepare($queryRespostas);
                    $stmtRespostas->bindValue(':id_manifestacao', $manifestacao['id_manifestacao'], PDO::PARAM_INT);
                    $stmtRespostas->execute();
                    $respostas = $stmtRespostas->fetchAll(PDO::FETCH_ASSOC);

                    $manifestacao['respostas'] = $respostas; // Adicionar as respostas ao JSON da manifestação

                    // Codificar os dados em JSON
                    $dados = json_encode($manifestacao);
                    $respostas = json_encode($respostas);

                    // // Construir a URL base manualmente
                    $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                    if ($request->getUri()->getPort()) {
                        $baseUrl .= ':' . $request->getUri()->getPort();
                    }

                    // Construir a URL de redirecionamento
                    $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_template.php?dados=' . urlencode($dados) . '&respostas=' . urlencode(json_encode($respostas));

                    // Redirecionar para a página PHP
                    return $response
                        ->withHeader('Location', $url)
                        ->withStatus(302) // Redirecionamento temporário
                        ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0') // Desabilitar cache
                        ->withHeader('Pragma', 'no-cache'); // Compatibilidade com HTTP/1.0

                } else {

                    $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                    // Construir a URL de redirecionamento
                    $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_template.php?dados=';

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

        $group->get('/consulta_manifestacoes_protocolo/{protocolo}', function ($request, $response, array $args) use ($conn) {
            $protocolo = $args['protocolo']; // Obter o protocolo a partir dos parâmetros da URL
            // Executar a consulta
            $query = "SELECT m.*, u.nome_usuario, u.sobrenome_usuario, u.cpf_usuario, u.email_usuario FROM tb_manifestacoes m JOIN tb_usuario u ON m.id_usuario_manifestacao = u.id_usuario WHERE m.protocolo_manifestacao = :protocolo";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':protocolo', $protocolo, PDO::PARAM_STR);
            $stmt->execute();
            $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($manifestacao) {
                $queryRespostas = "SELECT rm.* FROM tb_respostas_manifestacoes rm WHERE rm.id_manifestacao = :id_manifestacao";
                $stmtRespostas = $conn->prepare($queryRespostas);
                $stmtRespostas->bindValue(':id_manifestacao', $manifestacao['id_manifestacao'], PDO::PARAM_INT);
                $stmtRespostas->execute();
                $respostas = $stmtRespostas->fetchAll(PDO::FETCH_ASSOC);

                $manifestacao['respostas'] = $respostas; // Adicionar as respostas ao JSON da manifestação
            }

            if (!empty($manifestacao)) {

                $response->getBody()->write(json_encode($manifestacao));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('Manifestação Não Cadastrada.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->post('/consulta_manifestacoes_protocolo_template', function ($request, $response) use ($conn) {
            $body = $request->getParsedBody();
            $protocolo = $body['protocolo']; // Obter o protocolo do corpo da requisição

            // Executar a consulta
            $query = "SELECT m.*, u.nome_usuario, u.sobrenome_usuario, u.cpf_usuario, u.email_usuario 
              FROM tb_manifestacoes m 
              JOIN tb_usuario u ON m.id_usuario_manifestacao = u.id_usuario 
              WHERE m.protocolo_manifestacao = :protocolo";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':protocolo', $protocolo, PDO::PARAM_STR);
            $stmt->execute();
            $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($manifestacao) {
                $queryRespostas = "SELECT rm.* FROM tb_respostas_manifestacoes rm WHERE rm.id_manifestacao = :id_manifestacao";
                $stmtRespostas = $conn->prepare($queryRespostas);
                $stmtRespostas->bindValue(':id_manifestacao', $manifestacao['id_manifestacao'], PDO::PARAM_INT);
                $stmtRespostas->execute();
                $respostas = $stmtRespostas->fetchAll(PDO::FETCH_ASSOC);

                $manifestacao['respostas'] = $respostas; // Adicionar as respostas ao JSON da manifestação

                // Codificar os dados em JSON
                $dados = json_encode($manifestacao);
                $respostas = json_encode($respostas);

                // Construir a URL base manualmente
                $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
                if ($request->getUri()->getPort()) {
                    $baseUrl .= ':' . $request->getUri()->getPort();
                }

                // Construir a URL de redirecionamento
                $url = $baseUrl . '/api_ouvidoria_web/public/denuncia_template.php?dados=' . urlencode($dados) . '&respostas=' . urlencode($respostas);

                // Redirecionar para a página PHP
                return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302) // Redirecionamento temporário
                    ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0') // Desabilitar cache
                    ->withHeader('Pragma', 'no-cache'); // Compatibilidade com HTTP/1.0
            } else {
                $response->getBody()->write('Manifestação Não Cadastrada.');
                return $response->withStatus(404)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->post('/inserir_resposta', function (Request $request, Response $response, array $args) use ($conn) {
            $resposta = $request->getParsedBody();
            $tokken_api = 'api@itsolit';
            $tokken = $resposta['tokken'];

            if (password_verify($tokken_api, $tokken)) {

                if (!empty($resposta['resposta_manifestacao']) || !empty($resposta['nome_responsavel_resposta']) || !empty($resposta['id_manifestacao'])) {

                    $resposta_manifestacao = filter_var($resposta['resposta_manifestacao'], FILTER_SANITIZE_SPECIAL_CHARS);
                    $nome_responsavel_resposta = ucwords(filter_var($resposta['nome_responsavel_resposta'], FILTER_SANITIZE_SPECIAL_CHARS));
                    $id_manifestacao = intval(filter_var($resposta['id_manifestacao'], FILTER_SANITIZE_NUMBER_INT));

                    $query = "INSERT INTO tb_respostas_manifestacoes (resposta_manifestacao, nome_responsavel_resposta, id_manifestacao) VALUES (:resposta_manifestacao, :nome_responsavel_resposta, :id_manifestacao)";

                    $stmt = $conn->prepare($query);
                    $stmt->bindValue(':resposta_manifestacao', $resposta_manifestacao, PDO::PARAM_STR);
                    $stmt->bindValue(':nome_responsavel_resposta', $nome_responsavel_resposta, PDO::PARAM_STR);
                    $stmt->bindValue(':id_manifestacao', $id_manifestacao, PDO::PARAM_INT);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $response->getBody()->write('Resposta inserida com sucesso!');
                        return $response->withStatus(201)->withHeader('Content-Type', 'text/plain');
                    } else {
                        $response->getBody()->write('Erro ao inserir resposta.');
                        return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
                    }
                } else {
                    $response->getBody()->write('Resposta não informada.');
                    return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
                }
            } else {
                $response->getBody()->write('Código Hash inválido.');
                return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
            }
        });

        $group->get('/alterar_status_manifestacao/{id}', function (Request $request, Response $response) use ($conn) {
            $id = intval($request->getAttribute('id')); // Obter o ID da entidade a partir dos parâmetros da URL
            // Executar a consulta
            $query = "UPDATE tb_manifestacoes SET status_manifestacao = 'I' WHERE id_manifestacao = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $response->getBody()->write('Status alterado com sucesso!');
                return $response->withStatus(200)->withHeader('Content-Type', 'text/plain');
            } else {
                $response->getBody()->write('Erro ao alterar status.');
                return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
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
                    // Verifica se o token é válido
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
                    $response->getBody()->write('Sem dados enviados.');
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
