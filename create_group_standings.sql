CREATE TABLE IF NOT EXISTS `group_standings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `team_id` INT NOT NULL,
  `position` INT DEFAULT NULL,
  `matches` INT DEFAULT 0,
  `won` INT DEFAULT 0,
  `lost` INT DEFAULT 0,
  `drawn` INT DEFAULT 0,
  `tied` INT DEFAULT 0,
  `nr` INT DEFAULT 0,
  `points` INT DEFAULT 0,
  `nrr` DECIMAL(5,3) DEFAULT 0.000,
  UNIQUE KEY `unique_group_team` (`group_id`, `team_id`),
  KEY `idx_group` (`group_id`),
  KEY `idx_team` (`team_id`),
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
