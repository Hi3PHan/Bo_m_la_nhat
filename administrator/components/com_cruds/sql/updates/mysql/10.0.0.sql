ALTER TABLE `#__CRUDs_details` ADD COLUMN  `access` int(10) unsigned NOT NULL DEFAULT 0 AFTER `alias`;

ALTER TABLE `#__CRUDs_details` ADD KEY `idx_access` (`access`);
