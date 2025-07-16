-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 10:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_recipes`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Sobremesa'),
(2, 'Prato Principal'),
(3, 'Carne'),
(4, 'Salada'),
(5, 'Doces');

-- --------------------------------------------------------

--
-- Table structure for table `categorias_receitas`
--

CREATE TABLE `categorias_receitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_receita` int(11) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias_receitas`
--

INSERT INTO `categorias_receitas` (`id`, `id_receita`, `id_categoria`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 2, 1),
(4, 2, 5),
(5, 3, 5),
(6, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ingredientes`
--

CREATE TABLE `ingredientes` (
  `nome` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredientes`
--

INSERT INTO `ingredientes` (`nome`) VALUES
('Açúcar'),
('Água'),
('Alho'),
('Amêijoas'),
('Amêndoa Torrada'),
('Banana'),
('Banha de Porco'),
('Batata'),
('Caramelo Liquido'),
('Carne do Lombo de Porco'),
('Coentros'),
('Colorau'),
('Farinha de Aveia'),
('Farinha de Trigo'),
('Fermento'),
('Leite'),
('Louro'),
('Massa de Pimentão'),
('Óleo'),
('Ovos'),
('Pepitas de Chocolate'),
('Pickles'),
('Pimenta Branca'),
('Vinho Branco');

-- --------------------------------------------------------

--
-- Table structure for table `ingredientes_receitas`
--

CREATE TABLE `ingredientes_receitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome_ingrediente` varchar(40) DEFAULT NULL,
  `id_receita` int(11) DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `unidade` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredientes_receitas`
--

INSERT INTO `ingredientes_receitas` (`id`, `nome_ingrediente`, `id_receita`, `quantidade`, `unidade`) VALUES
(1, 'Carne do Lombo de Porco', 1, 1, 'kg'),
(2, 'Amêijoas', 1, 1, 'kg'),
(3, 'Alho', 1, 4, 'dentes'),
(4, 'Colorau', 1, 1, 'colher de sopa'),
(5, 'Pimenta Branca', 1, 0, 'q.b.'),
(6, 'Sal', 1, 0, 'q.b.'),
(7, 'Massa de Pimentão', 1, 2, 'colheres de sopa'),
(8, 'Louro', 1, 1, 'folha'),
(9, 'Vinho Branco', 1, 300, 'ml'),
(10, 'Azeite', 1, 250, 'ml'),
(11, 'Banha de Porco', 1, 250, 'g'),
(12, 'Batata', 1, 1, 'kg'),
(13, 'Óleo', 1, 0, 'q.b.'),
(14, 'Pickles', 1, 200, 'g'),
(15, 'Coentros', 1, 1, 'ramo'),
(16, 'Ovos', 2, 12, 'unidades'),
(17, 'Açúcar', 2, 12, 'colheres de sopa'),
(18, 'Sal', 2, 1, 'pitada'),
(19, 'Caramelo Liquido', 2, 0, 'q.b.'),
(20, 'Água', 2, 250, 'ml'),
(21, 'Açúcar', 2, 280, 'g'),
(22, 'Limão', 2, 2, 'lascas'),
(23, 'Amêndoa Torrada', 2, 0, 'q.b.'),
(24, 'Banana', 3, 2, 'unidades'),
(25, 'Farinha de Aveia', 3, 1, 'chávena'),
(26, 'Pepitas de Chocolate', 3, 2, 'colheres de sopa'),
(27, 'Farinha de Trigo', 4, 12, 'colheres de sopa'),
(28, 'Ovos', 4, 3, 'unidades'),
(29, 'Óleo', 4, 1, 'copo'),
(30, 'Sal', 4, 0, 'q.b.'),
(31, 'Fermento', 4, 1, 'colher de sobremesa'),
(32, 'Leite', 4, 2, 'copos');

-- --------------------------------------------------------

--
-- Table structure for table `receitas`
--

CREATE TABLE `receitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(40) NOT NULL,
  `descricao` varchar(2000) NOT NULL,
  `tempo_confecao` int(11) NOT NULL,
  `doses` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receitas`
--

INSERT INTO `receitas` (`id`, `nome`, `descricao`, `tempo_confecao`, `doses`) VALUES
(1, 'Carne de Porco à Alentejana', '1. Cortar a carne em cubos e colocar num recipiente.\r\n2. Adicionar o alho picado, o colorau, a pimenta, o sal e a massa de pimentão e misturar bem.\r\n3. Adicionar a folha de louro e regar com o vinho branco.\r\n4. Deixar marinar de um dia para o outro.\r\n5. Lavar bem as ameijoas e colocar num recipiente com água e sal durante uma hora para soltarem a areia.\r\n6. Descascar, lavar e cortar as batatas em cubos.\r\n7. Escorrer a carne e reservar a marinada.\r\n8. Adicionar o azeite e a banha num tacho grande e levar ao lume até ficar quente.\r\n9. Adicionar a carne escorrida e deixar fritar, mexendo de vez em quando até a carne alourar.\r\n10. Escorrer a gordura de fritar a carne mas deixar um pouco no fundo.\r\n11. Escorrer a água das ameijoas, lavar novamente, adicionar na carne e misturar um pouco.\r\n12. Adicionar a marinada da carne e deixar cozinhar até reduzir pela metade.\r\n13. Fritar a batata em óleo quente, escorrer bem e colocar numa travessa.\r\n14. Retificar temperos da carne e transferir para cima das batatas com o molho.\r\n15. Espalhar os picles picados por cima e polvilhar com os coentros picados.\r\n16. Servir de imediato!', 250, 5),
(2, 'Molotof com Doce de Ovos', '1. Forre muito bem com caramelo líquido uma forma grande antiaderente com buraco.\r\n2. Parta os ovos e separe as gemas das claras.\r\nColoque as gemas numa taça e as claras numa tigela grande ou num alguidar.\r\n3. Às claras, junte uma pitada de sal e bata muito bem em castelo.\r\nQuando as claras estiverem bem consistentes e sem parar de bater, adicione as 12 colheres de açúcar uma a uma.\r\nBata muito bem até que fique um merengue.\r\nPor fim, adicione 1 colher de sopa de caramelo e bata mais um pouco.\r\nÉ importante que as claras fiquem bem batidas.\r\nTodo o processo deve demorar aproximadamente 15 minutos.\r\n4. Na forma, espalhe colheres de claras.\r\nVá alisando e bata a forma na bancada para que fique tudo bem compacto.\r\n5. Leve ao forno pré-aquecido nos 180ºC de modo a que a forma fique bem centrada no forno.\r\nDeixe cozer durante 20 minutos.\r\nPassados os 20 minutos, abra ligeiramente a porta do forno e desligue-o.\r\nDeixe o Molotof arrefecer durante 30 minutos no forno.\r\nDepois de frio, desenforme-o.\r\n6. Entretanto, faça o doce de ovos.\r\nNum tacho leve ao lume a água, os 280g de açúcar e as casquinhas de limão.\r\nMexa e deixe ferver.\r\nQuando entrar no ponto de ebulição, deixe ferver durante 5 minutos.\r\nPassados os 5 minutos, apague o lume, retire as casquinhas de limão e deixe arrefecer a calda.\r\n7. Mexa as gemas com um garfo.\r\nPasse as gemas numa rede fina e misture-as com a calda morna.\r\nMexa muito bem com uma colher e leve novamente ao lume.\r\nDeixe cozinhar em lume moderado sem parar de mexer.\r\nLogo que as gemas engrossem e antes de começar a ferver, apague o lume e mexa mais um pouco para que as gemas não cozam.\r\nDepois do doce de ovos morno, guarde no frigorífico até que fique bem fresco.\r\n8. Na hora de servir, cubra o Molotof com o doce de ovos e decore com a amêndoa.\r\nA equipa do SaborIntenso.com deseja-lhe um bom apetite!', 80, 10),
(3, 'Cookies de Banana', '1. Amasse as bananas, adicione a aveia e o chocolate. Misture bem;\r\n2. Faça bolinhas com a massa e modele no formato de cookies;\r\n3. Coloque as bolinhas em uma forma untada e enfarinhada;\r\n4. Leve para assar em forno pré aquecido, 200º, por 15 minutos.', 35, 6),
(4, 'Torta Salgada', '1. Bata todos os ingredientes no liquidificador. \r\n2. Depois coloque a metade da massa em uma forma untada e coloque o recheio. \r\n3. Depois coloque o resto da massa. \r\n4. Leve para assar até ficar dourado. \r\n5. Recheio a gosto (ex frango, sardinha, etc).', 20, 20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categorias_receitas`
--
ALTER TABLE `categorias_receitas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`nome`);

--
-- Indexes for table `ingredientes_receitas`
--
ALTER TABLE `ingredientes_receitas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receitas`
--
ALTER TABLE `receitas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categorias_receitas`
--
ALTER TABLE `categorias_receitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ingredientes_receitas`
--
ALTER TABLE `ingredientes_receitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `receitas`
--
ALTER TABLE `receitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
