<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <style>
        /* Estilo para o spinner de aguarde */
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        #overlay .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 24px;
        }

        /* Estilo para centralizar o formulário */
        #wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        /* Estilo para espaçamento interno do formulário */
        #upload_form {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Estilo para a mensagem de erro */
        #error_message {
            display: none;
            margin-top: 10px;
            color: #a94442;
        }

        /* Estilo para a div com a imagem de carregamento */
        #loading-image {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
        }

        /* Estilo para a div com a imagem de carregamento */
        #loading-image img {
            width: 350px;
            height: 75px;
        }
    </style>
</head>
<body>
    <div id="wrap">
        <div class="container">
            <form class="form-horizontal" id="upload_form" enctype="multipart/form-data">
                <h2 class="text-center">Upload</h2>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Selecione o arquivo:</label>
                    <div class="col-sm-6">
                        <input type="file" name="file" id="file" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6 text-center">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </div>
                <!-- Novo botão para baixar o exemplo de CSV -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6 text-center">
                        <button type="button" class="btn btn-secondary" id="download_example">Clique aqui para baixar um exemplo</button>
                    </div>
                </div>
                <!-- Status Message -->
                <div id="status_message" class="form-group" style="display: none;">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div id="status_alert" class="alert" role="alert"></div>
                    </div>
                </div>
                <!-- Mensagem de Erro -->
                <div id="error_message" class="text-center"></div>
            </form>
        </div>
    </div>

    <!-- Tela de fundo escura -->
    <div id="overlay">
        <div class="spinner">
        </div>
        <div id="loading-image">
            <img src="logo_lk_transparente.png" alt="Carregando...">
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#upload_form").submit(function (e) {
                e.preventDefault();

                // Verificar se o arquivo foi selecionado
                var fileInput = $("#file");
                if (fileInput.get(0).files.length === 0) {
                    showError("Selecione um arquivo antes de enviar.");
                    return;
                }

                // Verificar se o arquivo é do tipo .csv
                var file = fileInput.get(0).files[0];
                var fileExtension = file.name.split('.').pop();
                if (fileExtension.toLowerCase() !== "csv") {
                    showError("O arquivo deve ser do tipo .csv.");
                    return;
                }

                // Bloquear a página com o plugin "blockUI"
                $.blockUI({ message: $('#overlay') });

                // Mostrar a imagem de carregamento
                $("#loading-image").show();

                var formData = new FormData(this);

                $.ajax({
                    url: "resultado.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        // Desbloquear a página ao concluir
                        $.unblockUI();

                        // Ocultar a imagem de carregamento
                        $("#loading-image").hide();

                        $("#status_message").show();
                        if (data && data.success) {
                            $("#status_alert").removeClass("alert-danger").addClass("alert-success").text(data.message);

                            // Após a mensagem de sucesso, executar a consulta e baixar o arquivo CSV
                            downloadCSV();
                        } else {
                            $("#status_alert").removeClass("alert-success").addClass("alert-danger").text("Erro ao processar o upload.");
                        }

                        // Esconder a mensagem após alguns segundos
                        setTimeout(function() {
                            $("#status_message").fadeOut();
                        }, 5000); // 5000 milissegundos = 5 segundos
                    },
                    error: function () {
                        // Desbloquear a página em caso de erro
                        $.unblockUI();

                        // Ocultar a imagem de carregamento
                        $("#loading-image").hide();

                        $("#status_message").show();
                        $("#status_alert").removeClass("alert-success").addClass("alert-danger").text("Erro ao processar o upload.");

                        // Esconder a mensagem após alguns segundos
                        setTimeout(function() {
                            $("#status_message").fadeOut();
                        }, 5000); // 5000 milissegundos = 5 segundos
                    }
                });
            });

            // Função para exibir mensagens de erro
            function showError(message) {
                $("#error_message").text(message).show();
                setTimeout(function() {
                    $("#error_message").fadeOut();
                }, 5000); // 5000 milissegundos = 5 segundos
            }

            // Função para baixar o arquivo CSV
            function downloadCSV() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'consulta.php', true);
                xhr.responseType = 'blob';
                xhr.onload = function () {
                    if (this.status === 200) {
                        var blob = new Blob([this.response], { type: 'text/csv' });
                        var url = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'resultado.csv';
                        a.click();
                    }
                };
                xhr.send();
            }

            // Função para baixar o CSV de exemplo
            $("#download_example").click(function () {
                var csvContent = "conc_data;conc_historico;conc_nota;conc_debito;conc_credito\n" +
                                 "25/08/2023;BOTICARIO PRODUTOS DE BELEZA LTDA ;24651;;1910.73\n" +
                                 "23/11/2023;BOTICARIO PRODUTOS DE BELEZA LTDA;24651;1910.73;\n";
                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                if (link.download !== undefined) { // Feature detection
                    // Browsers that support HTML5 download attribute
                    var url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", "exemplo.csv");
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    </script>
</body>
</html>
