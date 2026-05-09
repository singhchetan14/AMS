-- Messaging feature: one row per message between two users.
-- Run once: mysql -u root ams_db < messaging/sql/messages.sql
-- (or paste in phpMyAdmin while ams_db is selected)

CREATE TABLE IF NOT EXISTS `messages` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `sender_id`   INT(11)      NOT NULL,
  `receiver_id` INT(11)      NOT NULL,
  `body`        TEXT         NOT NULL,
  `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_receiver` (`receiver_id`, `is_read`),
  KEY `idx_thread`   (`sender_id`, `receiver_id`, `created_at`),
  CONSTRAINT `fk_messages_sender`
    FOREIGN KEY (`sender_id`)   REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_receiver`
    FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
