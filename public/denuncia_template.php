 <?php
        if (isset($_GET['dados'])) {
            // Decodificar os dados do JSON
            $dados = json_decode($_GET['dados'], true);

            // Exibir os dados ou usá-los como necessário
            // echo '<h1>Dados da Manifestação</h1>';
            // echo '<pre>';
            // print_r($dados);
            // echo '</pre>';
        } else {
            echo 'Nenhum dado foi passado.';
        }
        ?> 
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aplicativo web de Ouvidoria">
    <meta name="author" content="L3 Tecnologia/IT Soluções">
    <title>Ouvidoria Web - Anônimo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=communication" />
    <link rel="stylesheet" href="https://l3tecnologia.app.br/ouvidoriaweb/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Adicione um estilo específico para telas menores, como celulares */
        @media (max-width: 767px) {

            /* Seletor para a tabela que você deseja adicionar o scroll horizontal */
            .sua-tabela {
                /* Defina a largura máxima da tabela para ativar o scroll horizontal quando necessário */
                max-width: 100%;
                /* Adicione um scroll horizontal quando o conteúdo excede a largura da tabela */
                overflow-x: auto;
                display: block;
                /* Adicione display: block para forçar a barra de rolagem horizontal */
            }

            /* Opcional: Remova as bordas da tabela para um visual mais limpo */
            .sua-tabela,
            .sua-tabela th,
            .sua-tabela td {
                border: none;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="nav_controller navbar d-none d-lg-block d-xl-block d-xxl-block">
            <div class="container-fluid">
                <div class="container ">
                    <div class="row ">
                        <div class="col-3 col-xl-3 col-lg-3">
                            <a href="https://l3tecnologia.app.br/ouvidoriaweb/login.php"><img class="rounded-5" src="https://l3tecnologia.app.br/ouvidoriaweb/assets/images/ouvidoria_web_branco_logo.png" width="90px" height="90" alt="Ouvidoria Web"></a>
                        </div>
                        <div class="col-6 col-xl-6 col-lg-6">
                            <div class="cont_sessao2">
                                <center>
                                    <h3 class="text-white">Consulta Manifestação</h3>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <nav class="nav_controller_mobile navbar navbar-expand-lg d-lg-none d-xl-none d-xxl-none">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <img src="assets/images/ouvidoria_web_branco_logo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-top">
                </a>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">

                </div>
            </div>
        </nav>
    </header>
    <main>

        <div class="container">

            <?php if (!empty($dados)) { ?>
                <div class="row">
                    <div class="col-12 col-lg-12 col-xl-12 col-xxl-12 mt-5">
                        <div class="cont_sessao">
                            <h3 class="text-center">Dados da Manifestação</h3>
                            <h5 class="text-center"><strong>Protocolo:</strong> <?= $dados['protocolo_manifestacao'] ?></h5>
                            <p class="text-center">Abaixo estão os dados da manifestação que você solicitou.</p>
                        </div>
                    </div>
                    <div class="col-12 col-lg-12 col-xl-12 col-xxl-12 mt-5">
                        <table class="table table-striped">
                            <thead>
                            </thead>
                            <tbody>
                                <!-- <tr>
                                    <th>Tipo de Manifestação:</th>
                                    <td>Denúncia</td>
                                </tr> -->
                                <tr>
                                    <th>Motivo da Manifestação:</th>
                                    <td><?= $dados['motivo_manifestacao'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Conteúdo da Manifestação:</th>
                                    <td><?= $dados['conteudo_manifestacao'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Observações:</th>
                                    <td><?= $dados['observacoes_manifestacao'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Local da Manifestação:</th>
                                    <td><?= $dados['local_manifestacao'] ?></td>
                                </tr>
                                <tr>
                                    <?php if ($dados['arquivo_manifestacao'] == 'Sem anexos') { ?>
                                        <th scope="row">Anexo:</th>
                                        <td><?= $dados['arquivo_manifestacao'] ?></td>
                                    <?php } else { ?>
                                        <th scope="row">Anexo:</th>
                                        <td><a target="_blank" href="https://l3tecnologia.app.br/ouvidoriaweb/<?= $dados['arquivo_manifestacao'] ?>">ver anexo</a></td>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <th scope="row">Data da Manifestação:</th>
                                    <td><?= $dados['data_manifestacao'] ?></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-12 col-lg-12 col-xl-12 col-xxl-12 mt-5">
                    <div class="cont_sessao">
                        <h3 class="text-center">Resposta da Gestão</h3>
                    </div>
                </div>
                <?php } else { ?>
                    <div class="col-12 col-lg-12 col-xl-12 col-xxl-12 mt-5">
                        <div class="cont_sessao">
                            <h3 class="text-center">Não há nenhuma solicitação com este protocolo</h3>
                        </div>
                    </div>
                <?php } ?>
                
            </div>
        </div>



    </main>
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-xl-3 col-xxl-3 col-sm-12 col-md-12 logo_footer">
                    <img src="https://l3tecnologia.app.br/ouvidoriaweb/assets/images/ouvidoria_web_branco.png" width="280px" alt="Ouvidoria Web">
                </div>
                <div class="col-3 col-lg-3 col-xl-3 col-xxl-3 col-sm-12 col-md-12 logo_footer">
                    <h5>Solicitações</h5>
                    <ul>
                        <li><a href="consulta_solicitacoes_anonimas.php">Consultar Solicitações Anônimas</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-xl-3 col-xxl-3 col-sm-12 col-md-12 logo_footer">
                    <h5>Informações</h5>
                    <ul>
                        <li><a target="_blank" href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm">Lei Geral de Proteção de Dados</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-xl-3 col-xxl-3 col-sm-12 col-md-12 logo_footer">
                    <h5>Ajuda</h5>
                    <ul>
                        <li><a href="">Suporte</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <div class="subfooter">
                &copy;<?= date('Y') ?> Desenvolvido por L3tecnologia
            </div>
        </div>
    </footer>
    </main>
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                "order": [
                    [0, "desc"]
                ],
                "language": {
                    "decimal": "",
                    "emptyTable": "Nenhum dado disponível na tabela",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtrado de _MAX_ entradas totais)",
                    "infoPostFix": "",
                    "thousands": ".",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "loadingRecords": "Carregando...",
                    "processing": "Processando...",
                    "search": "Pesquisar:",
                    "zeroRecords": "Nenhum registro encontrado",
                    "paginate": {
                        "first": "Primeiro",
                        "last": "Último",
                        "next": "Próximo",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": ativar para ordenar a coluna em ordem crescente",
                        "sortDescending": ": ativar para ordenar a coluna em ordem decrescente"
                    }
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.date').mask('00/00/0000');
            $('.time').mask('00:00:00');
            $('.date_time').mask('00/00/0000 00:00:00');
            $('.cep').mask('00000-000');
            $('.phone').mask('0000-0000');
            $('.phone_with_ddd').mask('(00) 0000-0000');
            $('.phone_us').mask('(000) 000-0000');
            $('.mixed').mask('AAA 000-S0S');
            $('.cpf').mask('000.000.000-00', {
                reverse: true
            });
            $('.cnpj').mask('00.000.000/0000-00', {
                reverse: true
            });
            $('.money').mask('000.000.000.000.000,00', {
                reverse: true
            });
            $('.money2').mask("#.##0,00", {
                reverse: true
            });
            $('.ip_address').mask('0ZZ.0ZZ.0ZZ.0ZZ', {
                translation: {
                    'Z': {
                        pattern: /[0-9]/,
                        optional: true
                    }
                }
            });
            $('.ip_address').mask('099.099.099.099');
            $('.percent').mask('##0,00%', {
                reverse: true
            });
            $('.clear-if-not-match').mask("00/00/0000", {
                clearIfNotMatch: true
            });
            $('.placeholder').mask("00/00/0000", {
                placeholder: "__/__/____"
            });
            $('.fallback').mask("00r00r0000", {
                translation: {
                    'r': {
                        pattern: /[\/]/,
                        fallback: '/'
                    },
                    placeholder: "__/__/____"
                }
            });
            $('.selectonfocus').mask("00/00/0000", {
                selectOnFocus: true
            });
        });
    </script>
</body>

</html>