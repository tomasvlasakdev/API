-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el7.remi
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Stř 26. lis 2025, 09:42
-- Verze serveru: 5.5.68-MariaDB
-- Verze PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `vlasato23`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `pushNotifs`
--

CREATE TABLE `pushNotifs` (
  `id` int(11) NOT NULL,
  `subId` varchar(50) NOT NULL,
  `isSubscribed` int(11) NOT NULL,
  `userId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Vypisuji data pro tabulku `pushNotifs`
--

INSERT INTO `pushNotifs` (`id`, `subId`, `isSubscribed`, `userId`) VALUES
(7, '847fbc0d-194b-4855-af9b-1820d6b2c72d', 1, 1);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `pushNotifs`
--
ALTER TABLE `pushNotifs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `pushNotifs`
--
ALTER TABLE `pushNotifs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
