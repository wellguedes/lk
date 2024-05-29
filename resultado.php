<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_FILES["file"]["tmp_name"];

    if (($open = fopen($filename, "r")) !== FALSE) {
        // Parâmetros de conexão
        $host = 'babar.db.elephantsql.com';
        $dbname = 'zflzraba';
        $user = 'zflzraba';
        $password = 'fdjiXbYelirQ4_5_Wb3yyo8JgVstYNGQ';

        // Configuração da string de conexão
        $dsn = "pgsql:host=$host;dbname=$dbname";

        // Criação de uma instância do objeto PDO
        $conn = new PDO($dsn, $user, $password);

        setlocale(LC_ALL, 'pt_BR.utf8');

        // Ignorar a primeira linha do arquivo (cabeçalho)
        fgetcsv($open, 1000, ";");

        // Adição para rastrear o número de linhas processadas
        $rowCount = 0;

        while (($data = fgetcsv($open, 1000, ";")) !== FALSE) {
            $rowCount++;

            $conc_data = trim(DateTime::createFromFormat('d/m/Y', $data[0])->format('Y-m-d'));
            $conc_historico = trim($data[1]);
            $conc_nota = trim($data[2]);

            // Verificar e atribuir 0 se conc_debito for nulo
            $conc_debito = isset($data[3]) && $data[3] !== "" ? trim(str_replace(',', '.', $data[3])) : 0;

            // Verificar e atribuir 0 se conc_credito for nulo
            $conc_credito = isset($data[4]) && $data[4] !== "" ? trim(str_replace(',', '.', $data[4])) : 0;
            // Preparar a consulta SQL
            $stmt = $conn->prepare("INSERT INTO conciliacao (conc_data, conc_historico, conc_nota, conc_debito, conc_credito) 
                                    VALUES (:conc_data, :conc_historico, :conc_nota, :conc_debito, :conc_credito)");

            // Bind dos parâmetros
            $stmt->bindParam(':conc_data', $conc_data);
            $stmt->bindParam(':conc_historico', $conc_historico);
            $stmt->bindParam(':conc_nota', $conc_nota);
            $stmt->bindParam(':conc_debito', $conc_debito);
            $stmt->bindParam(':conc_credito', $conc_credito);
       
            // Adição de mensagens de log para debug
            error_log("Values: " . json_encode($data));
            
            // Executar a consulta
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a consulta: " . implode(", ", $stmt->errorInfo()));
            // Deletar o conteúdo da tabela conciliacao
            $sqlDelete = "DELETE FROM conciliacao";
            pg_query($conn, $sqlDelete);
            // Encerrar a conexão com o banco de dados
            pg_close($conn);

            }
        }

        fclose($open);

        // Retornar uma resposta JSON
        $response = array('success' => true, 'message' => "Dados importados com sucesso. Linhas processadas: $rowCount");
        echo json_encode($response);
    } else {
        // Retornar uma resposta JSON em caso de erro
        $response = array('success' => false, 'message' => 'Erro ao processar o upload.');
        echo json_encode($response);
    }
}
?>
