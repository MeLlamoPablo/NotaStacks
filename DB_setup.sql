-- This file will create an empty database ready to work with the script.
-- In order to import it, create a database called "NotaStacks" with UTF8 enconding.
-- Select it and go to phpMyAdmin and upload this file to the "Import" tab
-- Then, connect your database by editing connect.php

-- --------------------------------------------------------

-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2015 a las 19:16:33
-- Versión del servidor: 5.6.21
-- Versión de PHP: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de datos: `notastacks`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stacks`
--

CREATE TABLE IF NOT EXISTS `stacks` (
`id` int(11) NOT NULL,
  `gamemode` varchar(255) COLLATE utf8_bin NOT NULL,
  `time` bigint(20) NOT NULL,
  `ownerid` bigint(20) NOT NULL,
  `server` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stacks_players`
--

CREATE TABLE IF NOT EXISTS `stacks_players` (
`id` int(11) NOT NULL,
  `stack` int(11) NOT NULL,
  `player` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` bigint(20) NOT NULL,
  `steamid` bigint(20) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `avatar` varchar(2000) COLLATE utf8_bin NOT NULL,
  `lastRefresh` bigint(20) DEFAULT NULL,
  `ban` varchar(50) COLLATE utf8_bin DEFAULT '0',
  `lastmessage` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `stacks`
--
ALTER TABLE `stacks`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `stacks_players`
--
ALTER TABLE `stacks_players`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `stacks`
--
ALTER TABLE `stacks`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `stacks_players`
--
ALTER TABLE `stacks_players`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;