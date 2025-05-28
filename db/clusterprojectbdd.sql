-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 28, 2025 at 07:17 AM
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
  `ignore_student` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `answer_student`
--

INSERT INTO `answer_student` (`id_answer`, `id_user`, `id_demand`, `ignore_student`) VALUES
(1, 1, 1, 0),
(2, 2, 1, 0),
(3, 3, 1, 0),
(4, 4, 1, 0),
(5, 5, 1, 0),
(6, 6, 1, 0),
(7, 7, 1, 0),
(8, 8, 1, 0),
(9, 9, 1, 0),
(10, 10, 1, 0),
(11, 11, 1, 0),
(12, 12, 1, 0),
(13, 13, 1, 0),
(14, 14, 1, 0),
(15, 15, 1, 0),
(16, 16, 1, 0),
(17, 17, 1, 0),
(18, 18, 1, 0),
(19, 19, 1, 0),
(20, 20, 1, 0),
(21, 21, 1, 0),
(22, 22, 1, 0),
(23, 23, 1, 0),
(24, 24, 1, 0),
(25, 25, 1, 0),
(26, 26, 1, 0),
(27, 27, 1, 0);

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
  `repartition_score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `demand`
--

INSERT INTO `demand` (`id_demand`, `id_user`, `date_start`, `date_finish`, `ispublic`, `group_size`, `vote_size`, `istreated`, `repartition_score`) VALUES
(1, 21, '2025-05-27 10:00:00', '2025-05-28 17:00:00', 0, 3, 5, 1, 100);

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
(12, 1, '4');

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
(36, 12, 12);

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
(1, 'AKIL', 'Wael', 'password123', 'sdn', 'student', 'wael.akil@lacatholille.fr'),
(2, 'ALI-LIGALI', 'Mohammed Amir Kolawolé', 'password123', 'sdn', 'student', 'mohammed.ali-ligali@lacatholille.fr'),
(3, 'BCHOUTY', 'Léa', 'password123', 'sdn', 'student', 'lea.bchouty@lacatholille.fr'),
(4, 'BENMAMMAR', 'Thanina', 'password123', 'sdn', 'student', 'thanina.benmammar@lacatholille.fr'),
(5, 'DEJONGE', 'Satya', 'password123', 'sdn', 'student', 'satya.dejonge@lacatholille.fr'),
(6, 'DELEBECQUE', 'Jade', 'password123', 'sdn', 'student', 'jade.delebecque@lacatholille.fr'),
(7, 'GHOUL', 'Massinissa', 'password123', 'sdn', 'student', 'massinissa.ghoul@lacatholille.fr'),
(8, 'GIAMI', 'Candice', 'password123', 'sdn', 'student', 'candice.giami@lacatholille.fr'),
(9, 'GUILLEMET', 'Steven', 'password123', 'sdn', 'student', 'steven.guillemet@lacatholille.fr'),
(10, 'HAIFA', 'Joe', 'password123', 'sdn', 'student', 'joe.haifa@lacatholille.fr'),
(11, 'HEBBINCKUYS', 'Hugo', 'password123', 'sdn', 'student', 'hugo.hebbinckuys@lacatholille.fr'),
(12, 'HUGUET', 'Tom', 'password123', 'sdn', 'student', 'tom.huguet@lacatholille.fr'),
(13, 'IACOPINO', 'Charles', 'password123', 'sdn', 'student', 'charles.iacopino@lacatholille.fr'),
(14, 'KILITO', 'Yazid', 'password123', 'sdn', 'student', 'yazid.kilito@lacatholille.fr'),
(15, 'LANCEA', 'Mathéo', 'password123', 'sdn', 'student', 'matheo.lancea@lacatholille.fr'),
(16, 'LAURENCY', 'Yuna', 'password123', 'sdn', 'student', 'yuna.laurency@lacatholille.fr'),
(17, 'LEKHAL', 'Samy', 'password123', 'sdn', 'student', 'samy.lekhal@lacatholille.fr'),
(18, 'MARTIN', 'Maxence', 'password123', 'sdn', 'student', 'maxence.martin@lacatholille.fr'),
(19, 'MAZOUZ', 'Aymène', 'password123', 'sdn', 'student', 'aymene.mazouz@lacatholille.fr'),
(20, 'MEZOUÂR', 'Chahinez', 'password123', 'sdn', 'student', 'chahinez.mezouar@lacatholille.fr'),
(21, 'MOUSSAOUI', 'Yasmina', 'password123', 'sdn', 'student', 'yasmina.moussaoui@lacatholille.fr'),
(22, 'OKINDA', 'Carlos', 'password123', 'sdn', 'student', 'carlos.okinda@lacatholille.fr'),
(23, 'POLLOCK', 'Toby', 'password123', 'sdn', 'student', 'toby.pollock@lacatholille.fr'),
(24, 'SEBAOUI', 'Reda', 'password123', 'sdn', 'student', 'reda.sebaoui@lacatholille.fr'),
(25, 'STIEVENARD', 'Emma', 'password123', 'sdn', 'student', 'emma.stievenard@lacatholille.fr'),
(26, 'SZEWCZYK', 'Clément', 'password123', 'sdn', 'student', 'clement.szewczyk@lacatholille.fr'),
(27, 'VANDAMME', 'Nathan', 'password123', 'sdn', 'student', 'nathan.vandamme@lacatholille.fr'),
(28, 'MOUSIN', 'Lucien', 'FF14', NULL, 'teacher', 'lucien.mousin@lacatholille.fr');

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
(1, 1, 2, 1, 1),
(2, 1, 5, 1, 2),
(3, 1, 8, 1, 3),
(4, 1, 12, 1, 4),
(5, 1, 15, 1, 5),
(6, 2, 1, 2, 1),
(7, 2, 7, 2, 2),
(8, 2, 14, 2, 3),
(9, 2, 19, 2, 4),
(10, 2, 22, 2, 5),
(11, 3, 6, 3, 1),
(12, 3, 8, 3, 2),
(13, 3, 16, 3, 3),
(14, 3, 21, 3, 4),
(15, 3, 25, 3, 5),
(16, 4, 3, 4, 1),
(17, 4, 9, 4, 2),
(18, 4, 13, 4, 3),
(19, 4, 17, 4, 4),
(20, 4, 24, 4, 5),
(21, 5, 1, 5, 1),
(22, 5, 10, 5, 2),
(23, 5, 18, 5, 3),
(24, 5, 23, 5, 4),
(25, 5, 27, 5, 5),
(26, 6, 3, 6, 1),
(27, 6, 11, 6, 2),
(28, 6, 16, 6, 3),
(29, 6, 20, 6, 4),
(30, 6, 25, 6, 5),
(31, 7, 2, 7, 1),
(32, 7, 14, 7, 2),
(33, 7, 19, 7, 3),
(34, 7, 22, 7, 4),
(35, 7, 24, 7, 5),
(36, 8, 3, 8, 1),
(37, 8, 6, 8, 2),
(38, 8, 16, 8, 3),
(39, 8, 25, 8, 4),
(40, 8, 21, 8, 5),
(41, 9, 10, 9, 1),
(42, 9, 11, 9, 2),
(43, 9, 12, 9, 3),
(44, 9, 15, 9, 4),
(45, 9, 18, 9, 5),
(46, 10, 5, 10, 1),
(47, 10, 9, 10, 2),
(48, 10, 17, 10, 3),
(49, 10, 23, 10, 4),
(50, 10, 27, 10, 5),
(51, 11, 9, 11, 1),
(52, 11, 12, 11, 2),
(53, 11, 13, 11, 3),
(54, 11, 15, 11, 4),
(55, 11, 18, 11, 5),
(56, 12, 1, 12, 1),
(57, 12, 9, 12, 2),
(58, 12, 11, 12, 3),
(59, 12, 13, 12, 4),
(60, 12, 26, 12, 5),
(61, 13, 4, 13, 1),
(62, 13, 11, 13, 2),
(63, 13, 12, 13, 3),
(64, 13, 17, 13, 4),
(65, 13, 26, 13, 5),
(66, 14, 2, 14, 1),
(67, 14, 7, 14, 2),
(68, 14, 19, 14, 3),
(69, 14, 22, 14, 4),
(70, 14, 24, 14, 5),
(71, 15, 1, 15, 1),
(72, 15, 9, 15, 2),
(73, 15, 11, 15, 3),
(74, 15, 12, 15, 4),
(75, 15, 18, 15, 5),
(76, 16, 3, 16, 1),
(77, 16, 6, 16, 2),
(78, 16, 8, 16, 3),
(79, 16, 21, 16, 4),
(80, 16, 25, 16, 5),
(81, 17, 4, 17, 1),
(82, 17, 10, 17, 2),
(83, 17, 13, 17, 3),
(84, 17, 24, 17, 4),
(85, 17, 26, 17, 5),
(86, 18, 5, 18, 1),
(87, 18, 9, 18, 2),
(88, 18, 11, 18, 3),
(89, 18, 15, 18, 4),
(90, 18, 27, 18, 5),
(91, 19, 2, 19, 1),
(92, 19, 7, 19, 2),
(93, 19, 14, 19, 3),
(94, 19, 22, 19, 4),
(95, 19, 24, 19, 5),
(96, 20, 3, 20, 1),
(97, 20, 6, 20, 2),
(98, 20, 21, 20, 3),
(99, 20, 25, 20, 4),
(100, 20, 16, 20, 5),
(101, 21, 3, 21, 1),
(102, 21, 8, 21, 2),
(103, 21, 16, 21, 3),
(104, 21, 20, 21, 4),
(105, 21, 25, 21, 5),
(106, 22, 2, 22, 1),
(107, 22, 7, 22, 2),
(108, 22, 14, 22, 3),
(109, 22, 19, 22, 4),
(110, 22, 24, 22, 5),
(111, 23, 5, 23, 1),
(112, 23, 10, 23, 2),
(113, 23, 18, 23, 3),
(114, 23, 26, 23, 4),
(115, 23, 27, 23, 5),
(116, 24, 2, 24, 1),
(117, 24, 4, 24, 2),
(118, 24, 7, 24, 3),
(119, 24, 17, 24, 4),
(120, 24, 22, 24, 5),
(121, 25, 3, 25, 1),
(122, 25, 6, 25, 2),
(123, 25, 8, 25, 3),
(124, 25, 16, 25, 4),
(125, 25, 21, 25, 5),
(126, 26, 12, 26, 1),
(127, 26, 13, 26, 2),
(128, 26, 17, 26, 3),
(129, 26, 23, 26, 4),
(130, 26, 27, 26, 5),
(131, 27, 5, 27, 1),
(132, 27, 10, 27, 2),
(133, 27, 18, 27, 3),
(134, 27, 23, 27, 4),
(135, 27, 26, 27, 5);

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
  MODIFY `id_answer` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `demand`
--
ALTER TABLE `demand`
  MODIFY `id_demand` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
  MODIFY `id_group` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `group_user`
--
ALTER TABLE `group_user`
  MODIFY `id_group_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `user_answer`
--
ALTER TABLE `user_answer`
  MODIFY `id_user_answer` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

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
