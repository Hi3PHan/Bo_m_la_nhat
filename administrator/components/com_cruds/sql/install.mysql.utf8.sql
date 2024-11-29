CREATE TABLE IF NOT EXISTS `#__students` (
                                             `Masv` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                             `Tensv` varchar(50) NOT NULL,
                                             `Gioitinh` varchar(5) NOT NULL,
                                             `Ngaysinh` date NOT NULL,
                                             `Que` varchar(50) NOT NULL ,
                                             `Lop` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__subject` (
                                            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                            `MaMonHoc` varchar(20) NOT NULL,
                                            `SoTinChi` int(11) NOT NULL,
                                            `GiangVien` varchar(256)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;