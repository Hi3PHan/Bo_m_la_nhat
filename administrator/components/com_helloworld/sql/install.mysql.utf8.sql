CREATE TABLE IF NOT EXISTS `#__helloworld` (
                                            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                            `student_id` VARCHAR(255) NOT NULL,
                                            `name` VARCHAR(255) NOT NULL,
                                            `birthday` DATE NOT NULL,
                                            `avg` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




INSERT IGNORE INTO `#__helloworld` (`id`,`student_id`,`name`,`birthday`,`avg`)
VALUES
    (1,'CT070124','Phan Van Hiep', '19/06/2004',9),
    (2,'CT070125','Nguyen Nu Ngoc Mai', '19/06/2004',10),
    (3,'CT070126','Tran Quoc Toan','19/06/2004',8),
    (4,'CT070127','Phung Duy Anh', '19/06/2004',4),
    (5,'CT070128','Phan Van Nam', '19/06/2004',6),
    (6,'CT070129','Nguyen Quoc Tuan', '19/06/2004',7),
    (7,'CT070130','Vu Thi Linh', '19/06/2004',9);

