-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 28, 2025 at 01:54 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clusterprojectbdd`
--

-- --------------------------------------------------------

--
-- Table structure for table `answer_student`
--

CREATE TABLE `answer_student` (
  `id_answer` int NOT NULL,
  `id_user` int NOT NULL,
  `id_demand` int NOT NULL,
  `ignore_student` tinyint(1) DEFAULT '0',
  `as_answer` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `answer_student`
--

INSERT INTO `answer_student` (`id_answer`, `id_user`, `id_demand`, `ignore_student`, `as_answer`) VALUES
(1, 1, 1, 0, 0),
(2, 2, 1, 0, 0),
(3, 3, 1, 0, 0),
(4, 4, 1, 0, 0),
(5, 5, 1, 0, 0),
(6, 6, 1, 0, 0),
(7, 7, 1, 0, 0),
(8, 8, 1, 0, 0),
(9, 9, 1, 0, 0),
(10, 10, 1, 0, 0),
(11, 11, 1, 0, 0),
(12, 12, 1, 0, 0),
(13, 13, 1, 0, 0),
(14, 14, 1, 0, 0),
(15, 15, 1, 0, 0),
(16, 16, 1, 0, 0),
(17, 17, 1, 0, 0),
(18, 18, 1, 0, 0),
(19, 19, 1, 0, 0),
(20, 20, 1, 0, 0),
(21, 21, 1, 0, 0),
(22, 22, 1, 0, 0),
(23, 23, 1, 0, 0),
(24, 24, 1, 0, 0),
(25, 25, 1, 0, 0),
(37, 1, 4, 0, 1),
(38, 2, 4, 0, 1),
(39, 3, 4, 0, 1),
(40, 4, 4, 0, 1),
(41, 5, 4, 0, 1),
(42, 6, 4, 0, 1),
(43, 7, 4, 0, 1),
(44, 8, 4, 0, 1),
(45, 30, 4, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `demand`
--

CREATE TABLE `demand` (
  `id_demand` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_finish` datetime DEFAULT NULL,
  `ispublic` tinyint(1) DEFAULT '0',
  `group_size` int DEFAULT NULL,
  `vote_size` int DEFAULT NULL,
  `istreated` tinyint(1) DEFAULT '0',
  `repartition_score` float DEFAULT NULL,
  `demand_name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `demand`
--

INSERT INTO `demand` (`id_demand`, `id_user`, `date_start`, `date_finish`, `ispublic`, `group_size`, `vote_size`, `istreated`, `repartition_score`, `demand_name`) VALUES
(1, 21, '2025-05-27 10:00:00', '2025-05-28 17:00:00', 0, 3, 5, 1, 0, 'finaltest'),
(4, 29, '2025-05-28 14:37:00', '2025-05-29 14:37:00', 0, 3, NULL, 0, NULL, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE `group` (
  `id_group` int NOT NULL,
  `id_demand` int NOT NULL,
  `group_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group`
--

INSERT INTO `group` (`id_group`, `id_demand`, `group_name`) VALUES
(9, 1, '1'),
(10, 1, '2'),
(11, 1, '3'),
(12, 1, '4'),
(22, 1, '0'),
(23, 1, '1'),
(24, 1, '2'),
(25, 1, '3'),
(26, 1, '4'),
(27, 1, '5'),
(28, 1, '6'),
(29, 1, '7'),
(30, 1, '8');

-- --------------------------------------------------------

--
-- Table structure for table `group_user`
--

CREATE TABLE `group_user` (
  `id_group_user` int NOT NULL,
  `id_group` int NOT NULL,
  `id_user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group_user`
--

INSERT INTO `group_user` (`id_group_user`, `id_group`, `id_user`) VALUES
(25, 9, 1),
(26, 9, 2),
(27, 9, 3),
(28, 10, 4),
(29, 10, 5),
(30, 10, 6),
(31, 11, 7),
(32, 11, 8),
(33, 11, 9),
(34, 12, 10),
(35, 12, 11),
(36, 12, 12),
(64, 22, 22),
(65, 22, 16),
(66, 22, 27),
(67, 23, 6),
(68, 23, 19),
(69, 23, 5),
(70, 24, 20),
(71, 24, 1),
(72, 24, 4),
(73, 25, 11),
(74, 25, 7),
(75, 25, 8),
(76, 26, 15),
(77, 26, 3),
(78, 26, 2),
(79, 27, 12),
(80, 27, 17),
(81, 27, 18),
(82, 28, 24),
(83, 28, 26),
(84, 28, 10),
(85, 29, 21),
(86, 29, 9),
(87, 29, 23),
(88, 30, 25),
(89, 30, 13),
(90, 30, 14);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `lastname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `firstname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) NOT NULL,
  `class` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `lastname`, `firstname`, `password`, `class`, `role`, `email`) VALUES
(1, 'AKIL', 'Wael', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'wael.akil@lacatholille.fr'),
(2, 'ALI-LIGALI', 'Mohammed Amir Kolawolé', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'mohammed.ali-ligali@lacatholille.fr'),
(3, 'BCHOUTY', 'Léa', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'lea.bchouty@lacatholille.fr'),
(4, 'BENMAMMAR', 'Thanina', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'thanina.benmammar@lacatholille.fr'),
(5, 'DEJONGE', 'Satya', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'satya.dejonge@lacatholille.fr'),
(6, 'DELEBECQUE', 'Jade', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'jade.delebecque@lacatholille.fr'),
(7, 'GHOUL', 'Massinissa', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'massinissa.ghoul@lacatholille.fr'),
(8, 'GIAMI', 'Candice', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'candice.giami@lacatholille.fr'),
(9, 'GUILLEMET', 'Steven', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'steven.guillemet@lacatholille.fr'),
(10, 'HAIFA', 'Joe', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'joe.haifa@lacatholille.fr'),
(11, 'HEBBINCKUYS', 'Hugo', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'hugo.hebbinckuys@lacatholille.fr'),
(12, 'HUGUET', 'Tom', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'tom.huguet@lacatholille.fr'),
(13, 'IACOPINO', 'Charles', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'charles.iacopino@lacatholille.fr'),
(14, 'KILITO', 'Yazid', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'yazid.kilito@lacatholille.fr'),
(15, 'LANCEA', 'Mathéo', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'matheo.lancea@lacatholille.fr'),
(16, 'LAURENCY', 'Yuna', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'yuna.laurency@lacatholille.fr'),
(17, 'LEKHAL', 'Samy', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'samy.lekhal@lacatholille.fr'),
(18, 'MARTIN', 'Maxence', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'maxence.martin@lacatholille.fr'),
(19, 'MAZOUZ', 'Aymène', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'aymene.mazouz@lacatholille.fr'),
(20, 'MEZOUÂR', 'Chahinez', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'chahinez.mezouar@lacatholille.fr'),
(21, 'MOUSSAOUI', 'Yasmina', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'yasmina.moussaoui@lacatholille.fr'),
(22, 'OKINDA', 'Carlos', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'carlos.okinda@lacatholille.fr'),
(23, 'POLLOCK', 'Toby', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'toby.pollock@lacatholille.fr'),
(24, 'SEBAOUI', 'Reda', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'reda.sebaoui@lacatholille.fr'),
(25, 'STIEVENARD', 'Emma', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'emma.stievenard@lacatholille.fr'),
(26, 'SZEWCZYK', 'Clément', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'clement.szewczyk@lacatholille.fr'),
(27, 'VANDAMME', 'Nathan', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'student', 'nathan.vandamme@lacatholille.fr'),
(28, 'MOUSIN', 'Lucien', 'FF14', NULL, 'teacher', 'lucien.mousin@lacatholille.fr'),
(29, 'sebaaoui', 'Reda', '$2y$10$2cskNdBbGxmeTCBpABo0mOW5IXijtkzcXAbfT2mewTxqlCe20cI96', 'sdn', 'teacher', 'reda@gmail.com'),
(30, 'steve', 'steve', '$2y$10$sqlF2.R3zXvSBqHQiD860OX1UXMrocKFghsxBH1KI2QRbth9ST4we', 'sdn', 'student', 'steve@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `user_answer`
--

CREATE TABLE `user_answer` (
  `id_user_answer` int NOT NULL,
  `id_user` int NOT NULL,
  `id_user2` int NOT NULL,
  `id_answer` int NOT NULL,
  `Affinity` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_answer`
--

INSERT INTO `user_answer` (`id_user_answer`, `id_user`, `id_user2`, `id_answer`, `Affinity`) VALUES
(1, 1, 2, 1, 25),
(2, 1, 5, 1, 18),
(3, 1, 8, 1, 22),
(4, 1, 12, 1, 15),
(5, 1, 15, 1, 20),
(6, 2, 1, 2, 28),
(7, 2, 7, 2, 12),
(8, 2, 14, 2, 31),
(9, 2, 19, 2, 16),
(10, 2, 22, 2, 13),
(11, 3, 6, 3, 19),
(12, 3, 8, 3, 23),
(13, 3, 16, 3, 17),
(14, 3, 21, 3, 26),
(15, 3, 25, 3, 15),
(16, 4, 3, 4, 21),
(17, 4, 9, 4, 18),
(18, 4, 13, 4, 24),
(19, 4, 17, 4, 19),
(20, 4, 24, 4, 18),
(21, 5, 1, 5, 22),
(22, 5, 10, 5, 16),
(23, 5, 18, 5, 25),
(24, 5, 23, 5, 20),
(25, 5, 27, 5, 17),
(26, 6, 3, 6, 18),
(27, 6, 11, 6, 27),
(28, 6, 16, 6, 14),
(29, 6, 20, 6, 23),
(30, 6, 25, 6, 18),
(31, 7, 2, 7, 29),
(32, 7, 14, 7, 15),
(33, 7, 19, 7, 21),
(34, 7, 22, 7, 18),
(35, 7, 24, 7, 17),
(36, 8, 3, 8, 16),
(37, 8, 6, 8, 24),
(38, 8, 16, 8, 19),
(39, 8, 25, 8, 22),
(40, 8, 21, 8, 19),
(41, 9, 10, 9, 17),
(42, 9, 11, 9, 26),
(43, 9, 12, 9, 20),
(44, 9, 15, 9, 15),
(45, 9, 18, 9, 22),
(46, 10, 5, 10, 19),
(47, 10, 9, 10, 23),
(48, 10, 17, 10, 18),
(49, 10, 23, 10, 21),
(50, 10, 27, 10, 19),
(51, 11, 9, 11, 24),
(52, 11, 12, 11, 16),
(53, 11, 13, 11, 21),
(54, 11, 15, 11, 18),
(55, 11, 18, 11, 21),
(56, 12, 1, 12, 27),
(57, 12, 9, 12, 14),
(58, 12, 11, 12, 22),
(59, 12, 13, 12, 19),
(60, 12, 26, 12, 18),
(61, 13, 4, 13, 20),
(62, 13, 11, 13, 25),
(63, 13, 12, 13, 17),
(64, 13, 17, 13, 16),
(65, 13, 26, 13, 22),
(66, 14, 2, 14, 18),
(67, 14, 7, 14, 23),
(68, 14, 19, 14, 20),
(69, 14, 22, 14, 24),
(70, 14, 24, 14, 15),
(71, 15, 1, 15, 26),
(72, 15, 9, 15, 17),
(73, 15, 11, 15, 19),
(74, 15, 12, 15, 21),
(75, 15, 18, 15, 17),
(76, 16, 3, 16, 22),
(77, 16, 6, 16, 18),
(78, 16, 8, 16, 25),
(79, 16, 21, 16, 16),
(80, 16, 25, 16, 19),
(81, 17, 4, 17, 21),
(82, 17, 10, 17, 28),
(83, 17, 13, 17, 15),
(84, 17, 24, 17, 18),
(85, 17, 26, 17, 18),
(86, 18, 5, 18, 24),
(87, 18, 9, 18, 19),
(88, 18, 11, 18, 16),
(89, 18, 15, 18, 23),
(90, 18, 27, 18, 18),
(91, 19, 2, 19, 17),
(92, 19, 7, 19, 25),
(93, 19, 14, 19, 21),
(94, 19, 22, 19, 19),
(95, 19, 24, 19, 18),
(96, 20, 3, 20, 20),
(97, 20, 6, 20, 23),
(98, 20, 21, 20, 18),
(99, 20, 25, 20, 22),
(100, 20, 16, 20, 17),
(101, 21, 3, 21, 19),
(102, 21, 8, 21, 26),
(103, 21, 16, 21, 18),
(104, 21, 20, 21, 15),
(105, 21, 25, 21, 22),
(106, 22, 2, 22, 23),
(107, 22, 7, 22, 17),
(108, 22, 14, 22, 20),
(109, 22, 19, 22, 24),
(110, 22, 24, 22, 16),
(111, 23, 5, 23, 18),
(112, 23, 10, 23, 22),
(113, 23, 18, 23, 25),
(114, 23, 26, 23, 16),
(115, 23, 27, 23, 19),
(116, 24, 2, 24, 21),
(117, 24, 4, 24, 19),
(118, 24, 7, 24, 16),
(119, 24, 17, 24, 25),
(120, 24, 22, 24, 19),
(121, 25, 3, 25, 17),
(122, 25, 6, 25, 24),
(123, 25, 8, 25, 20),
(124, 25, 16, 25, 18),
(125, 25, 21, 25, 21),
(136, 30, 1, 45, 20),
(137, 30, 2, 45, 20),
(138, 30, 3, 45, 20),
(139, 30, 4, 45, 20),
(140, 30, 5, 45, 20),
(141, 1, 2, 37, 25),
(142, 1, 3, 37, 25),
(143, 1, 4, 37, 25),
(144, 1, 5, 37, 25),
(145, 2, 1, 38, 25),
(146, 2, 3, 38, 25),
(147, 2, 4, 38, 25),
(148, 2, 5, 38, 25),
(149, 3, 1, 39, 25),
(150, 3, 2, 39, 25),
(151, 3, 4, 39, 25),
(152, 3, 5, 39, 25),
(153, 4, 1, 40, 25),
(154, 4, 2, 40, 25),
(155, 4, 3, 40, 25),
(156, 4, 5, 40, 25),
(157, 5, 1, 41, 25),
(158, 5, 2, 41, 25),
(159, 5, 3, 41, 25),
(160, 5, 4, 41, 25),
(161, 6, 1, 42, 25),
(162, 6, 2, 42, 25),
(163, 6, 3, 42, 25),
(164, 6, 4, 42, 25),
(165, 7, 1, 43, 25),
(166, 7, 2, 43, 25),
(167, 7, 3, 43, 25),
(168, 7, 4, 43, 25),
(169, 8, 1, 44, 25),
(170, 8, 2, 44, 25),
(171, 8, 3, 44, 25),
(172, 8, 4, 44, 25);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answer_student`
--
ALTER TABLE `answer_student`
  ADD PRIMARY KEY (`id_answer`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_demand` (`id_demand`);

--
-- Indexes for table `demand`
--
ALTER TABLE `demand`
  ADD PRIMARY KEY (`id_demand`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `group`
--
ALTER TABLE `group`
  ADD PRIMARY KEY (`id_group`),
  ADD KEY `id_demand` (`id_demand`);

--
-- Indexes for table `group_user`
--
ALTER TABLE `group_user`
  ADD PRIMARY KEY (`id_group_user`),
  ADD KEY `id_group` (`id_group`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_answer`
--
ALTER TABLE `user_answer`
  ADD PRIMARY KEY (`id_user_answer`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_user2` (`id_user2`),
  ADD KEY `id_answer` (`id_answer`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answer_student`
--
ALTER TABLE `answer_student`
  MODIFY `id_answer` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `demand`
--
ALTER TABLE `demand`
  MODIFY `id_demand` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
  MODIFY `id_group` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `group_user`
--
ALTER TABLE `group_user`
  MODIFY `id_group_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `user_answer`
--
ALTER TABLE `user_answer`
  MODIFY `id_user_answer` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answer_student`
--
ALTER TABLE `answer_student`
  ADD CONSTRAINT `answer_student_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `answer_student_ibfk_2` FOREIGN KEY (`id_demand`) REFERENCES `demand` (`id_demand`) ON DELETE CASCADE;

--
-- Constraints for table `demand`
--
ALTER TABLE `demand`
  ADD CONSTRAINT `demand_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `group`
--
ALTER TABLE `group`
  ADD CONSTRAINT `group_ibfk_1` FOREIGN KEY (`id_demand`) REFERENCES `demand` (`id_demand`) ON DELETE CASCADE;

--
-- Constraints for table `group_user`
--
ALTER TABLE `group_user`
  ADD CONSTRAINT `group_user_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_user_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `user_answer`
--
ALTER TABLE `user_answer`
  ADD CONSTRAINT `user_answer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_answer_ibfk_2` FOREIGN KEY (`id_user2`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_answer_ibfk_3` FOREIGN KEY (`id_answer`) REFERENCES `answer_student` (`id_answer`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
