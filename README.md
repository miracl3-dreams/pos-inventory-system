🛒 Point of Sale (POS) & Inventory Management System
Isang lightweight at efficient na web-based system na idinisenyo para sa retail at small businesses. Ang system na ito ay nakatutok sa pag-manage ng products, stock levels, sales tracking, at supplier management gamit ang PHP at MySQL.

🛠 Tech Stack
Backend: PHP 8.x (PDO for Database Security)

Database: MySQL / MariaDB

Frontend: HTML5, CSS3, JavaScript (Vanilla & AJAX)

Library: AlertifyJS (para sa modern notifications at delete confirmation)

📂 Project Structure & Logic
Ang system ay nahahati sa tatlong main logic layers:

Database Layer (config/db_config.php): Ang sentral na koneksyon sa database.

Logic Layer (include/lx.pdodb.php): Naglalaman ng mga custom functions para sa safe na data insertion at update.

UI Layer (dashboard.php): Isang dynamic na dashboard na nag-o-load ng iba't ibang pages (Products, Categories, Sales) base sa URL parameter.

🚀 Key Features
1. Advanced Product Management (Variants)
Hindi lang pangalan ang naitatala; suportado rin ang Product Sizes. Halimbawa, maaari kang magkaroon ng:

Argentina Meatloaf (150g)

Argentina Meatloaf (250g)
Ang bawat variant ay may sariling SKU, Price, at Stock Quantity.

2. Intelligent Inventory Tracking
Reorder Level Warning: Awtomatikong nag-ba-highlight ng pula ang stock quantity kapag malapit na itong maubos base sa set mong limit.

Stock-In/Stock-Out: Real-time na update ng inventory sa bawat benta o pag-add ng bagong stocks.

3. Secure Transactions
Gumagamit ang system ng PDO Prepared Statements para protektahan ang iyong negosyo laban sa SQL Injection attacks.

🚦 Quick Start Guide
Database Connection: Siguraduhin na ang db_config.php ay may tamang credentials (hostname, username, password, at db_name).

Access Level: Ang system ay restricted sa ADMIN role. Siguraduhin na ang iyong session ay may tamang user role bago pumasok sa dashboard.

Adding Data: Magsimula sa pag-add ng Categories at Suppliers bago mag-input ng mga Products.

🛡 Security Practices
Lahat ng user inputs ay dumadaan sa trim() at htmlspecialchars().

Ang delete function ay may double-confirmation gamit ang AlertifyJS para maiwasan ang accidental deletion.

Ang pagination system ay gumagamit ng LIMIT at OFFSET para manatiling mabilis ang system kahit libo-libo na ang iyong data.

👨‍💻 Developer Notes
Para sa mga future updates:

Barcode Integration: Ang product_id o isang bagong barcode column ay maaaring gamitin para sa barcode scanner integration.

Reporting: Maaaring mag-generate ng Sales Report sa pamamagitan ng pag-join ng sales table at products table gamit ang product_id.
