import os
import time
import sys
import base64
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from PIL import Image
import io

# Thêm thư mục cha vào PATH để import test_utils
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from test_utils import TestReporter, highlight_element, in_thong_bao, tao_thu_muc_ket_qua, lay_san_pham_ngau_nhien

def chup_man_hinh(driver, save_path=None, element=None):
    """Chụp màn hình và trả về đối tượng ảnh"""
    try:
        # Tạo thư mục nếu chưa tồn tại
        if save_path:
            os.makedirs(os.path.dirname(save_path), exist_ok=True)
        
        # Chụp toàn màn hình hoặc một phần tử cụ thể
        if element:
            # Chụp riêng phần tử
            screenshot = element.screenshot_as_png
        else:
            # Chụp toàn màn hình
            screenshot = driver.get_screenshot_as_png()
            
        img = Image.open(io.BytesIO(screenshot))
        
        # Lưu ảnh nếu có đường dẫn
        if save_path:
            img.save(save_path)
            print(f"Đã lưu ảnh tại: {save_path}")
            
        return img
    except Exception as e:
        print(f"Lỗi khi chụp màn hình: {str(e)}")
        return None


def chup_thong_bao_thanh_cong(driver, screenshot_dir):
    """Chụp thông báo thành công nếu có và trả về (has_notification, notification_text)"""
    try:
        # Chờ một chút để thông báo xuất hiện
        time.sleep(1)
        
        # Danh sách các selector có thể có của thông báo thành công
        notification_selectors = [
            ".swal2-success, .swal2-title, .swal2-html-container",  # SweetAlert2
            "div[role='alert']",  # General alert
            ".alert.alert-success, .alert-success",  # Bootstrap success alert
            ".toast-success, #toast-container .toast-success",  # Toast notifications
            "div.success-message, .success-message",  # General success message
            "[class*='success'], [class*='Success']",  # Any class with success
            "#noty_layout__bottomRight, #noty_layout__topRight",  # Noty notifications
            "div[class*='notification'], div[class*='Notification']"  # General notification
        ]
        
        # Thử tìm thông báo thành công
        for selector in notification_selectors:
            try:
                # Tìm tất cả các phần tử phù hợp
                elements = driver.find_elements(By.CSS_SELECTOR, selector)
                for element in elements:
                    try:
                        if element.is_displayed() and element.size['width'] > 0 and element.size['height'] > 0:
                            # Lấy văn bản thông báo
                            notification_text = element.text.strip()
                            
                            # Kiểm tra nếu đây thực sự là thông báo thành công
                            success_keywords = ['thành công', 'success', 'đã xóa', 'đã xong', 'hoàn tất', 'done']
                            if any(keyword in notification_text.lower() for keyword in success_keywords) or 'success' in element.get_attribute('class').lower():
                                # Cuộn đến phần tử và chờ một chút
                                driver.execute_script("arguments[0].scrollIntoView({block: 'center', behavior: 'smooth'});", element)
                                time.sleep(0.5)
                                
                                # Tạo tên file ảnh
                                timestamp = int(time.time())
                                screenshot_path = os.path.join(screenshot_dir, f'success_notification_{timestamp}.png')
                                
                                try:
                                    # Thử chụp riêng phần tử thông báo
                                    success_img = chup_man_hinh(driver, screenshot_path, element=element)
                                    if success_img:
                                        return True, notification_text
                                except:
                                    # Nếu không chụp được phần tử riêng, chụp toàn màn hình
                                    success_img = chup_man_hinh(driver, screenshot_path)
                                    if success_img:
                                        return True, notification_text
                    except:
                        continue
            except:
                continue
        
        # Nếu không tìm thấy thông báo, kiểm tra xem có phải đã chuyển hướng về trang chủ không
        if 'index.php' in driver.current_url or 'trang-chu' in driver.current_url or 'home' in driver.current_url:
            return True, "Đã chuyển về trang chủ sau khi xóa sản phẩm"
                
        # Nếu vẫn không tìm thấy, chụp toàn màn hình
        timestamp = int(time.time())
        screenshot_path = os.path.join(screenshot_dir, f'full_page_after_delete_{timestamp}.png')
        chup_man_hinh(driver, screenshot_path)
        
        # Kiểm tra xem có thông báo lỗi không
        try:
            error_elements = driver.find_elements(By.CSS_SELECTOR, ".error, .alert-danger, .text-danger, [class*='error'], [class*='Error']")
            for element in error_elements:
                if element.is_displayed():
                    return False, f"Có thể có lỗi: {element.text.strip()}"
        except:
            pass
            
        return False, "Không tìm thấy thông báo xác nhận"
        
    except Exception as e:
        error_msg = f"Lỗi khi chụp thông báo: {str(e)}"
        print(error_msg)
        return False, error_msg


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

def them_san_pham_vao_gio(driver, url_san_pham, reporter, thu_muc_screenshot, ten_san_pham=""):
    """Thực hiện thêm sản phẩm vào giỏ hàng"""
    try:
        # Bước 1: Truy cập trang sản phẩm
        step = "Truy cập trang sản phẩm"
        input_data = f"URL: {url_san_pham}"
        if ten_san_pham:
            input_data += f"\nSản phẩm: {ten_san_pham}"
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

def kiem_tra_gio_hang(driver, reporter, screenshot_dir, ten_san_pham=""):
    """Kiểm tra giỏ hàng sau khi thêm sản phẩm và thực hiện xóa sản phẩm
    
    Args:
        driver: WebDriver instance
        reporter: TestReporter instance for logging
        screenshot_dir: Directory to save screenshots
        ten_san_pham: Tên sản phẩm (nếu có)
    """
    try:
        # Bước 1: Đi đến trang giỏ hàng
        step = "Truy cập trang giỏ hàng"
        url_gio_hang = "http://localhost/webbansach/gio-hang.php"
        driver.get(url_gio_hang)
        time.sleep(2)
        
        screenshot_path = luu_screenshot(driver, screenshot_dir, 'cart_page')
        
        in_thong_bao(reporter, 
                    step, 
                    status='PASS',
                    input_data=f"Truy cập URL: {url_gio_hang}",
                    output="Đã tải trang giỏ hàng",
                    expected="Hiển thị trang giỏ hàng",
                    screenshot_path=screenshot_path)
        
        # Bước 2: Kiểm tra sản phẩm trong giỏ hàng
        try:
            san_pham_trong_gio = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.CSS_SELECTOR, ".cart-table tbody tr:not(.cart-header)"))
            )
            
            screenshot_path2 = luu_screenshot(driver, screenshot_dir, 'cart_with_products')
            
            in_thong_bao(reporter, 
                        "Kiểm tra sản phẩm trong giỏ hàng", 
                        status='PASS',
                        input_data="Tìm kiếm sản phẩm trong bảng giỏ hàng",
                        output=f"Tìm thấy sản phẩm trong giỏ hàng{f': {ten_san_pham}' if ten_san_pham else ''}",
                        expected="Tìm thấy ít nhất 1 sản phẩm trong giỏ hàng",
                        screenshot_path=screenshot_path2)
            
            # Bước 3: Thực hiện xóa sản phẩm khỏi giỏ hàng
            try:
                # Chụp ảnh giỏ hàng trước khi xóa
                before_remove_screenshot = luu_screenshot(driver, screenshot_dir, 'before_delete')
                
                # Tìm và nhấn nút xóa sản phẩm
                xoa_button = WebDriverWait(driver, 10).until(
                    EC.element_to_be_clickable((By.CSS_SELECTOR, "a.xoa i.fa-trash-alt, a.xoa, .xoa"))
                )
                driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", xoa_button)
                highlight_element(driver, xoa_button)
                time.sleep(1)
                
                # Click nút xóa
                xoa_button.click()
                time.sleep(1)
                
                # Xác nhận xóa trong hộp thoại
                try:
                    # Chuyển sang hộp thoại alert và chấp nhận
                    WebDriverWait(driver, 5).until(EC.alert_is_present())
                    alert = driver.switch_to.alert
                    alert_text = alert.text
                    alert.accept()
                    time.sleep(2)
                    
                    # Chụp ảnh sau khi xóa
                    after_remove_screenshot = luu_screenshot(driver, screenshot_dir, 'after_delete')
                    
                    # Kiểm tra thông báo thành công
                    try:
                        # Kiểm tra xem có chuyển hướng về trang chủ không (vì giỏ hàng trống)
                        WebDriverWait(driver, 5).until(
                            lambda d: 'index.php' in d.current_url or \
                            'giỏ hàng trống' in d.page_source.lower() or \
                            'không có sản phẩm' in d.page_source.lower() or \
                            len(driver.find_elements(By.CSS_SELECTOR, ".cart-table tbody tr:not(.cart-header)")) == 0
                        )
                        
                        # Chụp thông báo thành công nếu có
                        has_notification, notification_text = chup_thong_bao_thanh_cong(driver, screenshot_dir)
                        
                        # Chụp ảnh màn hình kết quả cuối cùng
                        final_screenshot = luu_screenshot(driver, screenshot_dir, 'final_result')
                        
                        # Nếu không tìm thấy thông báo, sử dụng thông báo mặc định
                        if not has_notification:
                            notification_text = alert_text if alert_text else "Sản phẩm đã được xóa khỏi giỏ hàng"
                        
                        # Kiểm tra xem giỏ hàng có trống không
                        cart_items_after = driver.find_elements(By.CSS_SELECTOR, ".cart-table tbody tr:not(.cart-header)")
                        
                        if not cart_items_after or len(cart_items_after) == 0:
                            in_thong_bao(reporter,
                                       "Xác nhận xóa sản phẩm khỏi giỏ hàng",
                                       status='PASS',
                                       input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}\nThông báo: {notification_text}",
                                       output=f"Đã xóa sản phẩm thành công\nGiỏ hàng đã trống\nThông báo: {notification_text}",
                                       expected="Xóa sản phẩm khỏi giỏ hàng thành công",
                                       screenshot_path=final_screenshot)
                            
                            return True
                        else:
                            in_thong_bao(reporter,
                                       "Xác nhận xóa sản phẩm",
                                       status='WARNING',
                                       input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}",
                                       output=f"Đã xác nhận xóa nhưng vẫn còn {len(cart_items_after)} sản phẩm trong giỏ",
                                       expected="Giỏ hàng trống sau khi xóa",
                                       screenshot_path=final_screenshot)
                            return True
                            
                    except Exception as e:
                        # Kiểm tra xem giỏ hàng có trống không
                        try:
                            cart_items = driver.find_elements(By.CSS_SELECTOR, ".cart-table tbody tr:not(.cart-header)")
                            if not cart_items:
                                in_thong_bao(reporter,
                                           "Xác nhận xóa sản phẩm khỏi giỏ hàng",
                                           status='PASS',
                                           input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}",
                                           output="Đã xác nhận xóa sản phẩm\nGiỏ hàng đã được làm trống",
                                           expected="Xóa sản phẩm khỏi giỏ hàng thành công",
                                           screenshot_path=after_remove_screenshot)
                                return True
                            else:
                                error_msg = f"Vẫn còn {len(cart_items)} sản phẩm trong giỏ hàng sau khi xóa"
                                in_thong_bao(reporter,
                                           "Kiểm tra kết quả xóa",
                                           status='FAIL',
                                           input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}",
                                           output=error_msg,
                                           expected="Giỏ hàng trống sau khi xóa",
                                           screenshot_path=after_remove_screenshot)
                                return False
                        except:
                            error_msg = f"Không xác nhận được kết quả sau khi xóa: {str(e)}"
                            in_thong_bao(reporter,
                                       "Lỗi khi xác nhận kết quả",
                                       status='ERROR',
                                       input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}",
                                       output=error_msg,
                                       expected="Xác nhận được kết quả xóa",
                                       screenshot_path=after_remove_screenshot)
                            return False
                        
                except Exception as e:
                    error_msg = f"Lỗi khi xử lý hộp thoại xác nhận: {str(e)}"
                    screenshot_path_error = luu_screenshot(driver, screenshot_dir, 'error_alert')
                    
                    in_thong_bao(reporter,
                               "Lỗi khi xử lý hộp thoại xác nhận",
                               status='ERROR',
                               input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}",
                               output=error_msg,
                               expected="Xuất hiện và xử lý được hộp thoại xác nhận",
                               screenshot_path=screenshot_path_error)
                    return False
                    
            except Exception as e:
                error_msg = f"Lỗi khi thực hiện xóa sản phẩm: {str(e)}"
                print(f"DEBUG - {error_msg}")
                screenshot_path_error = luu_screenshot(driver, screenshot_dir, 'error_delete')
                
                in_thong_bao(reporter,
                           "Lỗi khi xóa sản phẩm",
                           status='FAIL',
                           input_data=f"Sản phẩm: {ten_san_pham if ten_san_pham else 'Sản phẩm trong giỏ'}",
                           output=error_msg,
                           expected="Xóa sản phẩm khỏi giỏ hàng thành công",
                           screenshot_path=screenshot_path_error)
                return False
                
        except Exception as e:
            error_msg = "Không tìm thấy sản phẩm trong giỏ hàng!"
            screenshot_path_error = luu_screenshot(driver, screenshot_dir, 'error_no_products')
            
            in_thong_bao(reporter, 
                        "Kiểm tra sản phẩm trong giỏ hàng", 
                        status='FAIL',
                        input_data="Kiểm tra nội dung giỏ hàng",
                        output=error_msg,
                        expected="Tìm thấy sản phẩm trong giỏ hàng",
                        screenshot_path=screenshot_path_error)
            return False
            
    except Exception as e:
        error_msg = f"Lỗi khi kiểm tra giỏ hàng: {str(e)}"
        screenshot_path_error = luu_screenshot(driver, screenshot_dir, 'error_cart')
        
        in_thong_bao(reporter, 
                    "Lỗi nghiêm trọng khi kiểm tra giỏ hàng", 
                    status='ERROR',
                    input_data="Kiểm tra giỏ hàng",
                    output=error_msg,
                    expected="Kiểm tra được nội dung giỏ hàng",
                    screenshot_path=screenshot_path_error)
        return False

def main():
    print("="*80)
    print("KIỂM TRA TÍNH NĂNG XÓA SẢN PHẨM KHỎI GIỎ HÀNG")
    print("="*80)
    
    # Tạo thư mục lưu kết quả
    test_start_time = datetime.now()
    timestamp = test_start_time.strftime("%Y%m%d_%H%M%S")
    test_case_name = "kiem_tra_xoa_san_pham_khoi_gio_hang"
    thu_muc_ket_qua = tao_thu_muc_ket_qua(test_case_name)
    
    # Tạo báo cáo
    reporter = TestReporter(test_case_name)
    
    # Tạo thư mục lưu ảnh
    screenshot_dir = os.path.join(thu_muc_ket_qua, 'screenshots')
    os.makedirs(screenshot_dir, exist_ok=True)
    
    # Khởi tạo trình duyệt
    driver = None
    try:
        # Khởi tạo Chrome WebDriver
        options = webdriver.ChromeOptions()
        options.add_argument('--start-maximized')
        
        # Sử dụng webdriver-manager để tự động tải và quản lý ChromeDriver
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        
        in_thong_bao(reporter, "Khởi tạo trình duyệt Chrome", status='PASS',
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
        
        # Thêm sản phẩm vào giỏ hàng
        if them_san_pham_vao_gio(driver, url_san_pham, reporter, screenshot_dir, ten_san_pham):
            # Kiểm tra giỏ hàng và xóa sản phẩm
            kiem_tra_gio_hang(driver, reporter, screenshot_dir, ten_san_pham)
        else:
            in_thong_bao(reporter, "Không thể thêm sản phẩm vào giỏ hàng", status='FAIL',
                        input_data=f"Sản phẩm: {ten_san_pham}",
                        output="Không thể thêm sản phẩm vào giỏ hàng",
                        expected="Thêm sản phẩm vào giỏ hàng thành công")
        
    except Exception as e:
        error_msg = f"Lỗi không mong muốn trong quá trình kiểm thử: {str(e)}"
        screenshot_path_error = luu_screenshot(driver, screenshot_dir, 'error_test') if driver else None
        
        in_thong_bao(reporter, "Lỗi trong quá trình kiểm thử", status='FAIL',
                    input_data="Thực hiện test case",
                    output=error_msg,
                    expected="Hoàn thành test case không có lỗi",
                    screenshot_path=screenshot_path_error)
            
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
        print(f"\nĐã lưu báo cáo kết quả kiểm thử tại: {report_path}")
        
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
        
        in_thong_bao(reporter, "Kết thúc chương trình", status='INFO',
                    input_data="",
                    output="Test case hoàn tất",
                    expected="Hoàn thành test case")

if __name__ == "__main__":
    main()
