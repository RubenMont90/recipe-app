<?php

/* Funcionalidades de Pesquisa e Listagens */
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
    
    // CÓDIGO REMOVIDO - um Ingrediente pode existir mesmo não ligado a uma receita.
    // procurar nos <ingredientes_receitas> por ingrediente da receita e eliminar de <ingredientes>
    // foreach($ingredientes as $ingrediente){
    //     $query = "SELECT * FROM ingredientes_receitas WHERE nome_ingrediente = '$ingrediente';";
    //     $result = mysqli_query($conn, $query);
    //     if(mysqli_num_rows($result) == 0){
    //         // eliminar o ingrediente em questão dos ingredientes_receitas (não está presente em mais receitas)
    //         $query = "DELETE FROM ingredientes WHERE nome = '$ingrediente';";
    //         echo mysqli_query($conn, $query)
    //         ? "Ingrediente (Nome:<$ingrediente>) eliminado de <ingredientes> com sucesso.\n" 
    //         : "Erro a eliminar o ingrediente de <ingredientes>.\n";
    //     }
    // }
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

// (DEPRECATED) Menu com todas as operações
function menu($conn, $phase = 0){
    //do{
    printSelectiveMenu($phase);

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
        case 10:
            listIngredients($conn);
            break;
        case 11:
            createIngredient($conn);
            break;
        case 12:
            createIngredientInRecipe($conn);
            break;
        case 13:
            updateIngredientInRecipe($conn);
            break;
        case 14:
            deleteIngredientFromRecipe($conn);
            break;
        case 15:
            listRecipeWithIngredients($conn);
            break;
            
        case 16:
            listRecipesInCategoryByName($conn);
            break;
        case 17:
            listAllRecipesWithIngredient($conn);
            break;
        case 18:
            listCompleteRecipe($conn);
            break;
        case 19:
            searchRecipeByTitle($conn);
            break;

        default:
            echo "\nERRO: Opção Inválida!\n";
            break;
    }
    //}while($menu_opt != 0);
    return $menu_opt;
}

// Menu com todas as operações
function tempMenu($conn, $phase = 0){
    do{
        printSelectiveMenu($phase);
        
        $menu_opt = readline("\n> ");
        $error = false;

        if($phase == 0){
            switch($menu_opt){
                case 0: break;
                /*******************| PHASE 4 |*******************/
                case 1: createRecipe($conn); break;
                case 2: listRecipes($conn); break;
                case 3: updateRecipe($conn); break;
                case 4: deleteRecipe($conn); break;
                /*******************| PHASE 5 |*******************/
                case 5: createCategory($conn); break;
                case 6: listCategories($conn); break;
                case 7: createCategoryRecipes($conn); break;
                case 8: deleteCategoryRecipes($conn); break;
                case 9: listRecipesInCategory($conn); break;
                /*******************| PHASE 6 |*******************/
                case 10: listIngredients($conn); break;
                case 11: createIngredient($conn); break;
                case 12: createIngredientInRecipe($conn); break;
                case 13: updateIngredientInRecipe($conn); break;
                case 14: deleteIngredientFromRecipe($conn); break;
                case 15: listRecipeWithIngredients($conn); break;
                /*******************| PHASE 7 |*******************/
                case 16: listRecipesInCategoryByName($conn); break;
                case 17: listAllRecipesWithIngredient($conn); break;
                case 18: listCompleteRecipe($conn); break;
                case 19: searchRecipeByTitle($conn); break;
                /*******************| Default |*******************/
                default: echo "\nERRO: Opção Inválida!\n"; $error = true; break;
            }
        } else if($phase == 4){
            switch($menu_opt){
                case 0: break;
                case 1: createRecipe($conn); break;
                case 2: listRecipes($conn); break;
                case 3: updateRecipe($conn); break;
                case 4: deleteRecipe($conn); break;
                default: echo "\nERRO: Opção Inválida!\n"; $error = true; break;
            }
        } else if($phase == 5){
            switch($menu_opt){
                case 0: break;
                case 5: createCategory($conn); break;
                case 6: listCategories($conn); break;
                case 7: createCategoryRecipes($conn); break;
                case 8: deleteCategoryRecipes($conn); break;
                case 9: listRecipesInCategory($conn); break;
                default: echo "\nERRO: Opção Inválida!\n"; $error = true; break;
            }
        } else if($phase == 6){
            switch($menu_opt){
                case 0: break;
                case 10: listIngredients($conn); break;
                case 11: createIngredient($conn); break;
                case 12: createIngredientInRecipe($conn); break;
                case 13: updateIngredientInRecipe($conn); break;
                case 14: deleteIngredientFromRecipe($conn); break;
                case 15: listRecipeWithIngredients($conn); break;
                default: echo "\nERRO: Opção Inválida!\n"; $error = true; break;
            }
        } else if($phase == 7){
            switch($menu_opt){
                case 0: break;
                case 16: listRecipesInCategoryByName($conn); break;
                case 17: listAllRecipesWithIngredient($conn); break;
                case 18: listCompleteRecipe($conn); break;
                case 19: searchRecipeByTitle($conn); break;
                default: echo "\nERRO: Opção Inválida!\n"; $error = true; break;
            }
        }
    }while($menu_opt != 0 && $error);
}

// Menu Inicial
function shorterMenu($conn){
    do{
        printStartMenu();
        $opt = readline("\n> ");
        $opt = strtolower($opt);
        switch($opt){
            case "1": tempMenu($conn); break;
            case "4": tempMenu($conn, intval($opt)); break;
            case "5": tempMenu($conn, intval($opt)); break;
            case "6": tempMenu($conn, intval($opt)); break;
            case "7": tempMenu($conn, intval($opt)); break;
            case "0": break;
            default: echo "\nERRO: Opção Inválida!\n"; break;
        }
    }while($opt != "0");
    echo "\n< Sair do Programa >\n";
}

// (DEPRECATED) Imprime Menu
function printMenu(){
    echo "\n* * * * * * * * * | Escolha uma opção | * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "*   0 => Sair do Programa\t\t\t\t*\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * | Fase  4 | * * * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "*   1 => Criar Novas Receitas\t\t\t\t*\n";
    echo "*   2 => Listar todas as Receitas\t\t\t*\n";
    echo "*   3 => Atualizar receitas existentes\t\t\t*\n";
    echo "*   4 => Apagar receitas\t\t\t\t*\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * | Fase  5 | * * * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "*   5 => Criar Categorias\t\t\t\t*\n";
    echo "*   6 => Listar Categorias\t\t\t\t*\n";
    echo "*   7 => Associar Receitas a Categorias\t\t\t*\n";
    echo "*   8 => Desassociar Receitas a Categorias\t\t*\n";
    echo "*   9 => Listar Receitas por Categoria\t\t\t*\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * | Fase  6 | * * * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "*  10 => Listar Ingredientes\t\t\t\t*\n";
    echo "*  11 => Adicionar Ingredientes\t\t\t\t*\n";
    echo "*  12 => Associar Ingredientes a uma Receita\t\t*\n";
    echo "*  13 => Atualizar Ingrediente da Receita\t\t*\n";
    echo "*  14 => Remover Ingrediente de uma Receita\t\t*\n";
    echo "*  15 => Mostrar Detalhes de uma Receita\t\t*\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * | Fase  7 | * * * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "*  16 => Listar Receitas de uma determinada Categoria\t*\n";
    echo "*  17 => Listar Receitas que contenham um Ingrediente\t*\n";
    echo "*  18 => Ver Detalhes completos de uma receita\t\t*\n";
    echo "*  19 => Pesquisar Receitas pelo título\t\t\t*\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
}

function printStartMenu(){
    echo "\n* * * * | Menu  Inicial | * * * *\n";
    echo "*\t\t\t\t*\n";
    echo "*   1 => Menu Completo\t\t*\n";
    echo "*\t\t\t\t*\n";
    echo "*   4 => Menu da Fase 4 \t*\n";
    echo "*   5 => Menu da Fase 5 \t*\n";
    echo "*   6 => Menu da Fase 6 \t*\n";
    echo "*   7 => Menu da Fase 7 \t*\n";
    echo "*\t\t\t\t*\n";
    echo "*   0 => Sair do Programa\t*\n";
    echo "*\t\t\t\t*\n";
    echo "* * * * * * * * * * * * * * * * *\n";
}

// Imprime vários Menus
function printSelectiveMenu($phase){
    echo "\n* * * * * * * * * | Menu Secundário | * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    if($phase == 0 || $phase == 4){
        echo "* * * * * * * * * * * | Fase  4 | * * * * * * * * * * * *\n";
        echo "*\t\t\t\t\t\t\t*\n";
        echo "*   1 => Criar Novas Receitas\t\t\t\t*\n";
        echo "*   2 => Listar todas as Receitas\t\t\t*\n";
        echo "*   3 => Atualizar receitas existentes\t\t\t*\n";
        echo "*   4 => Apagar receitas\t\t\t\t*\n";
        echo "*\t\t\t\t\t\t\t*\n";
    }
    if($phase == 0 || $phase == 5){
        echo "* * * * * * * * * * * | Fase  5 | * * * * * * * * * * * *\n";
        echo "*\t\t\t\t\t\t\t*\n";
        echo "*   5 => Criar Categorias\t\t\t\t*\n";
        echo "*   6 => Listar Categorias\t\t\t\t*\n";
        echo "*   7 => Associar Receitas a Categorias\t\t\t*\n";
        echo "*   8 => Desassociar Receitas a Categorias\t\t*\n";
        echo "*   9 => Listar Receitas por Categoria\t\t\t*\n";
        echo "*\t\t\t\t\t\t\t*\n";
    }
    if($phase == 0 || $phase == 6){
        echo "* * * * * * * * * * * | Fase  6 | * * * * * * * * * * * *\n";
        echo "*\t\t\t\t\t\t\t*\n";
        echo "*  10 => Listar Ingredientes\t\t\t\t*\n";
        echo "*  11 => Adicionar Ingredientes\t\t\t\t*\n";
        echo "*  12 => Associar Ingredientes a uma Receita\t\t*\n";
        echo "*  13 => Atualizar Ingrediente da Receita\t\t*\n";
        echo "*  14 => Remover Ingrediente de uma Receita\t\t*\n";
        echo "*  15 => Mostrar Detalhes de uma Receita\t\t*\n";
        echo "*\t\t\t\t\t\t\t*\n";
    }
    if($phase == 0 || $phase == 7){
        echo "* * * * * * * * * * * | Fase  7 | * * * * * * * * * * * *\n";
        echo "*\t\t\t\t\t\t\t*\n";
        echo "*  16 => Listar Receitas de uma determinada Categoria\t*\n";
        echo "*  17 => Listar Receitas que contenham um Ingrediente\t*\n";
        echo "*  18 => Ver Detalhes completos de uma receita\t\t*\n";
        echo "*  19 => Pesquisar Receitas pelo título\t\t\t*\n";
        echo "*\t\t\t\t\t\t\t*\n";
    }
    echo "* * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "*   0 => Voltar ao Menu Inicial\t\t\t\t*\n";
    echo "*\t\t\t\t\t\t\t*\n";
    echo "* * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
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

// ****************************************| FASE 6 |****************************************

// FASE 6 - 6.1.1 - Listar todos os Ingredientes
function listIngredients($conn){
    $ingredient_names = [];
    $query = "SELECT nome FROM ingredientes;";

    $resultado = mysqli_query($conn, $query);

    echo "\nIngredientes:\n";
    while($linha = mysqli_fetch_assoc($resultado)){
        echo "  " . $linha["nome"] . "\n";
        $ingredient_names[] = $linha["nome"];
    }
    return $ingredient_names;
}

// FASE 6 - 6.1.2 - Adicionar ingredientes
// Cria novo ingrediente (campos: nome)
function createIngredient($conn){
    $nome = readline("Nome do Ingrediente: ");

    $query = "INSERT INTO ingredientes (nome) VALUES ('$nome');";

    // listar ingredientes
    $ingredients = listIngredients($conn);

    if(in_array($nome, $ingredients)){
        echo "Erro: <$nome> já existe na Base de dados.\n";
        return;
    }

    $resultado = mysqli_query($conn, $query);
    if(mysqli_num_rows($resultado) == 0){
        echo "Erro: Ingrediente não adicionado.\n";
        return;
    }
}

// FASE 6 - 6.2 - Associar ingredientes a receitas com quantidade e unidade
function createIngredientInRecipe($conn){
    //listar receitas
    $recipe_ids = listRecipes($conn, false);

    //ler input id receita
    $id_recipe = readline("Id da Receita: ");

    // verificar se id de receita existe
    if(!in_array($id_recipe, $recipe_ids)){
        echo "Erro: Id<$id_recipe> não existe.\n";
        return;
    }

    //listar ingredientes
    $ingredients = listIngredients($conn);

    //ler input nome ingrediente
    $name = readline("Nome do Ingrediente: ");

    // verificar se o ingrediente existe
    if(!in_array($name, $ingredients)){
        echo "Erro: Ingrediente: <$name> não existe na Base de dados.\n";
        return;
    }

    //ler input quantidade
    $quantity = readline("Quantidade(numero): ");
    if(!is_numeric($quantity)){
        echo "Erro: Neste campo apenas pode ser introduzidos números!\n";
        return;
    }

    //ler input unidade
    $measure = readline("Unidade(texto): ");

    $query = "INSERT INTO ingredientes_receitas (nome_ingrediente, id_receita, quantidade, unidade) 
              VALUES ('$name', $id_recipe, $quantity, '$measure');";
    
    echo mysqli_query($conn, $query)
    ? "Ingrediente '$name' adicionado com sucesso à receita de Id<$id_recipe>)\n" 
    : "Erro a adicionar Receita.\n";
}

// FASE 6 - 6.3 - Atualizar quantidade/unidade de ingredientes de uma receita
function updateIngredientInRecipe($conn){
    $recipe_ids = listRecipes($conn, false);

    $id_recipe = readline("Id da Receita a alterar: ");

    if(!in_array($id_recipe, $recipe_ids)){
        echo "Erro: Id<$id_recipe> não existe na Base de dados.\n";
        return;
    }
    
    $ingrs_recipe = listIngredientsInRecipe($conn, $id_recipe);
    
    $id_ingr_recipe = readline("Id do ingrediente a alterar: ");

    if(!in_array($id_ingr_recipe, $ingrs_recipe)){
        echo "Erro: Ingrediente de id<$id_ingr_recipe> não existe na Base de dados, ou não pertence à receita selecionada.\n";
        return;
    }

    // pedir nova quantidade / unidade
    $quantity = readline("Quantidade(numero): ");
    if(!is_numeric($quantity)){
        echo "Erro: Neste campo apenas pode ser introduzidos números!\n";
        return;
    }

    //ler input unidade
    $measure = readline("Unidade(texto): ");

    $query = "UPDATE ingredientes_receitas 
    SET quantidade = $quantity, unidade = '$measure'
    WHERE id = $id_ingr_recipe;";

    echo mysqli_query($conn, $query)
    ? "Ingrediente (ID:<$id_ingr_recipe>) atualizado com sucesso.\n" 
    : "Erro a atualizar a informação do ingrediente da receita.\n";
}

// EXTRA - retorna ids de ingredientes_receitas
function listIngredientsInRecipe($conn, $id_recipe){

    $ingrs_id_in_recipe = [];

    $query = "SELECT nome, ingredientes_receitas.id, id_receita, nome_ingrediente, quantidade, unidade FROM receitas 
    INNER JOIN ingredientes_receitas 
    ON receitas.id = ingredientes_receitas.id_receita 
    WHERE id_receita = $id_recipe;";

    $resultado = mysqli_query($conn, $query);

    $first_iteration = true;
    while($linha = mysqli_fetch_assoc($resultado)){
        if($first_iteration){
            $first_iteration = false;
            echo "\nIngredientes de '".$linha["nome"]."':\n";
        }
        echo "| Id: " . ($linha["id"] < 10 ? "0" : "") . $linha["id"] . " | " . $linha["nome_ingrediente"];
        echo " | Quantidade: " . $linha["quantidade"] . " | Unidade: " . $linha["unidade"]. "\n";
        $ingrs_id_in_recipe[] = $linha["id"];
    }

    return $ingrs_id_in_recipe;
}

// FASE 6 - 6.4 - Remover ingredientes de uma receita
function deleteIngredientFromRecipe($conn){
    
    $recipe_ids = listRecipes($conn, false);

    if(count($recipe_ids) > 0){
        echo "Não existem receitas na Base de dados.\n";
        return;
    }

    $id_recipe = readline("Id da Receita para remover ingrediente: ");

    if(!in_array($id_recipe, $recipe_ids)){
        echo "Erro: Id<$id_recipe> não existe na Base de dados.\n";
        return;
    }
    
    $ingrs_recipe = listIngredientsInRecipe($conn, $id_recipe);
    
    $id_ingr_recipe = readline("Id do ingrediente a remover: ");

    if(!in_array($id_ingr_recipe, $ingrs_recipe)){
        echo "Erro: Ingrediente de id<$id_ingr_recipe> não existe na Base de dados, ou não pertence à receita selecionada.\n";
        return;
    }

    $query = "DELETE FROM ingredientes_receitas WHERE id = $id_ingr_recipe;";
    echo mysqli_query($conn, $query)
    ? "Ingrediente (Id:<$id_ingr_recipe>) eliminado da receita (Id:<$id_recipe>) com sucesso.\n" 
    : "Erro a eliminar ingrediente associado à receita.\n";
}

// FASE 6 - 6.5 - Mostrar os detalhes completos de uma receita (incluindo ingredientes e quantidades)
function listRecipeWithIngredients($conn){

    $recipe_ids = listRecipes($conn, false);

    $id_recipe = readline("Id da Receita: ");

    if(!in_array($id_recipe, $recipe_ids)){
        echo "Erro: Id<$id_recipe> não existe na Base de dados.\n";
        return;
    }

    $query = "SELECT receitas.id as id_receita, nome, descricao, tempo_confecao, doses, nome_ingrediente, quantidade, unidade FROM receitas 
    INNER JOIN ingredientes_receitas 
    ON receitas.id = ingredientes_receitas.id_receita 
    WHERE receitas.id = $id_recipe;";

    $resultado = mysqli_query($conn, $query);

    $first = true;
    while($linha = mysqli_fetch_assoc($resultado)){
        echo $first ? "| Nome: '" . $linha["nome"] . "' | " : "";
        echo $first ? "Tempo: " . $linha["tempo_confecao"] . " min | " : "";
        echo $first ? $linha["doses"] . " doses |\n" : "";
        if($first){
            $description_lines = explode("\n", $linha["descricao"]);
            echo "| Descrição |\n";
            foreach($description_lines as $desc_line)
                echo "| $desc_line\n";
        }
        echo $first ? "| Ingredientes |\n" : "";
        echo "| " . $linha["nome_ingrediente"] . " | Quantidade: " . ($linha["quantidade"] == 0 ? "": $linha["quantidade"] . " ")  . $linha["unidade"]. " |\n";
        $first = false;
    }
    return $recipe_ids;
}

// ****************************************| FASE 7 |****************************************

// FASE 7 - 7.1 - Listar todas as receitas de uma determinada categoria
// Dado um nome ou ID de categoria, listar todas as receitas associadas
//// O MESMO QUE 5.3 só que dá por pedir por nome?
function listRecipesInCategoryByName($conn){
    // echo "not implemented";
    // return;

    $categories = listCategories($conn);

    $category_input = readline("Id/nome da categoria para listar receitas: ");

    if(is_numeric($category_input)){
        $id_category = $category_input;        
        if(!in_array($id_category, $categories)){
            echo "Erro: Categoria inexistente.\n";
            return;
        }
    }
    else{
        $query = "SELECT id FROM categorias WHERE nome = '$category_input';";
        $resultado = mysqli_query($conn, $query);
        if(mysqli_num_rows($resultado) == 0){
            echo "Erro: Categoria inexistente.\n";
            return;
        }
        $info = mysqli_fetch_assoc($resultado);
        $id_category = $info["id"];
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


// FASE 7 - 7.2 - Listar todas as receitas que contenham um determinado ingrediente
function listAllRecipesWithIngredient($conn){
    echo "not implemented";
    return;
    // pedir id de ingrediente
    
    // query

}

// FASE 7 - 7.3 - Ver detalhes completos de uma receita 
// Dado um ID ou nome da receita, apresentar: (Título | Etapas de preparação (descrição) | Ingredientes, quantidades e unidades)
//// O MESMO QUE 6.5 só que dá por pedir por nome?
function listCompleteRecipe($conn){
    echo "not implemented";
    return;
    
}


// FASE 7 - 7.4 - Pesquisar receitas por parte do título
function searchRecipeByTitle($conn){
    echo "not implemented";
    return;
    
    // LIKE query
}


?>