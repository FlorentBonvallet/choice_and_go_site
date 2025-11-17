-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 17 nov. 2025 à 15:40
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
-- Base de données : `sae`
--

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL,
  `trajet_id` int NOT NULL,
  `passager_id` int NOT NULL,
  `statut_reservation` enum('en_attente','confirmée','annulée') NOT NULL,
  `date_reservation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `trajet_id`, `passager_id`, `statut_reservation`, `date_reservation`) VALUES
(1, 1, 2, 'confirmée', '2025-11-17 16:39:35'),
(2, 1, 4, 'confirmée', '2025-11-17 16:39:35'),
(3, 2, 3, 'en_attente', '2025-11-17 16:39:35'),
(4, 3, 5, 'confirmée', '2025-11-17 16:39:35');

-- --------------------------------------------------------

--
-- Structure de la table `trajets`
--

CREATE TABLE `trajets` (
  `trajet_id` int NOT NULL,
  `conducteur_id` int NOT NULL,
  `vehicule_id` int DEFAULT NULL,
  `lieu_depart` varchar(255) NOT NULL,
  `lieu_arrivee` varchar(255) NOT NULL,
  `date_heure_depart` datetime NOT NULL,
  `places_disponibles` int NOT NULL,
  `prix_par_place` decimal(10,2) NOT NULL,
  `statut_trajet` enum('ouvert','complet','annulé','terminé') NOT NULL,
  `depart_latitude` float DEFAULT NULL,
  `depart_longitude` float DEFAULT NULL,
  `arrivee_latitude` float DEFAULT NULL,
  `arrivee_longitude` float DEFAULT NULL,
  `date_creation_trajet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trajets`
--

INSERT INTO `trajets` (`trajet_id`, `conducteur_id`, `vehicule_id`, `lieu_depart`, `lieu_arrivee`, `date_heure_depart`, `places_disponibles`, `prix_par_place`, `statut_trajet`, `depart_latitude`, `depart_longitude`, `arrivee_latitude`, `arrivee_longitude`, `date_creation_trajet`) VALUES
(1, 1, 1, 'IUT Lyon', 'Grenoble Campus', '2025-03-01 08:00:00', 3, 8.00, 'ouvert', NULL, NULL, NULL, NULL, '2025-11-17 16:39:25'),
(2, 2, 2, 'IUT Lyon', 'Université Saint-Étienne', '2025-03-02 09:30:00', 2, 5.00, 'ouvert', NULL, NULL, NULL, NULL, '2025-11-17 16:39:25'),
(3, 3, 3, 'IUT Lille', 'Valenciennes', '2025-03-04 07:15:00', 4, 6.50, 'ouvert', NULL, NULL, NULL, NULL, '2025-11-17 16:39:25'),
(4, 1, 1, 'IUT Toulouse', 'Montpellier', '2025-03-05 10:45:00', 2, 9.00, 'ouvert', NULL, NULL, NULL, NULL, '2025-11-17 16:39:25');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `utilisateur_id` int NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`utilisateur_id`, `prenom`, `nom`, `email`, `mot_de_passe`, `telephone`, `date_inscription`) VALUES
(1, 'Lucas', 'Martin', 'lucas.martin@etu.com', '$2y$10$abcdefghijklmnopqrstuv', '0601020304', '2025-11-17 16:38:46'),
(2, 'Sarah', 'Dupont', 'sarah.dupont@etu.com', '$2y$10$qrstuvwxyzabcdefghijk', '0605060708', '2025-11-17 16:38:46'),
(3, 'Hugo', 'Leroy', 'hugo.leroy@etu.com', '$2y$10$lmnopqrstuvabcdefghi', '0609080706', '2025-11-17 16:38:46'),
(4, 'Emma', 'Roux', 'emma.roux@etu.com', '$2y$10$abcdefabcdefabcdefab', '0611223344', '2025-11-17 16:38:46'),
(5, 'Maxime', 'Petit', 'maxime.petit@etu.com', '$2y$10$12345123451234512345', '0699887766', '2025-11-17 16:38:46');

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

CREATE TABLE `vehicules` (
  `vehicule_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `marque` varchar(50) DEFAULT NULL,
  `modele` varchar(50) DEFAULT NULL,
  `couleur` varchar(30) DEFAULT NULL,
  `immatriculation` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vehicules`
--

INSERT INTO `vehicules` (`vehicule_id`, `utilisateur_id`, `marque`, `modele`, `couleur`, `immatriculation`) VALUES
(1, 1, 'Peugeot', '208', 'Bleu', 'AA-123-AA'),
(2, 2, 'Renault', 'Clio', 'Rouge', 'BB-456-BB'),
(3, 3, 'Citroen', 'C3', 'Noir', 'CC-789-CC');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_reservation_trajet` (`trajet_id`),
  ADD KEY `fk_reservation_passager` (`passager_id`);

--
-- Index pour la table `trajets`
--
ALTER TABLE `trajets`
  ADD PRIMARY KEY (`trajet_id`),
  ADD KEY `fk_trajet_conducteur` (`conducteur_id`),
  ADD KEY `fk_trajet_vehicule` (`vehicule_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`utilisateur_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD PRIMARY KEY (`vehicule_id`),
  ADD UNIQUE KEY `immatriculation` (`immatriculation`),
  ADD KEY `fk_vehicule_user` (`utilisateur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `trajets`
--
ALTER TABLE `trajets`
  MODIFY `trajet_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `utilisateur_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `vehicules`
--
ALTER TABLE `vehicules`
  MODIFY `vehicule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_passager` FOREIGN KEY (`passager_id`) REFERENCES `utilisateurs` (`utilisateur_id`),
  ADD CONSTRAINT `fk_reservation_trajet` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`trajet_id`);

--
-- Contraintes pour la table `trajets`
--
ALTER TABLE `trajets`
  ADD CONSTRAINT `fk_trajet_conducteur` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`utilisateur_id`),
  ADD CONSTRAINT `fk_trajet_vehicule` FOREIGN KEY (`vehicule_id`) REFERENCES `vehicules` (`vehicule_id`);

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `fk_vehicule_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
