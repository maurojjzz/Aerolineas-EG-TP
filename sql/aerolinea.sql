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
  `email` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `logoUrl` varchar(500) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `activo` tinyint NOT NULL DEFAULT '1',
  `logoPublicId` varchar(500) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `fechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
INSERT INTO `aerolinea` VALUES (1,'aeroflux','afx','asd','ARG','aeroflux@info.com',NULL,1,NULL,'2026-08-26 12:56:37'),(2,'prueba','prb','asd','ARG','pronando@as.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788571037/aerolineas/logos/wqlocqj1glmzbrv3rka9.webp',1,NULL,'2026-08-15 12:56:37'),(3,'Adela','ADL','aint in la','USA','prima@adelane.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788571246/aerolineas/logos/jlwfltef5dzcgrq05aco.webp',1,NULL,'2026-08-28 12:56:37'),(4,'Get Cloudy','GTCLD','Get Cloudy Airline is based on Texas flying around all the world','USA','getcloudy@airlines.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788571758/aerolineas/logos/duroifzbuvd8atkkqsqm.png',1,'aerolineas/logos/duroifzbuvd8atkkqsqm','2026-09-26 12:56:37'),(5,'2da pru','ppllk','','BRA','asdff@llkm.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1788572611/aerolineas/logos/r2gidpxg4rqyq2rs9316.png',0,'aerolineas/logos/r2gidpxg4rqyq2rs9316','2026-09-26 12:56:37'),(6,'ART','fnk','Aerolinea  hecha para ver si funcionan los cambios que hice','ARG','fksa@gma.com','https://res.cloudinary.com/dqydbsuuj/image/upload/v1790625553/aerolineas/logos/tbkyf9pee7iqafjilddv.jpg',1,'aerolineas/logos/tbkyf9pee7iqafjilddv','2026-09-26 12:56:37'),(7,'asdasasdasd','asew','','BRA','asdas@dasd.a','0',1,'0','2026-09-26 12:56:37'),(8,'qePAs','KPS','asd','CHL','asd@ff.ca','https://res.cloudinary.com/dqydbsuuj/image/upload/v1789428192/aerolineas/logos/hbkfrov38m3str7q54ki.webp',0,'aerolineas/logos/hbkfrov38m3str7q54ki','2026-09-26 12:56:37'),(10,'nueva','nusda','','ARG','adas@as.com',NULL,0,NULL,'2026-09-26 12:56:37');
/*!40000 ALTER TABLE `aerolinea` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `novedades`
--

DROP TABLE IF EXISTS `novedades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `novedades` (
  `idNovedad` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `subtitulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `contenido` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `categoria` enum('general','promocion','vuelo','mantenimiento','importante') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT 'general',
  `prioridad` enum('baja','media','alta') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT 'media',
  `estado` enum('borrador','publicada','archivada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT 'publicada',
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `fechaPublicacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaActualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idNovedad`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `novedades`
--

LOCK TABLES `novedades` WRITE;
/*!40000 ALTER TABLE `novedades` DISABLE KEYS */;
INSERT INTO `novedades` VALUES (1,'Nuevos hoteles 4 estrellas','Se ha inagurado un nuevo hotel para clientes exclusivos de volare en barcelona','Hotel con una exclusiva de 45 nuevas habitaciones incluyendo desde suite presidenciales hasta habitaciones compartidas','vuelo','baja','publicada','/public/img/nove3.webp','2026-10-19 17:17:39','2026-10-19 17:17:39','2026-10-19 17:17:39');
/*!40000 ALTER TABLE `novedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocion`
--

DROP TABLE IF EXISTS `promocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocion` (
  `idPromocion` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `idAerolinea` int NOT NULL,
  `idUsuarioCreador` int NOT NULL,
  `idVuelo` int DEFAULT NULL,
  `codigoOrigen` char(3) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `codigoDestino` char(3) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `descuentoPorcentaje` decimal(5,2) NOT NULL,
  `fechaInicio` date NOT NULL,
  `fechaFin` date NOT NULL,
  `estado` enum('Pendiente','Aprobada','Denegada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Pendiente',
  `condiciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `acumulable` tinyint(1) NOT NULL DEFAULT '0',
  `fechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notificacion_vista` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idPromocion`),
  UNIQUE KEY `codigo_UNIQUE` (`codigo`),
  KEY `fk_promocion_aerolinea_idx` (`idAerolinea`),
  KEY `fk_promocion_usuario_idx` (`idUsuarioCreador`),
  KEY `fk_promocion_vuelo_idx` (`idVuelo`),
  KEY `idx_promocion_alcance` (`idAerolinea`,`idVuelo`,`codigoOrigen`,`codigoDestino`),
  CONSTRAINT `fk_promocion_aerolinea` FOREIGN KEY (`idAerolinea`) REFERENCES `aerolinea` (`idAerolinea`) ON DELETE CASCADE,
  CONSTRAINT `fk_promocion_usuario` FOREIGN KEY (`idUsuarioCreador`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_promocion_vuelo` FOREIGN KEY (`idVuelo`) REFERENCES `vuelo` (`idVuelo`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocion`
--

LOCK TABLES `promocion` WRITE;
/*!40000 ALTER TABLE `promocion` DISABLE KEYS */;
INSERT INTO `promocion` VALUES (9,'PROM-E3E5',4,16,2,NULL,NULL,'prueba','fdfdfgd',20.00,'2026-10-22','2026-10-30','Aprobada','dfgf',0,'2026-10-22 00:14:48',0),(10,'PROM-182B',4,16,15,NULL,NULL,'hfdfg','hfgh',56.00,'2026-10-30','2026-10-31','Denegada','fgh',1,'2026-10-23 18:03:06',1);
/*!40000 ALTER TABLE `promocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reserva`
--

DROP TABLE IF EXISTS `reserva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva` (
  `idReserva` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `idVuelo` int NOT NULL,
  `fechaReserva` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoReserva` enum('pendiente de pago','confirmada','cancelada') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'pendiente de pago',
  `cantPasajeros` int NOT NULL DEFAULT '1',
  `precioTotal` decimal(12,2) NOT NULL,
  `idPromocion` int DEFAULT NULL,
  PRIMARY KEY (`idReserva`),
  KEY `fk_reserva_usuario` (`idUsuario`),
  KEY `fk_reserva_vuelo` (`idVuelo`),
  KEY `fk_reserva_promocion` (`idPromocion`),
  CONSTRAINT `fk_reserva_promocion` FOREIGN KEY (`idPromocion`) REFERENCES `promocion` (`idPromocion`),
  CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  CONSTRAINT `fk_reserva_vuelo` FOREIGN KEY (`idVuelo`) REFERENCES `vuelo` (`idVuelo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserva`
--

LOCK TABLES `reserva` WRITE;
/*!40000 ALTER TABLE `reserva` DISABLE KEYS */;
INSERT INTO `reserva` VALUES (1,15,6,'2026-10-11 01:45:30','cancelada',1,1200000.00,NULL),(2,15,6,'2026-10-13 19:59:49','cancelada',1,1200000.00,NULL),(3,15,11,'2026-10-13 20:24:42','confirmada',1,2100000.00,NULL),(4,15,27,'2026-10-14 00:18:19','cancelada',1,70000.00,NULL),(5,15,14,'2026-10-19 19:55:04','confirmada',1,1320000.00,NULL),(6,15,1,'2026-10-19 22:08:29','cancelada',1,334999.00,NULL),(7,15,19,'2026-10-20 17:31:35','confirmada',1,1180000.00,NULL),(8,15,28,'2026-10-20 17:31:35','confirmada',1,1180000.00,NULL);
/*!40000 ALTER TABLE `reserva` ENABLE KEYS */;
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
  `nroDocumento` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
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
  `fechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notificacion_vista` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `uk_documento` (`tipoDocumento`,`nroDocumento`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `idUsuario_UNIQUE` (`idUsuario`),
  UNIQUE KEY `nroDocumento_UNIQUE` (`nroDocumento`),
  KEY `fk_usuario_1_idx` (`idAerolinea`),
  CONSTRAINT `fk_usuario_1` FOREIGN KEY (`idAerolinea`) REFERENCES `aerolinea` (`idAerolinea`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Mauro','Jimenez','DNI','42130241','$2y$10$qmETw0daBiM3.GnGjuSpeOE4zEE7XeksSZaFb2nNr3/pUtEycNj7q','maurojim123@gmail.com','3413417150','1999-10-15','cliente',0,0,NULL,'a41dedcba7dba463c1a06b7a9c5d011b39ca21eeefec927f1e2fe2674917b332',NULL,'2026-09-26 12:59:31',0),(13,'Tessa','Testing','DNI','83743554','$2y$10$fazr.0l/Qe8RDDy/i4uIjOC9qVVc7XkNaRfZqT9oSkuWXc3udd.Bq','nosoy@real.com','43223233','1996-01-01','cliente',0,0,NULL,'c281c35e3b8269d3b4a4b900177fca2d88c4b7c92f440f15827f6bb6e5bd924d',NULL,'2026-09-26 12:59:31',0),(14,'admin','admin','DNI','99399939','$2y$10$dcW1FaEM96dcw8xzl1Awp.HTsrM8PJI3l7ZDw6DAscpKX0xDQXb/e','admin@admin.admin','54332212','2008-09-02','admin',1,1,NULL,'6dd6a9e9b6026fabb6e2e7ac195b6f045cbc330f7c05548b8859ce48d64d7df2',6,'2026-09-26 12:59:31',0),(15,'Damian','Perez','DNI','42312984','$2y$10$Tk4rDydj/F0qg5W9O9M7yOJUlFBQvuZWIS2TC1gpdiHffABDp.7yK','test@test.test','3413332211','1998-08-08','cliente',1,1,NULL,'017cce6bbd094ff7117fea76a4be0c8e0ec8f52b21b6cb1d992cf653dff51016',NULL,'2026-09-26 12:59:31',0),(16,'CEO','CEO','DNI','1111222','$2y$10$AaKH0zImRtULoT9LnXjrqe5ilHNGdxemnojq/a46F..nM3UMRaAGe','ceo@ceo.ceo','22211222','2008-07-07','ceo',1,1,'2026-09-26 12:59:31','73d7a954d84e3fb3c3bb12b280dbbc11ccb9617f2df023d3de1dfe200093b410',4,'2026-10-02 17:14:35',0),(17,'Moto','Rola','DNI','6767676','$2y$10$decOEDGxV/fsYGx7cAs3G.oaixxBNMtlHqFNFlaulXmZudiLUH8/G','sticclefalbre@gmail.com','67676776','1967-06-07','cliente',1,1,NULL,NULL,NULL,'2026-10-05 19:22:29',0),(18,'Juan','Perez','DNI','54323456','$2y$10$nG25fKsga96KfvhWhyf2F.Jy32w5u8gv7T/buqktq/l/9pIYhcEMO','maurocrunchy1@gmail.com','344312223','2008-10-01','admin',1,1,NULL,NULL,NULL,'2026-10-05 19:34:09',0),(19,'Carlos','Yztorya','DNI','76998488','$2y$10$WlBZaqtVMSvn3EFO73aJS.C5sTgLulnQv0/3nsT3ejC9CWSsqJDCG','salesrepjjzz+carlos@gmail.com','53234321','1989-07-21','ceo',1,1,'2026-10-21 16:55:41','',1,'2026-10-21 16:53:07',0);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vuelo`
--

DROP TABLE IF EXISTS `vuelo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vuelo` (
  `idVuelo` int NOT NULL AUTO_INCREMENT,
  `idAerolinea` int NOT NULL,
  `origenVuelo` varchar(245) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `codigoOrigen` varchar(3) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `destinoVuelo` varchar(245) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `codigoDestino` varchar(3) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `fechaHoraSalidaVuelo` datetime NOT NULL,
  `fechaHoraLlegadaVuelo` datetime NOT NULL,
  `asientosTotales` int NOT NULL DEFAULT '0',
  `asientosDisponibles` int NOT NULL DEFAULT '0',
  `precioVuelo` decimal(10,2) NOT NULL,
  `fechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idVuelo`),
  KEY `fk_vuelo_1_idx` (`idAerolinea`),
  CONSTRAINT `fk_vuelo_1` FOREIGN KEY (`idAerolinea`) REFERENCES `aerolinea` (`idAerolinea`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vuelo`
--

LOCK TABLES `vuelo` WRITE;
/*!40000 ALTER TABLE `vuelo` DISABLE KEYS */;
INSERT INTO `vuelo` VALUES (1,4,'Buenos Aires (EZE)','EZE','Lima (LIM)','LIM','2026-10-28 13:00:00','2026-10-29 01:00:00',300,290,334999.00,'2026-10-07 16:14:25'),(2,4,'Rosario (ROS)','ROS','Buenos Aires (EZE)','EZE','2026-10-31 12:30:00','2026-11-01 00:30:00',140,100,100000.00,'2026-10-07 17:49:22'),(6,4,'Madrid (MAD)','MAD','Buenos Aires (EZE)','EZE','2026-10-19 12:30:00','2026-10-20 00:30:00',339,6,1200000.00,'2026-10-08 22:09:00'),(7,4,'Buenos Aires (EZE)','EZE','Nueva York (JFK)','JFK','2026-11-02 22:15:00','2026-11-03 10:15:00',340,339,1250000.00,'2026-10-08 22:18:43'),(8,4,'Buenos Aires (EZE)','EZE','París (CDG)','CDG','2026-11-04 21:00:00','2026-11-05 09:00:00',320,320,1450000.00,'2026-10-08 22:18:43'),(9,4,'Buenos Aires (EZE)','EZE','Londres (LHR)','LHR','2026-11-06 20:30:00','2026-11-07 08:30:00',300,18,1380000.00,'2026-10-08 22:18:43'),(10,4,'Buenos Aires (EZE)','EZE','Roma (FCO)','FCO','2026-11-08 19:45:00','2026-11-09 07:45:00',310,300,1290000.00,'2026-10-08 22:18:43'),(11,4,'Buenos Aires (EZE)','EZE','Tokio (NRT)','NRT','2026-11-10 23:30:00','2026-11-11 11:30:00',280,199,2100000.00,'2026-10-08 22:18:43'),(12,4,'Buenos Aires (EZE)','EZE','Sídney (SYD)','SYD','2026-11-12 01:00:00','2026-11-12 13:00:00',290,100,1850000.00,'2026-10-08 22:18:43'),(13,4,'Buenos Aires (EZE)','EZE','Toronto (YYZ)','YYZ','2026-11-14 21:20:00','2026-11-15 09:20:00',300,100,980000.00,'2026-10-08 22:18:43'),(14,4,'Buenos Aires (EZE)','EZE','Ámsterdam (AMS)','AMS','2026-11-16 22:50:00','2026-11-17 10:50:00',310,49,1320000.00,'2026-10-08 22:18:43'),(15,4,'Buenos Aires (EZE)','EZE','Dubái (DXB)','DXB','2026-11-18 20:15:00','2026-11-19 08:15:00',330,40,1680000.00,'2026-10-08 22:18:43'),(16,4,'Buenos Aires (EZE)','EZE','Los Ángeles (LAX)','LAX','2026-11-20 23:55:00','2026-11-21 11:55:00',320,30,1100000.00,'2026-10-08 22:18:43'),(17,4,'Buenos Aires (EZE)','EZE','Berlín (BER)','BER','2026-11-22 21:40:00','2026-11-23 09:40:00',300,200,1275000.00,'2026-10-08 22:18:43'),(18,4,'Córdoba (COR)','COR','Miami (MIA)','MIA','2026-11-24 20:30:00','2026-11-25 08:30:00',250,10,875000.00,'2026-10-08 22:18:43'),(19,4,'Rosario (ROS)','ROS','Madrid (MAD)','MAD','2026-11-26 22:00:00','2026-11-27 10:00:00',240,27,1180000.00,'2026-10-08 22:18:43'),(20,4,'Mendoza (MDZ)','MDZ','Santiago (SCL)','SCL','2026-11-28 11:15:00','2026-11-28 23:15:00',160,30,145000.00,'2026-10-08 22:18:43'),(21,4,'Bariloche (BRC)','BRC','São Paulo (GRU)','GRU','2026-11-30 15:20:00','2026-12-01 03:20:00',180,30,420000.00,'2026-10-08 22:18:43'),(22,4,'Salta (SLA)','SLA','Lima (LIM)','LIM','2026-12-02 08:30:00','2026-12-02 20:30:00',170,42,380000.00,'2026-10-08 22:18:43'),(23,4,'Córdoba (COR)','COR','Panamá (PTY)','PTY','2026-12-04 13:45:00','2026-12-05 01:45:00',210,51,520000.00,'2026-10-08 22:18:43'),(24,4,'Madrid (MAD)','MAD','Buenos Aires (EZE)','EZE','2026-12-06 14:00:00','2026-12-07 02:00:00',320,42,1320000.00,'2026-10-08 22:18:43'),(25,4,'Miami (MIA)','MIA','Buenos Aires (EZE)','EZE','2026-12-08 11:30:00','2026-12-08 23:30:00',300,20,950000.00,'2026-10-08 22:18:43'),(26,4,'París (CDG)','CDG','Buenos Aires (EZE)','EZE','2026-12-10 13:15:00','2026-12-11 01:15:00',310,20,1480000.00,'2026-10-08 22:18:43'),(27,4,'Buenos Aires (AEP)','AEP','Buenos Aires (EZE)','EZE','2026-10-19 18:08:00','2026-10-26 17:07:00',156,156,70000.00,'2026-10-12 15:06:08'),(28,4,'Madrid (MAD)','MAD','Rosario (ROS)','ROS','2026-12-05 22:00:00','2026-12-10 10:00:00',44,344,1180000.00,'2026-10-08 22:18:43');
/*!40000 ALTER TABLE `vuelo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-23 18:07:09
