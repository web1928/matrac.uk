.
├── app
│   ├── Controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── GoodsReceiptController.php
│   │   ├── InventoryController.php
│   │   ├── RejectedStockController.php
│   │   └── TestController.php
│   ├── Middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── Models
│   │   ├── Batch.php
│   │   ├── Inventory.php
│   │   ├── Material.php
│   │   ├── Supplier.php
│   │   └── Transaction.php
│   ├── Views
│   │   ├── auth
│   │   │   └── login.php
│   │   ├── dashboard
│   │   │   └── index.php
│   │   ├── errors
│   │   │   └── index.php
│   │   ├── goods-receipt
│   │   │   └── index.php
│   │   ├── inventory
│   │   │   └── index.php
│   │   ├── layouts
│   │   │   └── main.php
│   │   ├── partials
│   │   │   └── sidebar.php
│   │   ├── rejected-stock
│   │   │   └── index.php
│   │   └── test.php
│   └── helpers.php
├── config
│   ├── app.php
│   ├── database.php
│   └── env.php
├── core
│   ├── Controller.php
│   ├── Middleware.php
│   ├── Model.php
│   ├── Request.php
│   └── Router.php
├── file_structure.md
├── includes
│   ├── auth.php
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── public
│   ├── assets
│   │   ├── css
│   │   │   ├── components.css
│   │   │   ├── forms.css
│   │   │   ├── layout.css
│   │   │   ├── reset.css
│   │   │   └── tables.css
│   │   ├── img
│   │   │   └── favicon.png
│   │   └── js
│   │       ├── pages
│   │       │   ├── goods-receipt.js
│   │       │   ├── inventory.js
│   │       │   └── rejected-stock.js
│   │       ├── sidebar.js
│   │       └── utils.js
│   ├── index.php
│   └── pages
│       ├── goods-issue.php
│       └── mixing.php
├── routes
│   └── Web.php
└── sql
    └── fmyuaekbps_2025-11-24.sql

29 directories, 69 files
