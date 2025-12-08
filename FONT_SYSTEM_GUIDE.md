# 📝 Hướng Dẫn Sử Dụng Font System - VKU Cinema

## 🎯 Tổng Quan

File `fonts.css` quản lý toàn bộ hệ thống phông chữ cho dự án VKU Cinema. Hệ thống này đảm bảo:
- ✅ Nhất quán về typography trên toàn website
- ✅ Dễ dàng thay đổi font cho toàn dự án
- ✅ Responsive và tối ưu hiệu năng
- ✅ Hỗ trợ nhiều loại font chuyên dụng

---

## 📚 Các Font Được Sử Dụng

### 1. **Roboto** (Font Chính)
- **Dùng cho**: Body text, nội dung chính, mô tả
- **Đặc điểm**: Clean, modern, dễ đọc
- **Variable**: `--font-primary`

### 2. **Montserrat** (Font Tiêu Đề)
- **Dùng cho**: Headings, titles, banners
- **Đặc điểm**: Bold, eye-catching, professional
- **Variable**: `--font-heading`

### 3. **Poppins** (Font Phụ)
- **Dùng cho**: Alternative text, UI elements
- **Đặc điểm**: Friendly, rounded, modern
- **Variable**: `--font-alt`

### 4. **Inter** (Font Sạch)
- **Dùng cho**: Professional sections, forms
- **Đặc điểm**: Clean, professional, minimal
- **Variable**: `--font-clean`

### 5. **Courier New** (Monospace)
- **Dùng cho**: Numbers, prices, countdown timers
- **Đặc điểm**: Fixed-width, clear digits
- **Variable**: `--font-mono`

---

## 🔧 Cách Sử Dụng

### 1. Import Font System
Thêm vào đầu file CSS của bạn:
```css
@import url('fonts.css');
```

### 2. Sử Dụng Font Variables

#### A. Font Families
```css
/* Font chính (Roboto) */
.element {
    font-family: var(--font-primary);
}

/* Font tiêu đề (Montserrat) */
.heading {
    font-family: var(--font-heading);
}

/* Font phụ (Poppins) */
.alternative {
    font-family: var(--font-alt);
}

/* Font sạch (Inter) */
.clean-text {
    font-family: var(--font-clean);
}

/* Font monospace */
.code-text {
    font-family: var(--font-mono);
}
```

#### B. Font Sizes
```css
/* Kích thước cơ bản */
font-size: var(--font-size-base);    /* 16px */
font-size: var(--font-size-sm);      /* 14px */
font-size: var(--font-size-lg);      /* 18px */

/* Kích thước lớn (tiêu đề) */
font-size: var(--font-size-2xl);     /* 24px */
font-size: var(--font-size-3xl);     /* 28px */
font-size: var(--font-size-4xl);     /* 32px */
font-size: var(--font-size-5xl);     /* 36px */
font-size: var(--font-size-6xl);     /* 48px */
font-size: var(--font-size-7xl);     /* 56px */
font-size: var(--font-size-8xl);     /* 64px */
```

#### C. Font Weights
```css
font-weight: var(--font-weight-light);      /* 300 */
font-weight: var(--font-weight-normal);     /* 400 */
font-weight: var(--font-weight-medium);     /* 500 */
font-weight: var(--font-weight-semibold);   /* 600 */
font-weight: var(--font-weight-bold);       /* 700 */
font-weight: var(--font-weight-extrabold);  /* 800 */
font-weight: var(--font-weight-black);      /* 900 */
```

#### D. Line Heights
```css
line-height: var(--line-height-tight);    /* 1.2 - Tiêu đề */
line-height: var(--line-height-normal);   /* 1.5 - Normal */
line-height: var(--line-height-relaxed);  /* 1.6 - Mô tả */
line-height: var(--line-height-loose);    /* 2.0 - Rộng rãi */
```

#### E. Letter Spacing
```css
letter-spacing: var(--letter-spacing-tight);   /* -0.5px */
letter-spacing: var(--letter-spacing-normal);  /* 0 */
letter-spacing: var(--letter-spacing-wide);    /* 1px */
letter-spacing: var(--letter-spacing-wider);   /* 2px */
letter-spacing: var(--letter-spacing-widest);  /* 4px */
```

---

## 🎨 Utility Classes (Sẵn Có)

### Font Families
```html
<p class="font-primary">Text với Roboto</p>
<h1 class="font-heading">Tiêu đề với Montserrat</h1>
<span class="font-mono">123,456 VND</span>
```

### Font Sizes
```html
<p class="text-sm">Chữ nhỏ (14px)</p>
<p class="text-base">Chữ cơ bản (16px)</p>
<h1 class="text-6xl">Tiêu đề lớn (48px)</h1>
```

### Font Weights
```html
<span class="font-light">Chữ nhẹ</span>
<span class="font-semibold">Chữ semi-bold</span>
<span class="font-black">Chữ đậm nhất</span>
```

### Text Transforms
```html
<span class="uppercase">CHỮ HOA</span>
<span class="lowercase">chữ thường</span>
<span class="capitalize">Viết Hoa Đầu Từ</span>
```

### Text Alignment
```html
<p class="text-left">Căn trái</p>
<p class="text-center">Căn giữa</p>
<p class="text-right">Căn phải</p>
```

### Text Truncate
```html
<p class="truncate">Văn bản dài sẽ bị cắt với dấu ...</p>
<p class="line-clamp-2">Giới hạn 2 dòng</p>
<p class="line-clamp-3">Giới hạn 3 dòng</p>
```

---

## 🎯 Pre-defined Styles (Sẵn Có)

### 1. Movie Title Style
```html
<h1 class="movie-title">TÊN PHIM</h1>
```
**Output**: Montserrat, 48px, Bold, Uppercase, Wide spacing

### 2. Section Title Style
```html
<h2 class="section-title">PHIM ĐANG CHIẾU</h2>
```
**Output**: Montserrat, 36px, Black, Wide spacing

### 3. Card Title Style
```html
<h3 class="card-title">Tên Phim</h3>
```
**Output**: Montserrat, 24px, Bold

### 4. Description Text Style
```html
<p class="description-text">Mô tả phim...</p>
```
**Output**: Roboto, 18px, Normal, Line-height relaxed

### 5. Button Text Style
```html
<button class="button-text">ĐẶT VÉ NGAY</button>
```
**Output**: Roboto, 16px, Semibold

### 6. Label Text Style
```html
<span class="label-text">ĐANG CHIẾU</span>
```
**Output**: Roboto, 14px, Medium, Uppercase, Wide spacing

### 7. Meta Text Style
```html
<span class="meta-text">Hành động | 120 phút</span>
```
**Output**: Roboto, 14px, Medium, Gray color

### 8. Price Text Style
```html
<span class="price-text">150,000 VND</span>
```
**Output**: Courier New, 24px, Bold

---

## 📱 Responsive Behavior

Font system tự động điều chỉnh kích thước trên các thiết bị:

### Desktop (> 768px)
- Kích thước font đầy đủ
- Spacing rộng rãi

### Tablet (≤ 768px)
- Font sizes giảm 10-15%
- Letter spacing giảm nhẹ

### Mobile (≤ 480px)
- Font sizes giảm 20-30%
- Letter spacing giảm đáng kể
- Line height tăng nhẹ để dễ đọc

---

## 🔄 Cách Thay Đổi Font Cho Toàn Dự Án

### Bước 1: Mở file `fonts.css`

### Bước 2: Thay đổi Google Fonts Import
```css
/* Thay đổi font này */
@import url('https://fonts.googleapis.com/css2?family=YourFont:wght@300;400;500;600;700;900&display=swap');
```

### Bước 3: Cập nhật CSS Variables
```css
:root {
    --font-primary: 'YourFont', sans-serif;
}
```

### Bước 4: Save & Refresh
Tất cả trang sẽ tự động cập nhật!

---

## 💡 Best Practices

### ✅ NÊN:
- Sử dụng `var(--font-primary)` thay vì hard-code font
- Dùng pre-defined classes như `.movie-title`, `.section-title`
- Sử dụng font variables cho consistency
- Test trên nhiều thiết bị

### ❌ KHÔNG NÊN:
- Hard-code font names trực tiếp: ~~`font-family: 'Roboto'`~~
- Hard-code font sizes: ~~`font-size: 16px`~~
- Dùng quá nhiều font weights khác nhau
- Ignore responsive breakpoints

---

## 📊 Font Weight Guide

| Weight | Number | Khi Nào Dùng |
|--------|--------|--------------|
| Light | 300 | Subtitle, secondary text |
| Normal | 400 | Body text, paragraphs |
| Medium | 500 | Labels, meta info |
| Semibold | 600 | Buttons, links, emphasis |
| Bold | 700 | Headings, titles |
| Extrabold | 800 | Hero titles, featured content |
| Black | 900 | Main titles, banners |

---

## 🎬 Ví Dụ Thực Tế

### Movie Card
```css
.movie-card-title {
    font-family: var(--font-heading);
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    line-height: var(--line-height-tight);
}

.movie-card-genre {
    font-family: var(--font-primary);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: #b0b0b0;
}
```

### Banner Title
```css
.banner-title {
    font-family: var(--font-heading);
    font-size: var(--font-size-6xl);
    font-weight: var(--font-weight-black);
    letter-spacing: var(--letter-spacing-wide);
    text-transform: uppercase;
}
```

### Price Display
```css
.ticket-price {
    font-family: var(--font-mono);
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
}
```

---

## 🐛 Troubleshooting

### Font không load?
1. Kiểm tra internet connection (Google Fonts cần internet)
2. Xóa cache trình duyệt (Ctrl + Shift + Delete)
3. Check Console cho lỗi CORS

### Font bị nhảy khi load?
- Đã có `font-display: swap` để tối ưu
- Thêm fallback fonts trong variable

### Font không responsive?
- Kiểm tra media queries trong `fonts.css`
- Đảm bảo viewport meta tag trong HTML

---

## 📞 Support

Nếu cần hỗ trợ thêm về font system, liên hệ:
- 📧 Email: support@vkucinema.com
- 📱 Hotline: 1900-xxxx

---

**Cập nhật lần cuối**: December 8, 2025
**Version**: 1.0.0
