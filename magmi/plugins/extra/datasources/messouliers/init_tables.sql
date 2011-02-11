-- CREATE ms_import_lcv;
DROP TABLE IF EXISTS `ms_import_lcv`;
CREATE TABLE IF NOT EXISTS `ms_import_lcv` (
  `CodeArticleLCV` varchar(7) DEFAULT NULL,
  `CodeBarreLCV` varchar(20) DEFAULT NULL,
  `RefFournMdle` varchar(200) DEFAULT NULL,
  `RefFournArticle` varchar(200) DEFAULT NULL,
  `CodeSaisonLCV` varchar(3) DEFAULT NULL,
  `model_label` varchar(160) DEFAULT NULL,
  `Taille` varchar(5) DEFAULT NULL,
  `QteStock` int(2) DEFAULT NULL,
  `PA3` decimal(5,2) DEFAULT NULL,
  `PA4` decimal(5,2) DEFAULT NULL,
  `PA5` decimal(5,2) DEFAULT NULL,
  `PA6` decimal(5,2) DEFAULT NULL,
  `PV1` decimal(5,2) DEFAULT NULL,
  `PV2` decimal(5,2) DEFAULT NULL,
  `PV3` decimal(5,2) DEFAULT NULL,
  `PV4` decimal(5,2) DEFAULT NULL,
  `PV5` decimal(5,2) DEFAULT NULL,
  `PV6` decimal(5,2) DEFAULT NULL,
  `Rayon` varchar(40) DEFAULT NULL,
  `Cat` varchar(40) DEFAULT NULL,
  `Famille` varchar(40) DEFAULT NULL,
  `Saison` varchar(3) DEFAULT NULL,
  `Autre` varchar(200) DEFAULT NULL,
  `Semelle` varchar(40) DEFAULT NULL,
  `Forme` varchar(40) DEFAULT NULL,
  `Couleur` varchar(40) DEFAULT NULL,
  `Matière` varchar(40) DEFAULT NULL,
  KEY `CodeArticleLCV` (`CodeArticleLCV`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- LOAD DATA FROM FILE;
LOAD DATA LOCAL INFILE '[[lcv_file]]' REPLACE INTO TABLE `ms_import_lcv`
CHARACTER SET latin1
FIELDS TERMINATED BY ';'
ENCLOSED BY '"'
ESCAPED BY '\\'
LINES TERMINATED BY '\n'
IGNORE 1
LINES;
-- TABLE CONFIGURABLE BASE;
DROP TABLE IF EXISTS `ms_import_mevia`;
CREATE TABLE IF NOT EXISTS `ms_import_mevia` (
`CodeArticleLCV` varchar(7) DEFAULT NULL,
 `CodeSaisonLCV` varchar(3) DEFAULT NULL,
 `model_label` varchar(200) DEFAULT NULL,
 `Marque` varchar(200) DEFAULT NULL,
 `Couleur` varchar(200) DEFAULT NULL,
 `Resume` varchar(200) DEFAULT NULL,
 `Description` TEXT DEFAULT NULL,
 `Keywords` varchar(200) DEFAULT NULL,
 `Cost` decimal(5,2) DEFAULT NULL,
 `Price` decimal(5,2) DEFAULT NULL,
 `Rayon` varchar(200) DEFAULT NULL,
 `Famille` varchar(200) DEFAULT NULL,
 `Semelle` varchar(200) DEFAULT NULL,
 `Exterieur` varchar(200) DEFAULT NULL,
 `Interieur` varchar(200) DEFAULT NULL,
 `Talon` varchar(200) DEFAULT NULL,
 `Chaussant` varchar(200) DEFAULT NULL,
 `img1` varchar(200) DEFAULT NULL,
 `img2` varchar(200) DEFAULT NULL,
 `img3` varchar(200) DEFAULT NULL,
 `img4` varchar(200) DEFAULT NULL,
 `img5` varchar(200) DEFAULT NULL,
 `img6` varchar(200) DEFAULT NULL,
 KEY `CodeArticleLCV` (`CodeArticleLCV`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- LOAD DATA FROM FILE;
LOAD DATA LOCAL INFILE '[[mevia_file]]' REPLACE INTO TABLE `ms_import_mevia`
CHARACTER SET utf8
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
ESCAPED BY '\\'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;
