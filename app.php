<?php

/* FASE 4: Operações CRUD Básicas para Receitas em app.php */

$conn = connectDB('localhost', 'root', '', 'db_recipes');

menu($conn);

mysqli_close($conn);

// Abre conexão com uma Base de Dados
function connectDB($hostname, $username, $password, $database){
    $conn = mysqli_connect($hostname, $username, $password, $database);
    echo $conn ? "Ligação à base de dados efetuada com sucesso!\n" : "Erro na conexão com a base de dados!\n";
    return $conn;
}

// Cria novas receitas (campos: nome, descrição, tempo de preparação, doses)
function createRecipe($conn){
    $nome = readline("Nome da Receita: ");
    $descricao = readline("Descrição: ");
    $tempo_confecao = readline("Tempo de Confeção (em minutos): ");
    $doses = readline("Doses: ");

    // Criar comando SQL
    $query = "INSERT INTO receitas (nome, descricao, tempo_confecao, doses) VALUES ('$nome', '$descricao', $tempo_confecao, $doses);";
    
    //Executar o comando
    echo mysqli_query($conn, $query)
    ? "Receita de '$nome' adicionada com Sucesso (ID:<".mysqli_insert_id($conn).">)\n" 
    : "Erro a adicionar Receita.\n";
}

/*  Lista todas as receitas  (*)
 *      $short = true, para imprimir uma versão mais curta sem descrição
 */
function listRecipes($conn, $short = false){

    // Criar comando SQL
    $query = "SELECT * FROM receitas;";

    $resultado = mysqli_query($conn, $query);

    echo $short ? "" : "\n\n* * * * * * * * * * * * * * * * * * | RECEITAS | * * * * * * * * * * * * * * * * * * *\n\n";
    while($linha = mysqli_fetch_assoc($resultado)){
        echo "| ID " . $linha["id"] . " | ";
        echo "Nome: '" . $linha["nome"] . "' | ";
        echo "Tempo: " . $linha["tempo_confecao"] . " min | ";
        echo $linha["doses"] . " doses |\n";
        echo $short ? "" : "Descrição:\n" . $linha["descricao"] . "\n\n";
        echo $short ? "" : "* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * \n";
    }

    //TODO (Optional): Check better formatting for descricao, possibly \t for each \n
}

// Atualiza receitas existentes
function updateRecipe($conn){
    // listar receitas (sem a descrição)
    listRecipes($conn, true);

    // pedir id da receita
    $id = readline("Id da Receita a Editar: ");

    // Verificar se a receita existe
    $query = "SELECT id FROM receitas WHERE id = $id;";
    $resultado = mysqli_query($conn, $query);
    if(mysqli_num_rows($resultado) == 0){
        echo "Receita não encontrada.\n";
        return;
    }

    // pedir os novos campos
    $novo_nome = readline("Nome da Receita: ");
    $novo_descricao = readline("Descrição: ");
    $novo_tempo_confecao = readline("Tempo de Confeção (em minutos): ");
    $novo_doses = readline("Doses: ");

    // atualizar receita
    $query = "UPDATE receitas 
    SET nome = '$novo_nome', 
    descricao = '$novo_descricao', 
    tempo_confecao = $novo_tempo_confecao, 
    doses = $novo_doses 
    WHERE id = $id;";
    echo mysqli_query($conn, $query)
    ? "Receita de '$novo_nome' atualizada com sucesso (ID:<$id>)\n" 
    : "Erro a atualizar a receita.\n";
}

// Apaga receitas
function deleteRecipes($conn){
    // TODO: IMPLEMENT
    echo("Por implementar...\n");
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
                createRecipe($conn);
                break;
            case 2:
                listRecipes($conn);
                break;
            case 3:
                updateRecipe($conn);
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