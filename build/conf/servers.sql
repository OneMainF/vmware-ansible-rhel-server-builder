CREATE DATABASE IF NOT EXISTS sysmgmt;
use sysmgmt; 

CREATE TABLE IF NOT EXISTS `servers` (
  `serverid` varchar(30) NOT NULL,
  `vmname` varchar(50) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `access` varchar(30) DEFAULT NULL,
  `sdescription` varchar(255) DEFAULT NULL,
  `ipaddress` varchar(50) DEFAULT NULL,
  `macaddress` varchar(30) DEFAULT NULL,
  `roles` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`serverid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
