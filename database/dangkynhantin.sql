-- Bảng đăng ký nhận thông báo sách mới
CREATE TABLE IF NOT EXISTS `dangkynhantin` (
  `madangky` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ngaydangky` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trangthai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=đang kích hoạt, 0=đã hủy',
  PRIMARY KEY (`madangky`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

