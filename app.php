<?php

/* FASE 5: Gestão de Categorias e Associação Receita-Categoria */
$conn = connectDB('localhost', 'root', '', 'db_recipes');
shorterMenu($conn);
mysqli_close($conn);

// ****************************************| CREATE |****************************************

// FASE 4 - 4.1 - Criar novas receitas
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

// FASE 5 - 5.1.1 - Criar Categorias
// Cria Categoria
function createCategory($conn){
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
        echo "Erro: Categoria '$nome' já existe na Base de Dados (ID:<" . array_search(strtolower($nome), $nomes) . ">)\n";
        return;
    }
    
    $query = "INSERT INTO categorias (nome) VALUES ('$nome');";
    echo mysqli_query($conn, $query)
    ? "Categoria '$nome' adicionada com sucesso. (ID:<" . mysqli_insert_id($conn) . ">)\n" 
    : "Erro a adicionar Categoria.\n";
}

// FASE 5 - 5.2.1 - Associar receitas a categorias 
// Cria registos na tabela categorias_receitas
function createCategoryRecipes($conn){
    listRecipes($conn, false);
    $id_recipe = readline("Id da Receita para adicionar categorias: ");
    
    // Verificar se $id_recipe existe e é válido
    $query = "SELECT * FROM receitas WHERE id = $id_recipe;";
    $resultado = mysqli_query($conn, $query);
    if(mysqli_num_rows($resultado) == 0){
        echo "Erro: Não existe Receita com o ID<$id_recipe>.\n";
        return;
    }

    // Listar as categorias que a receita (JÁ TEM ASSOCIADAS)
    // $already_assoc_categs = listRecipeCategories($conn, true, $id_recipe);
    $already_assoc_categs = listCategoriesInRecipe($conn, $id_recipe, false);

    // Listar as categorias possiveis de adicionar (NÃO TEM ASSOCIADAS)
    // $available_categs = listRecipeCategories($conn, false, $id_recipe);
    $available_categs = listCategoriesNotInRecipe($conn, $id_recipe);
    
    if(count($available_categs) == 0)
        return;
    
    $id_category = readline("Id da categoria a associar à receita: ");
    
    // Verificar se já se encontra associada à receita
    if(in_array($id_category ,$already_assoc_categs)){
        echo "Erro: Categoria com o Id<$id_category> já se encontra associada à receita.\n";
        return;
    }

    // Verificar se o $id_category pertence à lista de categorias por associar à receita, caso contrário não existe
    if(!in_array($id_category ,$available_categs)){
        echo "Erro: Categoria com o Id<$id_category> não existe.\n";
        return;
    }

    // INSERT em CATEGORIAS_RECEITAS
    $query = "INSERT INTO categorias_receitas (id_receita, id_categoria) VALUES ($id_recipe, $id_category);";
    echo mysqli_query($conn, $query)
    ? "Categoria associada à receita com sucesso.\n" 
    : "Erro a associar categoria à receita.\n";

}

// ****************************************| READ |****************************************

// FASE 4 - 4.2 - Listar todas as receitas
/*  Lista receitas  (*)
 *      $show_description = false, para imprimir uma versão mais curta sem descrição
 *      (usado em funções onde se pede ao user para colocar id para não poluir a consola)
 */
function listRecipes($conn, $show_description = true){
    $recipe_ids = [];
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
        $recipe_ids[] = $linha["id"];
    }
    return $recipe_ids;
    //TODO (Optional): Check better formatting for descricao, possibly \t for each \n
}

// FASE 5 - 5.1.2 - Listar Categorias
// Lista todas as Categorias
function listCategories($conn, $print = true){
    $categories = [];
    $query = "SELECT id, nome FROM categorias;";
    $resultado = mysqli_query($conn, $query);
    while($linha = mysqli_fetch_assoc($resultado)){
        echo $print ? "| ID " . $linha["id"] . " | Nome '" . $linha["nome"] . "' |\n": "";
        $categories[] = $linha["id"];
    }
    return $categories;
}

// FASE 5 - 5.3 - Consultar receitas filtradas por categoria
// Lista todas as Receitas dada uma Categoria
function listRecipesInCategory($conn){
    $categories = listCategories($conn);

    $id_category = readline("Id da categoria para listar receitas: ");

    if(!in_array($id_category, $categories)){
        echo "Erro: Categoria inexistente.\n";
        return;
    }
    
    $query = "SELECT receitas.id as id, receitas.nome as nome
    FROM categorias_receitas 
    INNER JOIN categorias 
    ON categorias_receitas.id_categoria = categorias.id 
    INNER JOIN receitas 
    ON categorias_receitas.id_receita = receitas.id 
    WHERE categorias.id = $id_category;";
    
    $resultado = mysqli_query($conn, $query);
    if(mysqli_num_rows($resultado) == 0){
        echo "Erro: Não existem receitas nesta categoria.\n";
        return;
    }

    while($linha = mysqli_fetch_assoc($resultado)){
        echo "  | ID: " . $linha["id"] . " | Nome: " . $linha["nome"] . " |\n";
    }
}

// EXTRA - lista as categorias associadas à receita
function listCategoriesInRecipe($conn, $id_recipe, $print = true){
    $categories_assoc = [];

    $query = "SELECT categorias.id, categorias.nome 
    FROM categorias_receitas 
    INNER JOIN categorias 
    ON categorias_receitas.id_categoria = categorias.id 
    WHERE categorias_receitas.id_receita = $id_recipe;";

    $resultado = mysqli_query($conn, $query);

    if(mysqli_num_rows($resultado) == 0)
        echo $print ? "Não existem categorias associadas a esta receita.\n" : "";

    while($linha = mysqli_fetch_assoc($resultado)){
        $categories_assoc[] = $linha["id"];
        echo $print ? "  | ID " . $linha["id"] . " | Nome: " . $linha["nome"] . " |\n" : "";
    }

    return $categories_assoc;
}

// EXTRA - lista as categorias não associadas à receita
function listCategoriesNotInRecipe($conn, $id_recipe, $print = true){
    $categories_not_assoc = [];

    $query = "SELECT categorias.id, categorias.nome 
    FROM categorias 
    LEFT JOIN categorias_receitas 
    ON categorias_receitas.id_categoria = categorias.id
    AND categorias_receitas.id_receita = $id_recipe
    WHERE categorias_receitas.id_receita IS NULL;";
    
    $resultado = mysqli_query($conn, $query);

    if(mysqli_num_rows($resultado) == 0)
        echo $print ? "Não existem categorias por associar a esta receita.\n" : "";
    
    while($linha = mysqli_fetch_assoc($resultado)){
        $categories_not_assoc[]= $linha["id"];
        echo $print ? "  | ID " . $linha["id"] . " | Nome '" . $linha["nome"] . "' |\n" : "";
    }

    return $categories_not_assoc;
}

// ****************************************| UPDATE |****************************************

// FASE 4 - 4.3 - Atualizar receitas existentes
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

// FASE 4 - 4.4 - Apagar receitas
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

// FASE 5 - 5.2.2 - Desassociar receitas a categorias 
// Apaga registos na tabela categorias_receitas
function deleteCategoryRecipes($conn){
    $recipe_ids = listRecipes($conn, false);
    $id_recipe = readline("Id da Receita para adicionar categorias: ");

    if(!in_array($id_recipe, $recipe_ids)){
        echo "Erro: Receita inexistente.\n";
        return;
    }

    // $associated_categs = listRecipeCategories($conn, true, $id_recipe);
    $associated_categs = listCategoriesInRecipe($conn, $id_recipe);

    if(count($associated_categs) == 0)
        return;
    
    $id_category = readline("Id da categoria a associar à receita: ");
    
    // Verificar se a categoria não se encontra associada à receita
    if(!in_array($id_category ,$associated_categs)){
        echo "Erro: Categoria com o Id<$id_category> não se encontra associada à receita.\n";
        return;
    }
    
    $query = "DELETE FROM categorias_receitas WHERE id_receita = $id_recipe AND id_categoria = $id_category;";
    echo mysqli_query($conn, $query)
    ? "Categoria desassociada à receita com sucesso.\n" 
    : "Erro a desassociar categoria à receita.\n";

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

// ****************************************| DEPRECATED |****************************************

// (DEPRECATED) Listar as categorias que a receita (JÁ TEM ASSOCIADAS) | (NÃO TEM ASSOCIADAS)
function listRecipeCategories($conn, $associated_with_recipe, $id_recipe = 0){
    $categories_assoc = [];
    $categories_not_assoc = [];
    
    if($id_recipe == 0)
        return;

    // associadas
    $query = "SELECT categorias.id, categorias.nome 
    FROM categorias_receitas 
    INNER JOIN categorias 
    ON categorias_receitas.id_categoria = categorias.id 
    WHERE categorias_receitas.id_receita = $id_recipe;";

    echo $associated_with_recipe ? "Categorias já associadas à receita:\n" : "";
    //save the associated indexes
    $resultado = mysqli_query($conn, $query);
    if(mysqli_num_rows($resultado) == 0 && $associated_with_recipe){
        echo $associated_with_recipe ? "Não existem categorias associadas a esta receita.\n" : "";
    }
    else{
        while($linha = mysqli_fetch_assoc($resultado)){
            $categories_assoc[] = $linha["id"];
            echo $associated_with_recipe ? "  | ID " . $linha["id"] . " | Nome: " . $linha["nome"] . " |\n" : "";
        }
    }

    // nao associadas
    $cats_string = implode(",", $categories_assoc);
    //caso não existam categorias associadas à receita, apenas listar categorias disponiveis
    $query = count($categories_assoc) == 0 ? "SELECT * FROM categorias;" : "SELECT * FROM categorias WHERE id NOT IN ($cats_string);";
    $resultado = mysqli_query($conn, $query);
    echo $associated_with_recipe ? "" : "Categorias ainda não associadas à receita:\n";
    while($linha = mysqli_fetch_assoc($resultado)){
        echo $associated_with_recipe ? "" : "  | ID " . $linha["id"] . " | Nome '" . $linha["nome"] . "' |\n";
        $categories_not_assoc[]= $linha["id"];
    }

    return $associated_with_recipe ? $categories_assoc : $categories_not_assoc;
}


?>