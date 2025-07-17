<?php

/* FASE 5: Gestão de Categorias e Associação Receita-Categoria */
$conn = connectDB('localhost', 'root', '', 'db_recipes');
shorterMenu($conn);
mysqli_close($conn);

// ****************************************| CREATE |****************************************

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

// Cria Categorias
function createCategory($conn){
    //Listar categorias
    listCategories($conn);
    
    $nome = readline("Nome da Categoria a Adicionar: ");

    // Verificar se já existe essa categoria (case insensitive) [se sim devolver id ao user]
    $nomes = [];
    $query = "SELECT * FROM categorias;";
    $resultado = mysqli_query($conn, $query);
    while($linha = mysqli_fetch_assoc($resultado)){
        $nomes += [ $linha["id"] => strtolower($linha["nome"])];
    }

    if(in_array(strtolower($nome), $nomes)){
        echo "Erro: Categoria não adicionada à Base de Dados.\n";
        echo "Categoria '$nome' já existe na Base de Dados (ID:<" . array_search(strtolower($nome), $nomes) . ">)\n";
        return;
    }
    
    // Criar comando SQL para inserir
    $query = "INSERT INTO categorias (nome) VALUES ('$nome');";

    //Executar o comando
    echo mysqli_query($conn, $query)
    ? "Categoria '$nome' adicionada com sucesso. (ID:<" . mysqli_insert_id($conn) . ">)\n" 
    : "Erro a adicionar Categoria.\n";
}

// Cria categorias_receitas
// (Associar categorias a uma receita)
function createCategoryRecipes($conn){
    // TODO: IMPLEMENT
    echo "Not Implemented yet";
    return;

    listRecipes($conn, false);

    $id_recipe = readline("Id da Receita para adicionar categorias: ");

    // Verificar se $id_recipe existe e é válido

    // Listar as categorias que a receita (JÁ TEM ASSOCIADAS) : IMPLEMENTAR listRecipeCategories()

    // Listar as categorias possiveis de adicionar (NÃO TEM ASSOCIADAS) : IMPLEMENTAR listAvailableRecipeCategories()

    $id_category = readline("Id da categoria a associar à receita: ");

    // Verificar se o $id_category pertence à lista listAvailableRecipeCategories()

    // INSERT em CATEGORIAS_RECEITAS
    
    // !!! PERGUNTAR SE É NECESSÁRIO LOOP PARA CONTINUAR A ADICIONAR CATEGORIAS OU SE É ONE AND DONE !!!

}

// ****************************************| READ |****************************************

/*  Lista todas as receitas  (*)
 *      $show_description = false, para imprimir uma versão mais curta sem descrição
 *      (usado em funções onde se pede ao user para colocar id)
 */
function listRecipes($conn, $show_description = true){
    // Criar comando SQL
    $query = "SELECT * FROM receitas;";

    $resultado = mysqli_query($conn, $query);

    echo $show_description ? "\n\n* * * * * * * * * * * * * * * * * * | RECEITAS | * * * * * * * * * * * * * * * * * * *\n\n" : "";
    while($linha = mysqli_fetch_assoc($resultado)){
        echo "| ID " . $linha["id"] . " | ";
        echo "Nome: '" . $linha["nome"] . "' | ";
        echo "Tempo: " . $linha["tempo_confecao"] . " min | ";
        echo $linha["doses"] . " doses |\n";
        echo $show_description ? "Descrição:\n" . $linha["descricao"] . "\n\n" : "";
        echo $show_description ?"* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * \n\n" : "";
    }

    //TODO (Optional): Check better formatting for descricao, possibly \t for each \n
}

// Lista todas as Categorias
function listCategories($conn){
    // Criar comando SQL
    $query = "SELECT id, nome FROM categorias;";
    $resultado = mysqli_query($conn, $query);
    while($linha = mysqli_fetch_assoc($resultado)){
        echo "| ID " . $linha["id"] . " | Nome '" . $linha["nome"] . "' |\n";
    }
}

// Lista todas as Receitas dada uma Categoria [*]
function listRecipesInCategory(){
    // TODO: IMPLEMENT
    echo "Not Implemented yet";
}

// ****************************************| UPDATE |****************************************

// Atualiza receitas existentes
function updateRecipe($conn){
    // listar receitas (sem a descrição)
    listRecipes($conn, false);

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
    ? "Receita de '$novo_nome' (ID:<$id>) atualizada com sucesso.\n" 
    : "Erro a atualizar a receita.\n";
}

// ****************************************| DELETE |****************************************

// Apaga receitas
function deleteRecipe($conn){

    // listar receitas (sem a descrição)
    listRecipes($conn, false);
    
    $id = readline("Id da Receita a Apagar: ");

    $query = "SELECT * FROM receitas WHERE id = $id;";

    $resultado = mysqli_query($conn, $query);
    if(mysqli_num_rows($resultado) == 0){
        echo "Receita não encontrada.\n";
        return;
    }

    // remover receita de <receitas>
    $query = "DELETE FROM receitas WHERE id = $id;";
    echo mysqli_query($conn, $query)
    ? "Receita (ID:<$id>) eliminada de <receitas> com sucesso.\n" 
    : "Erro a eliminar a receita de <receitas>.\n";
    
    // remover associacoes com a receita de <categorias_receitas>
    $query = "DELETE FROM categorias_receitas WHERE id_receita = $id;";
    echo mysqli_query($conn, $query)
    ? "Receita (ID:<$id>) eliminada de <categorias_receitas> com sucesso.\n" 
    : "Erro a eliminar a receita de <categorias_receitas>.\n";

    // verificar se os nomes de ingredientes estão presentes em mais alguma receita (ingredientes_receitas)
    $query = "SELECT * FROM ingredientes_receitas WHERE id_receita = $id;";
    $resultado = mysqli_query($conn, $query);
    $ingredientes = [];

    // guardar todos os ingredientes presentes na receita
    echo "Ingredientes da receita:\n";
    while($linha = mysqli_fetch_assoc($resultado)){
        $ingredientes[] = $linha["nome_ingrediente"];
        echo "> Nome: ". $linha["nome_ingrediente"] . "\n";
    }

    // remover associacoes com a receita de <ingredientes_receitas>
    $query = "DELETE FROM ingredientes_receitas WHERE id_receita = $id;";
    echo mysqli_query($conn, $query)
    ? "Receita (ID:<$id>) eliminada de <ingredientes_receitas> com sucesso.\n" 
    : "Erro a eliminar a receita de <ingredientes_receitas>.\n";
    
    // procurar nos <ingredientes_receitas> por ingrediente da receita e eliminar de <ingredientes>
    foreach($ingredientes as $ingrediente){
        $query = "SELECT * FROM ingredientes_receitas WHERE nome_ingrediente = '$ingrediente';";
        $result = mysqli_query($conn, $query);
        if(mysqli_num_rows($result) == 0){
            // eliminar o ingrediente em questão dos ingredientes_receitas (não está presente em mais receitas)
            $query = "DELETE FROM ingredientes WHERE nome = '$ingrediente';";
            echo mysqli_query($conn, $query)
            ? "Ingrediente (Nome:<$ingrediente>) eliminado de <ingredientes> com sucesso.\n" 
            : "Erro a eliminar o ingrediente de <ingredientes>.\n";
        }
    }
}

// Apaga categorias_receitas [*]
// (Desassociar categorias a uma receita)
function deleteCategoryRecipes(){
    // TODO: IMPLEMENT
    echo "Not Implemented yet";
}

// ****************************************| PROGRAM |****************************************

// Menu com todas as operações
function menu($conn){
    //do{
    printMenu();

    $menu_opt = readline("\n> ");

    switch($menu_opt){
        case 0:
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
            deleteRecipe($conn);
            break;
        case 5:
            createCategory($conn);
            break;
        case 6:
            listCategories($conn);
            break;
        case 7:
            createCategoryRecipes($conn);
            break;
        case 8:
            deleteCategoryRecipes($conn);
            break;
        case 9:
            listRecipesInCategory($conn);
            break;
        default:
            echo "\nERRO: Opção Inválida!\n";
            break;
    }
    //}while($menu_opt != 0);
    return $menu_opt;
}

// Menu mais curto
function shorterMenu($conn){
    do{
        $opt = readline("\n| [M]enu | [S]air | > ");
        $opt = strtolower($opt);
        switch($opt){
            case "m":
                $opt = menu($conn);
                $opt = $opt == 0 ? "s" : "";
                break;
            case "s":
                break;
            default:
                echo "\nERRO: Opção Inválida!\n";
                break;
        }
    }while($opt != "s");
    echo "\n< Sair do Programa >\n";
}

// Imprime Menu
function printMenu(){
    echo "\n* * * * * * * | Escolha uma opção | * * * * * * *\n";
    echo "*\t\t\t\t\t\t*\n";
    echo "*  0 => Sair do Programa\t\t\t*\n";
    echo "*\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * | Fase  4 | * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t*\n";
    echo "*  1 => Criar Novas Receitas \t\t\t*\n";
    echo "*  2 => Listar todas as Receitas\t\t*\n";
    echo "*  3 => Atualizar receitas existentes\t\t*\n";
    echo "*  4 => Apagar receitas\t\t\t\t*\n";
    echo "*\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * | Fase  5 | * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t*\n";
    echo "*  5 => Criar Categorias\t\t\t*\n";
    echo "*  6 => Listar Categorias\t\t\t*\n";
    echo "*  7 => Associar Receitas a Categorias\t\t*\n";
    echo "*  8 => Desassociar Receitas a Categorias\t*\n";
    echo "*  9 => Listar Receitas por Categoria\t\t*\n";
    echo "*\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * * * * * * * * * * * * * * *\n";
}

// Abre conexão com uma Base de Dados
function connectDB($hostname, $username, $password, $database){
    $conn = mysqli_connect($hostname, $username, $password, $database);
    echo $conn ? "\n> Ligação à base de dados efetuada com sucesso!\n" : "\n> Erro na conexão com a base de dados!\n";
    return $conn;
}



?>