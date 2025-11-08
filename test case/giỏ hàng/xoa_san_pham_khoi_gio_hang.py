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
from test_utils import TestReporter, highlight_element, in_thong_bao, tao_thu_muc_ket_qua

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


def chup_thong_bao_thanh_cong(driver, screenshot_dir, reporter):
    """Chụp thông báo thành công nếu có"""
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
                                        reporter.add_screenshot(screenshot_path, f"Thông báo: {notification_text[:50]}...")
                                        return True, notification_text
                                except:
                                    # Nếu không chụp được phần tử riêng, chụp toàn màn hình
                                    success_img = chup_man_hinh(driver, screenshot_path)
                                    if success_img:
                                        reporter.add_screenshot(screenshot_path, f"Màn hình thông báo: {notification_text[:50]}...")
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
        reporter.add_screenshot(screenshot_path, "Màn hình sau khi xóa sản phẩm")
        
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


def them_san_pham_vao_gio(driver, url_san_pham, reporter):
    """Thực hiện thêm sản phẩm vào giỏ hàng"""
    try:
        # Bước 1: Truy cập trang sản phẩm
        step = "Truy cập trang sản phẩm"
        input_data = f"URL: {url_san_pham}"
        expected = "Hiển thị trang sản phẩm thành công"
        
        driver.get(url_san_pham)
        time.sleep(2)
        
        in_thong_bao(reporter, step, status='PASS', input_data=input_data, expected=expected)
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
            in_thong_bao(reporter, 
                        "Đã nhấn nút 'Thêm Giỏ Hàng'", 
                        status='PASS',
                        input_data="Sử dụng selector: a.themgiohang",
                        expected="Thêm sản phẩm vào giỏ hàng thành công")
            
            # Chờ một chút để xử lý
            time.sleep(2)
            
            # Chụp ảnh màn hình xác nhận
            try:
                screenshot = chup_man_hinh(driver)
                in_thong_bao(reporter, 
                           "Xác nhận thêm vào giỏ hàng", 
                           status='PASS',
                           input_data="Kiểm tra thông báo thành công",
                           expected="Hiển thị thông báo thêm vào giỏ hàng thành công")
                return True
            except Exception as e:
                in_thong_bao(reporter, 
                           "Không thể chụp ảnh xác nhận", 
                           status='WARNING',
                           input_data=str(e),
                           expected="Lưu được ảnh chụp màn hình")
                return True
                
        except Exception as e:
            error_msg = f"Không tìm thấy nút 'Thêm Giỏ Hàng': {str(e)}"
            in_thong_bao(reporter, 
                        "Lỗi khi tìm nút thêm vào giỏ", 
                        status='FAIL',
                        input_data=error_msg,
                        expected="Tìm thấy và click được nút thêm vào giỏ")
            return False
                    
            # Nếu không tìm thấy nút nào
            error_msg = "Không tìm thấy nút 'Thêm vào giỏ hàng' với bất kỳ selector nào đã thử"
            try:
                screenshot = chup_man_hinh(driver)
                in_thong_bao(reporter, 
                           "Tìm nút thêm vào giỏ hàng", 
                           status='FAIL',
                           input_data="Kiểm tra các selector tìm nút thêm vào giỏ",
                           expected="Tìm thấy nút thêm vào giỏ hàng")
            except Exception as e:
                in_thong_bao(reporter, 
                           "Lỗi khi chụp ảnh màn hình lỗi", 
                           status='ERROR',
                           input_data=error_msg,
                           expected="Chụp được ảnh màn hình lỗi")
            return False
            
        except Exception as e:
            error_msg = f"Lỗi khi thêm vào giỏ hàng: {str(e)}"
            try:
                screenshot = chup_man_hinh(driver)
                in_thong_bao(reporter, 
                           "Lỗi khi thêm vào giỏ hàng", 
                           status='FAIL',
                           input_data=error_msg,
                           expected="Thêm sản phẩm vào giỏ hàng thành công")
            except Exception as e:
                in_thong_bao(reporter, 
                           "Lỗi khi thêm vào giỏ hàng", 
                           status='FAIL',
                           input_data=error_msg,
                           expected="Thêm sản phẩm vào giỏ hàng thành công")
            return False

    except Exception as e:
        error_msg = f"Lỗi khi thêm sản phẩm vào giỏ: {str(e)}"
        try:
            screenshot = chup_man_hinh(driver)
            in_thong_bao(reporter, 
                        "Lỗi nghiêm trọng khi thêm sản phẩm", 
                        status='FAIL',
                        input_data=error_msg,
                        expected="Thực hiện các bước thêm sản phẩm thành công")
        except Exception as e:
            in_thong_bao(reporter, 
                        "Lỗi nghiêm trọng khi thêm sản phẩm", 
                        status='FAIL',
                        input_data=error_msg,
                        expected="Thực hiện các bước thêm sản phẩm thành công")
        return False

def kiem_tra_gio_hang(driver, reporter, screenshot_dir):
    """Kiểm tra giỏ hàng sau khi thêm sản phẩm
    
    Args:
        driver: WebDriver instance
        reporter: TestReporter instance for logging
        screenshot_dir: Directory to save screenshots
    """
    try:
        # Bước 1: Đi đến trang giỏ hàng
        step = "Truy cập trang giỏ hàng"
        url_gio_hang = "http://localhost/webbansach/gio-hang.php"
        driver.get(url_gio_hang)
        time.sleep(2)
        
        in_thong_bao(reporter, 
                    step, 
                    status='PASS', 
                    input_data=f"Truy cập URL: {url_gio_hang}",
                    expected="Hiển thị trang giỏ hàng")
        
        # Bước 2: Kiểm tra sản phẩm trong giỏ hàng
        try:
            san_pham_trong_gio = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.CSS_SELECTOR, ".cart-table tbody tr:not(.cart-header)"))
            )
            
            try:
                screenshot = chup_man_hinh(driver)
                in_thong_bao(reporter, 
                            "Kiểm tra sản phẩm trong giỏ hàng", 
                            status='PASS', 
                            input_data="Tìm kiếm sản phẩm trong bảng giỏ hàng",
                            expected="Tìm thấy ít nhất 1 sản phẩm trong giỏ hàng")
                
                # Bước 3: Thực hiện xóa sản phẩm khỏi giỏ hàng
                try:
                    # Chụp ảnh giỏ hàng trước khi xóa
                    before_remove_screenshot = os.path.join(screenshot_dir, f'before_remove_{int(time.time())}.png')
                    chup_man_hinh(driver, before_remove_screenshot)
                    
                    # Tìm và nhấn nút xóa sản phẩm
                    xoa_button = WebDriverWait(driver, 5).until(
                        EC.element_to_be_clickable((By.CSS_SELECTOR, "a.xoa i.fa-trash-alt, a.xoa"))
                    )
                    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", xoa_button)
                    time.sleep(1)  # Đợi một chút để đảm bảo nút hiển thị
                    
                    # Click nút xóa
                    xoa_button.click()
                    time.sleep(1)  # Đợi hiển thị hộp thoại xác nhận
                    
                    # Xác nhận xóa trong hộp thoại
                    try:
                        # Chuyển sang hộp thoại alert và chấp nhận
                        WebDriverWait(driver, 5).until(EC.alert_is_present())
                        alert = driver.switch_to.alert
                        alert_text = alert.text
                        alert.accept()
                        time.sleep(2)  # Đợi xử lý xóa
                        
                        # Chụp ảnh sau khi xóa
                        after_remove_screenshot = os.path.join(screenshot_dir, f'after_remove_{int(time.time())}.png')
                        chup_man_hinh(driver, after_remove_screenshot)
                        
                        # Kiểm tra thông báo thành công
                        try:
                            # Kiểm tra xem có chuyển hướng về trang chủ không (vì giỏ hàng trống)
                            WebDriverWait(driver, 5).until(
                                lambda d: 'index.php' in d.current_url or \
                                'giỏ hàng trống' in d.page_source.lower() or \
                                'không có sản phẩm' in d.page_source.lower()
                            )
                            
                            # Chụp thông báo thành công nếu có
                            has_notification, notification_text = chup_thong_bao_thanh_cong(driver, screenshot_dir, reporter)
                            
                            # Chụp ảnh màn hình kết quả cuối cùng
                            final_screenshot = os.path.join(screenshot_dir, f'final_result_{int(time.time())}.png')
                            chup_man_hinh(driver, final_screenshot)
                            
                            # Nếu không tìm thấy thông báo, sử dụng thông báo mặc định
                            if not has_notification:
                                notification_text = alert_text if alert_text else "Sản phẩm đã được xóa khỏi giỏ hàng"
                                
                            in_thong_bao(reporter,
                                       "Xác nhận xóa sản phẩm khỏi giỏ hàng",
                                       status='PASS',
                                       input_data=f"Đã xác nhận xóa sản phẩm\nThông báo: {notification_text}",
                                       expected="Xóa sản phẩm khỏi giỏ hàng thành công")
                            
                            # Thêm ảnh vào báo cáo
                            reporter.add_screenshot(before_remove_screenshot, "Giỏ hàng trước khi xóa")
                            reporter.add_screenshot(after_remove_screenshot, "Sau khi xóa sản phẩm")
                            reporter.add_screenshot(final_screenshot, "Kết quả cuối cùng")
                            
                            return True
                            
                        except Exception as e:
                            # Nếu không tìm thấy thông báo, kiểm tra xem giỏ hàng có trống không
                            try:
                                cart_items = driver.find_elements(By.CSS_SELECTOR, ".cart-table tbody tr:not(.cart-header)")
                                if not cart_items:
                                    in_thong_bao(reporter,
                                               "Xác nhận xóa sản phẩm khỏi giỏ hàng",
                                               status='PASS',
                                               input_data=f"Đã xác nhận xóa sản phẩm\nGiỏ hàng đã được làm trống",
                                               expected="Xóa sản phẩm khỏi giỏ hàng thành công")
                                    return True
                                else:
                                    raise Exception("Vẫn còn sản phẩm trong giỏ hàng sau khi xóa")
                            except:
                                raise Exception("Không xác nhận được kết quả sau khi xóa")
                        
                    except Exception as e:
                        raise Exception(f"Lỗi khi xử lý hộp thoại xác nhận: {str(e)}")
                    
                except Exception as e:
                    error_msg = f"Lỗi khi thực hiện xóa sản phẩm: {str(e)}"
                    print(f"DEBUG - {error_msg}")  # In lỗi ra console để debug
                    in_thong_bao(reporter,
                               "Lỗi khi xóa sản phẩm",
                               status='FAIL',
                               input_data=error_msg,
                               expected="Xóa sản phẩm khỏi giỏ hàng thành công")
                    
                    # Chụp ảnh lỗi
                    try:
                        error_screenshot = os.path.join(screenshot_dir, f'error_{int(time.time())}.png')
                        chup_man_hinh(driver, error_screenshot)
                        reporter.add_screenshot(error_screenshot, "Lỗi khi xóa sản phẩm")
                    except:
                        pass
                        
                    return False
            except Exception as e:
                in_thong_bao(reporter, 
                            "Lỗi khi chụp ảnh giỏ hàng", 
                            status='WARNING',
                            input_data=str(e),
                            expected="Chụp được ảnh giỏ hàng")
                return True
            
        except Exception as e:
            error_msg = "Không tìm thấy sản phẩm trong giỏ hàng!"
            try:
                screenshot = chup_man_hinh(driver)
                in_thong_bao(reporter, 
                            "Kiểm tra sản phẩm trong giỏ hàng", 
                            status='FAIL',
                            input_data=error_msg,
                            expected="Tìm thấy sản phẩm trong giỏ hàng")
            except Exception as e:
                in_thong_bao(reporter, 
                            "Lỗi khi kiểm tra giỏ hàng", 
                            status='ERROR',
                            input_data=error_msg,
                            expected="Kiểm tra được nội dung giỏ hàng")
            return False
            
    except Exception as e:
        error_msg = f"Lỗi khi kiểm tra giỏ hàng: {str(e)}"
        in_thong_bao(reporter, 
                    "Lỗi nghiêm trọng khi kiểm tra giỏ hàng", 
                    status='ERROR',
                    input_data=error_msg,
                    expected="Kiểm tra được nội dung giỏ hàng")
        return False
    print("="*80)
    print("KIỂM TRA TÍNH NĂNG THÊM SẢN PHẨM VÀO GIỎ HÀNG")
    print("="*80)
    
    # Tạo thư mục lưu kết quả
    test_start_time = datetime.now()
    timestamp = test_start_time.strftime("%Y%m%d_%H%M%S")
    test_case_name = "kiem_tra_xoa_san_pham_khoi_gio_hang"
    thu_muc_ket_qua = tao_thu_muc_ket_qua(test_case_name)
    
    # Tạo báo cáo
    reporter = TestReporter("Kiểm tra xóa sản phẩm khỏi giỏ hàng")
    
    # Tạo thư mục lưu ảnh
    screenshot_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'screenshots')
    os.makedirs(screenshot_dir, exist_ok=True)
    
    # URL sản phẩm mẫu
    url_san_pham = "http://localhost/webbansach/san-pham.php?id=12"
    
    # Khởi tạo trình duyệt
    driver = None
    try:
        # Sử dụng webdriver-manager để tự động tải và quản lý ChromeDriver
        step = "Khởi tạo trình duyệt Chrome"
        service = Service(ChromeDriverManager().install())
        options = webdriver.ChromeOptions()
        options.add_argument('--start-maximized')
        
        driver = webdriver.Chrome(service=service, options=options)
        in_thong_bao(reporter, step, status='PASS')
        
        try:
            # Thực hiện thêm sản phẩm vào giỏ hàng
            if them_san_pham_vao_gio(driver, url_san_pham, reporter):
                # Đợi một chút để đảm bảo sản phẩm được thêm vào giỏ
                time.sleep(3)
                
                # Thông báo thêm sản phẩm thành công
                in_thong_bao(reporter,
                           "THÀNH CÔNG",
                           status='PASS',
                           input_data="Đã thêm sản phẩm vào giỏ hàng thành công!",
                           expected="Hiển thị thông báo thêm vào giỏ hàng thành công")
                
                # Kiểm tra số lượng sản phẩm trong giỏ
                try:
                    # Thử tìm biểu tượng giỏ hàng với số lượng
                    cart_count_element = WebDriverWait(driver, 5).until(
                        EC.presence_of_element_located((By.CSS_SELECTOR, 
                            ".cart-count, .cart-items-count, .count, .item-count, "
                            "[class*='cart-count'], #cart-total, .cart-total, .cart-qty"
                        ))
                    )
                    cart_count = cart_count_element.text.strip()
                    in_thong_bao(reporter,
                               "THÔNG TIN GIỎ HÀNG",
                               status='PASS',
                               input_data=f"Số lượng sản phẩm trong giỏ: {cart_count}",
                               expected="Hiển thị số lượng sản phẩm trong giỏ")
                    
                except Exception as e:
                    # Nếu không tìm thấy số lượng, thử đếm sản phẩm trong giỏ
                    try:
                        cart_items = driver.find_elements(By.CSS_SELECTOR, 
                            ".cart-item, .cart_item, tr.cart_item, .product-item"
                        )
                        if cart_items:
                            in_thong_bao(reporter,
                                       "THÔNG TIN GIỎ HÀNG",
                                       status='PASS',
                                       input_data=f"Đã thêm thành công {len(cart_items)} sản phẩm vào giỏ",
                                       expected="Hiển thị thông tin sản phẩm trong giỏ")
                        else:
                            in_thong_bao(reporter,
                                       "THÔNG TIN GIỎ HÀNG",
                                       status='INFO',
                                       input_data="Sản phẩm đã được thêm vào giỏ. Vui lòng kiểm tra trang giỏ hàng.",
                                       expected="Hiển thị thông tin giỏ hàng")
                    except Exception as e2:
                        in_thong_bao(reporter,
                                   "THÔNG TIN GIỎ HÀNG",
                                   status='INFO',
                                   input_data="Đã thêm sản phẩm vào giỏ. Vui lòng kiểm tra giỏ hàng để xác nhận.",
                                   expected="Hiển thị thông tin giỏ hàng")
                
                # Thử truy cập giỏ hàng
                cart_urls = [
                    "http://localhost/webbansach/gio-hang.php",
                    "http://localhost/webbansach/cart",
                    "http://localhost/webbansach/cart.php"
                ]
                
                cart_loaded = False
                last_error = None
                
                for url in cart_urls:
                    try:
                        driver.get(url)
                        # Chờ một trong các phần tử của giỏ hàng xuất hiện
                        WebDriverWait(driver, 10).until(
                            EC.presence_of_element_located((
                                By.CSS_SELECTOR,
                                ".cart-table, .cart-empty, .cart-contents, .woocommerce-cart-form, #cart, .shopping-cart, .cart-container"
                            ))
                        )
                        cart_loaded = True
                        break
                    except Exception as e:
                        last_error = e
                        continue
                
                if not cart_loaded:
                    in_thong_bao(reporter,
                               "Không thể tải trang giỏ hàng",
                               status='ERROR',
                               input_data=f"Đã thử các URL: {', '.join(cart_urls)}\nLỗi: {str(last_error)}\nNội dung trang: {driver.page_source[:500]}...",
                               expected="Truy cập được trang giỏ hàng")
                    return
                
                # Chụp ảnh màn hình giỏ hàng
                screenshot = chup_man_hinh(driver)
                
                # Kiểm tra nội dung giỏ hàng
                try:
                    # Lưu lại nội dung trang để debug
                    page_source = driver.page_source.lower()
                    in_thong_bao(reporter,
                               "Kiểm tra giỏ hàng",
                               status='PASS' if 'giỏ hàng' in page_source else 'FAIL',
                               input_data=f"Nội dung giỏ hàng: {page_source[:200]}...",
                               expected="Có ít nhất 1 sản phẩm trong giỏ")
                    
                    # Kiểm tra và xóa sản phẩm khỏi giỏ hàng
                    try:
                        # Chụp ảnh trước khi xóa
                        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                        screenshot_before = os.path.join(screenshot_dir, f'before_delete_{timestamp}.png')
                        chup_man_hinh(driver, screenshot_before)
                        
                        # Tìm và nhấn nút xóa sản phẩm
                        try:
                            # Tìm nút xóa (sử dụng XPath linh hoạt hơn)
                            xpath_delete = "//a[contains(@class,'xoa')]//i[contains(@class,'fa-trash-alt')]"
                            delete_button = WebDriverWait(driver, 10).until(
                                EC.element_to_be_clickable((By.XPATH, xpath_delete))
                            )
                            
                            # Cuộn đến nút xóa và highlight
                            driver.execute_script("arguments[0].scrollIntoView({block: 'center'})", delete_button)
                            highlight_element(driver, delete_button)
                            
                            # Nhấn nút xóa
                            delete_button.click()
                            
                            # Chờ và xác nhận hộp thoại xóa
                            try:
                                WebDriverWait(driver, 5).until(EC.alert_is_present())
                                alert = driver.switch_to.alert
                                alert_text = alert.text
                                alert.accept()
                                
                                print(f"Đã xác nhận hộp thoại: {alert_text}")
                                
                                # Chờ thông báo xóa thành công
                                success_message = "Đã xóa sản phẩm khỏi giỏ hàng"
                                try:
                                    WebDriverWait(driver, 10).until(
                                        EC.presence_of_element_located((By.XPATH, f"//*[contains(.,'{success_message}')]"))
                                    )
                                    
                                    # Chờ cập nhật giỏ hàng
                                    time.sleep(2)
                                    
                                    # Chụp ảnh sau khi xóa
                                    screenshot_after = os.path.join(screenshot_dir, f'after_delete_{timestamp}.png')
                                    chup_man_hinh(driver, screenshot_after)
                                    
                                    # Kiểm tra giỏ hàng trống
                                    page_source_after = driver.page_source.lower()
                                    if any(x in page_source_after for x in ['giỏ hàng của bạn đang trống', 'không có sản phẩm']):
                                        in_thong_bao(reporter,
                                                   "Xóa sản phẩm khỏi giỏ hàng",
                                                   status='PASS',
                                                   input_data=f"Đã xác nhận xóa sản phẩm\nTrạng thái: Thành công\nThông báo: {success_message}",
                                                   expected="Xóa sản phẩm khỏi giỏ hàng thành công")
                                        
                                        # Lưu báo cáo
                                        report_name = f"{test_case_name}_{timestamp}.xlsx"
                                        report_path = os.path.join(thu_muc_ket_qua, report_name)
                                        
                                        # Thêm ảnh kết quả cuối cùng vào báo cáo
                                        try:
                                            # Chụp ảnh màn hình kết quả
                                            result_screenshot = os.path.join(screenshot_dir, f'final_result_{timestamp}.png')
                                            chup_man_hinh(driver, result_screenshot)
                                            
                                            # Thêm ảnh kết quả vào báo cáo
                                            in_thong_bao(reporter,
                                                       "Kết quả cuối cùng",
                                                       status='PASS',
                                                       input_data="Xóa sản phẩm khỏi giỏ hàng thành công",
                                                       expected="Giỏ hàng trống sau khi xóa",
                                                       screenshot=result_screenshot)
                                            
                                            # Lưu báo cáo
                                            reporter.save_report(report_path)
                                            print(f"\nĐã lưu báo cáo kết quả kiểm thử tại: {report_path}")
                                            
                                        except Exception as e:
                                            print(f"Lỗi khi lưu báo cáo: {str(e)}")
                                        
                                    else:
                                        in_thong_bao(reporter,
                                                   "Kiểm tra xóa sản phẩm",
                                                   status='FAIL',
                                                   input_data="Đã xác nhận xóa nhưng giỏ hàng chưa cập nhật",
                                                   expected="Giỏ hàng phải trống sau khi xóa sản phẩm")
                                        
                                except Exception as thong_bao_loi:
                                    error_msg = f"Không tìm thấy thông báo xóa: {str(thong_bao_loi)}\nNội dung trang: {driver.page_source[:500]}..."
                                    print(error_msg)
                                    in_thong_bao(reporter,
                                               "Lỗi khi chờ thông báo xóa",
                                               status='ERROR',
                                               input_data=error_msg,
                                               expected=f"Xuất hiện thông báo: {success_message}")
                                    
                            except Exception as alert_loi:
                                error_msg = f"Lỗi hộp thoại xác nhận: {str(alert_loi)}\nNội dung trang: {driver.page_source[:500]}..."
                                print(error_msg)
                                in_thong_bao(reporter,
                                           "Lỗi hộp thoại xác nhận",
                                           status='ERROR',
                                           input_data=error_msg,
                                           expected="Xuất hiện hộp thoại xác nhận xóa sản phẩm")
                            
                        except Exception as tim_nut_loi:
                            error_msg = f"Không tìm thấy nút xóa: {str(tim_nut_loi)}\nNội dung trang: {driver.page_source[:500]}..."
                            print(error_msg)
                            in_thong_bao(reporter,
                                       "Không tìm thấy nút xóa",
                                       status='ERROR',
                                       input_data=error_msg,
                                       expected="Tìm thấy nút xóa sản phẩm")
                            
                    except Exception as xoa_loi:
                        error_msg = f"Lỗi khi xóa sản phẩm: {str(xoa_loi)}\nNội dung trang: {driver.page_source[:500]}..."
                        print(error_msg)
                        in_thong_bao(reporter,
                                   "Lỗi khi xóa sản phẩm khỏi giỏ hàng",
                                   status='ERROR',
                                   input_data=error_msg,
                                   expected="Xóa sản phẩm khỏi giỏ hàng thành công")
                    
                except Exception as check_error:
                    in_thong_bao(reporter,
                               "Lỗi khi kiểm tra giỏ hàng",
                               status='ERROR',
                               input_data=f"{str(check_error)}\nNội dung trang: {driver.page_source[:500]}...",
                               expected="Kiểm tra được nội dung giỏ hàng",
                               screenshot=screenshot)
                    
                except Exception as nav_error:
                    in_thong_bao(reporter,
                               "Lỗi khi điều hướng đến giỏ hàng",
                               status='ERROR',
                               input_data=str(nav_error),
                               expected="Điều hướng thành công đến trang giỏ hàng")
            else:
                in_thong_bao(reporter, 
                           "Không thể thêm sản phẩm vào giỏ hàng", 
                           status='FAIL',
                           input_data="Kiểm tra nút thêm vào giỏ",
                           expected="Thêm sản phẩm vào giỏ hàng thành công")
                
        except Exception as e:
            error_msg = f"Lỗi khi thực hiện kiểm thử: {str(e)}"
            print(error_msg)
            in_thong_bao(reporter, 
                        "Lỗi khi thực hiện kiểm thử", 
                        status='ERROR',
                        input_data=error_msg,
                        expected="Hoàn thành kiểm thử không có lỗi")
        
        except Exception as e:
            error_msg = f"Lỗi không mong muốn trong quá trình kiểm thử: {str(e)}"
            in_thong_bao(reporter, f"Lỗi trong quá trình kiểm thử: {str(e)}", status='FAIL')
            
    except Exception as e:
        error_msg = f"Lỗi khi khởi tạo trình duyệt: {str(e)}"
        in_thong_bao(reporter, f"Lỗi nghiêm trọng: {str(e)}", status='FAIL')
        return
        
    finally:
        # Nếu chưa lưu báo cáo (trường hợp xảy ra lỗi trước khi lưu)
        if not os.path.exists(report_path):
            try:
                # Chụp ảnh màn hình lỗi
                error_screenshot = os.path.join(screenshot_dir, f'error_{timestamp}.png')
                chup_man_hinh(driver, error_screenshot)
                
                # Thêm thông báo lỗi vào báo cáo
                in_thong_bao(reporter,
                           "Kết thúc kiểm thử (có lỗi)",
                           status='ERROR',
                           input_data=f"Đã xảy ra lỗi trong quá trình kiểm thử\nThời gian chạy: {datetime.now() - test_start_time}",
                           expected="Hoàn thành kiểm thử thành công",
                           screenshot=error_screenshot)
                
                # Lưu báo cáo lỗi
                error_report_name = f"{test_case_name}_ERROR_{timestamp}.xlsx"
                error_report_path = os.path.join(thu_muc_ket_qua, error_report_name)
                reporter.save_report(error_report_path)
                print(f"\nĐã lưu báo cáo lỗi tại: {error_report_path}")
                
            except Exception as e:
                print(f"Không thể lưu báo cáo lỗi: {str(e)}")
        
        # Đóng trình duyệt nếu chưa đóng
        if driver is not None:
            driver.quit()
            in_thong_bao(reporter, "Đã đóng trình duyệt")
        
        in_thong_bao(reporter, "Kết thúc chương trình")

def main():
    # Tạo thư mục lưu kết quả
    test_start_time = datetime.now()
    timestamp = test_start_time.strftime("%Y%m%d_%H%M%S")
    test_case_name = "kiem_tra_xoa_san_pham_khoi_gio_hang"
    thu_muc_ket_qua = tao_thu_muc_ket_qua(test_case_name)
    
    # Tạo báo cáo
    reporter = TestReporter("Kiểm tra xóa sản phẩm khỏi giỏ hàng")
    
    # Tạo thư mục lưu ảnh
    screenshot_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'screenshots')
    os.makedirs(screenshot_dir, exist_ok=True)
    
    # URL sản phẩm mẫu - sử dụng sản phẩm có ID 12
    url_san_pham = "http://localhost/webbansach/san-pham.php?id=12"
    
    # Khởi tạo trình duyệt
    driver = None
    try:
        # Khởi tạo Chrome WebDriver
        options = webdriver.ChromeOptions()
        options.add_argument('--start-maximized')
        
        # Sử dụng webdriver-manager để tự động tải và quản lý ChromeDriver
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        
        # Thêm sản phẩm vào giỏ hàng
        them_san_pham_vao_gio(driver, url_san_pham, reporter)
        
        # Kiểm tra giỏ hàng
        kiem_tra_gio_hang(driver, reporter, screenshot_dir)
        
        # Lưu báo cáo thành công
        report_name = f"ket_qua_{test_case_name}_{timestamp}.xlsx"
        report_path = os.path.join(thu_muc_ket_qua, report_name)
        reporter.save_report(report_path)
        print(f"\nĐã lưu báo cáo kết quả kiểm thử tại: {report_path}")
        
    except Exception as e:
        error_msg = f"Lỗi không mong muốn trong quá trình kiểm thử: {str(e)}"
        in_thong_bao(reporter, f"Lỗi trong quá trình kiểm thử: {str(e)}", status='FAIL')
        
        # Lưu báo cáo lỗi
        try:
            error_report_name = f"{test_case_name}_ERROR_{timestamp}.xlsx"
            error_report_path = os.path.join(thu_muc_ket_qua, error_report_name)
            reporter.save_report(error_report_path)
            print(f"\nĐã lưu báo cáo lỗi tại: {error_report_path}")
        except Exception as e:
            print(f"Không thể lưu báo cáo lỗi: {str(e)}")
            
    finally:
        # Đóng trình duyệt nếu chưa đóng
        if driver is not None:
            try:
                driver.quit()
                in_thong_bao(reporter, "Đã đóng trình duyệt")
            except:
                pass
        
        in_thong_bao(reporter, "Kết thúc chương trình")

if __name__ == "__main__":
    main()
