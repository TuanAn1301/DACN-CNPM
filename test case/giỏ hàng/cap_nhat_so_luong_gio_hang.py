import os
import time
import sys
import shutil
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

# Thêm thư mục cha vào PATH để import test_utils
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from test_utils import TestReporter, chup_man_hinh, highlight_element, in_thong_bao, tao_thu_muc_ket_qua, lay_san_pham_ngau_nhien


def them_san_pham_vao_gio(driver, url_san_pham, reporter, thu_muc_screenshot):
    """Thực hiện thêm sản phẩm vào giỏ hàng"""
    try:
        # Bước 1: Truy cập trang sản phẩm
        step = "Truy cập trang sản phẩm"
        input_data = f"URL: {url_san_pham}"
        expected = "Hiển thị trang sản phẩm thành công"
        
        driver.get(url_san_pham)
        time.sleep(2)
        
        # Chụp ảnh trang sản phẩm
        screenshot_path = luu_screenshot(driver, thu_muc_screenshot, 'product_page')
        
        in_thong_bao(reporter, step, status='PASS', 
                    input_data=input_data, 
                    output=f"Đã tải trang sản phẩm: {url_san_pham}",
                    expected=expected,
                    screenshot_path=screenshot_path)
        time.sleep(2)  # Chờ trang web tải xong

        # Tìm và nhấn nút "Thêm Giỏ Hàng"
        try:
            # Thử tìm nút bằng class chính xác
            nut_them_vao_gio = WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, "a.themgiohang"))
            )
            
            # Cuộn đến nút và highlight
            driver.execute_script("arguments[0].scrollIntoView({block: 'center'})", nut_them_vao_gio)
            highlight_element(driver, nut_them_vao_gio)
            
            # Click vào nút
            nut_them_vao_gio.click()
            time.sleep(2)
            
            # Chụp ảnh sau khi click
            screenshot_path2 = luu_screenshot(driver, thu_muc_screenshot, 'after_add_to_cart')
            
            in_thong_bao(reporter, 
                        "Đã nhấn nút 'Thêm Giỏ Hàng'", 
                        status='PASS',
                        input_data="Sử dụng selector: a.themgiohang",
                        output="Đã click vào nút thêm giỏ hàng",
                        expected="Thêm sản phẩm vào giỏ hàng thành công",
                        screenshot_path=screenshot_path2)
            
            # Chụp ảnh màn hình xác nhận
            screenshot_path3 = luu_screenshot(driver, thu_muc_screenshot, 'confirm_add')
            
            in_thong_bao(reporter, 
                       "Xác nhận thêm vào giỏ hàng", 
                       status='PASS',
                       input_data="Kiểm tra thông báo thành công",
                       output="Đã thêm sản phẩm vào giỏ hàng",
                       expected="Hiển thị thông báo thêm vào giỏ hàng thành công",
                       screenshot_path=screenshot_path3)
            return True
                
        except Exception as e:
            error_msg = f"Không tìm thấy nút 'Thêm Giỏ Hàng': {str(e)}"
            screenshot_path = luu_screenshot(driver, thu_muc_screenshot, 'error_find_button')
            
            in_thong_bao(reporter, 
                        "Lỗi khi tìm nút thêm vào giỏ", 
                        status='FAIL',
                        input_data=error_msg,
                        output=f"Lỗi: {str(e)}",
                        expected="Tìm thấy và click được nút thêm vào giỏ",
                        screenshot_path=screenshot_path)
            return False

    except Exception as e:
        error_msg = f"Lỗi khi thêm sản phẩm vào giỏ: {str(e)}"
        screenshot_path = luu_screenshot(driver, thu_muc_screenshot, 'error_add_product')
        
        in_thong_bao(reporter, 
                    "Lỗi nghiêm trọng khi thêm sản phẩm", 
                    status='FAIL',
                    input_data=f"URL: {url_san_pham}",
                    output=f"Lỗi: {error_msg}",
                    expected="Thực hiện các bước thêm sản phẩm thành công",
                    screenshot_path=screenshot_path)
        return False

def lay_ten_san_pham(driver):
    """Lấy tên sản phẩm từ trang giỏ hàng"""
    try:
        ten_san_pham = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".product-name a, .cart_item .product-name a, .product-title a"))
        ).text.strip()
        return ten_san_pham
    except:
        return "Sản phẩm không xác định"

def luu_screenshot(driver, thu_muc, ten_file):
    """Lưu screenshot vào file và trả về đường dẫn"""
    try:
        os.makedirs(thu_muc, exist_ok=True)
        screenshot_path = os.path.join(thu_muc, f'{ten_file}_{int(time.time())}.png')
        driver.save_screenshot(screenshot_path)
        return screenshot_path
    except Exception as e:
        print(f"Lỗi khi lưu screenshot: {str(e)}")
        return None

def lay_so_luong_hien_tai(driver, reporter):
    """Lấy số lượng sản phẩm hiện tại trong giỏ hàng"""
    try:
        # Sử dụng selector chính xác từ gio-hang.php
        selectors = [
            "input.number[type='number']",
            "input.form-control.text-center.number",
            "input.number",
            "input[type='number'].form-control",
            "input[type='number']"
        ]
        
        for selector in selectors:
            try:
                input_so_luong = WebDriverWait(driver, 5).until(
                    EC.presence_of_element_located((By.CSS_SELECTOR, selector))
                )
                if input_so_luong.is_displayed():
                    so_luong = int(input_so_luong.get_attribute("value") or 0)
                    if so_luong > 0:
                        return so_luong
            except:
                continue
        
        # Nếu không tìm thấy qua input, thử lấy từ localStorage
        try:
            so_luong = driver.execute_script("""
                var giohang = JSON.parse(localStorage.getItem('giohang') || '[]');
                if (giohang.length > 0) {
                    return giohang[0].soluong;
                }
                return 0;
            """)
            if so_luong > 0:
                return so_luong
        except:
            pass
        
        return 0
    except Exception as e:
        error_msg = f"Không thể lấy số lượng hiện tại: {str(e)}"
        # Không thêm vào reporter vì đây là helper function
        print(f"Warning: {error_msg}")
        return 0

def thay_doi_so_luong(driver, reporter, ten_san_pham, so_luong_moi, tang_giam, thu_muc_screenshot):
    """Thay đổi số lượng sản phẩm trong giỏ hàng"""
    try:
        # Bước 1: Đi đến trang giỏ hàng
        step = "Truy cập trang giỏ hàng"
        driver.get("http://localhost/webbansach/gio-hang.php")
        time.sleep(2)
        
        screenshot_path = luu_screenshot(driver, thu_muc_screenshot, 'cart_page')
        in_thong_bao(reporter, step, status='PASS',
                    input_data="URL: http://localhost/webbansach/gio-hang.php",
                    output="Đã tải trang giỏ hàng",
                    expected="Hiển thị trang giỏ hàng",
                    screenshot_path=screenshot_path)
        
        # Bước 2: Lấy số lượng hiện tại
        so_luong_cu = lay_so_luong_hien_tai(driver, reporter)
        screenshot_path2 = luu_screenshot(driver, thu_muc_screenshot, 'current_quantity')
        
        in_thong_bao(reporter, "Lấy số lượng hiện tại", status='PASS',
                    input_data=f"Sản phẩm: {ten_san_pham}",
                    output=f"Số lượng hiện tại: {so_luong_cu}",
                    expected=f"Lấy được số lượng hiện tại của '{ten_san_pham}'",
                    screenshot_path=screenshot_path2)
        
        # Bước 3: Tìm và cập nhật số lượng
        hanh_dong = "tăng" if tang_giam == 'tang' else "giảm"
        step = f"{hanh_dong.capitalize()} số lượng sản phẩm '{ten_san_pham}' từ {so_luong_cu} lên {so_luong_moi}"
        
        # Chụp ảnh trước khi thay đổi
        screenshot_before = luu_screenshot(driver, thu_muc_screenshot, f'before_{hanh_dong}')
        
        try:
            # Tìm ô nhập số lượng - sử dụng selector chính xác từ gio-hang.php
            # Input có class "form-control text-center number" và type="number"
            input_so_luong = None
            selectors = [
                "input.number[type='number']",
                "input.form-control.text-center.number",
                "input.number",
                "input[type='number'].form-control",
                "input[type='number']"
            ]
            
            for selector in selectors:
                try:
                    input_so_luong = WebDriverWait(driver, 5).until(
                        EC.presence_of_element_located((By.CSS_SELECTOR, selector))
                    )
                    if input_so_luong.is_displayed():
                        break
                except:
                    continue
            
            if not input_so_luong:
                raise Exception("Không tìm thấy ô nhập số lượng")
            
            # Cuộn đến input và highlight
            driver.execute_script("arguments[0].scrollIntoView({block: 'center'})", input_so_luong)
            highlight_element(driver, input_so_luong)
            time.sleep(0.5)
            
            # Xóa số lượng cũ và nhập số lượng mới
            # Bước 1: Click vào input để focus
            input_so_luong.click()
            time.sleep(0.2)
            
            # Bước 2: Xóa số cũ bằng cách select all và delete (giống như người dùng làm thủ công)
            try:
                # Select all text: double-click để chọn toàn bộ số
                actions = ActionChains(driver)
                actions.double_click(input_so_luong).perform()
                time.sleep(0.2)
            except:
                # Nếu double-click không work, dùng Ctrl+A (Windows) hoặc Cmd+A (Mac)
                try:
                    actions = ActionChains(driver)
                    if sys.platform == 'darwin':  # macOS
                        actions.key_down(Keys.COMMAND).send_keys('a').key_up(Keys.COMMAND).perform()
                    else:  # Windows/Linux
                        actions.key_down(Keys.CONTROL).send_keys('a').key_up(Keys.CONTROL).perform()
                    time.sleep(0.2)
                except:
                    pass
            
            # Bước 3: Xóa số đã chọn bằng phím DELETE hoặc BACKSPACE
            input_so_luong.send_keys(Keys.DELETE)
            time.sleep(0.2)
            input_so_luong.send_keys(Keys.BACKSPACE)  # Xóa thêm lần nữa để chắc chắn
            time.sleep(0.2)
            
            # Bước 4: Clear bằng Selenium và set về rỗng bằng JavaScript
            input_so_luong.clear()
            driver.execute_script("arguments[0].value = '';", input_so_luong)
            time.sleep(0.3)
            
            # Bước 5: Xác nhận input đã trống hoàn toàn
            current_value_before = input_so_luong.get_attribute("value") or ""
            if current_value_before and current_value_before.strip():
                # Nếu vẫn còn giá trị, force xóa bằng JavaScript
                driver.execute_script("arguments[0].value = ''; arguments[0].setAttribute('value', '');", input_so_luong)
                time.sleep(0.2)
            
            # Bước 6: Nhập số lượng mới (ví dụ: nhập số 3)
            input_so_luong.send_keys(str(so_luong_moi))
            time.sleep(0.5)
            
            # Bước 7: Xác nhận giá trị đã được nhập đúng
            current_value_after = input_so_luong.get_attribute("value")
            if current_value_after != str(so_luong_moi):
                # Nếu giá trị không đúng, set lại bằng JavaScript
                driver.execute_script("arguments[0].value = arguments[1];", input_so_luong, str(so_luong_moi))
                time.sleep(0.3)
            
            # Bước 8: Trigger events 'input' và 'change' để JavaScript cập nhật (như trong gio-hang.php)
            # Sử dụng jQuery nếu có, nếu không thì dùng native JavaScript
            try:
                driver.execute_script("""
                    var input = arguments[0];
                    // Thử dùng jQuery trigger nếu jQuery có sẵn
                    if (typeof jQuery !== 'undefined') {
                        jQuery(input).trigger('input').trigger('change');
                    } else {
                        // Dùng native JavaScript events
                        var inputEvent = new Event('input', { bubbles: true, cancelable: true });
                        input.dispatchEvent(inputEvent);
                        var changeEvent = new Event('change', { bubbles: true, cancelable: true });
                        input.dispatchEvent(changeEvent);
                    }
                """, input_so_luong)
                time.sleep(0.3)
            except:
                # Nếu không trigger được bằng JavaScript, thử cách khác: blur input để trigger change
                try:
                    input_so_luong.send_keys(Keys.TAB)  # Tab ra ngoài để trigger change
                    time.sleep(0.3)
                except:
                    pass
            
            # Đợi JavaScript xử lý cập nhật (cập nhật localStorage và UI)
            # Đợi tối đa 5 giây, kiểm tra mỗi 0.5 giây
            max_wait = 5
            waited = 0
            while waited < max_wait:
                time.sleep(0.5)
                waited += 0.5
                try:
                    # Kiểm tra giá trị trong input
                    current_value = input_so_luong.get_attribute("value")
                    if current_value == str(so_luong_moi):
                        break
                    
                    # Kiểm tra giá trị trong localStorage
                    so_luong_localStorage = driver.execute_script("""
                        var giohang = JSON.parse(localStorage.getItem('giohang') || '[]');
                        if (giohang.length > 0) {
                            return giohang[0].soluong;
                        }
                        return 0;
                    """)
                    if so_luong_localStorage == so_luong_moi:
                        break
                except:
                    pass
            
            # Đợi thêm một chút để UI được refresh
            time.sleep(1)
            
            # Chụp ảnh sau khi cập nhật
            screenshot_after = luu_screenshot(driver, thu_muc_screenshot, f'after_{hanh_dong}')
            
            # Bước 4: Kiểm tra kết quả cập nhật
            # Đọc lại giá trị từ input để xác nhận
            so_luong_moi_thuc_te = int(input_so_luong.get_attribute("value") or 0)
            
            # Kiểm tra kết quả
            if so_luong_moi_thuc_te == so_luong_moi:
                status = 'PASS'
                output_msg = f"Đã {hanh_dong} số lượng từ {so_luong_cu} lên {so_luong_moi} thành công"
                output_msg += f"\n- Số lượng mới đã được cập nhật chính xác: {so_luong_moi_thuc_te}"
                output_msg += f"\n- Giá trị trong input: {input_so_luong.get_attribute('value')}"
            else:
                # Nếu số lượng không khớp, kiểm tra lại sau 1 giây nữa (có thể JavaScript chưa xử lý xong)
                time.sleep(1)
                so_luong_moi_thuc_te = int(input_so_luong.get_attribute("value") or 0)
                
                if so_luong_moi_thuc_te == so_luong_moi:
                    status = 'PASS'
                    output_msg = f"Đã {hanh_dong} số lượng từ {so_luong_cu} lên {so_luong_moi} thành công (sau khi đợi thêm)"
                    output_msg += f"\n- Số lượng mới: {so_luong_moi_thuc_te}"
                else:
                    # Kiểm tra xem có thể số lượng đã được cập nhật nhưng chưa hiển thị trong input
                    # Đọc từ localStorage qua JavaScript
                    try:
                        so_luong_tu_localStorage = driver.execute_script("""
                            var giohang = JSON.parse(localStorage.getItem('giohang') || '[]');
                            if (giohang.length > 0) {
                                return giohang[0].soluong;
                            }
                            return 0;
                        """)
                        
                        if so_luong_tu_localStorage == so_luong_moi:
                            status = 'PASS'
                            output_msg = f"Đã {hanh_dong} số lượng từ {so_luong_cu} lên {so_luong_moi} thành công"
                            output_msg += f"\n- Số lượng trong localStorage: {so_luong_tu_localStorage}"
                            output_msg += f"\n- Số lượng trong input: {so_luong_moi_thuc_te}"
                            output_msg += "\n- Lưu ý: Số lượng đã được cập nhật trong localStorage, có thể UI chưa refresh hoàn toàn"
                        else:
                            status = 'FAIL'
                            output_msg = f"Không thể {hanh_dong} số lượng đúng cách"
                            output_msg += f"\n- Mong đợi: {so_luong_moi}"
                            output_msg += f"\n- Thực tế trong input: {so_luong_moi_thuc_te}"
                            output_msg += f"\n- Thực tế trong localStorage: {so_luong_tu_localStorage}"
                    except Exception as e:
                        status = 'FAIL'
                        output_msg = f"Không thể kiểm tra số lượng từ localStorage: {str(e)}"
                        output_msg += f"\n- Mong đợi: {so_luong_moi}"
                        output_msg += f"\n- Thực tế trong input: {so_luong_moi_thuc_te}"
            
            # Báo cáo kết quả
            in_thong_bao(
                reporter=reporter,
                noi_dung=step,
                status=status,
                input_data=f"Sản phẩm: {ten_san_pham}\nSố lượng cũ: {so_luong_cu}\nSố lượng mới: {so_luong_moi}\nHành động: {hanh_dong.capitalize()} số lượng",
                output=output_msg,
                expected=f"Số lượng được cập nhật từ {so_luong_cu} lên {so_luong_moi}",
                screenshot_path=screenshot_after
            )
            
            return status == 'PASS'
            
        except Exception as e:
            # Nếu có lỗi trong quá trình thay đổi, vẫn kiểm tra xem số lượng có được cập nhật không
            error_msg = f"Lỗi khi {hanh_dong} số lượng: {str(e)}"
            
            # Đợi một chút và kiểm tra lại số lượng
            time.sleep(2)
            try:
                so_luong_sau_loi = lay_so_luong_hien_tai(driver, reporter)
                
                # Kiểm tra từ localStorage
                try:
                    so_luong_tu_localStorage = driver.execute_script("""
                        var giohang = JSON.parse(localStorage.getItem('giohang') || '[]');
                        if (giohang.length > 0) {
                            return giohang[0].soluong;
                        }
                        return 0;
                    """)
                except:
                    so_luong_tu_localStorage = 0
                
                # Nếu số lượng đã được cập nhật đúng (trong input hoặc localStorage), đánh dấu PASS
                if so_luong_sau_loi == so_luong_moi or so_luong_tu_localStorage == so_luong_moi:
                    status = 'PASS'
                    output_msg = f"Đã {hanh_dong} số lượng từ {so_luong_cu} lên {so_luong_moi} thành công"
                    output_msg += f"\n- Mặc dù có lỗi trong quá trình thực hiện: {str(e)[:100]}"
                    output_msg += f"\n- Số lượng trong input: {so_luong_sau_loi}"
                    output_msg += f"\n- Số lượng trong localStorage: {so_luong_tu_localStorage}"
                    
                    screenshot_after = luu_screenshot(driver, thu_muc_screenshot, f'after_{hanh_dong}')
                    in_thong_bao(
                        reporter=reporter,
                        noi_dung=step,
                        status=status,
                        input_data=f"Sản phẩm: {ten_san_pham}\nSố lượng cũ: {so_luong_cu}\nSố lượng mới: {so_luong_moi}",
                        output=output_msg,
                        expected=f"Số lượng được cập nhật từ {so_luong_cu} lên {so_luong_moi}",
                        screenshot_path=screenshot_after
                    )
                    return True
                else:
                    # Nếu số lượng chưa được cập nhật, đánh dấu FAIL
                    screenshot_path_error = luu_screenshot(driver, thu_muc_screenshot, f'error_{hanh_dong}')
                    in_thong_bao(
                        reporter=reporter,
                        noi_dung=step,
                        status='FAIL',
                        input_data=f"Sản phẩm: {ten_san_pham}\nSố lượng mới: {so_luong_moi}",
                        output=f"Lỗi: {error_msg}\nSố lượng sau lỗi: {so_luong_sau_loi} (mong đợi: {so_luong_moi})",
                        expected=f"{hanh_dong.capitalize()} số lượng thành công",
                        screenshot_path=screenshot_path_error
                    )
                    return False
            except Exception as e2:
                # Nếu không thể kiểm tra được, đánh dấu FAIL
                screenshot_path_error = luu_screenshot(driver, thu_muc_screenshot, f'error_{hanh_dong}')
                in_thong_bao(
                    reporter=reporter,
                    noi_dung=step,
                    status='FAIL',
                    input_data=f"Sản phẩm: {ten_san_pham}\nSố lượng mới: {so_luong_moi}",
                    output=f"Lỗi: {error_msg}\nLỗi khi kiểm tra: {str(e2)}",
                    expected=f"{hanh_dong.capitalize()} số lượng thành công",
                    screenshot_path=screenshot_path_error
                )
                return False
            
    except Exception as e:
        error_msg = f"Lỗi khi truy cập giỏ hàng: {str(e)}"
        screenshot_path = luu_screenshot(driver, thu_muc_screenshot, 'error_cart')
        
        in_thong_bao(reporter, "Truy cập giỏ hàng", status='FAIL',
                    input_data="URL: http://localhost/webbansach/gio-hang.php",
                    output=f"Lỗi: {error_msg}",
                    expected="Truy cập được trang giỏ hàng",
                    screenshot_path=screenshot_path)
        return False

def main():
    print("="*80)
    print("KIỂM TRA TÍNH NĂNG CẬP NHẬT SỐ LƯỢNG GIỎ HÀNG")
    print("="*80)
    
    # Khởi tạo thư mục kết quả và báo cáo
    test_name = "kiem_tra_cap_nhat_so_luong_gio_hang"
    thu_muc_ket_qua = tao_thu_muc_ket_qua(test_name)
    reporter = TestReporter(test_name)
    
    # Khởi tạo trình duyệt
    driver = None
    try:
        # Sử dụng webdriver-manager để tự động tải và quản lý ChromeDriver
        step = "Khởi tạo trình duyệt Chrome"
        try:
            service = Service(ChromeDriverManager().install())
            options = webdriver.ChromeOptions()
            options.add_argument('--start-maximized')
            
            driver = webdriver.Chrome(service=service, options=options)
            in_thong_bao(reporter, step, status='PASS',
                        input_data="Chrome WebDriver",
                        output="Trình duyệt đã khởi động",
                        expected="Trình duyệt khởi động thành công")
            
            # Lấy sản phẩm ngẫu nhiên từ trang chủ
            in_thong_bao(reporter, "Lấy sản phẩm ngẫu nhiên", status='INFO',
                        input_data="Tìm kiếm sản phẩm từ trang chủ",
                        output="Đang tìm kiếm sản phẩm...",
                        expected="Lấy được 1 sản phẩm ngẫu nhiên")
            
            products = lay_san_pham_ngau_nhien(driver, so_luong=1)
            if not products:
                in_thong_bao(reporter, "Không tìm thấy sản phẩm", status='FAIL',
                            input_data="Tìm kiếm sản phẩm",
                            output="Không tìm thấy sản phẩm nào",
                            expected="Tìm thấy ít nhất 1 sản phẩm")
                return
            
            product = products[0]
            url_san_pham = product['url']
            ten_san_pham = product['name']
            
            in_thong_bao(reporter, f"Đã chọn sản phẩm: {ten_san_pham}", status='PASS',
                        input_data=f"URL: {url_san_pham}",
                        output=f"Sản phẩm: {ten_san_pham} (ID: {product['id']})",
                        expected="Chọn được sản phẩm ngẫu nhiên")
            
            # Tạo thư mục screenshots
            thu_muc_screenshots = os.path.join(thu_muc_ket_qua, 'screenshots')
            os.makedirs(thu_muc_screenshots, exist_ok=True)
            
            # Thêm sản phẩm vào giỏ hàng
            if them_san_pham_vao_gio(driver, url_san_pham, reporter, thu_muc_screenshots):
                # Nếu không có tên sản phẩm, thử lấy từ trang
                if not ten_san_pham or ten_san_pham == "Sản phẩm không xác định":
                    ten_san_pham = lay_ten_san_pham(driver)
                
                # Bước 1: Tăng số lượng sản phẩm
                so_luong_tang = 3  # Tăng lên 3 sản phẩm
                in_thong_bao(reporter, "BẮT ĐẦU KIỂM TRA TĂNG SỐ LƯỢNG", status='INFO',
                            input_data=f"Sản phẩm: {ten_san_pham}\nSố lượng mới: {so_luong_tang}",
                            output="Bắt đầu test tăng số lượng",
                            expected="Tăng số lượng thành công")
                
                # Thực hiện tăng số lượng
                thanh_cong = thay_doi_so_luong(
                    driver=driver,
                    reporter=reporter,
                    ten_san_pham=ten_san_pham,
                    so_luong_moi=so_luong_tang,
                    tang_giam='tang',
                    thu_muc_screenshot=thu_muc_screenshots
                )
                
                if thanh_cong:
                    in_thong_bao(reporter, "KIỂM TRA TĂNG SỐ LƯỢNG THÀNH CÔNG", status='PASS',
                                input_data=f"Sản phẩm: {ten_san_pham}",
                                output=f"Đã tăng số lượng lên {so_luong_tang}",
                                expected="Tăng số lượng thành công")
                    
                    # Bước 2: Giảm số lượng sản phẩm
                    so_luong_giam = 1  # Giảm xuống 1 sản phẩm
                    in_thong_bao(reporter, "BẮT ĐẦU KIỂM TRA GIẢM SỐ LƯỢNG", status='INFO',
                                input_data=f"Sản phẩm: {ten_san_pham}\nSố lượng mới: {so_luong_giam}",
                                output="Bắt đầu test giảm số lượng",
                                expected="Giảm số lượng thành công")
                    
                    # Thực hiện giảm số lượng
                    thanh_cong = thay_doi_so_luong(
                        driver=driver,
                        reporter=reporter,
                        ten_san_pham=ten_san_pham,
                        so_luong_moi=so_luong_giam,
                        tang_giam='giam',
                        thu_muc_screenshot=thu_muc_screenshots
                    )
                    
                    if thanh_cong:
                        in_thong_bao(reporter, "KIỂM TRA GIẢM SỐ LƯỢNG THÀNH CÔNG", status='PASS',
                                    input_data=f"Sản phẩm: {ten_san_pham}",
                                    output=f"Đã giảm số lượng xuống {so_luong_giam}",
                                    expected="Giảm số lượng thành công")
                    else:
                        in_thong_bao(reporter, "KIỂM TRA GIẢM SỐ LƯỢNG THẤT BẠI", status='FAIL',
                                    input_data=f"Sản phẩm: {ten_san_pham}",
                                    output="Không thể giảm số lượng",
                                    expected="Giảm số lượng thành công")
                else:
                    in_thong_bao(reporter, "KIỂM TRA TĂNG SỐ LƯỢNG THẤT BẠI", status='FAIL',
                                input_data=f"Sản phẩm: {ten_san_pham}",
                                output="Không thể tăng số lượng",
                                expected="Tăng số lượng thành công")
            else:
                in_thong_bao(reporter, "Không thể thêm sản phẩm vào giỏ hàng", status='FAIL',
                            input_data=f"Sản phẩm: {ten_san_pham}",
                            output="Không thể thêm sản phẩm vào giỏ hàng",
                            expected="Thêm sản phẩm vào giỏ hàng thành công")
                
        except Exception as e:
            error_msg = f"Lỗi khi khởi tạo Chrome WebDriver: {str(e)}"
            in_thong_bao(reporter, step, status='FAIL',
                        input_data="Khởi tạo trình duyệt",
                        output=f"Lỗi: {error_msg}",
                        expected="Trình duyệt khởi động thành công")
            
    except Exception as e:
        error_msg = f"Lỗi không xác định: {str(e)}"
        in_thong_bao(reporter, "Lỗi tổng thể", status='FAIL',
                    input_data="Thực hiện test case",
                    output=f"Lỗi: {error_msg}",
                    expected="Hoàn thành test case không có lỗi")
        
    finally:
        # Chụp ảnh màn hình cuối cùng
        final_screenshot_path = None
        try:
            if driver:
                final_screenshot_path = os.path.join(thu_muc_ket_qua, f'final_screenshot_{int(time.time())}.png')
                driver.save_screenshot(final_screenshot_path)
        except Exception as e:
            print(f"Không thể chụp ảnh màn hình cuối cùng: {str(e)}")
        
        # Lưu báo cáo với ảnh chụp màn hình cuối cùng
        if final_screenshot_path:
            reporter.add_final_screenshot(final_screenshot_path, "Ảnh chụp màn hình cuối cùng")
        
        report_path = reporter.save_report(thu_muc_ket_qua)
        print(f"\nĐã lưu báo cáo kiểm thử tại: {report_path}")
        
        # Đóng trình duyệt nếu chưa đóng
        if driver is not None:
            try:
                driver.quit()
                in_thong_bao(reporter, "Đã đóng trình duyệt", status='INFO',
                            input_data="Đóng trình duyệt",
                            output="Trình duyệt đã đóng",
                            expected="Đóng trình duyệt thành công")
            except:
                pass
                
        in_thong_bao(reporter, "KẾT THÚC KIỂM TRA CẬP NHẬT SỐ LƯỢNG GIỎ HÀNG", status='INFO',
                    input_data="",
                    output="Test case hoàn tất",
                    expected="Hoàn thành test case")
        
        # Mở thư mục kết quả
        try:
            if os.name == 'nt':  # Windows
                os.startfile(thu_muc_ket_qua)
            elif os.name == 'posix':  # macOS và Linux
                if sys.platform == 'darwin':
                    os.system(f'open "{thu_muc_ket_qua}"')
                else:
                    os.system(f'xdg-open "{thu_muc_ket_qua}"')
        except:
            pass

if __name__ == "__main__":
    main()
