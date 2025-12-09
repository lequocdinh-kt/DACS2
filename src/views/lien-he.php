<main class="info-page">
    <div class="info-container">
        <div class="info-header">
            <i class="fas fa-address-book"></i>
            <h1>Liên Hệ</h1>
        </div>
        
        <div class="info-content">
            <section class="info-section">
                <h2>Thông Tin Liên Hệ</h2>
                <div class="contact-grid">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Địa Chỉ</h3>
                            <p>470 Trần Đại Nghĩa, Hoà Hải, Ngũ Hành Sơn, Đà Nẵng</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Điện Thoại</h3>
                            <p>0795701805</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>dinhlq.24itb@vku.udn.vn</p>
                            <p>huynn.24itb@vku.udn.vn</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Giờ Hoạt Động</h3>
                            <p>Hàng ngày: 8:00 - 23:00</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="info-section">
                <h2>Gửi Tin Nhắn Cho Chúng Tôi</h2>
                <form class="contact-form" id="contactForm">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Họ và tên" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Số điện thoại">
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Nội dung tin nhắn" rows="5" required></textarea>
                    </div>
                    <div class="form-message" id="formMessage"></div>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                    </button>
                </form>
            </section>
        </div>
    </div>
</main>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('submitBtn');
    const formMessage = document.getElementById('formMessage');
    
    // Disable button và hiển thị loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    formMessage.textContent = '';
    formMessage.className = 'form-message';
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('/src/controllers/contactController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            formMessage.textContent = result.message;
            formMessage.className = 'form-message success';
            form.reset(); // Xóa form sau khi gửi thành công
        } else {
            formMessage.textContent = result.message;
            formMessage.className = 'form-message error';
        }
    } catch (error) {
        console.error('Error:', error);
        formMessage.textContent = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        formMessage.className = 'form-message error';
    } finally {
        // Enable button lại
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi Tin Nhắn';
    }
});
</script>
