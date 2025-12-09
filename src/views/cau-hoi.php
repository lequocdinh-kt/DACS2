<main class="info-page">
    <div class="info-container">
        <div class="info-header">
            <i class="fas fa-question-circle"></i>
            <h1>Câu Hỏi Thường Gặp</h1>
        </div>
        
        <div class="info-content">
            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Làm thế nào để đặt vé online?</h3>
                </div>
                <div class="faq-answer">
                    <p>Bạn có thể đặt vé trực tuyến bằng cách:</p>
                    <ol>
                        <li>Đăng nhập vào tài khoản của bạn</li>
                        <li>Chọn phim và suất chiếu mong muốn</li>
                        <li>Chọn ghế ngồi</li>
                        <li>Thanh toán và nhận mã vé qua email</li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Tôi có thể hủy vé đã đặt không?</h3>
                </div>
                <div class="faq-answer">
                    <p>Hiện tại VKU Cinema không hỗ trợ hủy vé sau khi đã thanh toán. Tuy nhiên, bạn có thể đổi suất chiếu trước giờ chiếu 2 tiếng bằng cách liên hệ hotline hoặc quầy vé.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Các hình thức thanh toán được chấp nhận?</h3>
                </div>
                <div class="faq-answer">
                    <p>Chúng tôi chấp nhận các hình thức thanh toán sau:</p>
                    <ul>
                        <li>Thẻ ATM nội địa (có Internet Banking)</li>
                        <li>Thẻ tín dụng Visa/Mastercard</li>
                        <li>Ví điện tử MoMo, ZaloPay</li>
                        <li>Thanh toán trực tiếp tại quầy vé</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Có được mang đồ ăn từ ngoài vào rạp không?</h3>
                </div>
                <div class="faq-answer">
                    <p>Theo quy định của rạp, khách hàng không được mang thức ăn, đồ uống từ bên ngoài vào. Rạp có quầy bắp rang bơ và nước giải khát phục vụ quý khách.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Làm thế nào để tích điểm thành viên?</h3>
                </div>
                <div class="faq-answer">
                    <p>Đăng ký tài khoản thành viên và đăng nhập khi mua vé, bạn sẽ tự động tích điểm. Mỗi 100,000 VNĐ chi tiêu tương đương 10 điểm. Điểm tích lũy có thể dùng để đổi quà hoặc giảm giá vé.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Có chỗ để xe không?</h3>
                </div>
                <div class="faq-answer">
                    <p>VKU Cinema có bãi đỗ xe rộng rãi, miễn phí cho khách hàng xem phim. Xe máy và ô tô đều được bảo quản an toàn.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    <h3>Trẻ em dưới 13 tuổi có cần người lớn đi cùng không?</h3>
                </div>
                <div class="faq-answer">
                    <p>Đối với phim có giới hạn độ tuổi 13+, 16+, 18+, trẻ em cần có người lớn đi cùng. Phim P (phổ biến) thì không yêu cầu.</p>
                </div>
            </div>

            <section class="info-section" style="margin-top: 50px;">
                <h2>Vẫn Còn Thắc Mắc?</h2>
                <p>Liên hệ với chúng tôi:</p>
                <p><i class="fas fa-envelope"></i> Email: dinhlq.24itb@vku.udn.vn</p>
                <p><i class="fas fa-phone"></i> Hotline: 0795701805</p>
            </section>
        </div>
    </div>
</main>

<script>
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', function() {
        const icon = this.querySelector('i');
        
        // Toggle icon rotation
        if (icon.style.transform === 'rotate(90deg)') {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(90deg)';
        }
    });
});
</script>
