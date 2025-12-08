# 🎨 VKU Cinema - Bảng Màu & Thiết Kế

## 📋 Mục lục
1. [Màu chủ đạo](#màu-chủ-đạo)
2. [Màu theo trang](#màu-theo-trang)
3. [Gradients](#gradients)
4. [Màu chức năng](#màu-chức-năng)
5. [Hướng dẫn sử dụng](#hướng-dẫn-sử-dụng)

---

## 🎯 Màu chủ đạo

### Background Colors
| Màu | Hex Code | RGB | Sử dụng |
|-----|----------|-----|---------|
| **Dark Navy** | `#0a0e1a` | `rgb(10, 14, 26)` | Background chính toàn website |
| **Navy** | `#0a0e27` | `rgb(10, 14, 39)` | Background phụ, cards |
| **Deep Blue** | `#1a1f3a` | `rgb(26, 31, 58)` | Header, sections |
| **Dark Blue** | `#2d3561` | `rgb(45, 53, 97)` | Header gradient end |

### Primary Colors
| Màu | Hex Code | RGB | Sử dụng |
|-----|----------|-----|---------|
| **Orange** | `#ff6b00` | `rgb(255, 107, 0)` | Accent chính - Lịch chiếu, CTAs |
| **Light Orange** | `#ffa500` | `rgb(255, 165, 0)` | Hover states, warnings |
| **Purple** | `#667eea` | `rgb(102, 126, 234)` | Accent phụ - Home, buttons |
| **Deep Purple** | `#764ba2` | `rgb(118, 75, 162)` | Gradient end, hover |

### Text Colors
| Màu | Hex Code | RGB | Sử dụng |
|-----|----------|-----|---------|
| **White** | `#ffffff` | `rgb(255, 255, 255)` | Text chính |
| **Light Gray** | `#e0e0e0` | `rgb(224, 224, 224)` | Text phụ |
| **Gray** | `#b0b0b0` | `rgb(176, 176, 176)` | Text mờ |
| **Dark Gray** | `#999` | `rgb(153, 153, 153)` | Metadata, labels |
| **Blue Gray** | `#a0aec0` | `rgb(160, 174, 192)` | Info text |

---

## 🏠 Màu theo trang

### Home Page (home.css)
```css
/* Primary Gradients */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);  /* Buttons */
background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);  /* Trailer button */
background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);  /* New badge */
background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);  /* Hot badge */

/* Text Colors */
color: #ffffff;  /* Headings */
color: #e0e0e0;  /* Descriptions */
color: #b0b0b0;  /* Genre, duration */
color: #ffd700;  /* Ratings */
color: #00ff88; /* Countdown timer */
```

### Schedule Page (schedule.css)
```css
/* Orange Theme */
background: #0a0e1a;           /* Main background */
color: #ff6b00;                /* Headers, accent */
border: 3px solid #ff6b00;     /* Dividers */

/* Cards & Buttons */
background: rgba(255, 255, 255, 0.03);  /* Card background */
border: 2px solid rgba(255, 107, 0, 0.3);  /* Card borders */
background: #ff6b00;           /* Active states */
```

### Header (header.css)
```css
/* Purple Theme */
background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 100%);
border-bottom: 3px solid #667eea;

/* Logo Gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
```

---

## 🌈 Gradients

### Primary Gradients
```css
/* Purple Gradient - Main CTA */
linear-gradient(135deg, #667eea 0%, #764ba2 100%)

/* Orange Gradient - Schedule */
linear-gradient(135deg, #ff6b00 0%, #ff8800 100%)

/* Pink Gradient - Trailer/Hot */
linear-gradient(135deg, #f093fb 0%, #f5576c 100%)

/* Red Gradient - Booking */
linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%)

/* Blue Gradient - New Badge */
linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)

/* Navy Gradient - Backgrounds */
linear-gradient(135deg, #1a1f3a 0%, #0a0e1a 100%)
```

### Overlay Gradients
```css
/* Dark Overlay - Left to Right */
linear-gradient(to right, 
    rgba(10, 14, 39, 1) 0%, 
    rgba(10, 14, 39, 0.95) 25%, 
    rgba(10, 14, 39, 0.6) 50%, 
    rgba(10, 14, 39, 0.3) 70%, 
    transparent 100%
)

/* Dark Overlay - Bottom to Top */
linear-gradient(to top, 
    rgba(0, 0, 0, 0.95) 0%, 
    rgba(0, 0, 0, 0.3) 50%, 
    transparent 100%
)
```

---

## ⚡ Màu chức năng

### Status Colors
| Trạng thái | Màu | Hex | Sử dụng |
|------------|-----|-----|---------|
| **Success** | Green | `#00ff88` | Countdown, success messages |
| **Warning** | Orange | `#ffa500` | Few seats, warnings |
| **Error** | Red | `#ff4757` | Full seats, errors |
| **Info** | Blue | `#00c8ff` | Information, clock icons |
| **Hot** | Pink-Red | `#f5576c` | Hot movies, trending |
| **New** | Cyan | `#00f2fe` | New releases |

### Interactive States
```css
/* Hover Effects */
background: rgba(255, 107, 0, 0.2);  /* Orange hover */
background: rgba(255, 255, 255, 0.2); /* White hover */

/* Active States */
background: #ff6b00;  /* Orange active */
background: #667eea;  /* Purple active */

/* Disabled States */
opacity: 0.4;
cursor: not-allowed;
background: rgba(255, 255, 255, 0.02);
```

---

## 🎨 Transparency & RGBA

### Background Transparencies
```css
/* Light Overlays */
rgba(255, 255, 255, 0.05)  /* 5% white - Cards */
rgba(255, 255, 255, 0.1)   /* 10% white - Hover */
rgba(255, 255, 255, 0.2)   /* 20% white - Active */

/* Orange Overlays */
rgba(255, 107, 0, 0.1)     /* 10% orange - Hover */
rgba(255, 107, 0, 0.2)     /* 20% orange - Active */
rgba(255, 107, 0, 0.3)     /* 30% orange - Borders */

/* Dark Overlays */
rgba(0, 0, 0, 0.3)         /* 30% black - Shadow */
rgba(0, 0, 0, 0.8)         /* 80% black - Modal overlay */
rgba(0, 0, 0, 0.95)        /* 95% black - Deep overlay */
```

### Shadow Colors
```css
/* Purple Shadow */
box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);

/* Orange Shadow */
box-shadow: 0 8px 20px rgba(255, 107, 0, 0.3);

/* Red Shadow */
box-shadow: 0 6px 20px rgba(255, 65, 108, 0.4);

/* Black Shadow */
box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
```

---

## 📝 Hướng dẫn sử dụng

### 1. Màu theo Context

#### **Trang chủ (Home)**
- Primary: Purple gradient `#667eea → #764ba2`
- Accent: Pink `#f093fb → #f5576c` (Trailer)
- Background: `#0a0e27`

#### **Lịch chiếu (Schedule)**
- Primary: Orange `#ff6b00`
- Accent: Orange variants
- Background: `#0a0e1a`

#### **Header & Navigation**
- Background: Navy gradient `#1a1f3a → #2d3561`
- Border: Purple `#667eea`
- Text: White `#ffffff`

### 2. Button Colors

```css
/* Primary CTA - Purple */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Secondary CTA - Orange */
.btn-secondary {
    background: #ff6b00;
}

/* Trailer - Pink */
.btn-trailer {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

/* Booking - Red */
.btn-booking {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
}
```

### 3. Text Hierarchy

```css
/* Heading 1 - Bold White */
h1 { color: #ffffff; font-weight: 900; }

/* Heading 2 - Orange/Purple Gradient */
h2 { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Body Text - Light Gray */
p { color: #e0e0e0; }

/* Secondary Text - Gray */
span { color: #b0b0b0; }

/* Meta Text - Dark Gray */
.meta { color: #999; }
```

### 4. Cards & Components

```css
/* Card Background */
.card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 107, 0, 0.1);
}

/* Card Hover */
.card:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 107, 0, 0.3);
    box-shadow: 0 8px 20px rgba(255, 107, 0, 0.2);
}
```

### 5. Responsive Considerations

- Giữ nguyên màu chính trên mobile
- Giảm opacity của shadow trên thiết bị nhỏ
- Sử dụng solid colors thay vì gradients nếu performance kém

---

## 🎯 Color Palette Summary

### Core Colors (Hex)
```
#0a0e1a - Dark Navy (Background)
#1a1f3a - Navy (Sections)
#667eea - Purple (Primary)
#764ba2 - Deep Purple (Gradient)
#ff6b00 - Orange (Schedule Accent)
#ffffff - White (Text)
#999999 - Gray (Secondary Text)
```

### Accent Colors (Hex)
```
#f093fb - Pink (Trailer)
#f5576c - Hot Pink (Hot Badge)
#4facfe - Light Blue (New Badge)
#00f2fe - Cyan (New Badge End)
#ff416c - Red (Booking)
#ff4b2b - Dark Red (Booking End)
#ffd700 - Gold (Rating)
#00ff88 - Green (Countdown)
```

---

## 📱 Design Tokens

```css
:root {
    /* Primary Colors */
    --primary-purple: #667eea;
    --primary-orange: #ff6b00;
    --primary-white: #ffffff;
    
    /* Background Colors */
    --bg-dark: #0a0e1a;
    --bg-navy: #0a0e27;
    --bg-section: #1a1f3a;
    
    /* Text Colors */
    --text-primary: #ffffff;
    --text-secondary: #e0e0e0;
    --text-muted: #b0b0b0;
    --text-gray: #999999;
    
    /* Accent Colors */
    --accent-orange: #ff6b00;
    --accent-purple: #667eea;
    --accent-pink: #f5576c;
    --accent-green: #00ff88;
    --accent-gold: #ffd700;
    
    /* Shadows */
    --shadow-purple: 0 10px 25px rgba(102, 126, 234, 0.4);
    --shadow-orange: 0 8px 20px rgba(255, 107, 0, 0.3);
    --shadow-dark: 0 5px 20px rgba(0, 0, 0, 0.3);
}
```

---

**Cập nhật lần cuối:** 08/12/2025  
**Version:** 1.0  
**Project:** VKU Cinema Website
