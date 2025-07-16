<?php

/* FASE 3: Criação da Base de Dados e Exportação de Dados de Teste */

// 1. e 2.
$conn = connectDB('localhost', 'root', '', 'db_recipes');

// 3. Fecha a ligação à base de dados.
mysqli_close($conn);        

// Abre conexão com uma Base de Dados
function connectDB($hostname, $username, $password, $database){
    // 1. Estabelece ligação à base de dados com mysqli
    $conn = mysqli_connect($hostname, $username, $password, $database);
    // 2. Mostra a mensagem: Ligação à base de dados efetuada com sucesso!
    echo $conn ? " Ligação à base de dados efetuada com sucesso!\n" : "Erro na conexão com a base de dados!\n";
    return $conn;
}

?>