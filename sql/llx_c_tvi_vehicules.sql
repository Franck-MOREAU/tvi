CREATE TABLE IF NOT EXISTS `llx_c_tvi_véhicules` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parc` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `immat` varchar(10) NOT NULL,
  `chassis` varchar(7) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB;



