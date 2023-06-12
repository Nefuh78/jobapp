CREATE TABLE `top_articles` (
  `kArtikel` int(50) NOT NULL,
  `cArtNr` varchar(50) NOT NULL,
  `fAnzahl` float(10,2) NOT NULL,
  `cName` varchar(255) NOT NULL,
  `cEinheit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `top_articles`
  ADD UNIQUE KEY `cArtNr` (`cArtNr`),
  ADD KEY `fAnzahl` (`fAnzahl`);
COMMIT;