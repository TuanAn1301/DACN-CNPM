import os
import time
import sys
import io
from datetime import datetime
from PIL import Image
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException, WebDriverException

# Thêm thư mục chứa test_utils.py vào path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from test_utils import TestReporter, tao_thu_muc_ket_qua, in_thong_bao

def chup_man_hinh(driver, save_path=None, element=None):
    """Chụp màn hình và trả về đối tượng ảnh"""
    try:
        # Tạo thư mục nếu chưa tồn tại
        if save_path:
            os.makedirs(os.path.dirname(save_path), exist_ok=True)
        
        # Chụp ảnh
        if element:
            screenshot = element.screenshot_as_png
        else:
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

def kiem_tra_phan_tu(driver, loai, gia_tri, timeout=10):
    """Kiểm tra sự tồn tại của phần tử"""
    try:
        WebDriverWait(driver, timeout).until(
            EC.presence_of_element_located((loai, gia_tri))
        )
        return True
    except TimeoutException:
        return False

def kiem_tra_loi_dang_nhap(driver):
    """Kiểm tra thông báo lỗi đăng nhập"""
    try:
        # Kiểm tra thông báo lỗi
        thong_bao_loi = WebDriverWait(driver, 5).until(
            EC.visibility_of_element_located((By.CSS_SELECTOR, ".alert.alert-danger, .error-message, .text-danger"))
        )
        if thong_bao_loi:
            return True, thong_bao_loi.text.strip()
    except:
        pass
    
    # Kiểm tra URL vẫn ở trang đăng nhập
    if "dang-nhap" in driver.current_url or "login" in driver.current_url:
        return True, "Đăng nhập thất bại: URL vẫn ở trang đăng nhập"
        
    return False, "Không xác định được lỗi đăng nhập"

def main():
    # Danh sách các test case
    test_cases = [
        {
            "ten_test_case": "Kiểm tra đăng nhập với tài khoản không tồn tại",
            "ten_dang_nhap": "tai_khoan_khong_ton_tai",
            "mat_khau": "mat_khau_bat_ky",
            "expected_result": "Hiển thị thông báo lỗi đăng nhập"
        },
        {
            "ten_test_case": "Kiểm tra đăng nhập với mật khẩu không chính xác",
            "ten_dang_nhap": "quan",  # Thay bằng tài khoản hợp lệ
            "mat_khau": "mat_khau_sai",
            "expected_result": "Hiển thị thông báo lỗi đăng nhập"
        }
    ]
    
    url_dang_nhap = 'http://localhost/webbansach/dang-nhap.php'
    
    # Tạo thư mục kết quả và khởi tạo báo cáo
    test_run_dir = tao_thu_muc_ket_qua("kiem_tra_dang_nhap_that_bai")
    reporter = TestReporter("Kiểm tra đăng nhập thất bại")
    
    # Thêm thông tin chung vào báo cáo
    reporter.add_step("Bắt đầu kiểm thử đăng nhập thất bại", "INFO", 
                     input_data=f"Số lượng test case: {len(test_cases)}",
                     expected="Tất cả các test case đều thất bại khi nhập thông tin không hợp lệ")
    
    # Tùy chọn cho Chrome
    options = webdriver.ChromeOptions()
    options.add_argument('--start-maximized')  # Mở cửa sổ lớn nhất
    options.add_experimental_option('excludeSwitches', ['enable-logging'])  # Tắt thông báo console
    
    print("\n=== KIỂM TRA ĐĂNG NHẬP THẤT BẠI ===\n")
    print(f"Số lượng test case: {len(test_cases)}")
    print("Mục đích: Kiểm tra xử lý khi đăng nhập thất bại\n")

    driver = None
    try:
        # Khởi tạo Chrome WebDriver
        in_thong_bao(reporter, "Khởi tạo trình duyệt Chrome", "INFO")
        
        try:
            service = Service(ChromeDriverManager().install())
            driver = webdriver.Chrome(service=service, options=options)
            in_thong_bao(reporter, "Đã khởi tạo trình duyệt thành công", "PASS")
        except Exception as e:
            error_msg = f"Lỗi khi khởi tạo trình duyệt: {str(e)}"
            in_thong_bao(reporter, error_msg, "ERROR")
            reporter.save_report(test_run_dir)
            return

        # Mở trang đăng nhập
        in_thong_bao(reporter, f"Truy cập trang đăng nhập", "INFO", 
                    input_data=f"URL: {url_dang_nhap}",
                    expected="Hiển thị trang đăng nhập")
        
        try:
            driver.get(url_dang_nhap)
            WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.TAG_NAME, "body"))
            )
            
            # Chụp màn hình trang đăng nhập
            login_screenshot = os.path.join(test_run_dir, "trang_dang_nhap.png")
            chup_man_hinh(driver, login_screenshot)
            
            # Kiểm tra xem có phải trang đăng nhập không
            if "dang-nhap" in driver.current_url or "login" in driver.current_url or \
               driver.find_elements(By.NAME, "taikhoan") or driver.find_elements(By.NAME, "email"):
                in_thong_bao(reporter, "Đã tải trang đăng nhập thành công", "PASS")
                reporter.add_final_screenshot(login_screenshot)
            else:
                in_thong_bao(reporter, "Có thể không phải là trang đăng nhập", "WARNING")
                
        except TimeoutException:
            in_thong_bao(reporter, "Không thể tải trang đăng nhập", "FAIL",
                        expected="Tải được trang đăng nhập trong vòng 10 giây",
                        notes="Kiểm tra kết nối hoặc URL")
            reporter.save_report(test_run_dir)
            return
            
        except Exception as e:
            in_thong_bao(reporter, f"Lỗi khi tải trang đăng nhập: {str(e)}", "ERROR")
            reporter.save_report(test_run_dir)
            return

        # Thực hiện từng test case
        for i, test_case in enumerate(test_cases, 1):
            current_username = test_case["ten_dang_nhap"]
            current_password = test_case["mat_khau"]
            test_case_name = test_case["ten_test_case"]
            expected_result = test_case["expected_result"]
            
            # Thêm thông tin test case vào báo cáo
            in_thong_bao(reporter, 
                        f"Bắt đầu test case {i}: {test_case_name}", 
                        "INFO",
                        input_data=f"Tài khoản: {current_username}\nMật khẩu: {'*' * len(current_password) if current_password else '(để trống)'}",
                        expected=expected_result)
            
            # Tạo thư mục cho test case hiện tại
            test_case_dir = os.path.join(test_run_dir, f"test_case_{i}")
            os.makedirs(test_case_dir, exist_ok=True)
            
            # Làm mới trang đăng nhập cho mỗi test case
            in_thong_bao(reporter, "Làm mới trang đăng nhập", "INFO")
            try:
                driver.get(url_dang_nhap)
                WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.TAG_NAME, "body"))
                )
                time.sleep(1)
            except TimeoutException as e:
                in_thong_bao(reporter, f"Không thể tải trang đăng nhập trong vòng 10 giây: {str(e)}", "FAIL")
                continue
            except Exception as e:
                in_thong_bao(reporter, f"Lỗi khi làm mới trang đăng nhập: {str(e)}", "ERROR")
                continue
            
            # Tìm và điền thông tin đăng nhập
            in_thong_bao(reporter, "Điền thông tin đăng nhập", "INFO")
            
            # Tìm và điền tên đăng nhập (nếu có)
            username_filled = False
            if current_username:
                try:
                    # Thử tìm ô nhập tên đăng nhập bằng ID
                    try:
                        o_tai_khoan = WebDriverWait(driver, 5).until(
                            EC.presence_of_element_located((By.ID, "email1"))
                        )
                        o_tai_khoan.clear()
                        o_tai_khoan.send_keys(current_username)
                        in_thong_bao(reporter, "Đã nhập tên đăng nhập (ID: email1)", "PASS")
                        username_filled = True
                    except (TimeoutException, Exception) as e:
                        in_thong_bao(reporter, f"Không tìm thấy ô tên đăng nhập bằng ID: {str(e)}", "INFO")
                        # Nếu không tìm thấy bằng ID, thử tìm bằng name
                        try:
                            o_tai_khoan = WebDriverWait(driver, 5).until(
                                EC.presence_of_element_located((By.NAME, "taikhoan"))
                            )
                            o_tai_khoan.clear()
                            o_tai_khoan.send_keys(current_username)
                            in_thong_bao(reporter, "Đã nhập tên đăng nhập (name: taikhoan)", "PASS")
                            username_filled = True
                        except TimeoutException as e:
                            in_thong_bao(reporter, f"Không tìm thấy ô tên đăng nhập bằng name trong vòng 5 giây: {str(e)}", "FAIL")
                        except Exception as e:
                            in_thong_bao(reporter, f"Lỗi khi điền tên đăng nhập: {str(e)}", "ERROR")
                        o_tai_khoan.send_keys(current_username)
                        in_thong_bao(reporter, "Đã nhập tên đăng nhập (name: taikhoan)", "PASS")
                        username_filled = True
                        
                    time.sleep(0.5)
                except Exception as e:
                    in_thong_bao(reporter, f"Không thể tìm thấy ô tên đăng nhập: {str(e)}", "FAIL")
            
            # Tìm và điền mật khẩu (nếu có)
            password_filled = False
            if current_password and username_filled:  # Chỉ điền mật khẩu nếu đã điền được tên đăng nhập
                try:
                    # Thử tìm ô nhập mật khẩu bằng ID
                    try:
                        o_mat_khau = WebDriverWait(driver, 5).until(
                            EC.presence_of_element_located((By.ID, "login-password"))
                        )
                        o_mat_khau.clear()
                        o_mat_khau.send_keys(current_password)
                        in_thong_bao(reporter, "Đã nhập mật khẩu (ID: login-password)", "PASS")
                        password_filled = True
                    except:
                        # Nếu không tìm thấy bằng ID, thử tìm bằng name
                        o_mat_khau = driver.find_element(By.NAME, "matkhau")
                        o_mat_khau.clear()
                        o_mat_khau.send_keys(current_password)
                        in_thong_bao(reporter, "Đã nhập mật khẩu (name: matkhau)", "PASS")
                        password_filled = True
                        
                    time.sleep(0.5)
                except Exception as e:
                    in_thong_bao(reporter, f"Không thể tìm thấy ô mật khẩu: {str(e)}", "FAIL")

            # Chụp màn hình trước khi đăng nhập
            screenshot_before = os.path.join(test_case_dir, "truoc_khi_dang_nhap.png")
            before_login_img = chup_man_hinh(driver, screenshot_before)
            if before_login_img:
                reporter.add_final_screenshot(screenshot_before)

            # Nhấn nút đăng nhập
            try:
                in_thong_bao(reporter, "Nhấn nút đăng nhập", "INFO")
                
                # Tìm nút đăng nhập (thử nhiều selector khác nhau)
                nut_dang_nhap = None
                nut_selectors = [
                    (By.NAME, "dangnhap"),
                    (By.XPATH, "//button[contains(text(),'Đăng nhập')]"),
                    (By.XPATH, "//input[@type='submit']"),
                    (By.CSS_SELECTOR, "button[type='submit']"),
                    (By.CLASS_NAME, "btn-login")
                ]
                
                for by, selector in nut_selectors:
                    try:
                        nut_dang_nhap = WebDriverWait(driver, 3).until(
                            EC.element_to_be_clickable((by, selector))
                        )
                        break
                    except:
                        continue
                
                if not nut_dang_nhap:
                    raise Exception("Không tìm thấy nút đăng nhập")
                
                # Cuộn đến vị trí nút đăng nhập và chờ một chút
                driver.execute_script("arguments[0].scrollIntoView({block: 'center', behavior: 'smooth'});", nut_dang_nhap)
                time.sleep(0.5)
                
                # Chụp màn hình trước khi click đăng nhập
                before_click_screenshot = os.path.join(test_case_dir, "truoc_khi_nhan_dang_nhap.png")
                chup_man_hinh(driver, before_click_screenshot)
                
                # Thử click bằng JavaScript
                driver.execute_script("arguments[0].click();", nut_dang_nhap)
                in_thong_bao(reporter, "Đã nhấn nút đăng nhập", "PASS")
                
                # Chờ đợi kết quả đăng nhập
                time.sleep(2)  # Chờ cho các hiệu ứng hoàn tất
                
                # Kiểm tra kết quả đăng nhập
                try:
                    # Chờ đợi thông báo lỗi hoặc chuyển hướng
                    WebDriverWait(driver, 5).until(
                        lambda d: "thất bại" in d.page_source.lower() or 
                                "sai" in d.page_source.lower() or
                                "lỗi" in d.page_source.lower() or
                                "dang-nhap" not in d.current_url
                    )
                    
                    # Chụp màn hình sau khi đăng nhập thất bại
                    after_login_screenshot = os.path.join(test_case_dir, "sau_khi_dang_nhap_that_bai.png")
                    after_login_img = chup_man_hinh(driver, after_login_screenshot)
                    
                    if after_login_img:
                        reporter.add_final_screenshot(after_login_screenshot)
                    
                    # Kiểm tra xem có thông báo lỗi không
                    error_messages = driver.find_elements(By.CSS_SELECTOR, ".alert.alert-danger, .error-message, .text-danger, [class*='error']")
                    if error_messages:
                        error_text = "\n".join([msg.text for msg in error_messages if msg.is_displayed()])
                        in_thong_bao(reporter, "Phát hiện thông báo lỗi", "PASS", 
                                    input_data=error_text[:500],  # Giới hạn độ dài
                                    expected=expected_result)
                    else:
                        # Kiểm tra xem có phải vẫn ở trang đăng nhập không
                        if "dang-nhap" in driver.current_url or "login" in driver.current_url:
                            in_thong_bao(reporter, "Đăng nhập thất bại (vẫn ở trang đăng nhập)", "PASS",
                                       expected=expected_result)
                        else:
                            in_thong_bao(reporter, "Không xác định được kết quả đăng nhập", "WARNING",
                                       notes="Không tìm thấy thông báo lỗi nhưng có thể đăng nhập thất bại")
                    
                except TimeoutException:
                    # Nếu không tìm thấy thông báo lỗi, kiểm tra xem có đăng nhập thành công không
                    if "dang-nhap" not in driver.current_url and "login" not in driver.current_url:
                        in_thong_bao(reporter, "Cảnh báo: Có thể đã đăng nhập thành công", "WARNING",
                                   expected="Đăng nhập thất bại nhưng có vẻ đã đăng nhập thành công")
                        
                        # Chụp màn hình trang sau đăng nhập
                        after_login_screenshot = os.path.join(test_case_dir, "co_the_da_dang_nhap_thanh_cong.png")
                        chup_man_hinh(driver, after_login_screenshot)
                    else:
                        in_thong_bao(reporter, "Không xác định được kết quả đăng nhập", "WARNING")
                
            except Exception as e:
                error_msg = f"Lỗi khi thực hiện đăng nhập: {str(e)}"
                in_thong_bao(reporter, error_msg, "ERROR")
                
                # Chụp màn hình lỗi
                error_screenshot = os.path.join(test_case_dir, "loi_khi_dang_nhap.png")
                error_img = chup_man_hinh(driver, error_screenshot)
                if error_img:
                    reporter.add_final_screenshot(error_img)
                
                # Lưu mã nguồn trang lỗi
                error_html = os.path.join(test_case_dir, "ma_nguon_loi_dang_nhap.html")
                with open(error_html, 'w', encoding='utf-8') as f:
                    f.write(driver.page_source)
                try:
                    # Thử click bình thường trước
                    nut_dang_nhap.click()
                except:
                    # Nếu không được, thử click bằng JavaScript
                    driver.execute_script("arguments[0].click();", nut_dang_nhap)
                
                in_thong_bao("   - Đã gửi yêu cầu đăng nhập", "OK")
                
                # Chờ xử lý đăng nhập
                in_thong_bao("\nĐang xử lý đăng nhập...")
                
                # Chờ đợi một trong hai điều kiện:
                # 1. Có thông báo lỗi hiển thị
                # 2. Hoặc URL thay đổi
                try:
                    WebDriverWait(driver, 5).until(
                        lambda d: "Sai tài khoản hoặc mật khẩu" in d.page_source or 
                                "dang-nhap" not in d.current_url
                    )
                except:
                    pass  # Tiếp tục nếu hết thời gian chờ
                
                time.sleep(1)  # Chờ thêm 1 giây để đảm bảo trang đã tải xong
                
                # Chụp màn hình sau khi đăng nhập
                screenshot_after = os.path.join(test_case_dir, "sau_khi_dang_nhap.png")
                driver.save_screenshot(screenshot_after)
                in_thong_bao(f"   - Đã lưu ảnh màn hình sau khi đăng nhập: {screenshot_after}")
                
                # Lưu mã nguồn trang sau khi đăng nhập
                page_source_after = os.path.join(test_case_dir, "trang_sau_khi_dang_nhap.html")
                with open(page_source_after, 'w', encoding='utf-8') as f:
                    f.write(driver.page_source)
                in_thong_bao(f"   - Đã lưu mã nguồn trang sau khi đăng nhập: {page_source_after}")
                
                # Kiểm tra thông báo lỗi
                co_loi, thong_bao = kiem_tra_loi_dang_nhap(driver)
                
                if co_loi:
                    in_thong_bao(f"\n[✗] ĐĂNG NHẬP THẤT BẠI NHƯ DỰ KIẾN", "LOI")
                    in_thong_bao(f"   - Thông báo lỗi: {thong_bao}")
                    in_thong_bao(f"   - URL hiện tại: {driver.current_url}")
                    
                    # Lưu thông tin lỗi chi tiết
                    error_log = os.path.join(test_case_dir, "chi_tiet_loi.txt")
                    with open(error_log, 'w', encoding='utf-8') as f:
                        f.write(f"Tên test case: {test_case_name}\n")
                        f.write(f"Thời gian: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                        f.write(f"Tài khoản: {current_username}\n")
                        f.write(f"Mật khẩu: {'*' * len(current_password) if current_password else '(để trống)'}\n")
                        f.write(f"URL: {driver.current_url}\n")
                        f.write(f"Thông báo lỗi: {thong_bao}\n")
                        f.write(f"Kết quả: THẤT BẠI (Đúng như mong đợi)\n")
                    in_thong_bao(f"   - Đã lưu thông tin lỗi: {error_log}")
                    
                    in_thong_bao("\n✓ KIỂM TRA THÀNH CÔNG: Hệ thống đã xử lý đăng nhập thất bại đúng cách", "OK")
                    
                    # Lưu kết quả test case
                    result_file = os.path.join(test_case_dir, "ket_qua_test_case.txt")
                    with open(result_file, 'w', encoding='utf-8') as f:
                        f.write(f"TÊN TEST CASE: {test_case_name}\n")
                        f.write(f"THỜI GIAN: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                        f.write("TRẠNG THÁI: THÀNH CÔNG\n")
                        f.write("MÔ TẢ: Hệ thống hiển thị thông báo lỗi phù hợp khi đăng nhập thất bại\n")
                    
                else:
                    in_thong_bao("\n[!] CẢNH BÁO: Không phát hiện thông báo lỗi đăng nhập", "LOI")
                    in_thong_bao(f"   - URL hiện tại: {driver.current_url}")
                    
                    # Lưu thông tin cảnh báo
                    warning_log = os.path.join(test_case_dir, "canh_bao_khong_phat_hien_loi.txt")
                    with open(warning_log, 'w', encoding='utf-8') as f:
                        f.write(f"Tên test case: {test_case_name}\n")
                        f.write(f"Thời gian: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                        f.write(f"Tài khoản: {current_username}\n")
                        f.write(f"Mật khẩu: {'*' * len(current_password) if current_password else '(để trống)'}\n")
                        f.write(f"URL: {driver.current_url}\n")
                        f.write("Cảnh báo: Không phát hiện thông báo lỗi đăng nhập\n")
                        f.write("Kết quả: THẤT BẠI (Không đúng như mong đợi)\n")
                    
            except Exception as e:
                in_thong_bao(f"   - Lỗi khi nhấn nút đăng nhập: {str(e)}", "LOI")
                # Lưu thông tin lỗi
                error_log = os.path.join(test_case_dir, "loi_khi_nhan_nut_dang_nhap.txt")
                with open(error_log, 'w', encoding='utf-8') as f:
                    f.write(f"Tên test case: {test_case_name}\n")
                    f.write(f"Thời gian: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                    f.write(f"Lỗi: {str(e)}\n")
                    f.write("\n=== TRACE BACK ===\n")
                    import traceback
                    f.write(traceback.format_exc())
                continue  # Tiếp tục với test case tiếp theo
                f.write(f"Thời gian lỗi: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                f.write(f"Lỗi: {str(e)}\n")
                if 'current_username' in locals():
                    f.write(f"Tài khoản: {current_username}\n")
                if 'current_password' in locals():
                    f.write(f"Mật khẩu: {'*' * len(current_password) if current_password else '(để trống)'}\n")
                f.write("\n=== TRACE BACK ===\n")
                import traceback
                f.write(traceback.format_exc())
            
            # Chụp màn hình lỗi
            error_screenshot = os.path.join(test_run_dir, "man_hinh_loi.png")
            driver.save_screenshot(error_screenshot)
            in_thong_bao(f"   - Đã lưu ảnh lỗi: {error_screenshot}")
            
            # Lưu mã nguồn trang lỗi
            error_page = os.path.join(test_run_dir, "ma_nguon_loi.html")
            with open(error_page, 'w', encoding='utf-8') as f:
                f.write(driver.page_source)
            in_thong_bao(f"   - Đã lưu mã nguồn trang lỗi: {error_page}")

        # Giữ trình duyệt mở trong 10 giây trước khi đóng
        if driver:
            try:
                # Chụp màn hình cuối cùng trước khi đóng
                final_screenshot = os.path.join(test_run_dir, "ket_thuc_test.png")
                driver.save_screenshot(final_screenshot)
                in_thong_bao(f"Đã lưu ảnh màn hình cuối cùng: {final_screenshot}")
                
                # Lưu mã nguồn trang cuối cùng
                final_page_source = os.path.join(test_run_dir, "trang_cuoi_cung.html")
                with open(final_page_source, 'w', encoding='utf-8') as f:
                    f.write(driver.page_source)
                in_thong_bao(f"Đã lưu mã nguồn trang cuối cùng: {final_page_source}")
                
                # Đếm ngược trước khi đóng
                print("\nSẽ đóng trình duyệt sau 10 giây nữa...")
                for i in range(10, 0, -1):
                    print(f"\rĐóng sau {i} giây...", end="")
                    time.sleep(1)
            except Exception as e:
                error_msg = f"Lỗi khi lưu thông tin cuối cùng: {str(e)}"
                print(f"\n{error_msg}")
                # Ghi log lỗi vào file
                error_log = os.path.join(test_run_dir, "loi_luu_thong_tin_cuoi.txt")
                with open(error_log, 'w', encoding='utf-8') as f:
                    f.write(f"Thời gian lỗi: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                    f.write(f"Lỗi: {str(e)}\n")
                    import traceback
                    f.write("\n=== TRACE BACK ===\n")
                    traceback.print_exc(file=f)
    except Exception as e:
        error_msg = f"Lỗi không mong muốn: {str(e)}"
        print(f"\n{error_msg}")
        import traceback
        traceback.print_exc()
        
        # Ghi log lỗi
        error_log = os.path.join(test_run_dir, "loi_he_thong.txt")
        with open(error_log, 'w', encoding='utf-8') as f:
            f.write(f"Thời gian lỗi: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
            f.write(f"Lỗi: {str(e)}\n")
            f.write("\n=== TRACE BACK ===\n")
            traceback.print_exc(file=f)
            
    finally:
        if driver:
            try:
                # Chụp màn hình cuối cùng trước khi đóng
                final_screenshot = os.path.join(test_run_dir, "ket_thuc_test.png")
                final_img = chup_man_hinh(driver, final_screenshot)
                
                # Lưu báo cáo trước khi đóng trình duyệt
                reporter.save_report(test_run_dir, final_img if final_img else None)
                
                # Đóng trình duyệt
                driver.quit()
                in_thong_bao(reporter, "Đã đóng trình duyệt", "INFO")
                
                print(f"\nĐã lưu báo cáo tại thư mục: {test_run_dir}")
                
                # Tạo file đánh dấu hoàn thành
                completion_file = os.path.join(test_run_dir, "hoan_thanh.txt")
                with open(completion_file, 'w', encoding='utf-8') as f:
                    f.write(f"Test hoàn thành lúc: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                    f.write(f"Báo cáo: {os.path.join(test_run_dir, 'ket_qua_kiem_tra_dang_nhap_that_bai_*.xlsx')}\n")
                
            except Exception as e:
                print(f"Lỗi khi kết thúc chương trình: {str(e)}")
                # Vẫn cố gắng lưu báo cáo ngay cả khi có lỗi
                try:
                    reporter.save_report(test_run_dir)
                except:
                    pass
        else:
            # Vẫn lưu báo cáo nếu driver không được khởi tạo
            try:
                reporter.save_report(test_run_dir)
                print(f"\nĐã lưu báo cáo tại thư mục: {test_run_dir}")
            except:
                pass

if __name__ == "__main__":
    main()
