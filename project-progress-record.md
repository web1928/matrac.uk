# MatraC - Development Progress Log

## POJECT OVERVIEW
The first important consideration is that the MatraC application build is Danny's use-case for self-teaching full stack web development, using vanilla HTML, CSS, JavaScript, PHP & SQL (mariaDB). Danny prefers not to use Frameworks, but is happy to implement composer packages/helpers e.g. phpMailer.

MatraC is intended to be used as standalone material traceability web application, specifically aimed at the bakery manufacturing industry. Although standalone initially, future enhancements may include the implementation of API integration with a key ERP system such as Oracle Fusion (via Oracle Fusion OIC) or other 3rd party [complementary] solutions, e.g. Tracegains (https://api-docs.tracegains.net/).

This implementation phase #1 targets the first key 3 stages of the operational process:-
- Goods Receipt : The receipt of all materials at the point of delivery from the supplier.
- Goods Issue : The issue of a material from stock, either directly to the production line or to the dough mixing area.
- Mixing : The presentation and creation of a dough mix, referencing the presented recipe.

The key function of MatraC is the automatic generation of a unique and immutable batch code for each material receipted into the system. This batch code is then used to reference every transactional change to the material, ensuring both backwards as well as forward material traceability. The batch code format is "YY-DDD-NNN"m where ""YY" represents the short code of the current YEAR, "DDD" represents the julian code of the current day of the year & "NNN" represnts the current [autoincremental] number of the material delivery within the current day, identified at the GR stage.

A fundamental security aspect of MatraC is that it implements a role based, transactional access model. Key, defined roles during Phase #1 are as follows:-
- Goods Receiver: This role permits a user to access the GR form to receipt a material delivery into the system inventory.
- Goods Issuer: This role permits a user to access the GI form to issue a material/batch combination from the GR stage, either directly to the production line or to the mixing stage.
- QA User : This role goves permission to the user to change the batch/material combination status,within the inventory, from 'Available' to 'On-Hold' or vice-versa. It also allows the user to change the status from 'On-Hold' to 'Rejected'.
- Inventory Manager: This role permits the user to make material/batch inventory adjustments e.g. quantity adjustments.

In implementation phase #1, the application does track material inventory as this is a dependency of any traceability functionality. However, the inventory level is not determined by warehouse location, but rather each process "stage" e.g. "GR" (Goods Receipted), "GI" Goods Issued etc. As a future enhancement, there may be an option to implement warehouse/site location inventory management; depending on the needs of the business.

More details of each specific process are outlined in the following section: Process Details.

## PROCESS DETAILS
GOODS RECEIPT
The GR process allows a user with the required role permission to access the 'New Goods receipt' transaction within the system. This transactionpresent the GR form, in which the user begins by choosing the material in the 'Material' field on the form. This is an autocmplete field, allowing the user to enter a minimum of 3 alphanumeric characters in the field, which then uses a fetch() method to query the `material` field in the DB, returning material list, presented in a dropdown table. The user can then select the relevant material being receipted.

### Goods Issue
The Goods Issue (GR) process is the stage at which the  

### 2024-11-24 - Environment Variable Migration
- Moved database credentials from hardcoded to .env
- Created .env.example template
- Added .gitignore for security
- Benefits: Environment-agnostic, more secure, production-ready

### 2024-11-27 - Error Handling Implementation
- Created Matrac\Framework\ErrorHandler class
- Handles errors, exceptions, and fatal errors
- Environment-aware display (dev vs production)
- API detection for JSON responses
- Daily log rotation
- Issues fixed: Namespace (App → Framework), API context detection

### 2024-11-28 - Architecture Refactoring
- Migrated from manual spl_autoload to Composer PSR-4
- Renamed /core to /Framework (namespace: Matrac\Framework)
- Renamed /app to /App (namespace: App)
- Moved routes/web.php to config/routes.php
- Created bootstrap.php for application initialization
- Removed unused config/app.php and config() helper

### 2024-11-29 - Inventory Autocomplete Enhancement
- Added material autocomplete to inventory filter
- Fixed debounce implementation
- Fixed filter value preservation
- Fixed search criteria (code OR description)
- Fixed autocomplete selection parsing (" - " separator)
- Benefits: Consistent UX across forms, easier material filtering
- Danny performed analysis on the common autocomplete functionality

### 2024-11-29 - Material Autocomplete Component
- Extracted material autocomplete to reusable component
- Works on Goods Receipt and Inventory pages
- Fixed debouncing, early exit, scope issues
- **Future enhancement**: Generalize to handle suppliers, customers, etc.

### 2024-11-30 - AuthController login method conversion
- Migrated login() method from basic hard-coded array to DB authentication

## Next Steps
- [ ] Implement session timeout in AuthMiddleware
- [ ] Add rate limiting for security
- [ ] Plan Goods Issue module build