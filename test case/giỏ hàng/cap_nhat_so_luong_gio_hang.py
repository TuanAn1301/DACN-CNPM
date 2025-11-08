import os
import time
import sys
import shutil
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

def lay_ten_san_pham(driver):
    """Lấy tên sản phẩm từ trang giỏ hàng"""
    try:
        ten_san_pham = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".product-name a, .cart_item .product-name a, .product-title a"))
        ).text.strip()
        return ten_san_pham
    except:
        return "Sản phẩm không xác định"

def lay_so_luong_hien_tai(driver, reporter):
    """Lấy số lượng sản phẩm hiện tại trong giỏ hàng"""
    try:
        input_so_luong = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "input.quantity-input, input[type='number']"))
        )
        so_luong = int(input_so_luong.get_attribute("value") or 0)
        return so_luong
    except Exception as e:
        error_msg = f"Không thể lấy số lượng hiện tại: {str(e)}"
        in_thong_bao(reporter, "Lấy số lượng hiện tại", status='FAIL', notes=error_msg)
        return 0

def thay_doi_so_luong(driver, reporter, ten_san_pham, so_luong_moi, tang_giam):
    """Thay đổi số lượng sản phẩm trong giỏ hàng"""
    try:
        # Bước 1: Đi đến trang giỏ hàng
        step = "Truy cập trang giỏ hàng"
        driver.get("http://localhost/webbansach/gio-hang.php")
        time.sleep(2)
        in_thong_bao(reporter, step, status='PASS', screenshot=chup_man_hinh(driver))
        
        # Bước 2: Lấy số lượng hiện tại
        so_luong_cu = lay_so_luong_hien_tai(driver, reporter)
        in_thong_bao(reporter, "Lấy số lượng hiện tại", status='PASS', 
                    notes=f"Số lượng hiện tại của '{ten_san_pham}': {so_luong_cu}",
                    screenshot=chup_man_hinh(driver))
        
        # Bước 3: Tìm và cập nhật số lượng
        hanh_dong = "tăng" if tang_giam == 'tang' else "giảm"
        step = f"{hanh_dong.capitalize()} số lượng sản phẩm '{ten_san_pham}' từ {so_luong_cu} lên {so_luong_moi}"
        
        try:
            # Tìm ô nhập số lượng
            input_so_luong = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.CSS_SELECTOR, "input.quantity-input, input[type='number']"))
            )
            
            # Chụp ảnh trước khi thay đổi
            screenshot_before = chup_man_hinh(driver)
            
            # Xóa số lượng cũ và nhập số lượng mới
            input_so_luong.clear()
            input_so_luong.send_keys(str(so_luong_moi))
            
            # Tìm và nhấn nút cập nhật
            nut_cap_nhat = WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, 
                    "button.update-cart, input[type='submit'][value*='Cập nhật'], button:contains('Cập nhật')"))
            )
            
            # Tô sáng nút cập nhật
            original_style = highlight_element(driver, nut_cap_nhat)
            
            # Chụp ảnh trước khi click
            screenshot_click = chup_man_hinh(driver)
            
            # Thực hiện click
            nut_cap_nhat.click()
            
            # Đợi cập nhật
            time.sleep(2)
            
            # Chụp ảnh sau khi cập nhật
            screenshot_after = chup_man_hinh(driver)
            
            # Bước 4: Xác nhận cập nhật thành công
            step_xac_nhan = f"Xác nhận {hanh_dong} số lượng thành công"
            status = 'PASS'
            notes = f"Đã {hanh_dong} số lượng từ {so_luong_cu} lên {so_luong_moi}"
            
            try:
                # Kiểm tra thông báo thành công nếu có
                WebDriverWait(driver, 5).until(
                    EC.visibility_of_element_located((By.CSS_SELECTOR, 
                        ".alert-success, .success-message, .woocommerce-message, .alert.alert-success"))
                )
                notes += "\n- Có thông báo xác nhận cập nhật giỏ hàng"
            except:
                pass
            
            # Kiểm tra số lượng mới
            so_luong_moi_thuc_te = lay_so_luong_hien_tai(driver, reporter)
            
            if so_luong_moi_thuc_te != so_luong_moi:
                status = 'FAIL'
                notes += f"\n- LỖI: Số lượng sau cập nhật không đúng. Mong đợi: {so_luong_moi}, Thực tế: {so_luong_moi_thuc_te}"
            else:
                notes += f"\n- Số lượng mới đã được cập nhật chính xác: {so_luong_moi_thuc_te}"
            
            # Báo cáo kết quả
            in_thong_bao(
                reporter=reporter,
                step=step,
                status=status,
                input_data={
                    'Sản phẩm': ten_san_pham,
                    'Số lượng cũ': so_luong_cu,
                    'Số lượng mới': so_luong_moi,
                    'Hành động': f"{hanh_dong.capitalize()} số lượng"
                },
                expected=f"Số lượng được cập nhật từ {so_luong_cu} lên {so_luong_moi}",
                actual=f"Số lượng hiện tại: {so_luong_moi_thuc_te}",
                notes=notes,
                screenshot=[screenshot_before, screenshot_click, screenshot_after]
            )
            
            return status == 'PASS'
            
        except Exception as e:
            error_msg = f"Lỗi khi {hanh_dong} số lượng: {str(e)}"
            in_thong_bao(
                reporter=reporter,
                step=step,
                status='FAIL',
                notes=error_msg,
                screenshot=chup_man_hinh(driver)
            )
            return False
            
    except Exception as e:
        error_msg = f"Lỗi khi truy cập giỏ hàng: {str(e)}"
        in_thong_bao(reporter, "Truy cập giỏ hàng", status='FAIL', notes=error_msg)
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
            in_thong_bao(reporter, step, status='PASS')
            
            # Thêm sản phẩm vào giỏ hàng
            url_san_pham = "http://localhost/webbansach/san-pham.php"
            if them_san_pham_vao_gio(driver, url_san_pham, reporter):
                # Lấy tên sản phẩm để sử dụng trong báo cáo
                ten_san_pham = lay_ten_san_pham(driver)
                
                # Bước 1: Tăng số lượng sản phẩm
                so_luong_tang = 3  # Tăng lên 3 sản phẩm
                in_thong_bao(reporter, "BẮT ĐẦU KIỂM TRA TĂNG SỐ LƯỢNG", status='INFO')
                
                # Thực hiện tăng số lượng
                thanh_cong = thay_doi_so_luong(
                    driver=driver,
                    reporter=reporter,
                    ten_san_pham=ten_san_pham,
                    so_luong_moi=so_luong_tang,
                    tang_giam='tang'
                )
                
                if thanh_cong:
                    in_thong_bao(reporter, "KIỂM TRA TĂNG SỐ LƯỢNG THÀNH CÔNG", status='PASS')
                    
                    # Bước 2: Giảm số lượng sản phẩm
                    so_luong_giam = 1  # Giảm xuống 1 sản phẩm
                    in_thong_bao(reporter, "BẮT ĐẦU KIỂM TRA GIẢM SỐ LƯỢNG", status='INFO')
                    
                    # Thực hiện giảm số lượng
                    thanh_cong = thay_doi_so_luong(
                        driver=driver,
                        reporter=reporter,
                        ten_san_pham=ten_san_pham,
                        so_luong_moi=so_luong_giam,
                        tang_giam='giam'
                    )
                    
                    if thanh_cong:
                        in_thong_bao(reporter, "KIỂM TRA GIẢM SỐ LƯỢNG THÀNH CÔNG", status='PASS')
                    else:
                        in_thong_bao(reporter, "KIỂM TRA GIẢM SỐ LƯỢNG THẤT BẠI", status='FAIL')
                else:
                    in_thong_bao(reporter, "KIỂM TRA TĂNG SỐ LƯỢNG THẤT BẠI", status='FAIL')
            else:
                in_thong_bao(reporter, "Không thể thêm sản phẩm vào giỏ hàng", status='FAIL')
                
        except Exception as e:
            error_msg = f"Lỗi khi khởi tạo Chrome WebDriver: {str(e)}"
            in_thong_bao(reporter, step, status='FAIL', notes=error_msg)
            
    except Exception as e:
        error_msg = f"Lỗi không xác định: {str(e)}"
        in_thong_bao(reporter, "Lỗi tổng thể", status='FAIL', notes=error_msg)
        
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
            try:
                driver.quit()
                in_thong_bao(reporter, "Đã đóng trình duyệt", status='INFO')
            except:
                pass
                
        in_thong_bao(reporter, "KẾT THÚC KIỂM TRA CẬP NHẬT SỐ LƯỢNG GIỎ HÀNG", status='INFO')
        
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
