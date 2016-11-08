-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Nov 2016 um 14:36
-- Server Version: 5.5.50-0+deb8u1
-- PHP-Version: 5.6.24-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `feuerwehr`
--
CREATE DATABASE IF NOT EXISTS `feuerwehr` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `feuerwehr`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `einsaetze`
--
-- Erstellt am: 07. Aug 2016 um 13:20
--

CREATE TABLE IF NOT EXISTS `einsaetze` (
`id` int(10) unsigned NOT NULL,
  `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `vorfall` longtext COLLATE utf8_unicode_ci NOT NULL,
  `leistung` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ort` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ausrueckordnung` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `einstellungen`
--
-- Erstellt am: 17. Aug 2016 um 16:02
--

CREATE TABLE IF NOT EXISTS `einstellungen` (
`EID` int(10) unsigned NOT NULL,
  `Name` text NOT NULL,
  `Wert` text NOT NULL,
  `Beschreibung` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Einstellungen des Einsatzmonitores';

--
-- Daten für Tabelle `einstellungen`
--

INSERT INTO `einstellungen` (`EID`, `Name`, `Wert`, `Beschreibung`) VALUES
(1, 'SSE-Timeout', '5', 'Timeout des SSE-Servers in Minuten'),
(2, 'Zukunft', '1', 'Einsätze für die Zukunft erlauben (nur bei Manuellen Eingaben sinnvoll)'),
(3, 'Anzeigedauer', '60', 'Anzeigedauer eines Einsatzes in Minuten');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `einsaetze`
--
ALTER TABLE `einsaetze`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `einstellungen`
--
ALTER TABLE `einstellungen`
 ADD PRIMARY KEY (`EID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `einsaetze`
--
ALTER TABLE `einsaetze`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=58;
--
-- AUTO_INCREMENT für Tabelle `einstellungen`
--
ALTER TABLE `einstellungen`
MODIFY `EID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
