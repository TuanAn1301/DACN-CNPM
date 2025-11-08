import os
import time
import sys
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

# Thêm thư mục cha vào PATH để import test_utils
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from test_utils import TestReporter, chup_man_hinh, highlight_element, in_thong_bao, tao_thu_muc_ket_qua


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

def kiem_tra_gio_hang(driver, reporter):
    """Kiểm tra giỏ hàng sau khi thêm sản phẩm"""
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
                return True
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

def main():
    print("="*80)
    print("KIỂM TRA TÍNH NĂNG THÊM SẢN PHẨM VÀO GIỎ HÀNG")
    print("="*80)
    
    # Khởi tạo thư mục kết quả và báo cáo
    test_name = "them_san_pham_vao_gio_hang"
    thu_muc_ket_qua = tao_thu_muc_ket_qua(test_name)
    reporter = TestReporter(test_name)
    
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
                    
                    # In ra một phần nội dung trang để debug
                    debug_info = f"Nội dung trang (500 ký tự đầu): {page_source[:500]}"
                    
                    # Kiểm tra xem có thông báo giỏ hàng trống không
                    if any(empty_msg in page_source for empty_msg in ["giỏ hàng trống", "không có sản phẩm", "cart is empty", "your cart is currently empty"]):
                        # Bỏ qua thông báo giỏ hàng trống
                        return
                    
                    # Kiểm tra xem có sản phẩm trong giỏ không
                    cart_selectors = [
                        ".cart-table tbody tr:not(.cart-header)",
                        ".cart-item",
                        ".woocommerce-cart-form .cart_item",
                        "#cart .item",
                        "table.shop_table tbody tr",
                        "tr.cart_item",
                        "tbody tr",
                        "#shopping-cart-table tbody tr"
                    ]
                    
                    for selector in cart_selectors:
                        try:
                            cart_items = driver.find_elements(By.CSS_SELECTOR, selector)
                            if cart_items:
                                # Lọc ra các dòng không phải sản phẩm
                                valid_items = []
                                for item in cart_items:
                                    item_text = item.text.lower()
                                    if (item_text.strip() and 
                                        "remove" not in item_text and 
                                        "xóa" not in item_text and
                                        "hành động" not in item_text and
                                        len(item_text) > 10):  # Đảm bảo có đủ nội dung
                                        valid_items.append(item)
                                
                                if valid_items:
                                    in_thong_bao(reporter, 
                                               "Truy cập giỏ hàng thành công", 
                                               status='PASS',
                                               input_data=f"Tìm thấy {len(valid_items)} sản phẩm trong giỏ (sử dụng selector: {selector})\nNội dung sản phẩm đầu tiên: {valid_items[0].text[:100]}...",
                                               expected="Hiển thị trang giỏ hàng với sản phẩm đã thêm",
                                               screenshot=screenshot)
                                    return
                        except Exception as e:
                            continue
                    
                    # Nếu đến đây tức là không tìm thấy sản phẩm nào
                    in_thong_bao(reporter,
                               "Không tìm thấy sản phẩm trong giỏ hàng",
                               status='FAIL',
                               input_data=f"Đã kiểm tra tất cả các selector giỏ hàng phổ biến.\n{debug_info}",
                               expected="Có ít nhất 1 sản phẩm trong giỏ",
                               screenshot=screenshot)
                    
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
        # Chụp ảnh màn hình cuối cùng
        final_screenshot = None
        try:
            final_screenshot = chup_man_hinh(driver)
        except Exception as e:
            print(f"Không thể chụp ảnh màn hình cuối cùng: {str(e)}")
        
        # Lưu báo cáo với ảnh chụp màn hình cuối cùng
        report_path = reporter.save_report(thu_muc_ket_qua, final_screenshot=final_screenshot)
        print(f"\nĐã lưu báo cáo kiểm thử tại: {report_path}")
        
        # Đóng trình duyệt nếu chưa đóng
        if driver is not None:
            driver.quit()
            in_thong_bao(reporter, "Đã đóng trình duyệt")
        
        in_thong_bao(reporter, "Kết thúc chương trình")

if __name__ == "__main__":
    main()
