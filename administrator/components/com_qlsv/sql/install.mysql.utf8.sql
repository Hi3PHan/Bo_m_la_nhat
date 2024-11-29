-- Xóa cơ sở dữ liệu
drop table if exists `#__QLSV_Sinhvien`;
drop table if exists `#__QLSV_Ketqua`;
drop table if exists `#__QLSV_Monhoc`;

-- Tạo bảng Sinhvien
CREATE TABLE `#__QLSV_Sinhvien` (
                                    `Masv` INT AUTO_INCREMENT PRIMARY KEY,
                                    `Tensv` NVARCHAR(50) NOT NULL,
                                    `Gioitinh` NVARCHAR(5) DEFAULT 'Nam',
                                    `Ngaysinh` DATE ,
                                    `Que` NVARCHAR(50) NOT NULL,
                                    `Lop` NVARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng Monhoc
CREATE TABLE `#__QLSV_Monhoc` (
                                  `Mamh` INT AUTO_INCREMENT PRIMARY KEY,
                                  `Tenmh` NVARCHAR(50) UNIQUE,
                                  `DVHT` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng Ketqua với các khóa ngoại và khóa chính tổ hợp
CREATE TABLE `#__QLSV_Ketqua` (
                                  `Masv` INT,
                                  `Mamh` INT,
                                  `Diem` FLOAT CHECK (`Diem` BETWEEN 0 AND 10),
                                  PRIMARY KEY (`Masv`, `Mamh`),
                                  FOREIGN KEY (`Masv`) REFERENCES `#__QLSV_Sinhvien`(`Masv`) ON DELETE RESTRICT,
                                  FOREIGN KEY (`Mamh`) REFERENCES `#__QLSV_Monhoc`(`Mamh`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chèn dữ liệu vào bảng Sinhvien
INSERT INTO `#__QLSV_Sinhvien` (`Tensv`, `Gioitinh`, `Ngaysinh`, `Que`, `Lop`)
VALUES
    (N'Trần Bảo Trọng', N'Nam', '1995-12-14', N'Hà Giang', 'L02'),
    (N'Lê Thùy Dương', N'Nữ', '1997-05-12', N'Hà Nội', 'L02'),
    (N'Trần Phương Thảo', N'Nữ', '1996-03-30', N'Quảng Ninh', N'L01'),
    (N'Lê Trường An', N'Nam', '1995-11-20', N'Ninh Bình', N'L04'),
    (N'Phạm Thị Hương Giang', N'Nữ', '1999-02-21', N'Hòa Bình', N'L02'),
    (N'Trần Anh Bảo', N'Nam', '1995-12-14', N'Hà Giang', N'L02'),
    (N'Lê Thùy Dung', N'Nữ', '1997-05-12', N'Hà Nội', N'L03'),
    (N'Phạm Trung Tính', N'Nam', '1996-03-30', N'Quảng Ninh', N'L01'),
    (N'Lê An Hải', N'Nam', '1995-11-20', N'Ninh Bình', N'L04'),
    (N'Phạm Thị Giang Hương', N'Nữ', '1999-02-21', N'Hòa Bình', N'L02'),
    (N'Đoàn Duy Thức', N'Nam', '1994-04-12', N'Hà Nội', N'L01'),
    (N'Dương Tuấn Thông', N'Nam', '1991-04-12', N'Nam Định', N'L03'),
    (N'Lê Thành Đạt', N'Nam', '1993-04-15', N'Phú Thọ', N'L04'),
    (N'Nguyễn Hằng Nga', N'Nữ', '1993-05-25', N'Hà Nội', N'L01'),
    (N'Trần Thanh Nga', N'Nữ', '1994-06-20', N'Phú Thọ', N'L03'),
    (N'Trần Trọng Hoàng', N'Nam', '1995-12-14', N'An Giang', N'L02'),
    (N'Nguyễn Mai Hoa', N'Nữ', '1995-12-14', N'Hà Giang', N'L02'),
    (N'Lê Thúy An', N'Nam', '1998-03-23', N'Hà Nội', N'L01');

-- Chèn dữ liệu vào bảng Monhoc
INSERT INTO `#__QLSV_Monhoc` (Tenmh, DVHT)
VALUES
    (N'Toán cao cấp', 3),
    (N'Mạng máy tính', 3),
    (N'Tin đại cương', 4);

-- Chèn dữ liệu vào bảng Ketqua
INSERT INTO `#__QLSV_Ketqua` (Masv, Mamh, Diem)
VALUES
    (1, 1, 8),
    (1, 2, 5),
    (1, 3, 7),
    (2, 1, 9),
    (2, 2, 5),
    (2, 3, 2),
    (3, 1, 4),
    (3, 2, 2),
    (4, 1, 1),
    (4, 2, 3),
    (5, 1, 4),
    (6, 1, 2),
    (6, 2, 7),
    (6, 3, 9),
    (7, 1, 4),
    (7, 2, 5),
    (7, 3, 8),
    (8, 1, 9),
    (8, 2, 8),
    (9, 1, 7),
    (9, 2, 7),
    (9, 3, 5),
    (10, 1, 3),
    (10, 3, 6),
    (11, 1, 6),
    (12, 1, 8),
    (12, 2, 7),
    (12, 3, 5),
    (13, 1, 6),
    (13, 2, 5),
    (13, 3, 5),
    (14, 1, 8),
    (14, 2, 9),
    (14, 3, 7),
    (15, 1, 3),
    (15, 2, 6),
    (15, 3, 4);