CREATE DATABASE  IF NOT EXISTS `aerolinea` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `aerolinea`;
-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: aerolinea
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aerolinea`
--

DROP TABLE IF EXISTS `aerolinea`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aerolinea` (
  `idAerolinea` int NOT NULL AUTO_INCREMENT,
  `nombreAerolinea` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `codigoIATA` varchar(5) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `codPais` varchar(3) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `logoUrl` varchar(500) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `activo` tinyint NOT NULL DEFAULT '1',
  `logoPublicId` varchar(500) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`idAerolinea`),
  UNIQUE KEY `codigoIATA_UNIQUE` (`codigoIATA`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aerolinea`
--

LOCK TABLES `aerolinea` WRITE;
/*!40000 ALTER TABLE `aerolinea` DISABLE KEYS */;
INSERT INTO `aerolinea` VALUES (1,'aeroflux','afx','asd','ARG','aeroflux@info.com',NULL,1,NULL),(2,'prueba','prb','asd','ARG','pronando@as.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788571037/aerolineas/logos/wqlocqj1glmzbrv3rka9.webp',1,NULL),(3,'Adela','ADL','aint in la','USA','prima@adelane.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788571246/aerolineas/logos/jlwfltef5dzcgrq05aco.webp',1,NULL),(4,'another','testi','aasda','BRA','asdasdasda@asda.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788571758/aerolineas/logos/duroifzbuvd8atkkqsqm.png',1,'aerolineas/logos/duroifzbuvd8atkkqsqm'),(5,'2da pru','ppllk','','BRA','asdff@llkm.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788572611/aerolineas/logos/r2gidpxg4rqyq2rs9316.png',0,'aerolineas/logos/r2gidpxg4rqyq2rs9316'),(6,'funka?','fnk','esta funcionando?','ARG','fksa@gma.com',NULL,1,NULL),(7,'asdasasdasd','asew','','BRA','asdas@dasd.a','0',1,'0'),(8,'qePAs','KPS','asd','CHL','asd@ff.ca','https://res.cloudinary.com/dqydbsuuj/image/upload/v1789428192/aerolineas/logos/hbkfrov38m3str7q54ki.webp',1,'aerolineas/logos/hbkfrov38m3str7q54ki'),(10,'nueva','nusda','','ARG','adas@as.com',NULL,1,NULL);
/*!40000 ALTER TABLE `aerolinea` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `apellido` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tipoDocumento` enum('DNI','pasaporte','lc','le') COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nroDocumento` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `telefono` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `fechaNacimiento` date NOT NULL,
  `rol` enum('cliente','ceo','admin') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'cliente',
  `activo` tinyint NOT NULL DEFAULT '0',
  `emailVerificado` tinyint NOT NULL DEFAULT '0',
  `fecha_hora_autorizacion` datetime DEFAULT NULL,
  `tokenVerificacion` varchar(64) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `idAerolinea` int DEFAULT NULL,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `uk_documento` (`tipoDocumento`,`nroDocumento`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `idUsuario_UNIQUE` (`idUsuario`),
  UNIQUE KEY `nroDocumento_UNIQUE` (`nroDocumento`),
  KEY `fk_usuario_1_idx` (`idAerolinea`),
  CONSTRAINT `fk_usuario_1` FOREIGN KEY (`idAerolinea`) REFERENCES `aerolinea` (`idAerolinea`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Mauro','Jimenez','DNI','42130241','$2y$10$qmETw0daBiM3.GnGjuSpeOE4zEE7XeksSZaFb2nNr3/pUtEycNj7q','maurojim123@gmail.com','3413417150','1999-10-15','cliente',0,0,NULL,'a41dedcba7dba463c1a06b7a9c5d011b39ca21eeefec927f1e2fe2674917b332',NULL),(6,'Kim','Petras','pasaporte','PP45332424','$2y$10$LzAeQEUY3y2FqtOG8OVMh.7MURZQGXeog9VwLZ2Lwp4olP4odSaSW','dtla@detour.com','2339233923','1992-08-08','ceo',0,0,NULL,'814c2080baeec29302f0d42466789ad4ea8890f0e6f0e4062ecce266b6914e73',2),(7,'Ejemplo','Jimenez','DNI','42130243','$2y$10$T6yOga/HlWghlG13qocc7eG15tyiPWWP/uJXOkaaXJHH5HGw0EuRG','ejejej@jojojo.com','2331233923','2008-09-09','ceo',0,0,NULL,'7c03dedfbcc64f2a7f0625264b229946271343c8752274d3628041c194a254ad',7),(9,'Adela','Prima','DNI','41776781','$2y$10$syq8os0psjpK89z9HOp5w.wWtcvr.p0Bm9bZnhJ2JDOaNEp5K/Qya','primadela@dela.com','332332334','2008-09-02','ceo',0,0,NULL,'5f33f0d7fc8564e483004ff35e75014016be585811527d44fd38e45983058d8d',6),(13,'Tessa','Testing','DNI','83743554','$2y$10$fazr.0l/Qe8RDDy/i4uIjOC9qVVc7XkNaRfZqT9oSkuWXc3udd.Bq','nosoy@real.com','43223233','1996-01-01','cliente',0,0,NULL,'c281c35e3b8269d3b4a4b900177fca2d88c4b7c92f440f15827f6bb6e5bd924d',NULL),(14,'admin','admin','DNI','99399939','$2y$10$dcW1FaEM96dcw8xzl1Awp.HTsrM8PJI3l7ZDw6DAscpKX0xDQXb/e','admin@admin.admin','54332212','2008-09-02','admin',1,1,NULL,'6dd6a9e9b6026fabb6e2e7ac195b6f045cbc330f7c05548b8859ce48d64d7df2',6),(15,'proban','ndouseu','DNI','42312984','$2y$10$Tk4rDydj/F0qg5W9O9M7yOJUlFBQvuZWIS2TC1gpdiHffABDp.7yK','test@test.test','3413332211','1998-08-08','cliente',1,1,NULL,'017cce6bbd094ff7117fea76a4be0c8e0ec8f52b21b6cb1d992cf653dff51016',NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-21 18:01:44
