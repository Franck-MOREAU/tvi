CREATE TABLE IF NOT EXISTS `llx_c_tvi_genre` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `genre` varchar(255) NOT NULL,
  `labelexcel` varchar(45),
  `active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB;


