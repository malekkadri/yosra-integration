-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 12 déc. 2025 à 14:09
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `safespace`
--
CREATE DATABASE IF NOT EXISTS `safespace` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `safespace`;

-- --------------------------------------------------------

--
-- Structure de la table `biometric_credentials`
--

CREATE TABLE `biometric_credentials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `credential_id` varchar(255) NOT NULL,
  `public_key` text NOT NULL,
  `counter` int(11) DEFAULT 0,
  `device_name` varchar(100) DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `face_auth`
--

CREATE TABLE `face_auth` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `face_data` text NOT NULL,
  `registered_at` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fingerprint_logs`
--

CREATE TABLE `fingerprint_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fingerprint_logs`
--

INSERT INTO `fingerprint_logs` (`id`, `user_id`, `action`, `success`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'login_failed', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-07 00:55:08'),
(2, NULL, 'login_failed', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-07 00:55:12'),
(3, NULL, 'login_failed', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-07 00:55:15');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'fatimaghabbara18@gmail.com', '4f29646bdd20fda17597c2e64b48a93e51fa1ca6591af6d30ef72a5f25887d2f', '2025-12-07 00:30:23', '2025-12-06 22:30:23'),
(2, 'fatimaghabbara18@gmail.com', 'fc5464eada12f5b3dc47d8cd1cd58b3a6c0a20bebfa44a5e3e056d32fef52bb2', '2025-12-07 00:30:25', '2025-12-06 22:30:25'),
(3, 'fatimaghabbara18@gmail.com', '8ab22acf7a89433740e6782b923bba02265d8b364272b04ef3ef2c4f0f3814e5', '2025-12-07 00:30:27', '2025-12-06 22:30:27'),
(4, 'fatimaghabbara18@gmail.com', '110d5b78c21d2b8ad51133438cf5eec23fd92a5da5d75f62f954110b6f58a1b5', '2025-12-07 00:30:37', '2025-12-06 22:30:37'),
(5, 'fatimaghabbara18@gmail.com', 'a8ba14d8f8b482dd11d193100f2488f0cdfd061159ffe01ee86ad8f7ae766189', '2025-12-07 00:32:39', '2025-12-06 22:32:39'),
(6, 'fatimaghabbara18@gmail.com', 'cd48ac39228454dec39bbe6f899fdc356cf02bc0a5fe3453b21596258f2b1bf2', '2025-12-07 00:32:43', '2025-12-06 22:32:43'),
(7, 'fatimaghabbara18@gmail.com', '001087aada9322134dd78d8128e5d9d0c1418508e2df0b54351b2126328e3b93', '2025-12-07 00:32:50', '2025-12-06 22:32:50');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','moderateur','expert','membre') DEFAULT 'membre',
  `status` enum('actif','en attente','suspendu') DEFAULT 'en attente',
  `profile_picture` varchar(255) DEFAULT 'assets/images/default-avatar.png',
  `date_naissance` date DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `faceid_enabled` tinyint(1) DEFAULT 0,
  `fingerprint_enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `password`, `role`, `status`, `profile_picture`, `date_naissance`, `telephone`, `adresse`, `bio`, `specialite`, `created_at`, `updated_at`, `faceid_enabled`, `fingerprint_enabled`) VALUES
(1, 'Admin', 'admin@safespace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif', 'profile_1_1765133603.jpg', '0000-00-00', '', '', '', '', '2025-12-06 19:26:21', '2025-12-07 19:53:23', 0, 0),
(2, 'kylie jenner', 'kylie77@gmail.com', '$2y$10$AoB4GpK8Vzc.w8gEQiQdp.owDKtLtnP9aYSDCymzVUAjMe6hR7rG2', 'membre', 'actif', 'profile_2_1765052057.jpg', '2004-07-30', '216 8858474', 'GGG.IIOO', '', '', '2025-12-06 20:10:16', '2025-12-06 21:14:17', 0, 0),
(4, 'fatima ghabbara', 'fatimaghabbara@gmail.com', '$2y$10$GEiVF2IFEdK36qR3WOnms.Rrq3GEl766hZBz3C5NXcYw71Hm5t7tq', '', 'actif', 'profile_4_1765055005.jpg', '2025-12-11', '+216 22204097', ' soghra,ariana', '', '', '2025-12-06 22:01:25', '2025-12-06 22:03:25', 0, 0),
(5, 'fatyy gh', 'fatimaghabbara18@gmail.com', '$2y$10$UopozBaxk7fs5CSlpO3VHOIB3ue6GZv3n0PAHOnYcTP/PygQBiIvu', 'membre', 'actif', 'profile_5_1765060172.jpg', '0000-00-00', '', '', '', '', '2025-12-06 23:28:27', '2025-12-08 14:12:40', 0, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `biometric_credentials`
--
ALTER TABLE `biometric_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_credential` (`credential_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `face_auth`
--
ALTER TABLE `face_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Index pour la table `fingerprint_logs`
--
ALTER TABLE `fingerprint_logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `biometric_credentials`
--
ALTER TABLE `biometric_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `face_auth`
--
ALTER TABLE `face_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fingerprint_logs`
--
ALTER TABLE `fingerprint_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `biometric_credentials`
--
ALTER TABLE `biometric_credentials`
  ADD CONSTRAINT `biometric_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `face_auth`
--
ALTER TABLE `face_auth`
  ADD CONSTRAINT `face_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
