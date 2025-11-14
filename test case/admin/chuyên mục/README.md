# Test Cases cho Quản Lý Chuyên Mục

Thư mục này chứa các test case tự động cho chức năng quản lý chuyên mục trong hệ thống admin.

## Các Test Case

### 1. Test Thêm Chuyên Mục (`test_add_category.py`)
- **Mô tả**: Test chức năng thêm chuyên mục mới
- **Các bước thực hiện**:
  1. Đăng nhập vào admin
  2. Truy cập trang quản lý chuyên mục
  3. Click nút "Thêm Chuyên Mục"
  4. Nhập tên chuyên mục
  5. Submit form
  6. Kiểm tra chuyên mục đã được thêm vào danh sách

**Input**: Tên chuyên mục (tự động tạo với timestamp)
**Output**: File Excel báo cáo kết quả test
**Kết quả mong đợi**: PASS - Chuyên mục được thêm thành công

### 2. Test Sửa Chuyên Mục (`test_edit_category.py`)
- **Mô tả**: Test chức năng sửa thông tin chuyên mục
- **Các bước thực hiện**:
  1. Đăng nhập vào admin
  2. Truy cập trang quản lý chuyên mục
  3. Chọn một chuyên mục để sửa (click nút sửa)
  4. Cập nhật tên chuyên mục
  5. Submit form
  6. Kiểm tra chuyên mục đã được cập nhật

**Input**: Tên chuyên mục mới (tự động tạo với timestamp)
**Output**: File Excel báo cáo kết quả test
**Kết quả mong đợi**: PASS - Chuyên mục được sửa thành công

### 3. Test Xóa Chuyên Mục (`test_delete_category.py`)
- **Mô tả**: Test chức năng xóa chuyên mục
- **Các bước thực hiện**:
  1. Đăng nhập vào admin
  2. Truy cập trang quản lý chuyên mục
  3. Chọn một chuyên mục để xóa (click nút xóa)
  4. Xác nhận xóa (tự động redirect)
  5. Kiểm tra chuyên mục đã bị xóa khỏi danh sách

**Input**: Chuyên mục đầu tiên trong danh sách
**Output**: File Excel báo cáo kết quả test
**Kết quả mong đợi**: PASS - Chuyên mục được xóa thành công

### 4. Test Tìm Kiếm Chuyên Mục (`test_search_category.py`)
- **Mô tả**: Test chức năng tìm kiếm chuyên mục
- **Các bước thực hiện**:
  1. Đăng nhập vào admin
  2. Truy cập trang quản lý chuyên mục
  3. Nhập từ khóa tìm kiếm vào ô tìm kiếm
  4. Click nút tìm kiếm
  5. Kiểm tra kết quả tìm kiếm

**Input**: Từ khóa tìm kiếm (mặc định: "Sách")
**Output**: File Excel báo cáo kết quả test với danh sách kết quả tìm được
**Kết quả mong đợi**: PASS - Hiển thị kết quả tìm kiếm phù hợp

## Cấu trúc Thư mục

```
chuyên mục/
├── test_add_category.py          # Test thêm chuyên mục
├── test_edit_category.py         # Test sửa chuyên mục
├── test_delete_category.py       # Test xóa chuyên mục
├── test_search_category.py       # Test tìm kiếm chuyên mục
├── screenshots/                  # Thư mục chứa ảnh chụp màn hình
├── ket-qua-test/                 # Thư mục chứa file Excel kết quả
└── README.md                     # File hướng dẫn này
```

## Cách Chạy Test

### Yêu cầu
- Python 3.7+
- Selenium WebDriver
- Chrome Browser
- Các thư viện: openpyxl, selenium, PIL

### Cài đặt Dependencies
```bash
pip install selenium openpyxl pillow webdriver-manager
```

### Chạy từng test
```bash
# Test thêm chuyên mục
python test_add_category.py

# Test sửa chuyên mục
python test_edit_category.py

# Test xóa chuyên mục
python test_delete_category.py

# Test tìm kiếm chuyên mục
python test_search_category.py
```

## Cấu hình

Các thông tin cấu hình có thể chỉnh sửa trong file test:
- `admin_url`: URL trang admin
- `login_url`: URL trang đăng nhập
- `category_url`: URL trang quản lý chuyên mục
- `username`: Tên đăng nhập admin
- `password`: Mật khẩu admin
- `search_term`: Từ khóa tìm kiếm (cho test tìm kiếm)

## Kết quả Test

Mỗi test sẽ tạo ra:
1. **File Excel báo cáo** trong thư mục `ket-qua-test/` với tên:
   - `test_add_category_YYYYMMDD_HHMMSS.xlsx`
   - `test_edit_category_YYYYMMDD_HHMMSS.xlsx`
   - `test_delete_category_YYYYMMDD_HHMMSS.xlsx`
   - `test_search_category_YYYYMMDD_HHMMSS.xlsx`

2. **Ảnh chụp màn hình** trong thư mục `screenshots/` cho từng bước test

3. **File log** với tên tương ứng (ví dụ: `test_add_category.log`)

## Nội dung File Excel Báo Cáo

File Excel báo cáo bao gồm:
- **Thông tin kiểm thử**: Thời gian, dữ liệu test, URL
- **Các bước kiểm thử**: 
  - Bước kiểm thử
  - Kết quả mong đợi
  - Kết quả thực tế
  - Trạng thái (PASS/FAIL)
  - Đường dẫn hình ảnh
- **Tổng kết kết quả**: Tổng số bước PASS/FAIL

## Lưu ý

1. Đảm bảo server đang chạy tại `http://localhost/webbansach`
2. Đảm bảo có tài khoản admin hợp lệ
3. Đảm bảo có ít nhất một chuyên mục trong hệ thống để test sửa/xóa
4. Test xóa sẽ xóa chuyên mục thực sự, cần cẩn thận khi chạy

## Troubleshooting

- **Lỗi WebDriver**: Cài đặt ChromeDriver hoặc sử dụng webdriver-manager
- **Lỗi timeout**: Tăng thời gian chờ trong code hoặc kiểm tra kết nối mạng
- **Lỗi không tìm thấy element**: Kiểm tra lại selector hoặc cấu trúc HTML của trang

