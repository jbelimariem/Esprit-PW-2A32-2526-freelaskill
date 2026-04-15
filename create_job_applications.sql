-- Fix job_offer structure first
ALTER TABLE `job_offer` MODIFY `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- Create job_applications table
CREATE TABLE IF NOT EXISTS `job_applications` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `job_id`       INT NOT NULL,
    `name`         VARCHAR(255) NOT NULL,
    `email`        VARCHAR(255) NOT NULL,
    `job_title`    VARCHAR(255) NOT NULL,
    `message`      TEXT NOT NULL,
    `cv_link`      VARCHAR(512) NOT NULL,
    `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_job_offer` FOREIGN KEY (`job_id`) REFERENCES `job_offer`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
