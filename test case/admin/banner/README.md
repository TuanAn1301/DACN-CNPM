# Hướng dẫn chạy Test Banner

Thư mục này chứa các file test tự động cho chức năng quản lý banner trong trang admin.

## Cấu trúc thư mục

```
banner/
├── test_add_banner.py      # Test thêm banner mới
├── test_edit_banner.py     # Test sửa banner
├── test_delete_banner.py   # Test xóa banner
├── screenshots/            # Thư mục chứa ảnh chụp màn hình
├── ket-qua-test/           # Thư mục chứa file Excel kết quả
└── README.md               # File hướng dẫn này
```

## Yêu cầu hệ thống

1. **Python 3.7+**
2. **Selenium WebDriver**
3. **Chrome Browser** (hoặc ChromeDriver)
4. **Các thư viện Python:**
   - `selenium`
   - `openpyxl`
   - `Pillow` (PIL)

## Cài đặt

### 1. Cài đặt các thư viện cần thiết

```bash
pip install selenium openpyxl Pillow
```

### 2. Cài đặt ChromeDriver

**Cách 1: Tự động (khuyến nghị)**
- Code sẽ tự động tải ChromeDriver nếu chưa có

**Cách 2: Thủ công**
- Tải ChromeDriver từ: https://chromedriver.chromium.org/
- Đặt vào PATH hoặc cùng thư mục với script

## Chạy test

### Test thêm banner

```bash
python test_add_banner.py
```

**Chức năng:**
- Đăng nhập vào admin panel
- Truy cập trang quản lý banner
- Thêm banner mới với thông tin:
  - Tên banner (tự động tạo với timestamp)
  - Hình ảnh (sử dụng file từ `admin/upload/`)
  - Vị trí, thứ tự, loại link
- Chụp ảnh màn hình các bước
- Tạo báo cáo Excel với ảnh nhúng

### Test sửa banner

```bash
python test_edit_banner.py
```

**Chức năng:**
- Đăng nhập vào admin panel
- Truy cập trang quản lý banner
- Chọn banner đầu tiên để sửa
- Cập nhật thông tin banner:
  - Tên banner mới
  - Vị trí mới
  - Thứ tự mới
  - Hình ảnh mới (nếu có)
- Chụp ảnh màn hình các bước
- Tạo báo cáo Excel với ảnh nhúng

### Test xóa banner

```bash
python test_delete_banner.py
```

**Chức năng:**
- Đăng nhập vào admin panel
- Truy cập trang quản lý banner
- Chọn banner đầu tiên để xóa
- Xóa banner và xác nhận
- Chụp ảnh màn hình các bước
- Tạo báo cáo Excel với ảnh nhúng

## Kết quả test

### File Excel báo cáo

Sau khi chạy test, file Excel sẽ được tạo trong thư mục `ket-qua-test/` với tên:
- `test_add_banner_YYYYMMDD_HHMMSS.xlsx`
- `test_edit_banner_YYYYMMDD_HHMMSS.xlsx`
- `test_delete_banner_YYYYMMDD_HHMMSS.xlsx`

**Nội dung báo cáo:**
- Thông tin test (thời gian, dữ liệu test)
- Các bước kiểm thử:
  - Bước kiểm thử
  - Kết quả mong đợi
  - Kết quả thực tế
  - Trạng thái (PASS/FAIL)
  - Hình ảnh (nhúng trực tiếp vào Excel)
- Tổng kết kết quả

### Ảnh chụp màn hình

Tất cả ảnh chụp màn hình được lưu trong thư mục `screenshots/` với tên:
- `login_success_TIMESTAMP.png`
- `banner_page_TIMESTAMP.png`
- `add_banner_form_TIMESTAMP.png`
- `banner_added_success_TIMESTAMP.png`
- ...và các ảnh khác tùy theo test

## Cấu hình

### Thay đổi thông tin đăng nhập

Mở file test và sửa trong phần `__init__`:

```python
self.test_data = {
    'username': 'admin',      # Thay đổi username
    'password': 'admin',      # Thay đổi password
    ...
}
```

### Thay đổi URL

Nếu URL khác với mặc định, sửa trong `self.test_data`:

```python
self.test_data = {
    'banner_url': 'http://localhost/webbansach/admin/banner.php',
    ...
}
```

### Thay đổi hình ảnh test

File test sử dụng hình ảnh từ `admin/upload/a-1762569357_690eac8d95ad1-690f1a45d9360.webp`

Để thay đổi, sửa trong `__init__`:

```python
image_path = os.path.join(project_root, 'admin', 'upload', 'TEN_FILE_ANH_CUA_BAN')
```

## Xử lý lỗi

### Lỗi ChromeDriver không tìm thấy

- Code sẽ tự động tải ChromeDriver nếu có `webdriver_manager`
- Hoặc cài đặt thủ công ChromeDriver và đặt vào PATH

### Lỗi đăng nhập

- Kiểm tra username/password trong file test
- Kiểm tra URL đăng nhập có đúng không
- Kiểm tra kết nối đến server

### Lỗi không tìm thấy element

- Kiểm tra trang web có thay đổi cấu trúc HTML không
- Kiểm tra kết nối mạng
- Kiểm tra server có đang chạy không

## Lưu ý

1. **Đảm bảo server đang chạy** trước khi chạy test
2. **Đảm bảo có ít nhất 1 banner** trong hệ thống để test sửa/xóa
3. **File Excel có thể lớn** do chứa ảnh nhúng, cần đủ dung lượng
4. **Test sẽ tạo dữ liệu mới** mỗi lần chạy (tên banner với timestamp)
5. **Test xóa sẽ xóa banner thật**, cần cẩn thận khi chạy

## Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
1. File log: `test_add_banner.log`, `test_edit_banner.log`, `test_delete_banner.log`
2. Ảnh chụp màn hình trong thư mục `screenshots/`
3. File Excel báo cáo trong thư mục `ket-qua-test/`

