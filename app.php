<?php

/* FASE 4: Operações CRUD Básicas para Receitas em app.php */

$conn = connectDB('localhost', 'root', '', 'db_recipes');

//

mysqli_close($conn);

// Abre conexão com uma Base de Dados
function connectDB($hostname, $username, $password, $database){
    $conn = mysqli_connect($hostname, $username, $password, $database);
    echo $conn ? " Ligação à base de dados efetuada com sucesso!\n" : "Erro na conexão com a base de dados!\n";
    return $conn;
}

// Cria novas receitas (campos: nome, descrição, tempo de preparação, doses)
function createNewRecipe($conn){
    // TODO: IMPLEMENT
}

// Lista todas as receitas
function listRecipes($conn){
    // TODO: IMPLEMENT
}

// Atualiza receitas existentes
function updateRecipes($conn){
    // TODO: IMPLEMENT
}

// Apaga receitas
function deleteRecipes($conn){
    // TODO: IMPLEMENT
}

?>