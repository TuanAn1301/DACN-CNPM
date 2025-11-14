import os
import time
import sys
import io
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException, WebDriverException
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.drawing.image import Image as XLImage
from openpyxl.utils import get_column_letter
from PIL import Image as PILImage
import traceback

# Import từ test_utils
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from test_utils import TestReporter, in_thong_bao as in_thong_bao_utils, tao_thu_muc_ket_qua as tao_thu_muc_ket_qua_utils

# Sử dụng TestReporter từ test_utils
# TestReporter sẽ được import từ test_utils

def in_thong_bao(*args, **kwargs):
    """In thông báo ra màn hình và thêm vào báo cáo.
    Hỗ trợ các cách gọi:
    - in_thong_bao(reporter, "Thông điệp", ...)
    - in_thong_bao("Thông điệp", reporter=reporter, ...)
    - in_thong_bao("Thông điệp", ...)
    """
    reporter = None
    noi_dung = ''

    # Lấy reporter từ kwargs nếu có
    if 'reporter' in kwargs and kwargs['reporter'] is not None:
        reporter = kwargs.pop('reporter')

    # Phân tích args
    if len(args) == 0:
        pass
    elif len(args) == 1:
        # Có thể là (noi_dung,) hoặc (reporter,)
        if hasattr(args[0], 'add_step'):
            reporter = args[0]
        else:
            noi_dung = args[0]
    else:
        # (reporter, noi_dung, ...)
        if hasattr(args[0], 'add_step'):
            reporter = args[0]
            noi_dung = args[1]
        else:
            # (noi_dung, ...), giữ nguyên reporter từ kwargs
            noi_dung = args[0]

    # Lấy các tham số mặc định còn lại từ kwargs
    status = kwargs.pop('status', 'PASS')
    input_data = kwargs.pop('input_data', '')
    output = kwargs.pop('output', '')
    expected = kwargs.pop('expected', '')
    screenshot_path = kwargs.pop('screenshot_path', None)

    # Đảm bảo reporter hợp lệ
    try:
        _ = getattr(reporter, 'add_step', None)
    except Exception:
        reporter = None

    return in_thong_bao_utils(reporter, noi_dung, status, input_data, output, expected, screenshot_path, **kwargs)

def tao_thu_muc_ket_qua(test_name, base_dir=None):
    """Tạo thư mục kết quả bằng tiện ích từ test_utils"""
    return tao_thu_muc_ket_qua_utils(test_name, base_dir)

def chup_man_hinh(driver, ten_file, reporter=None, description=""):
    """Chụp màn hình và lưu vào file, trả về đường dẫn file"""
    try:
        # Tạo thư mục nếu chưa tồn tại
        os.makedirs(os.path.dirname(ten_file), exist_ok=True)
        
        # Chụp màn hình
        driver.save_screenshot(ten_file)
        
        # Thêm vào báo cáo nếu có reporter
        if reporter and description and os.path.exists(ten_file):
            # Thêm bước với screenshot
            reporter.add_step(
                description=description,
                status='INFO',
                input_data='',
                output=f'Đã chụp màn hình: {os.path.basename(ten_file)}',
                expected='Chụp màn hình thành công',
                screenshot_path=ten_file
            )
            
        return ten_file
    except Exception as e:
        error_msg = f"Lỗi khi chụp màn hình: {str(e)}"
        if reporter:
            in_thong_bao(reporter, error_msg, 'ERROR', output=error_msg)
        return None

def kiem_tra_phan_tu(driver, loai, gia_tri, timeout=10, reporter=None):
    """Kiểm tra sự tồn tại của phần tử"""
    try:
        WebDriverWait(driver, timeout).until(
            EC.presence_of_element_located((loai, gia_tri))
        )
        return True
    except TimeoutException:
        return False

def kiem_tra_dang_nhap_thanh_cong(driver):
    """Kiểm tra xem đăng nhập có thành công không"""
    try:
        # Kiểm tra xem có thông báo lỗi không
        thong_bao_loi = driver.find_elements(By.CSS_SELECTOR, ".alert.alert-danger, .error-message")
        if thong_bao_loi:
            return False, f"Lỗi: {thong_bao_loi[0].text}"
            
        # Kiểm tra URL hiện tại
        if "index.php" in driver.current_url:
            return True, "Đăng nhập thành công!"
            
        # Kiểm tra xem có thông báo thành công không
        thong_bao_thanh_cong = driver.find_elements(By.CSS_SELECTOR, ".alert.alert-success")
        if thong_bao_thanh_cong and "thành công" in thong_bao_thanh_cong[0].text.lower():
            return True, "Đăng nhập thành công!"
            
        # Kiểm tra xem có nút đăng xuất không
        if kiem_tra_phan_tu(driver, By.XPATH, "//a[contains(@href, 'dang-xuat') or contains(text(), 'Đăng xuất')]"):
            return True, "Đăng nhập thành công!"
            
        return False, "Không xác định được trạng thái đăng nhập"
        
    except Exception as e:
        return False, f"Lỗi khi kiểm tra đăng nhập: {str(e)}"

def dang_nhap(driver, tai_khoan, mat_khau, thu_muc_ket_qua, reporter=None):
    """Thực hiện đăng nhập vào hệ thống"""
    try:
        in_thong_bao(reporter, "Đang thực hiện đăng nhập...", "INFO", 
                    input_data=f"Tài khoản: {tai_khoan}",
                    expected="Đăng nhập thành công")
        
        # Mở trang đăng nhập
        in_thong_bao(reporter, "Mở trang đăng nhập", "INFO", 
                    input_data="http://localhost/webbansach/dang-nhap.php",
                    expected="Trang đăng nhập được tải thành công")
        driver.get("http://localhost/webbansach/dang-nhap.php")
        time.sleep(2)
        
        # Chụp màn hình trước khi đăng nhập
        screenshot_path = chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "truoc_dang_nhap.png"), 
                                       reporter, "Trang đăng nhập")
        
        # Tìm và điền thông tin đăng nhập
        in_thong_bao(reporter, "Tìm và điền thông tin đăng nhập", "INFO", 
                    input_data=f"Tài khoản: {tai_khoan}",
                    expected="Điền thành công tên đăng nhập và mật khẩu")
        
        # Tìm và điền tên đăng nhập (sử dụng ID 'email1' hoặc name 'taikhoan')
        o_tai_khoan = None
        try:
            o_tai_khoan = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.ID, "email1"))
            )
        except:
            try:
                o_tai_khoan = WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.NAME, "taikhoan"))
                )
            except:
                in_thong_bao(reporter, "Không tìm thấy ô tên đăng nhập", "FAIL",
                            output="Không tìm thấy ô tên đăng nhập bằng ID hoặc name",
                            expected="Tìm thấy ô tên đăng nhập")
                return False
        
        o_tai_khoan.clear()
        o_tai_khoan.send_keys(tai_khoan)
        in_thong_bao(reporter, "Đã nhập tên đăng nhập", "PASS",
                    input_data=tai_khoan,
                    output="Đã nhập thành công tên đăng nhập",
                    expected="Nhập tên đăng nhập thành công")
        time.sleep(1)

        # Tìm và điền mật khẩu (sử dụng ID 'login-password' hoặc name 'matkhau')
        o_mat_khau = None
        try:
            o_mat_khau = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.ID, "login-password"))
            )
        except:
            try:
                o_mat_khau = WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.NAME, "matkhau"))
                )
            except:
                in_thong_bao(reporter, "Không tìm thấy ô mật khẩu", "FAIL",
                            output="Không tìm thấy ô mật khẩu bằng ID hoặc name",
                            expected="Tìm thấy ô mật khẩu")
                return False

        o_mat_khau.clear()
        o_mat_khau.send_keys(mat_khau)
        in_thong_bao(reporter, "Đã nhập mật khẩu", "PASS",
                    input_data="***",
                    output="Đã nhập thành công mật khẩu",
                    expected="Nhập mật khẩu thành công")
        time.sleep(1)

        # Chụp màn hình sau khi điền thông tin
        screenshot_path = chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "sau_khi_dien_thong_tin.png"),
                                       reporter, "Sau khi điền thông tin đăng nhập")
        
        # Nhấn nút đăng nhập
        in_thong_bao(reporter, "Nhấn nút đăng nhập", "INFO",
                    expected="Nút đăng nhập được click thành công")
        try:
            # Thử tìm nút đăng nhập bằng nhiều cách khác nhau
            nut_dang_nhap = None
            try:
                nut_dang_nhap = WebDriverWait(driver, 5).until(
                    EC.element_to_be_clickable((By.NAME, "dangnhap"))
                )
            except:
                try:
                    nut_dang_nhap = WebDriverWait(driver, 5).until(
                        EC.element_to_be_clickable((By.CSS_SELECTOR, "input[type='submit'][name='dangnhap']"))
                    )
                except:
                    try:
                        nut_dang_nhap = driver.find_element(By.CSS_SELECTOR, "button[type='submit'], input[type='submit']")
                    except:
                        in_thong_bao(reporter, "Không tìm thấy nút đăng nhập", "FAIL",
                                    output="Không tìm thấy nút đăng nhập",
                                    expected="Tìm thấy nút đăng nhập")
                        return False
            
            # Cuộn đến nút đăng nhập để đảm bảo nó hiển thị
            driver.execute_script("arguments[0].scrollIntoView(true);", nut_dang_nhap)
            time.sleep(0.5)
            
            # Thử click bằng JavaScript
            try:
                driver.execute_script("arguments[0].click();", nut_dang_nhap)
            except:
                nut_dang_nhap.click()
            
            in_thong_bao(reporter, "Đã nhấn nút đăng nhập", "PASS",
                        output="Đã click nút đăng nhập thành công",
                        expected="Click nút đăng nhập thành công")
            
            # Chờ chuyển trang hoặc cập nhật giao diện
            time.sleep(3)  # Chờ đăng nhập xử lý
            
            # Kiểm tra kết quả đăng nhập
            thanh_cong, thong_bao = kiem_tra_dang_nhap_thanh_cong(driver)
            if thanh_cong:
                screenshot_path = chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "dang_nhap_thanh_cong.png"),
                                               reporter, "Đăng nhập thành công")
                in_thong_bao(reporter, "Đăng nhập thành công", "PASS",
                            output=thong_bao,
                            expected="Đăng nhập thành công và chuyển về trang chủ")
                return True
            else:
                in_thong_bao(reporter, "Đăng nhập thất bại", "FAIL",
                            output=thong_bao,
                            expected="Đăng nhập thành công")
                return False
                
        except Exception as e:
            in_thong_bao(reporter, f"Lỗi khi nhấn nút đăng nhập: {str(e)}", "ERROR",
                        output=str(e),
                        expected="Nhấn nút đăng nhập thành công")
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "loi_nhan_nut_dang_nhap.png"),
                         reporter, "Lỗi nhấn nút đăng nhập")
            return False
            
    except Exception as e:
        in_thong_bao(reporter, f"Lỗi khi thực hiện đăng nhập: {str(e)}", "ERROR",
                    output=str(e),
                    expected="Đăng nhập thành công")
        return False

def mua_ngay_tu_banner(driver, thu_muc_ket_qua, reporter=None):
    """Thực hiện mua ngay từ banner"""
    try:
        in_thong_bao("Đang tìm nút Mua ngay trên banner...")
        
        # Chờ cho banner tải xong
        time.sleep(3)
        
        # Chụp màn hình để kiểm tra
        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "truoc_khi_tim_nut_mua_ngay.png"))
        
        # Danh sách các cách tìm nút Mua ngay
        locators = [
            # Cách 1: Tìm bằng class chính xác
            (By.CSS_SELECTOR, "a.btn-outlined--primary"),
            # Cách 2: Tìm trong slider
            (By.CSS_SELECTOR, ".single-slide .btn-outlined--primary"),
            # Cách 3: Tìm bằng text
            (By.XPATH, "//a[contains(translate(., 'MUA NGAY', 'mua ngay'), 'mua ngay')]"),
            # Cách 4: Tìm bằng class chứa
            (By.XPATH, "//a[contains(@class, 'btn-outlined')]"),
            # Cách 5: Tìm trong home-content
            (By.CSS_SELECTOR, ".home-content a[class*='btn']")
        ]
        
        nut_mua_ngay = None
        for locator in locators:
            try:
                in_thong_bao(f"   - Thử tìm bằng {locator[0]}: {locator[1]}")
                elements = driver.find_elements(*locator)
                for element in elements:
                    try:
                        if element.is_displayed() and element.is_enabled():
                            # Kiểm tra thêm text để chắc chắn đúng nút
                            text = element.text.lower()
                            if 'mua' in text and 'ngay' in text:
                                nut_mua_ngay = element
                                in_thong_bao(f"   - Tìm thấy nút Mua ngay bằng {locator[0]}", "OK")
                                break
                    except:
                        continue
                if nut_mua_ngay:
                    break
            except Exception as e:
                in_thong_bao(f"   - Không tìm thấy bằng {locator[0]}: {str(e)[:100]}...", "LOI")
                continue
        
        if not nut_mua_ngay:
            # Thử tìm bằng JavaScript nếu các cách trên thất bại
            try:
                in_thong_bao("   - Thử tìm bằng JavaScript...")
                nut_mua_ngay = driver.execute_script(
                    """
                    // Tìm tất cả các nút có chứa text 'Mua ngay' không phân biệt hoa thường
                    const buttons = Array.from(document.querySelectorAll('a'));
                    return buttons.find(btn => 
                        btn.textContent.toLowerCase().includes('mua') && 
                        btn.textContent.toLowerCase().includes('ngay') &&
                        window.getComputedStyle(btn).display !== 'none'
                    );
                    """
                )
                in_thong_bao("   - Tìm thấy nút bằng JavaScript", "OK" if nut_mua_ngay else "LOI")
            except Exception as e:
                in_thong_bao(f"   - Lỗi khi tìm bằng JavaScript: {str(e)[:100]}...", "LOI")
        
        if not nut_mua_ngay:
            # Chụp ảnh màn hình để debug
            in_thong_bao("   - Không tìm thấy nút, chụp ảnh màn hình để debug...")
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "debug_khong_tim_thay_nut.png"))
            raise Exception("Không tìm thấy nút Mua ngay trên banner")
        
        # Cuộn đến nút và chờ nó có thể click được
        in_thong_bao("   - Đang cuộn đến nút...")
        driver.execute_script("arguments[0].scrollIntoView({block: 'center', behavior: 'smooth'});", nut_mua_ngay)
        time.sleep(1)
        
        # Đánh dấu nút bằng đường viền đỏ để dễ nhận biết
        driver.execute_script("arguments[0].style.border='3px solid red';", nut_mua_ngay)
        
        # Chụp màn hình trước khi click
        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "truoc_khi_click_mua_ngay.png"))
        
        # Thử click bằng JavaScript
        try:
            in_thong_bao("   - Đang thử click bằng JavaScript...")
            driver.execute_script("arguments[0].click();", nut_mua_ngay)
            in_thong_bao("   - Đã gửi lệnh click bằng JavaScript", "OK")
        except Exception as e:
            in_thong_bao(f"   - Lỗi khi click bằng JavaScript: {str(e)[:100]}...", "LOI")
            # Thử click bình thường nếu JavaScript thất bại
            try:
                in_thong_bao("   - Đang thử click thông thường...")
                nut_mua_ngay.click()
                in_thong_bao("   - Đã click thành công", "OK")
            except Exception as e2:
                # Thử cách cuối cùng là gọi trực tiếp sự kiện onclick
                try:
                    in_thong_bao("   - Đang thử gọi sự kiện onclick trực tiếp...")
                    onclick = nut_mua_ngay.get_attribute('onclick')
                    if onclick:
                        driver.execute_script(onclick)
                        in_thong_bao("   - Đã gọi sự kiện onclick trực tiếp", "OK")
                    else:
                        raise Exception("Không tìm thấy sự kiện onclick")
                except Exception as e3:
                    raise Exception(f"Không thể click vào nút Mua ngay: {str(e3)}")
        
        in_thong_bao("Đã nhấn nút Mua ngay trên banner")
        time.sleep(3)
        
        # Chụp màn hình sau khi thêm vào giỏ hàng
        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "sau_khi_them_vao_gio.png"))
        
        # Kiểm tra xem đã chuyển đến trang thanh toán chưa
        try:
            WebDriverWait(driver, 10).until(
                EC.url_contains("thanh-toan.php") | EC.url_contains("gio-hang.php") |
                EC.presence_of_element_located((By.NAME, "hoten")) |
                EC.presence_of_element_located((By.ID, "checkout-form"))
            )
            in_thong_bao("Đã chuyển đến trang thanh toán/giỏ hàng")
        except:
            # Nếu không tự động chuyển, thử điều hướng thủ công
            in_thong_bao("Tự động chuyển đến trang thanh toán...")
            driver.get("http://localhost/webbansach/thanh-toan.php")
            
        time.sleep(2)
        
        # Chụp màn hình trước khi điền thông tin
        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "truoc_khi_dien_thong_tin.png"))
        
        # Điền thông tin thanh toán
        try:
            in_thong_bao("Đang điền thông tin thanh toán...")
            
            # Hàm điền thông tin vào trường
            def dien_thong_tin(ten_truong, gia_tri, bat_buoc=True):
                try:
                    # Thử tìm bằng class name
                    element = WebDriverWait(driver, 5).until(
                        EC.presence_of_element_located((By.CLASS_NAME, ten_truong))
                    )
                    
                    # Cuộn đến phần tử
                    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", element)
                    time.sleep(0.5)
                    
                    # Xóa nội dung hiện tại và điền mới
                    element.clear()
                    element.send_keys(gia_tri)
                    in_thong_bao(f"   - Đã điền {ten_truong}: {gia_tri}", "OK")
                    return True
                except Exception as e:
                    if bat_buoc:
                        in_thong_bao(f"   - Không tìm thấy trường {ten_truong}: {str(e)[:100]}...", "LOI")
                    else:
                        in_thong_bao(f"   - Bỏ qua trường không bắt buộc: {ten_truong}", "CẢNH BÁO")
                    return False
                    
                    # Cuộn đến phần tử
                    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", element)
                    time.sleep(0.5)
                    
                    # Xóa nội dung hiện tại và điền mới
                    element.clear()
                    element.send_keys(gia_tri)
                    in_thong_bao(f"   - Đã điền {ten_truong}: {gia_tri}", "OK")
                    return True
                except Exception as e:
                    if bat_buoc:
                        in_thong_bao(f"   - Không tìm thấy trường {ten_truong}: {str(e)[:100]}...", "LOI")
                    else:
                        in_thong_bao(f"   - Bỏ qua trường không bắt buộc: {ten_truong}", "CẢNH BÁO")
                    return False
            
            # Điền các trường thông tin thanh toán
            thong_tin = {
                "sonha": "123",
                "thonxom": "Thôn 1",
                "phuongxa": "Phường 5",
                "huyen": "Quận 1",
                "tinhthanh": "TP. Hồ Chí Minh"
            }
            
            # Chờ form tải xong
            try:
                WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.CLASS_NAME, "sonha"))
                )
            except:
                in_thong_bao("   - Không tìm thấy form địa chỉ", "LOI")
                return False
            
            for field, value in thong_tin.items():
                dien_thong_tin(field, value, field != "ghichu")
            
            # Chấp nhận điều khoản
            try:
                dieu_khoan = WebDriverWait(driver, 5).until(
                    EC.presence_of_element_located((By.ID, "accept_terms2"))
                )
                if not dieu_khoan.is_selected():
                    driver.execute_script("arguments[0].click();", dieu_khoan)
                    in_thong_bao("   - Đã chấp nhận điều khoản", "OK")
            except Exception as e:
                in_thong_bao(f"   - Không tìm thấy hoặc không thể chọn điều khoản: {str(e)}", "CẢNH BÁO")
            
            # Chụp màn hình sau khi điền thông tin
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "sau_khi_dien_thong_tin.png"))
            
            # Nhấn nút đặt hàng
            in_thong_bao("Đang gửi đơn hàng...")
            
            # Thử tìm nút đặt hàng bằng nhiều cách khác nhau
            nut_dat_hang = None
            nut_selectors = [
                (By.CLASS_NAME, "dathang"),
                (By.XPATH, "//button[contains(., 'ĐẶT HÀNG') or contains(., 'Thanh toán')]"),
                (By.CSS_SELECTOR, "button.place-order, button[type='submit']"),
                (By.CLASS_NAME, "btn-primary")
            ]
            
            for selector in nut_selectors:
                try:
                    nut_dat_hang = WebDriverWait(driver, 3).until(
                        EC.element_to_be_clickable(selector)
                    )
                    break
                except:
                    continue
            
            if not nut_dat_hang:
                raise Exception("Không tìm thấy nút đặt hàng")
            
            # Cuộn đến nút và đánh dấu
            driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", nut_dat_hang)
            driver.execute_script("arguments[0].style.border='3px solid green';", nut_dat_hang)
            time.sleep(1)
            
            # Chụp màn hình trước khi click
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "truoc_khi_nhan_dat_hang.png"))
            
            # Thử click bằng JavaScript
            try:
                driver.execute_script("arguments[0].click();", nut_dat_hang)
            except:
                nut_dat_hang.click()
                
            in_thong_bao("Đã gửi yêu cầu đặt hàng")
            time.sleep(3)
            
            # Chụp màn hình sau khi gửi đơn hàng
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "sau_khi_gui_don_hang.png"),
                         reporter, "Màn hình sau khi gửi đơn hàng")
            
            # Chờ một chút để hệ thống xử lý
            time.sleep(2)
            
            # Kiểm tra đặt hàng thành công
            in_thong_bao("Đang kiểm tra kết quả đặt hàng...")
            
            # Chờ chuyển trang hoặc hiển thị thông báo
            try:
                WebDriverWait(driver, 10).until(
                    lambda d: any([
                        "hoan-thanh" in d.current_url.lower(),
                        "thank" in d.current_url.lower(),
                        "success" in d.current_url.lower(),
                        "thành công" in d.page_source.lower(),
                        "cảm ơn" in d.page_source.lower()
                    ])
                )
                # Chụp toàn bộ trang xác nhận đơn hàng
                try:
                    # Cuộn lên đầu trang trước khi chụp
                    driver.execute_script("window.scrollTo(0, 0);")
                    time.sleep(0.5)  # Chờ trang cuộn
                    
                    # Chụp toàn bộ trang (có thể cuộn)
                    total_height = driver.execute_script("return Math.max( document.body.scrollHeight, document.body.offsetHeight, document.documentElement.clientHeight, document.documentElement.scrollHeight, document.documentElement.offsetHeight );")
                    viewport_height = driver.execute_script("return window.innerHeight")
                    
                    # Nếu trang ngắn hơn viewport, chỉ cần chụp 1 lần
                    if total_height <= viewport_height:
                        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "xac_nhan_don_hang_toan_bo.png"), 
                                    reporter, "Toàn bộ trang xác nhận đơn hàng")
                    else:
                        # Nếu trang dài, chụp nhiều ảnh và ghép lại
                        scroll_amount = viewport_height - 100  # Chồng lấp 100px
                        
                        # Tạo ảnh mới để ghép
                        full_img = PILImage.new('RGB', (driver.get_window_size()['width'], total_height))
                        current_y = 0
                        
                        while current_y < total_height:
                            # Cuộn đến vị trí hiện tại
                            driver.execute_script(f"window.scrollTo(0, {current_y});")
                            time.sleep(0.5)  # Chờ cho hoạt ảnh cuộn hoàn tất
                            
                            # Chụp màn hình
                            screenshot = driver.get_screenshot_as_png()
                            img = PILImage.open(io.BytesIO(screenshot))
                            
                            # Tính toán vị trí cắt ảnh
                            if current_y + viewport_height > total_height:
                                # Nếu là ảnh cuối cùng, chỉ lấy phần cần thiết
                                remaining_height = total_height - current_y
                                img = img.crop((0, viewport_height - remaining_height, 
                                              img.width, viewport_height))
                                full_img.paste(img, (0, current_y + viewport_height - remaining_height))
                            else:
                                full_img.paste(img, (0, current_y))
                            
                            # Cập nhật vị trí hiện tại
                            current_y += scroll_amount
                            
                            # Nếu đã vượt quá kích thước, dừng lại
                            if current_y >= total_height:
                                break
                        
                        # Lưu ảnh đã ghép
                        full_img_path = os.path.join(thu_muc_ket_qua, "xac_nhan_don_hang_toan_bo.png")
                        full_img.save(full_img_path)
                        
                        # Thêm vào báo cáo
                        if reporter:
                            reporter.add_screenshot(full_img_path, "Toàn bộ trang xác nhận đơn hàng (đầy đủ)")
                        
                        # Xóa ảnh tạm
                        if os.path.exists(full_img_path):
                            os.remove(full_img_path)
                            
                except Exception as e:
                    print(f"Lỗi khi chụp toàn bộ trang: {str(e)}")
                    # Nếu có lỗi, thử chụp bình thường
                    chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "xac_nhan_don_hang_toan_bo.png"), 
                                reporter, "Toàn bộ trang xác nhận đơn hàng (chụp thông thường)")
                
                # Lấy thông tin đơn hàng nếu có
                thong_tin_don_hang = {}
                try:
                    # Thử lấy mã đơn hàng
                    ma_don_hang = driver.find_elements(By.XPATH, "//*[contains(text(), 'Mã đơn hàng') or contains(text(), 'Order number') or contains(text(), 'Mã đơn')]//following::*[1]")
                    if ma_don_hang:
                        thong_tin_don_hang['Mã đơn hàng'] = ma_don_hang[0].text.strip()
                    
                    # Lấy thời gian đặt hàng
                    thong_tin_don_hang['Thời gian đặt hàng'] = datetime.now().strftime('%d/%m/%Y %H:%M:%S')
                    
                    # Thử lấy tổng tiền
                    tong_tien_selectors = [
                        "//*[contains(text(), 'Tổng tiền') or contains(text(), 'Tổng cộng') or contains(text(), 'Total')]//following::*[1]",
                        "//*[contains(@class, 'total') or contains(@class, 'amount') or contains(@class, 'price')]"
                    ]
                    
                    for selector in tong_tien_selectors:
                        tong_tien = driver.find_elements(By.XPATH, selector)
                        if tong_tien and any(char.isdigit() for char in tong_tien[0].text):
                            thong_tin_don_hang['Tổng tiền'] = tong_tien[0].text.strip()
                            break
                    
                    # Thử lấy phương thức thanh toán
                    phuong_thuc = driver.find_elements(By.XPATH, "//*[contains(text(), 'Phương thức thanh toán') or contains(text(), 'Payment method')]//following::*[1]")
                    if phuong_thuc:
                        thong_tin_don_hang['Phương thức thanh toán'] = phuong_thuc[0].text.strip()
                    
                    # Thử lấy trạng thái đơn hàng
                    trang_thai = driver.find_elements(By.XPATH, "//*[contains(text(), 'Trạng thái') or contains(text(), 'Status')]//following::*[1]")
                    if trang_thai:
                        thong_tin_don_hang['Trạng thái đơn hàng'] = trang_thai[0].text.strip()
                    
                    # Lấy thông tin sản phẩm
                    san_pham = []
                    
                    # Thử tìm bảng sản phẩm
                    bang_san_pham = driver.find_elements(By.CSS_SELECTOR, "table.order-items, table.cart, .order-details")
                    if bang_san_pham:
                        # Nếu có bảng sản phẩm, lấy thông tin chi tiết
                        rows = bang_san_pham[0].find_elements(By.CSS_SELECTOR, "tr")
                        for row in rows[1:]:  # Bỏ qua dòng tiêu đề
                            try:
                                cells = row.find_elements(By.CSS_SELECTOR, "td")
                                if len(cells) >= 3:  # Có ít nhất 3 cột: tên, số lượng, đơn giá
                                    sp_info = {
                                        'ten': cells[0].text.strip(),
                                        'so_luong': cells[1].text.strip() if len(cells) > 1 else '1',
                                        'don_gia': cells[2].text.strip() if len(cells) > 2 else '',
                                        'thanh_tien': cells[3].text.strip() if len(cells) > 3 else ''
                                    }
                                    san_pham.append(sp_info)
                            except:
                                continue
                    else:
                        # Nếu không có bảng, thử lấy danh sách sản phẩm đơn giản
                        san_pham_elements = driver.find_elements(By.CSS_SELECTOR, ".order-item, .product-name, .cart-item, .item")
                        for i, sp in enumerate(san_pham_elements[:10], 1):  # Giới hạn 10 sản phẩm
                            san_pham.append(f"{i}. {sp.text[:200].strip()}")
                    
                    if san_pham:
                        thong_tin_don_hang['san_pham'] = san_pham
                    
                    # Cập nhật thông tin đơn hàng vào báo cáo
                    if reporter:
                        reporter.update_order_info(thong_tin_don_hang)
                    
                    # Chuyển đổi thành chuỗi để hiển thị trong log
                    thong_tin_text = "\n".join([f"{k}: {v}" for k, v in thong_tin_don_hang.items() 
                                              if k != 'san_pham'])
                    if 'san_pham' in thong_tin_don_hang:
                        thong_tin_text += "\n\nSản phẩm đã đặt:"
                        if isinstance(thong_tin_don_hang['san_pham'], list):
                            for i, sp in enumerate(thong_tin_don_hang['san_pham'], 1):
                                if isinstance(sp, dict):
                                    thong_tin_text += f"\n{i}. {sp.get('ten', '')} - Số lượng: {sp.get('so_luong', 1)} - Đơn giá: {sp.get('don_gia', '')}"
                                else:
                                    thong_tin_text += f"\n{i}. {str(sp)[:200]}"
                    
                    thong_tin_don_hang = thong_tin_text
                    
                except Exception as e:
                    thong_tin_don_hang = f"Không thể lấy đầy đủ thông tin đơn hàng: {str(e)}"
                
                # Chụp ảnh khu vực thông tin đơn hàng
                try:
                    # Thử tìm phần tử chứa thông tin đơn hàng với nhiều selector khác nhau
                    order_selectors = [
                        ".order-details", 
                        ".order-info", 
                        ".order-summary", 
                        ".checkout-success",
                        ".woocommerce-order",
                        ".checkout-confirmation",
                        "[id*='order']",
                        "[class*='confirmation']",
                        "[id*='confirmation']"
                    ]
                    
                    order_info = None
                    for selector in order_selectors:
                        try:
                            order_info = driver.find_element(By.CSS_SELECTOR, selector)
                            if order_info:
                                break
                        except:
                            continue
                    
                    if order_info:
                        # Lưu vị trí cuộn hiện tại
                        original_scroll = driver.execute_script("return window.pageYOffset;")
                        
                        # Làm nổi bật phần tử
                        highlight_script = """
                        var element = arguments[0];
                        var original_style = element.getAttribute('style');
                        element.style.border = '3px solid #4CAF50';
                        element.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
                        return original_style;
                        """
                        original_style = driver.execute_script(highlight_script, order_info)
                        
                        # Chờ một chút để hiệu ứng hiển thị
                        time.sleep(1)
                        
                        # Chụp toàn bộ phần tử (kể cả phần bị ẩn)
                        try:
                            # Lấy tọa độ và kích thước của phần tử
                            location = order_info.location
                            size = order_info.size
                            
                            # Chụp toàn bộ trang
                            screenshot = driver.get_screenshot_as_png()
                            img = PILImage.open(io.BytesIO(screenshot))
                            
                            # Tính toán vị trí cắt ảnh
                            left = location['x']
                            top = location['y']
                            right = location['x'] + size['width']
                            bottom = location['y'] + size['height']
                            
                            # Cắt ảnh theo kích thước phần tử
                            img = img.crop((left, top, right, bottom))
                            
                            # Lưu ảnh
                            img_path = os.path.join(thu_muc_ket_qua, "chi_tiet_don_hang.png")
                            img.save(img_path)
                            
                            # Thêm ảnh vào báo cáo
                            if reporter:
                                reporter.add_screenshot(img_path, "Chi tiết đơn hàng")
                                
                        except Exception as e:
                            print(f"Lỗi khi chụp ảnh chi tiết: {str(e)}")
                            # Nếu không chụp được chi tiết, chụp toàn màn hình
                            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "toan_bo_trang.png"),
                                        reporter, "Toàn bộ trang xác nhận đơn hàng")
                        
                        # Khôi phục style gốc
                        if original_style is not None:
                            driver.execute_script("arguments[0].setAttribute('style', arguments[1])", 
                                               order_info, original_style)
                        else:
                            driver.execute_script("arguments[0].removeAttribute('style')", order_info)
                        
                        # Cuộn về vị trí ban đầu
                        driver.execute_script(f"window.scrollTo(0, {original_scroll});")
                    
                    # Chụp thêm ảnh toàn màn hình để đảm bảo có ảnh trong mọi trường hợp
                    chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "xac_nhan_don_hang_toan_canh.png"),
                                reporter, "Toàn cảnh trang xác nhận đơn hàng")
                                
                except Exception as e:
                    print(f"Lỗi khi chụp ảnh chi tiết đơn hàng: {str(e)}")
                    # Chụp toàn màn hình nếu có lỗi
                    chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "xac_nhan_loi.png"),
                                reporter, "Màn hình lỗi khi xác nhận đơn hàng")
                
                in_thong_bao("Đặt hàng thành công!", "THÀNH CÔNG", reporter, thong_tin_don_hang)
                return True
                
            except Exception as e:
                # Kiểm tra xem có thông báo lỗi không
                try:
                    thong_bao_loi = driver.find_elements(By.CSS_SELECTOR, 
                        ".alert.alert-danger, .error-message, .text-danger, .alert-error, .has-error"
                    )
                    if thong_bao_loi:
                        loi_text = "\n".join([e.text for e in thong_bao_loi if e.text.strip()])
                        in_thong_bao(f"Lỗi khi đặt hàng: {loi_text}", "LỖI")
                        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "loi_dat_hang.png"))
                        return False
                except:
                    pass
                
                # Nếu không xác định được lỗi, chụp màn hình hiện tại
                chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "ket_qua_khong_ro_rang.png"))
                
                # Kiểm tra xem có phải vẫn đang ở trang thanh toán không
                if "thanh-toan" in driver.current_url or "gio-hang" in driver.current_url:
                    in_thong_bao("Có vẻ như đơn hàng chưa được xử lý xong", "CẢNH BÁO")
                else:
                    in_thong_bao("Không xác định được kết quả đặt hàng", "CẢNH BÁO")
                
                return False
                
        except Exception as e:
            in_thong_bao(f"Lỗi khi điền thông tin thanh toán: {str(e)}", "LỖI")
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "loi_thanh_toan.png"))
            return False
            
    except Exception as e:
        in_thong_bao(f"Lỗi khi thực hiện mua ngay: {str(e)}", "LỖI")
        return False

def main():
    # Tạo thư mục kết quả
    test_name = "mua_ngay_da_dang_nhap"
    thu_muc_ket_qua = tao_thu_muc_ket_qua(test_name)
    
    # Khởi tạo báo cáo
    reporter = TestReporter("Kiểm thử tính năng Mua ngay đã đăng nhập")
    in_thong_bao(f"Bắt đầu kiểm thử tính năng Mua ngay (đã đăng nhập)", reporter=reporter)
    in_thong_bao(f"Thư mục kết quả: {thu_muc_ket_qua}", reporter=reporter)
    
    # Tùy chọn cho Chrome
    options = webdriver.ChromeOptions()
    options.add_argument('--start-maximized')  # Mở cửa sổ lớn nhất
    options.add_experimental_option('excludeSwitches', ['enable-logging'])  # Tắt thông báo console
    
    # Khởi tạo trình duyệt
    in_thong_bao("Đang khởi tạo trình duyệt...")
    driver = None
    try:
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        in_thong_bao("Đã khởi tạo trình duyệt thành công", "OK", reporter)
    except Exception as e:
        in_thong_bao(f"Lỗi khi khởi tạo trình duyệt: {str(e)}", "LỖI", reporter)
        # Thử cách khác nếu cách trên thất bại
        try:
            driver = webdriver.Chrome(options=options)
            in_thong_bao("Đã khởi tạo trình duyệt thành công (dùng Chrome mặc định)", "OK", reporter)
        except Exception as e2:
            in_thong_bao(f"Không thể khởi tạo trình duyệt: {str(e2)}", "LỖI", reporter)
            reporter.save_report(thu_muc_ket_qua)
            return
    
    try:
        # Thông tin đăng nhập
        TAI_KHOAN = "quân"
        MAT_KHAU = "1"
        
        # Thực hiện đăng nhập
        in_thong_bao("Bắt đầu quá trình đăng nhập...", reporter=reporter)
        if not dang_nhap(driver, TAI_KHOAN, MAT_KHAU, thu_muc_ket_qua):
            in_thong_bao("Không thể đăng nhập, dừng kiểm thử", "LỖI", reporter)
            reporter.save_report(thu_muc_ket_qua)
            return
        
        # Quay lại trang chủ
        in_thong_bao("Đang quay lại trang chủ...", reporter=reporter)
        driver.get("http://localhost/webbansach/")
        in_thong_bao("Đã quay lại trang chủ", "OK", reporter)
        time.sleep(2)
        
        # Thực hiện mua ngay từ banner
        in_thong_bao("Bắt đầu quy trình mua ngay từ banner...", reporter=reporter)
        if not mua_ngay_tu_banner(driver, thu_muc_ket_qua):
            in_thong_bao("Có lỗi xảy ra khi thực hiện mua ngay", "LỖI", reporter)
        else:
            in_thong_bao("ĐÃ HOÀN THÀNH QUY TRÌNH MUA HÀNG THÀNH CÔNG!", "THÀNH CÔNG", reporter)
        
    except Exception as e:
        error_msg = f"Lỗi không xác định: {str(e)}"
        in_thong_bao(error_msg, "LỖI", reporter, error_msg)
        chup_man_hinh(driver, 
                     os.path.join(thu_muc_ket_qua, "loi_khong_xac_dinh.png"),
                     reporter,
                     "Lỗi không xác định")
    
    finally:
        # Lưu báo cáo
        report_path = reporter.save_report(thu_muc_ket_qua)
        
        # Kết thúc
        in_thong_bao("Hoàn thành kiểm thử", reporter=reporter)
        if report_path:
            in_thong_bao(f"Đã lưu báo cáo tại: {os.path.abspath(report_path)}", "OK", reporter)
        
        # Đóng trình duyệt
        if driver:
            driver.quit()

if __name__ == "__main__":
    print("="*80)
    print("KIỂM TRA TÍNH NĂNG MUA NGAY TỪ BANNER (ĐÃ ĐĂNG NHẬP)")
    print("="*80)
    main()
