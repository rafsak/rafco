-- Tunisian ERP (Finco-like) - Initial installation schema
-- Target: MySQL 8+
-- Compliance: Tunisia 2026 e-invoicing foundations (TEJ/TTN/TunTrace traceability)

SET NAMES utf8mb4;
SET time_zone = '+01:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS report_exports;
DROP TABLE IF EXISTS dashboard_snapshots;
DROP TABLE IF EXISTS fiscal_integration_logs;
DROP TABLE IF EXISTS legal_archives;
DROP TABLE IF EXISTS invoice_qr_codes;
DROP TABLE IF EXISTS payment_allocations;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS warehouses;
DROP TABLE IF EXISTS invoice_lines;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS quote_lines;
DROP TABLE IF EXISTS quotes;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS product_categories;
DROP TABLE IF EXISTS partners;
DROP TABLE IF EXISTS company_tax_rates;
DROP TABLE IF EXISTS company_settings;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS audit_logs;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(80) NOT NULL UNIQUE,
    role_name VARCHAR(120) NOT NULL,
    role_description VARCHAR(255) NULL,
    is_system_role TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(120) NOT NULL UNIQUE,
    permission_name VARCHAR(120) NOT NULL,
    module_name VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB;

CREATE TABLE company_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    legal_name VARCHAR(200) NOT NULL,
    trade_name VARCHAR(200) NULL,
    registration_number VARCHAR(120) NOT NULL,
    tax_identification_number VARCHAR(120) NOT NULL,
    vat_number VARCHAR(120) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(120) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(120) NOT NULL DEFAULT 'Tunisia',
    phone VARCHAR(40) NULL,
    email VARCHAR(180) NULL,
    logo_path VARCHAR(255) NULL,
    invoice_prefix VARCHAR(20) NOT NULL DEFAULT 'FAC',
    invoice_next_number BIGINT UNSIGNED NOT NULL DEFAULT 1,
    quote_prefix VARCHAR(20) NOT NULL DEFAULT 'DEV',
    quote_next_number BIGINT UNSIGNED NOT NULL DEFAULT 1,
    default_currency CHAR(3) NOT NULL DEFAULT 'TND',
    fiscal_year_start DATE NOT NULL,
    fiscal_year_end DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE company_tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tax_code VARCHAR(40) NOT NULL UNIQUE,
    tax_name VARCHAR(120) NOT NULL,
    rate_percent DECIMAL(8,4) NOT NULL,
    tax_type ENUM('TVA','FODEC','RETENTION','OTHER') NOT NULL,
    applies_on ENUM('NET','NET_PLUS_FODEC') NOT NULL DEFAULT 'NET',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE partners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    partner_type ENUM('CLIENT','SUPPLIER','BOTH') NOT NULL,
    code VARCHAR(40) NOT NULL UNIQUE,
    legal_name VARCHAR(200) NOT NULL,
    contact_name VARCHAR(160) NULL,
    email VARCHAR(180) NULL,
    phone VARCHAR(40) NULL,
    tax_identification_number VARCHAR(120) NULL,
    vat_number VARCHAR(120) NULL,
    address_line1 VARCHAR(255) NULL,
    city VARCHAR(120) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(120) NOT NULL DEFAULT 'Tunisia',
    payment_terms_days INT NOT NULL DEFAULT 0,
    credit_limit DECIMAL(18,3) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_partners_type (partner_type),
    INDEX idx_partners_legal_name (legal_name)
) ENGINE=InnoDB;

CREATE TABLE product_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(120) NOT NULL,
    parent_category_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_category_id) REFERENCES product_categories(id)
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(60) NOT NULL UNIQUE,
    product_type ENUM('PRODUCT','SERVICE') NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    unit VARCHAR(40) NOT NULL DEFAULT 'U',
    sale_price_ht DECIMAL(18,3) NOT NULL,
    purchase_price_ht DECIMAL(18,3) NOT NULL DEFAULT 0,
    tva_rate DECIMAL(8,4) NOT NULL DEFAULT 19,
    fodec_rate DECIMAL(8,4) NOT NULL DEFAULT 1,
    withholding_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    stock_enabled TINYINT(1) NOT NULL DEFAULT 1,
    stock_alert_threshold DECIMAL(18,3) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories(id),
    INDEX idx_products_name (name)
) ENGINE=InnoDB;

CREATE TABLE warehouses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    warehouse_code VARCHAR(40) NOT NULL UNIQUE,
    warehouse_name VARCHAR(120) NOT NULL,
    address_line1 VARCHAR(255) NULL,
    city VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE quotes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_number VARCHAR(40) NOT NULL UNIQUE,
    quote_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    partner_id BIGINT UNSIGNED NOT NULL,
    status ENUM('DRAFT','SENT','ACCEPTED','REJECTED','EXPIRED') NOT NULL DEFAULT 'DRAFT',
    subtotal_ht DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_fodec DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_tva DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_ttc DECIMAL(18,3) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotes_partner FOREIGN KEY (partner_id) REFERENCES partners(id),
    CONSTRAINT fk_quotes_user FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_quotes_date (quote_date)
) ENGINE=InnoDB;

CREATE TABLE quote_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(18,3) NOT NULL,
    unit_price_ht DECIMAL(18,3) NOT NULL,
    discount_percent DECIMAL(8,4) NOT NULL DEFAULT 0,
    fodec_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    tva_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    line_total_ht DECIMAL(18,3) NOT NULL,
    line_total_ttc DECIMAL(18,3) NOT NULL,
    CONSTRAINT fk_quote_lines_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    CONSTRAINT fk_quote_lines_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(40) NOT NULL UNIQUE,
    legal_sequence BIGINT UNSIGNED NOT NULL UNIQUE,
    invoice_type ENUM('SALES','PURCHASE','CREDIT_NOTE') NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    partner_id BIGINT UNSIGNED NOT NULL,
    source_quote_id BIGINT UNSIGNED NULL,
    status ENUM('DRAFT','PENDING_TEJ','VALIDATED_TEJ','SENT_TTN','FINALIZED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
    currency_code CHAR(3) NOT NULL DEFAULT 'TND',
    subtotal_ht DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_fodec DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_tva DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_withholding DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_ttc DECIMAL(18,3) NOT NULL DEFAULT 0,
    total_to_pay DECIMAL(18,3) NOT NULL DEFAULT 0,
    is_immutable TINYINT(1) NOT NULL DEFAULT 0,
    immutable_hash CHAR(64) NULL,
    fiscal_timestamp DATETIME NULL,
    tej_reference VARCHAR(120) NULL,
    ttn_reference VARCHAR(120) NULL,
    tuntrace_qr_payload TEXT NULL,
    pdf_path VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    validated_by BIGINT UNSIGNED NULL,
    validated_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_partner FOREIGN KEY (partner_id) REFERENCES partners(id),
    CONSTRAINT fk_invoices_quote FOREIGN KEY (source_quote_id) REFERENCES quotes(id),
    CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_invoices_validated_by FOREIGN KEY (validated_by) REFERENCES users(id),
    INDEX idx_invoices_type_date (invoice_type, invoice_date),
    INDEX idx_invoices_status (status)
) ENGINE=InnoDB;

CREATE TABLE invoice_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    line_number INT NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(18,3) NOT NULL,
    unit_price_ht DECIMAL(18,3) NOT NULL,
    discount_percent DECIMAL(8,4) NOT NULL DEFAULT 0,
    fodec_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    tva_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    withholding_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    line_total_ht DECIMAL(18,3) NOT NULL,
    line_fodec DECIMAL(18,3) NOT NULL,
    line_tva DECIMAL(18,3) NOT NULL,
    line_withholding DECIMAL(18,3) NOT NULL,
    line_total_ttc DECIMAL(18,3) NOT NULL,
    CONSTRAINT fk_invoice_lines_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_lines_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movement_date DATETIME NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    movement_type ENUM('IN','OUT','ADJUSTMENT') NOT NULL,
    quantity DECIMAL(18,3) NOT NULL,
    reference_type ENUM('INVOICE','PURCHASE','MANUAL') NOT NULL,
    reference_id BIGINT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_movements_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_stock_movements_user FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_stock_lookup (warehouse_id, product_id, movement_date)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_date DATE NOT NULL,
    partner_id BIGINT UNSIGNED NOT NULL,
    payment_type ENUM('INCOMING','OUTGOING') NOT NULL,
    method ENUM('CASH','BANK_TRANSFER','CHEQUE') NOT NULL,
    amount DECIMAL(18,3) NOT NULL,
    reference VARCHAR(120) NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_partner FOREIGN KEY (partner_id) REFERENCES partners(id),
    CONSTRAINT fk_payments_user FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_payments_date (payment_date)
) ENGINE=InnoDB;

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    allocated_amount DECIMAL(18,3) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_allocations_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_allocations_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    UNIQUE KEY uq_payment_invoice (payment_id, invoice_id)
) ENGINE=InnoDB;

CREATE TABLE invoice_qr_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL UNIQUE,
    qr_payload TEXT NOT NULL,
    qr_image_path VARCHAR(255) NULL,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_qr_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE legal_archives (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    archive_type ENUM('INVOICE_XML','INVOICE_PDF','TTN_RECEIPT','TEJ_RECEIPT') NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_hash CHAR(64) NOT NULL,
    archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    archived_by BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_legal_archives_user FOREIGN KEY (archived_by) REFERENCES users(id),
    INDEX idx_legal_archives_entity (archive_type, entity_id)
) ENGINE=InnoDB;

CREATE TABLE fiscal_integration_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    integration_channel ENUM('TEJ','TTN','TUNTRACE') NOT NULL,
    action_name VARCHAR(120) NOT NULL,
    entity_type ENUM('INVOICE','PAYMENT','DECLARATION') NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    request_payload LONGTEXT NULL,
    response_payload LONGTEXT NULL,
    status ENUM('SUCCESS','FAILED','PENDING') NOT NULL,
    external_reference VARCHAR(180) NULL,
    executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    executed_by BIGINT UNSIGNED NULL,
    CONSTRAINT fk_fiscal_logs_user FOREIGN KEY (executed_by) REFERENCES users(id),
    INDEX idx_fiscal_logs_channel (integration_channel, executed_at)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(120) NOT NULL,
    module_name VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id BIGINT UNSIGNED NULL,
    action_summary VARCHAR(255) NOT NULL,
    event_data JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_audit_module_date (module_name, created_at)
) ENGINE=InnoDB;

CREATE TABLE dashboard_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    snapshot_date DATE NOT NULL,
    metric_code VARCHAR(80) NOT NULL,
    metric_value DECIMAL(18,3) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_snapshot_metric (snapshot_date, metric_code)
) ENGINE=InnoDB;

CREATE TABLE report_exports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_code VARCHAR(80) NOT NULL,
    format ENUM('PDF','XLSX','CSV') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    generated_by BIGINT UNSIGNED NOT NULL,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_exports_user FOREIGN KEY (generated_by) REFERENCES users(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed roles and permissions
INSERT INTO roles (role_key, role_name, role_description, is_system_role) VALUES
('SUPER_ADMIN', 'Super Administrateur', 'Accès complet ERP', 1),
('ACCOUNTANT', 'Comptable', 'Gestion fiscale, facturation, paiements', 1),
('SALES_MANAGER', 'Responsable Commercial', 'Devis, facturation client, suivi ventes', 1),
('STOCK_MANAGER', 'Responsable Stock', 'Gestion entrepôts et mouvements de stock', 1);

INSERT INTO permissions (permission_key, permission_name, module_name) VALUES
('AUTH_MANAGE_USERS', 'Gérer les utilisateurs', 'AUTH'),
('AUTH_MANAGE_ROLES', 'Gérer les rôles et permissions', 'AUTH'),
('COMPANY_EDIT', 'Modifier configuration société', 'COMPANY'),
('PARTNER_MANAGE', 'Gérer clients et fournisseurs', 'PARTNERS'),
('PRODUCT_MANAGE', 'Gérer produits et services', 'PRODUCTS'),
('STOCK_MANAGE', 'Gérer stock', 'STOCK'),
('SALES_QUOTE_MANAGE', 'Gérer devis', 'SALES'),
('SALES_INVOICE_MANAGE', 'Gérer factures de vente', 'SALES'),
('PURCHASE_INVOICE_MANAGE', 'Gérer factures fournisseur', 'PURCHASE'),
('PAYMENT_MANAGE', 'Gérer paiements', 'PAYMENTS'),
('REPORT_VIEW', 'Consulter rapports', 'REPORTING'),
('AUDIT_VIEW', 'Consulter journaux audit', 'AUDIT'),
('FISCAL_VALIDATE_TEJ', 'Valider facture via TEJ', 'FISCAL'),
('FISCAL_SEND_TTN', 'Transmettre facture via TTN', 'FISCAL'),
('FISCAL_QR_GENERATE', 'Générer QR TunTrace', 'FISCAL');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.role_key = 'SUPER_ADMIN';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
  'SALES_INVOICE_MANAGE','SALES_QUOTE_MANAGE','PAYMENT_MANAGE','REPORT_VIEW','FISCAL_VALIDATE_TEJ','FISCAL_SEND_TTN','FISCAL_QR_GENERATE','PURCHASE_INVOICE_MANAGE'
)
WHERE r.role_key = 'ACCOUNTANT';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN ('SALES_QUOTE_MANAGE','SALES_INVOICE_MANAGE','REPORT_VIEW')
WHERE r.role_key = 'SALES_MANAGER';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN ('STOCK_MANAGE','PRODUCT_MANAGE','REPORT_VIEW')
WHERE r.role_key = 'STOCK_MANAGER';

-- Default company parameters
INSERT INTO company_settings (
  legal_name, trade_name, registration_number, tax_identification_number, vat_number,
  address_line1, city, postal_code, phone, email,
  invoice_prefix, quote_prefix, default_currency, fiscal_year_start, fiscal_year_end
) VALUES (
  'Demo ERP Tunisia SARL', 'Demo ERP Tunisia', 'B0000002026', '1234567AAM000', 'TVA123456',
  'Rue de la République', 'Tunis', '1000', '+21670000000', 'contact@demo-erp.tn',
  'FAC', 'DEV', 'TND', '2026-01-01', '2026-12-31'
);

INSERT INTO company_tax_rates (tax_code, tax_name, rate_percent, tax_type, applies_on) VALUES
('TVA19', 'TVA 19%', 19.0000, 'TVA', 'NET_PLUS_FODEC'),
('TVA7', 'TVA 7%', 7.0000, 'TVA', 'NET_PLUS_FODEC'),
('FODEC1', 'FODEC 1%', 1.0000, 'FODEC', 'NET'),
('RET1_5', 'Retenue à la source 1.5%', 1.5000, 'RETENTION', 'NET');

-- Default admin user (password: Admin@2026)
INSERT INTO users (full_name, email, password_hash, is_active)
VALUES ('System Administrator', 'admin@erp.local', '$2y$10$7dUjDABfMWwl40YplN17ieAt6vcMX4/2C4wErNnWw6u2f7hMhRoUO', 1);

INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.role_key = 'SUPER_ADMIN'
WHERE u.email = 'admin@erp.local';

-- Sample partners and catalog
INSERT INTO partners (partner_type, code, legal_name, email, phone, payment_terms_days, is_active)
VALUES
('CLIENT', 'CLI0001', 'Société Alpha', 'contact@alpha.tn', '+21671111111', 30, 1),
('SUPPLIER', 'FOU0001', 'Fournisseur Beta', 'sales@beta.tn', '+21672222222', 45, 1);

INSERT INTO product_categories (category_name) VALUES ('Services'), ('Produits finis');

INSERT INTO products (sku, product_type, category_id, name, sale_price_ht, purchase_price_ht, tva_rate, fodec_rate, withholding_rate, stock_enabled, stock_alert_threshold)
VALUES
('SRV-CONSULT', 'SERVICE', 1, 'Consulting ERP', 500.000, 0, 19, 0, 1.5, 0, 0),
('PRD-0001', 'PRODUCT', 2, 'Terminal fiscal', 1200.000, 900.000, 19, 1, 1.5, 1, 5);

INSERT INTO warehouses (warehouse_code, warehouse_name, city)
VALUES ('WH-TUN', 'Entrepôt Tunis', 'Tunis');
