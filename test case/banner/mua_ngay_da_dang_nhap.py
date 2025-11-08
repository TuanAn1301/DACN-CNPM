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
from PIL import Image as PILImage
import traceback

# Tạo thư mục kết quả
def tao_thu_muc_ket_qua():
    base_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "ket_qua_test")
    if not os.path.exists(base_dir):
        os.makedirs(base_dir)
    
    # Tạo thư mục theo thời gian
    thoi_gian = datetime.now().strftime("%Y%m%d_%H%M%S")
    thu_muc_test = os.path.join(base_dir, f"tc_mua_ngay_da_dang_nhap_{thoi_gian}")
    os.makedirs(thu_muc_test)
    
    return thu_muc_test

class TestReporter:
    def __init__(self, test_name):
        self.test_name = test_name
        self.wb = Workbook()
        # Tạo sheet kết quả chính
        self.ws = self.wb.active
        self.ws.title = "Kết quả kiểm thử"
        # Tạo sheet thông tin đơn hàng
        self.ws_order = self.wb.create_sheet("Thông tin đơn hàng")
        self.current_row = 1
        self.screenshot_count = 0
        self.test_steps = []
        self.order_info = {}
        self.setup_worksheet()
        self.setup_order_sheet()
        
    def setup_worksheet(self):
        # Set up column headers
        headers = ["Bước thực hiện", "Trạng thái", "Ghi chú"]
        for col_num, header in enumerate(headers, 1):
            cell = self.ws.cell(row=self.current_row, column=col_num, value=header)
            cell.font = Font(bold=True, color="000000")
            cell.fill = PatternFill(start_color="D9EAD3", end_color="D9EAD3", fill_type="solid")
            
        # Set column widths
        self.ws.column_dimensions['A'].width = 50
        self.ws.column_dimensions['B'].width = 15
        self.ws.column_dimensions['C'].width = 50
        
        # Định dạng cột
        for col in self.ws.columns:
            for cell in col:
                cell.alignment = Alignment(vertical='center', wrap_text=True)
    
    def setup_order_sheet(self):
        """Thiết lập sheet thông tin đơn hàng"""
        # Tiêu đề
        self.ws_order.merge_cells('A1:B1')
        title_cell = self.ws_order['A1']
        title_cell.value = "THÔNG TIN ĐƠN HÀNG"
        title_cell.font = Font(bold=True, size=14, color="FFFFFF")
        title_cell.fill = PatternFill(start_color="4472C4", end_color="4472C4", fill_type="solid")
        title_cell.alignment = Alignment(horizontal='center', vertical='center')
        
        # Đặt chiều cao hàng tiêu đề
        self.ws_order.row_dimensions[1].height = 25
        
        # Đặt chiều rộng cột
        self.ws_order.column_dimensions['A'].width = 25
        self.ws_order.column_dimensions['B'].width = 50
        
        # Thêm thông tin test case
        self.ws_order.append(["Tên test case", self.test_name])
        self.ws_order.append(["Thời gian bắt đầu", datetime.now().strftime('%d/%m/%Y %H:%M:%S')])
        self.ws_order.append(["Trạng thái", "Đang thực hiện..."])
        
        # Định dạng các ô
        for row in self.ws_order.iter_rows(min_row=2, max_row=4, min_col=1, max_col=2):
            for cell in row:
                cell.font = Font(size=11)
                cell.border = Border(left=Side(style='thin'), right=Side(style='thin'), 
                                   top=Side(style='thin'), bottom=Side(style='thin'))
        
        # Căn giữa cột đầu tiên
        for row in self.ws_order.iter_rows(min_row=2, max_row=4, min_col=1, max_col=1):
            for cell in row:
                cell.font = Font(bold=True)
                cell.fill = PatternFill(start_color="F2F2F2", end_color="F2F2F2", fill_type="solid")
    
    def update_order_info(self, order_data):
        """Cập nhật thông tin đơn hàng vào sheet"""
        if not order_data:
            return
            
        # Lưu thông tin đơn hàng
        self.order_info.update(order_data)
        
        # Xóa dữ liệu cũ (giữ lại 4 dòng tiêu đề)
        for row in range(5, self.ws_order.max_row + 1):
            for col in range(1, 3):
                self.ws_order.cell(row=row, column=col).value = None
        
        # Thêm thông tin đơn hàng
        row = 5
        
        # Thêm thông tin cơ bản
        for key in ['Mã đơn hàng', 'Thời gian đặt hàng', 'Tổng tiền', 'Phương thức thanh toán', 'Trạng thái đơn hàng']:
            if key in order_data:
                self.ws_order.cell(row=row, column=1, value=key).font = Font(bold=True)
                self.ws_order.cell(row=row, column=1).fill = PatternFill(
                    start_color="F2F2F2", end_color="F2F2F2", fill_type="solid")
                self.ws_order.cell(row=row, column=2, value=order_data[key])
                row += 1
        
        # Thêm thông tin sản phẩm
        if 'san_pham' in order_data and order_data['san_pham']:
            self.ws_order.cell(row=row, column=1, value="Sản phẩm đã đặt").font = Font(bold=True, color="FFFFFF")
            self.ws_order.cell(row=row, column=1).fill = PatternFill(
                start_color="4472C4", end_color="4472C4", fill_type="solid")
            self.ws_order.merge_cells(start_row=row, start_column=1, end_row=row, end_column=2)
            row += 1
            
            # Đặt tiêu đề cột cho bảng sản phẩm
            headers = ["Tên sản phẩm", "Số lượng", "Đơn giá", "Thành tiền"]
            for col, header in enumerate(headers, 1):
                cell = self.ws_order.cell(row=row, column=col, value=header)
                cell.font = Font(bold=True)
                cell.fill = PatternFill(start_color="D9EAD3", end_color="D9EAD3", fill_type="solid")
            row += 1
            
            # Thêm từng sản phẩm
            if isinstance(order_data['san_pham'], list):
                for sp in order_data['san_pham']:
                    if isinstance(sp, dict):
                        # Xử lý sản phẩm dạng dict
                        self.ws_order.cell(row=row, column=1, value=sp.get('ten', ''))
                        self.ws_order.cell(row=row, column=2, value=sp.get('so_luong', 1))
                        self.ws_order.cell(row=row, column=3, value=sp.get('don_gia', ''))
                        self.ws_order.cell(row=row, column=4, value=sp.get('thanh_tien', ''))
                    else:
                        # Xử lý sản phẩm dạng chuỗi
                        self.ws_order.cell(row=row, column=1, value=str(sp))
                        self.ws_order.merge_cells(start_row=row, start_column=1, end_row=row, end_column=4)
                    row += 1
            
            # Thêm tổng cộng
            if 'tong_tien' in order_data:
                self.ws_order.cell(row=row, column=3, value="Tổng cộng:").font = Font(bold=True)
                self.ws_order.cell(row=row, column=4, value=order_data['tong_tien']).font = Font(bold=True, color="FF0000")
                row += 1
            
            row += 1  # Thêm dòng trống
        
        # Thêm thông tin khác nếu có
        for key, value in order_data.items():
            if key not in ['Mã đơn hàng', 'Thời gian đặt hàng', 'Tổng tiền', 
                         'Phương thức thanh toán', 'Trạng thái đơn hàng', 'san_pham', 'tong_tien']:
                self.ws_order.cell(row=row, column=1, value=key).font = Font(bold=True)
                self.ws_order.cell(row=row, column=1).fill = PatternFill(
                    start_color="F2F2F2", end_color="F2F2F2", fill_type="solid")
                self.ws_order.cell(row=row, column=2, value=str(value))
                row += 1
        
        # Tự động điều chỉnh độ rộng cột
        for col in self.ws_order.columns:
            max_length = 0
            column = col[0].column_letter
            for cell in col:
                try:
                    if len(str(cell.value)) > max_length:
                        max_length = len(str(cell.value))
                except:
                    pass
            adjusted_width = (max_length + 2) * 1.2
            self.ws_order.column_dimensions[column].width = min(adjusted_width, 50)
        
        # Thêm đường viền cho các ô
        for r in range(1, row):
            for c in range(1, 5):
                cell = self.ws_order.cell(row=r, column=c)
                if cell.value:  # Chỉ thêm border cho ô có dữ liệu
                    cell.border = Border(left=Side(style='thin'), right=Side(style='thin'), 
                                       top=Side(style='thin'), bottom=Side(style='thin'))
        
        # Cập nhật thông tin đơn hàng vào báo cáo
        if hasattr(self, 'ws_order'):
            self.ws_order.sheet_state = 'visible'
        
    def add_step(self, description, status='PASS', notes=''):
        """Add a test step to the report"""
        # Save step info to list
        step_info = {
            'description': description,
            'status': status,
            'notes': notes,
            'screenshot': None,
            'timestamp': datetime.now().strftime('%H:%M:%S')
        }
        self.test_steps.append(step_info)
        
        # Add to worksheet with timestamp
        self.ws.append([
            f"[{step_info['timestamp']}] {description}",
            status,
            notes
        ])
        
        # Format status cell
        status_cell = self.ws.cell(row=self.current_row, column=2)
        if status == 'PASS' or status == 'THÀNH CÔNG':
            status_cell.fill = PatternFill(start_color="C6EFCE", end_color="C6EFCE", fill_type="solid")
            status_cell.font = Font(color="006100", bold=True)
        elif status in ['FAIL', 'ERROR', 'LỖI', 'LOI']:
            status_cell.fill = PatternFill(start_color="FFC7CE", end_color="FFC7CE", fill_type="solid")
            status_cell.font = Font(color="9C0006", bold=True)
        else:
            status_cell.fill = PatternFill(start_color="FFEB9C", end_color="FFEB9C", fill_type="solid")
            status_cell.font = Font(color="9C5700")
            
        # Nếu là thông báo đặt hàng thành công, cập nhật thông tin đơn hàng
        if "Đặt hàng thành công" in description and notes:
            order_data = {}
            for line in notes.split('\n'):
                if ':' in line:
                    key, value = line.split(':', 1)
                    order_data[key.strip()] = value.strip()
            self.update_order_info(order_data)
            
        # Center align cells
        for col in range(1, 4):
            self.ws.cell(row=self.current_row, column=col).alignment = Alignment(
                vertical='center', 
                wrap_text=True
            )
            
        self.current_row += 1
        
    def add_screenshot(self, image_path, description=""):
        """Add a screenshot to the report with improved formatting"""
        try:
            if not os.path.exists(image_path):
                self.add_step(f"Không tìm thấy ảnh: {image_path}", "CẢNH BÁO")
                return False
                
            # Thêm mô tả ảnh với định dạng đẹp hơn
            if description:
                cell = self.ws.cell(row=self.current_row, column=1, value=f"Hình ảnh: {description}")
                cell.font = Font(bold=True, color="2E75B6")
                cell.fill = PatternFill(start_color="E2EFDA", end_color="E2EFDA", fill_type="solid")
                self.ws.merge_cells(start_row=self.current_row, start_column=1, end_row=self.current_row, end_column=3)
                self.current_row += 1
            
            # Đọc và xử lý ảnh
            img = PILImage.open(image_path)
            
            # Tính toán kích thước mới để phù hợp với Excel
            max_width = 800
            max_height = 500
            
            # Lấy tỷ lệ co giãn
            width_ratio = max_width / img.width
            height_ratio = max_height / img.height
            
            # Chọn tỷ lệ nhỏ hơn để đảm bảo ảnh vừa với cả hai chiều
            scale = min(width_ratio, height_ratio, 1.0)  # Không phóng to ảnh nhỏ hơn kích thước gốc
            
            # Tính kích thước mới
            new_width = int(img.width * scale)
            new_height = int(img.height * scale)
            
            # Thay đổi kích thước ảnh
            img = img.resize((new_width, new_height), PILImage.Resampling.LANCZOS)
            
            # Lưu ảnh đã xử lý vào file tạm
            temp_img_path = os.path.join(os.path.dirname(image_path), f"excel_{os.path.basename(image_path)}")
            img.save(temp_img_path, quality=85)  # Giảm chất lượng để giảm kích thước file
            
            try:
                # Thêm ảnh vào Excel
                img_for_excel = XLImage(temp_img_path)
                
                # Đặt vị trí ảnh (căn giữa)
                img_col = 'A'
                img_row = self.current_row
                
                # Thêm ảnh vào ô A hiện tại
                self.ws.add_image(img_for_excel, f'{img_col}{img_row}')
                
                # Điều chỉnh chiều cao hàng để vừa với ảnh
                # Chuyển đổi từ pixel sang đơn vị hàng Excel (xấp xỉ)
                row_height = img.height * 0.75
                self.ws.row_dimensions[img_row].height = min(row_height, 409)  # Giới hạn chiều cao tối đa của Excel
                
                # Điều chỉnh cột A để vừa với ảnh
                col_letter = img_col
                col_width = (img.width / 7) + 2  # Điều chỉnh tỷ lệ cho phù hợp
                current_width = self.ws.column_dimensions[col_letter].width or 8.43  # Giá trị mặc định của Excel
                self.ws.column_dimensions[col_letter].width = min(max(col_width, current_width), 50)  # Giới hạn độ rộng tối đa
                
                # Thêm border cho ảnh
                for r in range(img_row, img_row + int(img.height/15) + 1):
                    for c in range(1, 4):  # Cột A đến C
                        cell = self.ws.cell(row=r, column=c)
                        if not cell.value:  # Chỉ thêm border nếu ô trống
                            cell.border = Border(
                                left=Side(style='thin'),
                                right=Side(style='thin'),
                                top=Side(style='thin'),
                                bottom=Side(style='thin')
                            )
                
                self.current_row += int(img.height/15) + 2  # Thêm khoảng cách sau ảnh
                
            except Exception as img_error:
                self.add_step(f"Lỗi khi thêm ảnh vào Excel: {str(img_error)[:200]}", "LỖI")
                return False
            finally:
                # Xóa file tạm
                if os.path.exists(temp_img_path):
                    try:
                        os.remove(temp_img_path)
                    except:
                        pass
            
            return True
            
        except Exception as e:
            error_msg = str(e)[:200]
            print(f"Lỗi khi xử lý ảnh: {error_msg}")
            self.add_step(f"Lỗi khi xử lý ảnh: {error_msg}", "LỖI")
            return False
    
    def save_report(self, folder_path):
        """Save the report to an Excel file"""
        try:
            # Create directory if it doesn't exist
            if not os.path.exists(folder_path):
                os.makedirs(folder_path)
            
            # Cập nhật thời gian kết thúc
            end_time = datetime.now().strftime('%d/%m/%Y %H:%M:%S')
            self.ws_order['B4'] = end_time
            
            # Thêm thống kê test case
            total_steps = len(self.test_steps)
            passed = sum(1 for s in self.test_steps if s['status'] in ['PASS', 'THÀNH CÔNG'])
            failed = sum(1 for s in self.test_steps if s['status'] in ['FAIL', 'ERROR', 'LỖI', 'LOI'])
            
            # Thêm thông tin thống kê vào sheet thông tin đơn hàng
            self.ws_order.append(["Tổng số bước", total_steps])
            self.ws_order.append(["Thành công", passed])
            self.ws_order.append(["Thất bại", failed])
            
            # Định dạng các ô thống kê
            for row in range(5, 8):
                self.ws_order.cell(row=row, column=1).font = Font(bold=True)
                self.ws_order.cell(row=row, column=1).fill = PatternFill(
                    start_color="F2F2F2", end_color="F2F2F2", fill_type="solid")
                
                for col in [1, 2]:
                    cell = self.ws_order.cell(row=row, column=col)
                    cell.border = Border(left=Side(style='thin'), right=Side(style='thin'), 
                                       top=Side(style='thin'), bottom=Side(style='thin'))
            
            # Điều chỉnh chiều rộng cột cho phù hợp
            for column in self.ws.columns:
                max_length = 0
                column_letter = column[0].column_letter
                for cell in column:
                    try:
                        if len(str(cell.value)) > max_length:
                            max_length = len(str(cell.value))
                    except:
                        pass
                adjusted_width = (max_length + 2) * 1.2
                self.ws.column_dimensions[column_letter].width = min(adjusted_width, 100)
            
            # Generate report filename
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            report_filename = os.path.join(folder_path, f"ket_qua_kiem_thu_{timestamp}.xlsx")
            
            # Save the file
            try:
                self.wb.save(report_filename)
                print(f"\nĐã lưu báo cáo tại: {os.path.abspath(report_filename)}")
                
                # Try to open the file automatically
                try:
                    if os.name == 'nt':  # For Windows
                        os.startfile(os.path.abspath(report_filename))
                    elif os.name == 'posix':  # For macOS and Linux
                        if sys.platform == 'darwin':  # macOS
                            os.system(f'open "{os.path.abspath(report_filename)}"')
                        else:  # Linux
                            os.system(f'xdg-open "{os.path.abspath(report_filename)}"')
                except Exception as e:
                    print(f"Không thể mở file tự động: {str(e)}")
                
                return report_filename
                
            except PermissionError:
                # Nếu không thể lưu file, thử lưu với tên khác
                alt_filename = os.path.join(folder_path, f"ket_qua_kiem_thu_{timestamp}_1.xlsx")
                self.wb.save(alt_filename)
                print(f"Lưu file thay thế tại: {os.path.abspath(alt_filename)}")
                return alt_filename
            
        except Exception as e:
            print(f"Lỗi khi lưu báo cáo: {str(e)}")
            import traceback
            traceback.print_exc()
            return None

def in_thong_bao(buoc, trang_thai="", reporter=None, notes=""):
    """Hiển thị thông báo từng bước thực hiện và thêm vào báo cáo nếu có reporter"""
    # Hiển thị ra console
    if trang_thai.upper() == "OK":
        print(f"[✓] {buoc}")
        status = "PASS"
    elif trang_thai.upper() in ["LOI", "LỖI"]:
        print(f"[✗] {buoc}")
        status = "ERROR"
    else:
        print(f"[ ] {buoc}")
        status = "INFO"
    
    sys.stdout.flush()
    
    # Thêm vào báo cáo nếu có reporter
    if reporter:
        reporter.add_step(description=buoc, status=status, notes=notes)
        
    return status

def chup_man_hinh(driver, ten_file, reporter=None, description=""):
    """Chụp màn hình và lưu vào file"""
    try:
        # Tạo thư mục nếu chưa tồn tại
        os.makedirs(os.path.dirname(ten_file), exist_ok=True)
        
        # Chụp màn hình
        driver.save_screenshot(ten_file)
        
        # Thêm vào báo cáo nếu có reporter
        if reporter and description:
            reporter.add_screenshot(ten_file, description)
            
        return True
    except Exception as e:
        error_msg = f"Lỗi khi chụp màn hình: {str(e)}"
        in_thong_bao(error_msg, "LỖI", reporter, error_msg)
        return False

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
        in_thong_bao("Đang thực hiện đăng nhập...")
        
        # Mở trang đăng nhập
        in_thong_bao("   - Đang mở trang đăng nhập...")
        driver.get("http://localhost/webbansach/dang-nhap.php")
        time.sleep(2)
        
        # Chụp màn hình trước khi đăng nhập
        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "truoc_dang_nhap.png"))
        
        # Tìm và điền thông tin đăng nhập
        in_thong_bao("   - Đang điền thông tin đăng nhập...")
        
        # Tìm và điền tên đăng nhập (sử dụng ID 'email1')
        try:
            o_tai_khoan = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.ID, "email1"))
            )
            o_tai_khoan.clear()
            o_tai_khoan.send_keys(tai_khoan)
            in_thong_bao("   - Đã nhập tên đăng nhập", "OK")
            time.sleep(1)
        except Exception as e:
            in_thong_bao(f"   - Không tìm thấy ô tên đăng nhập (ID: email1): {str(e)}", "LOI")
            # Thử tìm bằng name nếu không tìm thấy bằng ID
            try:
                o_tai_khoan = driver.find_element(By.NAME, "taikhoan")
                o_tai_khoan.clear()
                o_tai_khoan.send_keys(tai_khoan)
                in_thong_bao("   - Đã nhập tên đăng nhập (tìm bằng name)", "OK")
                time.sleep(1)
            except Exception as e2:
                in_thong_bao(f"   - Không thể tìm thấy ô tên đăng nhập: {str(e2)}", "LOI")
                chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "loi_tim_o_dang_nhap.png"))
                return False

        # Tìm và điền mật khẩu (sử dụng ID 'login-password')
        try:
            o_mat_khau = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.ID, "login-password"))
            )
            o_mat_khau.clear()
            o_mat_khau.send_keys(mat_khau)
            in_thong_bao("   - Đã nhập mật khẩu", "OK")
            time.sleep(1)
        except Exception as e:
            in_thong_bao(f"   - Không tìm thấy ô mật khẩu (ID: login-password): {str(e)}", "LOI")
            # Thử tìm bằng name nếu không tìm thấy bằng ID
            try:
                o_mat_khau = driver.find_element(By.NAME, "matkhau")
                o_mat_khau.clear()
                o_mat_khau.send_keys(mat_khau)
                in_thong_bao("   - Đã nhập mật khẩu (tìm bằng name)", "OK")
                time.sleep(1)
            except Exception as e2:
                in_thong_bao(f"   - Không thể tìm thấy ô mật khẩu: {str(e2)}", "LOI")
                chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "loi_tim_mat_khau.png"))
                return False

        # Chụp màn hình sau khi điền thông tin
        chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "sau_khi_dien_thong_tin.png"))
        
        # Nhấn nút đăng nhập
        try:
            # Thử tìm nút đăng nhập bằng nhiều cách khác nhau
            try:
                # Cách 1: Tìm bằng name="dangnhap"
                nut_dang_nhap = WebDriverWait(driver, 5).until(
                    EC.element_to_be_clickable((By.NAME, "dangnhap"))
                )
            except:
                # Cách 2: Tìm bằng CSS selector cho nút submit
                try:
                    nut_dang_nhap = WebDriverWait(driver, 5).until(
                        EC.element_to_be_clickable((By.CSS_SELECTOR, "input[type='submit'][name='dangnhap']"))
                    )
                except:
                    # Cách 3: Tìm bất kỳ nút submit nào
                    nut_dang_nhap = driver.find_element(By.CSS_SELECTOR, "button[type='submit'], input[type='submit']")
            
            # Cuộn đến nút đăng nhập để đảm bảo nó hiển thị
            driver.execute_script("arguments[0].scrollIntoView(true);", nut_dang_nhap)
            time.sleep(1)
            
            # Thử click bằng JavaScript nếu click thông thường không hoạt động
            try:
                nut_dang_nhap.click()
            except:
                driver.execute_script("arguments[0].click();", nut_dang_nhap)
            
            in_thong_bao("   - Đã nhấn nút đăng nhập", "OK")
            
            # Chờ chuyển trang hoặc cập nhật giao diện
            in_thong_bao("   - Đang chờ xử lý đăng nhập...")
            time.sleep(3)  # Chờ đăng nhập xử lý
            
            # Kiểm tra kết quả đăng nhập
            thanh_cong, thong_bao = kiem_tra_dang_nhap_thanh_cong(driver)
            if thanh_cong:
                in_thong_bao(f"   - {thong_bao}", "OK")
                chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "dang_nhap_thanh_cong.png"))
                return True
            else:
                in_thong_bao(f"   - {thong_bao}", "LOI")
                return False
                
        except Exception as e:
            in_thong_bao(f"   - Lỗi khi nhấn nút đăng nhập: {str(e)}", "LOI")
            chup_man_hinh(driver, os.path.join(thu_muc_ket_qua, "loi_nhan_nut_dang_nhap.png"))
            return False
            
    except Exception as e:
        in_thong_bao(f"Lỗi khi thực hiện đăng nhập: {str(e)}", "LOI")
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
    thu_muc_ket_qua = tao_thu_muc_ket_qua()
    
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
