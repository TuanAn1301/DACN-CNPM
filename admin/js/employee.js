// Handle employee form submission
function handleEmployeeFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Đang xử lý...';
    
    // Get the action URL (add or edit)
    const isEdit = form.dataset.edit === 'true';
    const url = isEdit ? 'api/update-employee.php' : 'api/add-employee.php';
    
    // Send AJAX request
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            if (typeof showToast === 'function') {
                showToast('success', data.message || 'Thao tác thành công!', 'Thành công');
            }
            
            // Redirect to employee list after a short delay
            setTimeout(() => {
                window.location.href = 'nhan-vien.php';
            }, 1500);
        } else {
            // Show error message
            if (typeof showToast === 'function') {
                showToast('error', data.message || 'Có lỗi xảy ra!', 'Lỗi');
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('error', 'Lỗi kết nối! Vui lòng thử lại.', 'Lỗi');
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

// Handle delete employee
function deleteEmployee(id) {
    if (confirm('Bạn có chắc chắn muốn xóa nhân viên này?')) {
        fetch(`api/delete-employee.php?id=${id}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('success', data.message || 'Xóa nhân viên thành công!', 'Thành công');
                }
                // Remove the row from the table
                const row = document.querySelector(`tr[data-employee-id="${id}"]`);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // If no more rows, show empty message
                        const tbody = document.querySelector('table tbody');
                        if (tbody && tbody.children.length === 0) {
                            const emptyRow = document.createElement('tr');
                            emptyRow.innerHTML = '<td colspan="6" class="text-center">Chưa có nhân viên nào.</td>';
                            tbody.appendChild(emptyRow);
                        }
                    }, 300);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast('error', data.message || 'Xóa nhân viên thất bại!', 'Lỗi');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('error', 'Lỗi kết nối! Vui lòng thử lại.', 'Lỗi');
            }
        });
    }
}

// Initialize employee form
function initEmployeeForm() {
    const form = document.getElementById('employee-form');
    if (form) {
        form.addEventListener('submit', handleEmployeeFormSubmit);
    }
    
    // Initialize delete buttons
    document.querySelectorAll('.btn-delete-employee').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            if (id) {
                deleteEmployee(id);
            }
        });
    });
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initEmployeeForm();
});
