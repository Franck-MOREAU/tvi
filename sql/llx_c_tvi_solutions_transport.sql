CREATE TABLE IF NOT EXISTS `llx_c_tvi_solutions_transport` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nom` varchar(255) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB;