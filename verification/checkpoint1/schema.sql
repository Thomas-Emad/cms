-- Minimal parent tables needed for FK integrity (translated from earlier
-- phase migrations, trimmed to only the columns pages/page_versions FK against)
CREATE TABLE hotels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active','suspended','draft') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT UNSIGNED NULL,
    role ENUM('super_admin','hotel_admin','hotel_staff') NOT NULL DEFAULT 'hotel_staff',
    status ENUM('active','invited','disabled') NOT NULL DEFAULT 'active',
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_users_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT UNSIGNED NOT NULL,
    disk VARCHAR(255) NOT NULL DEFAULT 'public',
    path VARCHAR(255) NOT NULL,
    mediable_type VARCHAR(255) NOT NULL,
    mediable_id BIGINT UNSIGNED NOT NULL,
    collection VARCHAR(255) NOT NULL DEFAULT 'gallery',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_media_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE facilities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'other',
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_facilities_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    UNIQUE KEY facilities_hotel_slug_unique (hotel_id, slug)
) ENGINE=InnoDB;

-- ===================== Phase 3 checkpoint tables ============================
-- Exact translation of 2024_01_03_000001_create_pages_table.php

CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    is_home TINYINT(1) NOT NULL DEFAULT 0,
    seo_title VARCHAR(255) NULL,
    seo_description VARCHAR(255) NULL,
    seo_og_image_media_id BIGINT UNSIGNED NULL,
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_pages_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    CONSTRAINT fk_pages_seo_media FOREIGN KEY (seo_og_image_media_id) REFERENCES media(id) ON DELETE SET NULL,
    UNIQUE KEY pages_hotel_slug_unique (hotel_id, slug)
) ENGINE=InnoDB;

-- The exact statements from the migration's up() method, unmodified.
ALTER TABLE pages ADD COLUMN home_marker BIGINT UNSIGNED
    GENERATED ALWAYS AS (IF(is_home = 1, hotel_id, NULL)) VIRTUAL;
ALTER TABLE pages ADD UNIQUE INDEX pages_home_marker_unique (home_marker);

-- Exact translation of 2024_01_03_000002_create_page_versions_table.php

CREATE TABLE page_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED NOT NULL,
    sections JSON NOT NULL,
    state ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    published_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    CONSTRAINT fk_pv_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    CONSTRAINT fk_pv_publisher FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX page_versions_page_id_state_index (page_id, state)
) ENGINE=InnoDB;

ALTER TABLE pages ADD COLUMN draft_version_id BIGINT UNSIGNED NULL AFTER status;
ALTER TABLE pages ADD CONSTRAINT fk_pages_draft_version FOREIGN KEY (draft_version_id) REFERENCES page_versions(id) ON DELETE SET NULL;

ALTER TABLE pages ADD COLUMN published_version_id BIGINT UNSIGNED NULL AFTER draft_version_id;
ALTER TABLE pages ADD CONSTRAINT fk_pages_published_version FOREIGN KEY (published_version_id) REFERENCES page_versions(id) ON DELETE SET NULL;
