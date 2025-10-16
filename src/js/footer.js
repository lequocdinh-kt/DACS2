// Footer Map Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleMapBtn = document.getElementById('toggleMapBtn');
    const closeMapBtn = document.getElementById('closeMapBtn');
    const mapContainer = document.getElementById('mapContainer');
    const mapBtnText = toggleMapBtn ? toggleMapBtn.childNodes[toggleMapBtn.childNodes.length - 1] : null;

    // Hàm mở map
    function openMap(e) {
        e.preventDefault();
        mapContainer.classList.add('active');
        
        // Đổi text button thành "ẨN BẢN ĐỒ"
        if (mapBtnText && mapBtnText.nodeType === Node.TEXT_NODE) {
            mapBtnText.textContent = ' ẨN BẢN ĐỒ';
        }
        
        // Scroll smooth đến map sau khi animation bắt đầu
        setTimeout(() => {
            mapContainer.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 100);
    }

    // Hàm đóng map
    function closeMap() {
        mapContainer.classList.remove('active');
        
        // Đổi text button về "XEM BẢN ĐỒ"
        if (mapBtnText && mapBtnText.nodeType === Node.TEXT_NODE) {
            mapBtnText.textContent = ' XEM BẢN ĐỒ';
        }
    }

    // Hàm toggle map (mở/đóng)
    function toggleMap(e) {
        e.preventDefault();
        if (mapContainer.classList.contains('active')) {
            closeMap();
        } else {
            openMap(e);
        }
    }

    // Event listeners
    if (toggleMapBtn) {
        toggleMapBtn.addEventListener('click', toggleMap);
    }

    if (closeMapBtn) {
        closeMapBtn.addEventListener('click', closeMap);
    }

    // Nhấn ESC để đóng map
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mapContainer.classList.contains('active')) {
            closeMap();
        }
    });
});
