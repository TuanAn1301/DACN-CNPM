import os
import time
import io
from datetime import datetime
from PIL import Image as PILImage
from openpyxl import Workbook, load_workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter
from openpyxl.cell import MergedCell
from openpyxl.drawing.image import Image as XLImage

class TestReporter:
    def __init__(self, test_name):
        self.test_name = test_name
        self.wb = Workbook()
        self.ws = self.wb.active
        self.ws.title = "Kết quả kiểm thử"
        self.current_row = 1
        self.screenshot_count = 0
        self.temp_files = []  # To keep track of temporary files
        self.test_steps = []
        self.setup_worksheet()
        
    def setup_worksheet(self):
        # Thiết lập tiêu đề và định dạng cho bảng tính
        self.ws = self.wb.active
        self.ws.title = self.test_name[:31]  # Giới hạn độ dài tiêu đề cho Excel
        
        # Định dạng cột
        self.ws.column_dimensions['A'].width = 15  # Thời gian
        self.ws.column_dimensions['B'].width = 40  # Bước thực hiện
        self.ws.column_dimensions['C'].width = 30  # Dữ liệu đầu vào
        self.ws.column_dimensions['D'].width = 30  # Kết quả mong đợi
        self.ws.column_dimensions['E'].width = 15  # Trạng thái
        self.ws.column_dimensions['F'].width = 60  # Ghi chú/Chi tiết
        
        # Tạo tiêu đề báo cáo
        self.ws.merge_cells('A1:F1')
        title_cell = self.ws.cell(row=1, column=1, value=f'BÁO CÁO KIỂM THỬ: {self.test_name.upper()}')
        title_cell.font = Font(size=14, bold=True, color='FFFFFF')
        title_cell.fill = PatternFill(start_color='4472C4', end_color='4472C4', fill_type='solid')
        title_cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
        
        # Thêm tiêu đề cột
        headers = ['Thời gian', 'Bước thực hiện', 'Dữ liệu đầu vào', 'Kết quả mong đợi', 'Trạng thái', 'Ghi chú/Chi tiết']
        for col_num, header in enumerate(headers, 1):
            cell = self.ws.cell(row=2, column=col_num, value=header)
            cell.font = Font(bold=True, color='FFFFFF')
            cell.fill = PatternFill(start_color='4F81BD', end_color='4F81BD', fill_type='solid')
            cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
            
        self.current_row = 3
    
    def add_step(self, description, status='PASS', input_data='', expected='', notes=''):
        """Thêm một bước kiểm thử vào báo cáo"""
        timestamp = datetime.now().strftime('%H:%M:%S')
        row_data = [timestamp, description, input_data, expected, status, notes]
        
        # Thêm dữ liệu vào bảng tính
        for col_num, value in enumerate(row_data, 1):
            cell = self.ws.cell(row=self.current_row, column=col_num, value=value)
            cell.alignment = Alignment(vertical='top', wrap_text=True)
            
            # Định dạng ô trạng thái
            if col_num == 5:  # Cột trạng thái
                if status == 'PASS':
                    cell.fill = PatternFill(start_color='C6EFCE', end_color='C6EFCE', fill_type='solid')
                    cell.font = Font(color='006100', bold=True)
                elif status == 'FAIL':
                    cell.fill = PatternFill(start_color='FFC7CE', end_color='FFC7CE', fill_type='solid')
                    cell.font = Font(color='9C0006', bold=True)
                elif status == 'WARNING':
                    cell.fill = PatternFill(start_color='FFEB9C', end_color='FFEB9C', fill_type='solid')
                    cell.font = Font(color='9C5700', bold=True)
                elif status == 'ERROR':
                    cell.fill = PatternFill(start_color='FFC7CE', end_color='FFC7CE', fill_type='solid')
                    cell.font = Font(color='FF0000', bold=True)
                else:  # INFO
                    cell.fill = PatternFill(start_color='DDEBF7', end_color='DDEBF7', fill_type='solid')
                    cell.font = Font(color='000000')
                cell.alignment = Alignment(horizontal='center', vertical='center')
        
        self.current_row += 1
            
    def add_final_screenshot(self, screenshot):
        """Thêm ảnh chụp màn hình cuối cùng vào báo cáo"""
        if not screenshot:
            return
            
        try:
            # Lưu ảnh vào bộ nhớ thay vì file tạm
            img_io = io.BytesIO()
            screenshot.save(img_io, format='PNG')
            img_io.seek(0)
            
            # Thêm dòng trống trước khi chèn ảnh
            self.current_row += 1
            
            # Thêm tiêu đề cho phần ảnh chụp màn hình
            self.ws.merge_cells(f'A{self.current_row}:F{self.current_row}')
            title_cell = self.ws.cell(row=self.current_row, column=1, value='HÌNH ẢNH KẾT QUẢ KIỂM THỬ')
            title_cell.font = Font(bold=True, color='FFFFFF')
            title_cell.fill = PatternFill(start_color='4472C4', end_color='4472C4', fill_type='solid')
            title_cell.alignment = Alignment(horizontal='center', vertical='center')
            
            # Thêm ảnh vào báo cáo (tạo một ô lớn từ cột A đến F)
            self.current_row += 1
            
            # Tạo một file tạm thời với tên ngắn hơn
            temp_dir = os.path.join(os.getcwd(), 'temp_screenshots')
            os.makedirs(temp_dir, exist_ok=True)
            temp_img_path = os.path.join(temp_dir, f'scr_{self.screenshot_count}.png')
            self.screenshot_count += 1
            
            # Lưu ảnh vào file tạm
            with open(temp_img_path, 'wb') as f:
                f.write(img_io.getvalue())
            
            # Thêm ảnh vào Excel
            try:
                img = XLImage(temp_img_path)
                
                # Tính toán kích thước ảnh
                img_ratio = img.height / img.width
                img_width = 600  # Độ rộng mục tiêu
                img_height = int(img_width * img_ratio)
                
                # Giới hạn chiều cao tối đa
                max_height = 400
                if img_height > max_height:
                    img_height = max_height
                    img_width = int(img_height / img_ratio)
                
                img.width = img_width
                img.height = img_height
                
                # Thêm ảnh vào worksheet
                img_anchor = f'A{self.current_row}'
                img.anchor = img_anchor
                self.ws.add_image(img)
                
                # Điều chỉnh chiều cao hàng
                row_height = min(int(img_height * 0.8), 400)
                self.ws.row_dimensions[self.current_row].height = row_height
                
                # Thêm border cho các ô
                for col in range(1, 7):
                    cell = self.ws.cell(row=self.current_row, column=col)
                    cell.border = Border(left=Side(style='thin'), 
                                       right=Side(style='thin'), 
                                       top=Side(style='thin'), 
                                       bottom=Side(style='thin'))
                
                # Thêm vào danh sách file tạm để xóa sau
                self.temp_files.append(temp_img_path)
                
            except Exception as img_error:
                print(f"Lỗi khi thêm ảnh vào báo cáo: {str(img_error)}")
                
        except Exception as e:
            print(f"Lỗi khi xử lý ảnh chụp màn hình: {str(e)}")
    
    def save_report(self, folder_path, final_screenshot=None):
        """Lưu báo cáo ra file Excel"""
        # Thêm ảnh chụp màn hình cuối cùng nếu có
        if final_screenshot:
            self.add_final_screenshot(final_screenshot)
            
        # Tạo thư mục nếu chưa tồn tại
        os.makedirs(folder_path, exist_ok=True)
        
        # Tạo tên file báo cáo
        report_name = f"ket_qua_{self.test_name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.xlsx"
        report_path = os.path.join(folder_path, report_name)
            
        # Tự động điều chỉnh độ rộng cột
        for col_idx in range(1, 7):  # Chúng ta có 6 cột từ A đến F
            max_length = 0
            column_letter = get_column_letter(col_idx)
            
            # Bỏ qua nếu cột đã được merge
            is_merged = False
            for merged_range in self.ws.merged_cells.ranges:
                if column_letter in [get_column_letter(c) for c in range(merged_range.min_col, merged_range.max_col + 1)]:
                    is_merged = True
                    break
            
            if is_merged:
                continue
                
            # Tìm độ dài lớn nhất của nội dung trong cột
            for row in self.ws.iter_rows(min_col=col_idx, max_col=col_idx):
                cell = row[0]
                if cell.value and not isinstance(cell, MergedCell):
                    try:
                        cell_value = str(cell.value)
                        if '\n' in cell_value:
                            max_line_length = max(len(line) for line in cell_value.split('\n'))
                            if max_line_length > max_length:
                                max_length = max_line_length
                        else:
                            if len(cell_value) > max_length:
                                max_length = len(cell_value)
                    except:
                        pass
            
            # Điều chỉnh độ rộng cột
            if max_length > 0:
                adjusted_width = (max_length + 2) * 1.2
                # Giới hạn độ rộng tối đa cho từng cột
                max_widths = {'A': 15, 'B': 50, 'C': 50, 'D': 50, 'E': 15, 'F': 50}
                max_width = max_widths.get(column_letter, 30)
                self.ws.column_dimensions[column_letter].width = min(adjusted_width, max_width)
        
        # Đặt chế độ in vừa trang
        self.ws.page_setup.fitToWidth = 1
        self.ws.page_setup.fitToHeight = 0
        
        # Tạo thư mục nếu chưa tồn tại
        os.makedirs(folder_path, exist_ok=True)
        
        # Tạo tên file báo cáo
        report_name = f"ket_qua_{self.test_name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.xlsx"
        report_path = os.path.join(folder_path, report_name)
        
        try:
            # Lưu file
            self.wb.save(report_path)
            return report_path
        finally:
            # Dọn dẹp file tạm
            for temp_file in self.temp_files:
                try:
                    if os.path.exists(temp_file):
                        os.remove(temp_file)
                except Exception as e:
                    print(f"Không thể xóa file tạm {temp_file}: {str(e)}")
            
            # Xóa thư mục tạm nếu rỗng
            temp_dir = os.path.join(os.getcwd(), 'temp_screenshots')
            try:
                if os.path.exists(temp_dir) and not os.listdir(temp_dir):
                    os.rmdir(temp_dir)
            except Exception as e:
                print(f"Không thể xóa thư mục tạm: {str(e)}")

def tao_thu_muc_ket_qua(test_name):
    """Tạo thư mục lưu kết quả kiểm thử"""
    thu_muc_goc = os.path.join(os.path.dirname(__file__), "ket_qua_test")
    os.makedirs(thu_muc_goc, exist_ok=True)
    
    now = datetime.now()
    thu_muc_ket_qua = os.path.join(thu_muc_goc, f"{test_name}_{now.strftime('%Y%m%d_%H%M%S')}")
    os.makedirs(thu_muc_ket_qua, exist_ok=True)
    return thu_muc_ket_qua

def chup_man_hinh(driver):
    """Chụp màn hình và trả về đối tượng ảnh"""
    try:
        screenshot = driver.get_screenshot_as_png()
        return PILImage.open(io.BytesIO(screenshot))
    except Exception as e:
        print(f"Lỗi khi chụp màn hình: {str(e)}")
        return None

def highlight_element(driver, element):
    """Tô sáng phần tử để dễ nhận biết"""
    try:
        original_style = element.get_attribute('style')
        driver.execute_script("arguments[0].style.border='3px solid red';", element)
        time.sleep(0.5)
        return original_style
    except Exception as e:
        print(f"Không thể tô sáng phần tử: {str(e)}")
        return ""

def in_thong_bao(reporter, noi_dung, status='PASS', input_data='', expected='', **kwargs):
    """In thông báo ra màn hình và thêm vào báo cáo
    
    Args:
        reporter: Đối tượng báo cáo
        noi_dung: Nội dung bước kiểm thử
        status: Trạng thái (PASS/FAIL/INFO/WARNING/ERROR)
        input_data: Dữ liệu đầu vào (nếu có)
        expected: Kết quả mong đợi (nếu có)
        **kwargs: Các tham số bổ sung (sẽ được thêm vào ghi chú)
    """
    timestamp = datetime.now().strftime('[%H:%M:%S]')
    status = status.upper()
    status_text = status if status in ['PASS', 'FAIL', 'INFO', 'WARNING', 'ERROR'] else 'INFO'
    
    # In ra console
    print(f"{timestamp} {noi_dung}")
    
    # Xử lý ghi chú từ các tham số bổ sung
    notes = []
    for key, value in kwargs.items():
        if value:  # Chỉ thêm nếu có giá trị
            notes.append(f"{key}: {value}")
    
    # Thêm vào báo cáo Excel
    if reporter:
        reporter.add_step(
            description=noi_dung,
            status=status_text,
            input_data=str(input_data) if input_data else '',
            expected=str(expected) if expected else '',
            notes='\n'.join(notes) if notes else ''
        )

    return True
