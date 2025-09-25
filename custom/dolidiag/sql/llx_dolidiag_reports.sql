-- This file is part of Dolibarr ERP/CRM software.
-- Copyright (C) 2025 Massaoud Bouzenad    <massaoud@dzprod.net>
CREATE TABLE IF NOT EXISTS llx_dolidiag_reports (
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    date_creation DATETIME NOT NULL,
    user_id INTEGER NOT NULL
) ENGINE=InnoDB;