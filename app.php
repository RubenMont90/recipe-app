<?php

/* FASE 4: Operações CRUD Básicas para Receitas em app.php */

$conn = connectDB('localhost', 'root', '', 'db_recipes');

menu($conn);

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

// Loop Menu para interagir com o User
function menu($conn){
    do{
        printMenu();

        $menu_opt = readline("\n> ");

        switch($menu_opt){
            case 0:
                echo "\n< Sair do Programa >";
                break;
            case 1:
                createNewRecipe($conn);
                break;
            case 2:
                listRecipes($conn);
                break;
            case 3:
                updateRecipes($conn);
                break;
            case 4:
                deleteRecipes($conn);
                break;
            default:
                echo "\nERRO: Opção Inválida!\n";
                break;
        }
    }while($menu_opt != 0);
}

// Imprime Menu
function printMenu(){
    echo "\n* * * * * * Escolha uma opção * * * * * *\n";
    echo "*\t\t\t\t\t*\n";
    echo "*  1 => Criar Novas Receitas \t\t*\n";
    echo "*  2 => Listar todas as Receitas\t*\n";
    echo "*  3 => Atualizar receitas existentes\t*\n";
    echo "*  4 => Apagar receitas\t\t\t*\n";
    echo "*\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * * * * * * * * * * *\n";
    echo "*\t\t\t\t\t*\n";
    echo "*  0 => Sair do Programa\t\t*\n";
    echo "*\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * * * * * * * * * * *\n";
}
?>