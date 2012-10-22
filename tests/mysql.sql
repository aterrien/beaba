
-- Clean database `__tests`
DROP DATABASE IF EXISTS `__tests`;
CREATE DATABASE IF NOT EXISTS `__tests`;

-- Structure de la table `tests`
CREATE TABLE IF NOT EXISTS `__tests`.`tests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rand` int(11) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Structure de la table `units`
CREATE TABLE IF NOT EXISTS `__tests`.`units` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  `test` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk0` (`test`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;