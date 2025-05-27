-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 27 mai 2025 à 11:08
-- Version du serveur : 8.4.3
-- Version de PHP : 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `clusterproject`
--

-- --------------------------------------------------------

--
-- Structure de la table `answer_student`
--

CREATE TABLE `answer_student` (
  `id_answer` int NOT NULL,
  `id_user` int NOT NULL,
  `id_demand` int NOT NULL,
  `ignore_student` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demand`
--

CREATE TABLE `demand` (
  `id_demand` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_finish` datetime DEFAULT NULL,
  `ispublic` tinyint(1) DEFAULT '0',
  `number` int DEFAULT NULL,
  `group_size` int DEFAULT NULL,
  `group_number` int DEFAULT NULL,
  `istreated` tinyint(1) DEFAULT '0',
  `repartition_score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `group`
--

CREATE TABLE `group` (
  `id_group` int NOT NULL,
  `id_demand` int NOT NULL,
  `group_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `group_user`
--

CREATE TABLE `group_user` (
  `id_group_user` int NOT NULL,
  `id_demand` int NOT NULL,
  `id_user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prénom` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `classe` varchar(50) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_answer`
--

CREATE TABLE `user_answer` (
  `id_user_answer` int NOT NULL,
  `id_user` int NOT NULL,
  `id_user2` int NOT NULL,
  `id_answer` int NOT NULL,
  `Affinity` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `answer_student`
--
ALTER TABLE `answer_student`
  ADD PRIMARY KEY (`id_answer`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_demand` (`id_demand`);

--
-- Index pour la table `demand`
--
ALTER TABLE `demand`
  ADD PRIMARY KEY (`id_demand`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `group`
--
ALTER TABLE `group`
  ADD PRIMARY KEY (`id_group`),
  ADD KEY `id_demand` (`id_demand`);

--
-- Index pour la table `group_user`
--
ALTER TABLE `group_user`
  ADD PRIMARY KEY (`id_group_user`),
  ADD KEY `id_demand` (`id_demand`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_answer`
--
ALTER TABLE `user_answer`
  ADD PRIMARY KEY (`id_user_answer`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_user2` (`id_user2`),
  ADD KEY `id_answer` (`id_answer`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `answer_student`
--
ALTER TABLE `answer_student`
  MODIFY `id_answer` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demand`
--
ALTER TABLE `demand`
  MODIFY `id_demand` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `group`
--
ALTER TABLE `group`
  MODIFY `id_group` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `group_user`
--
ALTER TABLE `group_user`
  MODIFY `id_group_user` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_answer`
--
ALTER TABLE `user_answer`
  MODIFY `id_user_answer` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `answer_student`
--
ALTER TABLE `answer_student`
  ADD CONSTRAINT `answer_student_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `answer_student_ibfk_2` FOREIGN KEY (`id_demand`) REFERENCES `demand` (`id_demand`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demand`
--
ALTER TABLE `demand`
  ADD CONSTRAINT `demand_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `group`
--
ALTER TABLE `group`
  ADD CONSTRAINT `group_ibfk_1` FOREIGN KEY (`id_demand`) REFERENCES `demand` (`id_demand`) ON DELETE CASCADE;

--
-- Contraintes pour la table `group_user`
--
ALTER TABLE `group_user`
  ADD CONSTRAINT `group_user_ibfk_1` FOREIGN KEY (`id_demand`) REFERENCES `demand` (`id_demand`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_user_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_answer`
--
ALTER TABLE `user_answer`
  ADD CONSTRAINT `user_answer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_answer_ibfk_2` FOREIGN KEY (`id_user2`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_answer_ibfk_3` FOREIGN KEY (`id_answer`) REFERENCES `answer_student` (`id_answer`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
