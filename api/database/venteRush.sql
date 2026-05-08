-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: VenteRush
-- ------------------------------------------------------
-- Server version	5.5.5-10.11.13-MariaDB-0ubuntu0.24.04.1
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!50503 SET NAMES utf8mb4 */;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;

/*!40103 SET TIME_ZONE='+00:00' */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `evenement`
--
DROP DATABASE IF EXISTS `VenteRush`;

CREATE DATABASE `VenteRush`;

use VenteRush;

DROP TABLE IF EXISTS `evenement`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!50503 SET character_set_client = utf8mb4 */;

CREATE TABLE
  `evenement` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `nom` varchar(100) NOT NULL,
    `date` date NOT NULL,
    `description` varchar(255) NOT NULL,
    `nbMaxUtilisateurs` int (11) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evenement`
--
LOCK TABLES `evenement` WRITE;

/*!40000 ALTER TABLE `evenement` DISABLE KEYS */;

/*!40000 ALTER TABLE `evenement` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `evenement_utilisateurs`
--
DROP TABLE IF EXISTS `evenement_utilisateurs`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!50503 SET character_set_client = utf8mb4 */;

CREATE TABLE
  `evenement_utilisateurs` (
    `utilisateurs_id` int (11) NOT NULL,
    `evenements_id` int (11) NOT NULL,
    PRIMARY KEY (`utilisateurs_id`, `evenements_id`),
    KEY `fk_evenement` (`evenements_id`),
    CONSTRAINT `fk_evenement` FOREIGN KEY (`evenements_id`) REFERENCES `evenement` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_utilisateur` FOREIGN KEY (`utilisateurs_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evenement_utilisateurs`
--
LOCK TABLES `evenement_utilisateurs` WRITE;

/*!40000 ALTER TABLE `evenement_utilisateurs` DISABLE KEYS */;

/*!40000 ALTER TABLE `evenement_utilisateurs` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--
DROP TABLE IF EXISTS `utilisateurs`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!50503 SET character_set_client = utf8mb4 */;

CREATE TABLE
  `utilisateurs` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `nom` varchar(100) NOT NULL,
    `prenom` varchar(100) NOT NULL,
    `email` varchar(150) NOT NULL,
    `mdp` varchar(255) NOT NULL,
    `token` varchar(255),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--
LOCK TABLES `utilisateurs` WRITE;

/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;

/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Dumping routines for database 'VenteRush'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-27 11:25:59