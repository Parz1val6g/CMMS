CREATE TABLE `app_settings` (
  `id` VARCHAR(36) NOT NULL,
  `key` VARCHAR(50) NOT NULL,
  `value` TEXT NOT NULL,
  `section` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key_section` (`key`, `section`)
);
CREATE TABLE `roles` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `columns` VARCHAR(250) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE `users` (
  `id` VARCHAR(36) NOT NULL,
  `first_name` VARCHAR(250) NOT NULL,
  `last_name` VARCHAR(250) NOT NULL,
  `phone` VARCHAR(14) NOT NULL,
  `email` VARCHAR(250) NOT NULL,
  `password` VARCHAR(250) NULL,
  `status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT unique_phone UNIQUE (`phone`),
  CONSTRAINT unique_email UNIQUE (`email`)
);
CREATE TABLE `districts` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE `service_types` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(250) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE `units` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `abbreviation` VARCHAR(10) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_abbreviation` (`abbreviation`)
);
CREATE TABLE `equipments` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `serial_number` VARCHAR(250) NOT NULL,
  `manager_id` VARCHAR(36) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `is_loanable` BOOLEAN NOT NULL DEFAULT true,
  `revision_interval_days` INTEGER NOT NULL,
  `last_revision_date` DATETIME NULL,
  `next_revision_date` DATETIME NULL,
  `description` VARCHAR(250) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_serial_number` (`serial_number`),
  CONSTRAINT fk_equipment_manager FOREIGN KEY (`manager_id`) REFERENCES users(`id`)
);
CREATE TABLE `materials` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `unit_id` VARCHAR(36) NOT NULL,
  `stock_quantity` DECIMAL(10, 2) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_materials_unit FOREIGN KEY (`unit_id`) REFERENCES units(`id`),
  CONSTRAINT check_stock_qty CHECK (stock_quantity >= 0)
);
CREATE TABLE `role_permissions` (
  `id` VARCHAR(36) NOT NULL,
  `role_id` VARCHAR(36) NOT NULL,
  `resource` VARCHAR(50) NOT NULL,
  `action` VARCHAR(10) NOT NULL,
  `description` VARCHAR(250) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`, `resource`, `action`),
  CONSTRAINT fk_rp_role FOREIGN KEY (`role_id`) REFERENCES roles(`id`)
);
CREATE TABLE `user_roles` (
  `user_id` VARCHAR(36) NOT NULL,
  `role_id` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  CONSTRAINT fk_ur_user FOREIGN KEY (`user_id`) REFERENCES users(`id`),
  CONSTRAINT fk_ur_role FOREIGN KEY (`role_id`) REFERENCES roles(`id`)
);
CREATE TABLE `user_preferences` (
  `id` VARCHAR(36) NOT NULL,
  `user_id` VARCHAR(36) NOT NULL,
  `key` VARCHAR(50) NOT NULL,
  `value` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_preference` (`user_id`, `key`),
  CONSTRAINT fk_up_user FOREIGN KEY (`user_id`) REFERENCES users(`id`)
);
CREATE TABLE `clients` (
  `id` VARCHAR(36) NOT NULL,
  `user_id` VARCHAR(36) NOT NULL,
  `nif` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nif` (`nif`) CONSTRAINT fk_clients_user FOREIGN KEY (`user_id`) REFERENCES users(`id`)
);
CREATE TABLE `municipalities` (
  `id` VARCHAR(36) NOT NULL,
  `district_id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_municipalities_district FOREIGN KEY (`district_id`) REFERENCES districts(`id`)
);
CREATE TABLE `parishes` (
  `id` VARCHAR(36) NOT NULL,
  `municipality_id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_parishes_municipality FOREIGN KEY (`municipality_id`) REFERENCES municipalities(`id`)
);
CREATE TABLE `locations` (
  `id` VARCHAR(36) NOT NULL,
  `parish_id` VARCHAR(36) NOT NULL,
  `postal_code` VARCHAR(8) NOT NULL,
  `street_address` VARCHAR(100) NOT NULL,
  `landmark` VARCHAR(100) NOT NULL,
  `latitude` DECIMAL(10, 8) NULL,
  `longitude` DECIMAL(10, 8) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_locations_parish FOREIGN KEY (`parish_id`) REFERENCES parishes(`id`)
);
CREATE TABLE `sectors` (
  `id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `head_id` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_sectors_head FOREIGN KEY (`head_id`) REFERENCES users(`id`)
);
CREATE TABLE `teams` (
  `id` VARCHAR(36) NOT NULL,
  `sector_id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_teams_sector FOREIGN KEY (`sector_id`) REFERENCES sectors(`id`)
);
CREATE TABLE `service_orders` (
  `id` VARCHAR(36) NOT NULL,
  `process` VARCHAR(250) NOT NULL,
  `client_id` VARCHAR(36) NULL,
  `manager_id` VARCHAR(36) NOT NULL,
  `location_id` VARCHAR(36) NOT NULL,
  `service_type_id` VARCHAR(36) NULL,
  `workflow_type` VARCHAR(50) NOT NULL,
  `equipment_id` VARCHAR(36) NULL,
  `priority` VARCHAR(20) NOT NULL,
  `execution_date` DATE NULL,
  `status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_so_client FOREIGN KEY (`client_id`) REFERENCES clients(`id`),
  CONSTRAINT fk_so_manager FOREIGN KEY (`manager_id`) REFERENCES users(`id`),
  CONSTRAINT fk_so_location FOREIGN KEY (`location_id`) REFERENCES locations(`id`),
  CONSTRAINT fk_so_st FOREIGN KEY (`service_type_id`) REFERENCES service_types(`id`),
  CONSTRAINT fk_so_equipment FOREIGN KEY (`equipment_id`) REFERENCES equipments(`id`)
);
CREATE TABLE `tasks` (
  `id` VARCHAR(36) NOT NULL,
  `service_order_id` VARCHAR(36) NOT NULL,
  `manager_id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_tasks_so FOREIGN KEY (`service_order_id`) REFERENCES service_orders(`id`),
  CONSTRAINT fk_tasks_manager FOREIGN KEY (`manager_id`) REFERENCES users(`id`)
);
CREATE TABLE `tasks_sectors` (
  `task_id` VARCHAR(36) NOT NULL,
  `sector_id` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`task_id`, `sector_id`),
  CONSTRAINT fk_ts_task FOREIGN KEY (`task_id`) REFERENCES tasks(`id`),
  CONSTRAINT fk_ts_sector FOREIGN KEY (`sector_id`) REFERENCES sectors(`id`)
);
CREATE TABLE `mini_tasks` (
  `id` VARCHAR(36) NOT NULL,
  `task_id` VARCHAR(36) NOT NULL,
  `supervisor_id` VARCHAR(36) NOT NULL,
  `description` VARCHAR(250) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_mt_task FOREIGN KEY (`task_id`) REFERENCES tasks(`id`),
  CONSTRAINT fk_mt_supervisor FOREIGN KEY (`supervisor_id`) REFERENCES users(`id`)
);
CREATE TABLE `work_logs` (
  `id` VARCHAR(36) NOT NULL,
  `mini_task_id` VARCHAR(36) NOT NULL,
  `started_at` TIMESTAMP NOT NULL,
  `completed_at` TIMESTAMP NOT NULL,
  `description` VARCHAR(250) NOT NULL,
  `duration_minutes` INT GENERATED ALWAYS AS (TIMESTAMPDIFF(MINUTE, started_at, completed_at)) STORED,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_wl_mt FOREIGN KEY (`mini_task_id`) REFERENCES mini_tasks(`id`),
  CONSTRAINT check_time_order CHECK (completed_at > started_at)
);
CREATE TABLE `work_logs_materials` (
  `id` VARCHAR(36) NOT NULL,
  `work_log_id` VARCHAR(36) NOT NULL,
  `material_id` VARCHAR(36) NOT NULL,
  `unit_price_at_use` DECIMAL(10, 2) NULL,
  `quantity_used` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wl_material` (`work_log_id`, `material_id`),
  CONSTRAINT fk_wlm_wl FOREIGN KEY (`work_log_id`) REFERENCES work_logs(`id`),
  CONSTRAINT fk_wlm_material FOREIGN KEY (`material_id`) REFERENCES materials(`id`),
  CONSTRAINT check_qty_positive CHECK (quantity_used > 0)
);
CREATE TABLE `workers` (
  `id` VARCHAR(36) NOT NULL,
  `user_id` VARCHAR(36) NOT NULL,
  `team_id` VARCHAR(36) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT unique_workers_user UNIQUE (`user_id`),
  CONSTRAINT fk_workers_user FOREIGN KEY (`user_id`) REFERENCES users(`id`),
  CONSTRAINT fk_workers_team FOREIGN KEY (`team_id`) REFERENCES teams(`id`),
);
CREATE TABLE `work_logs_workers` (
  `work_log_id` VARCHAR(36) NOT NULL,
  `worker_id` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`work_log_id`, `worker_id`),
  CONSTRAINT fk_wlw_wl FOREIGN KEY (`work_log_id`) REFERENCES work_logs(`id`),
  CONSTRAINT fk_wlw_worker FOREIGN KEY (`worker_id`) REFERENCES workers(`id`)
);
CREATE TABLE `mini_tasks_workers_teams` (
  `id` VARCHAR(36) NOT NULL,
  `mini_task_id` VARCHAR(36) NOT NULL,
  `worker_id` VARCHAR(36) NULL,
  `team_id` VARCHAR(36) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_mtwt_mt FOREIGN KEY (`mini_task_id`) REFERENCES mini_tasks(`id`),
  CONSTRAINT fk_mtwt_worker FOREIGN KEY (`worker_id`) REFERENCES workers(`id`),
  CONSTRAINT fk_mtwt_team FOREIGN KEY (`team_id`) REFERENCES teams(`id`),
  CONSTRAINT check_worker_or_team CHECK (
    (
      worker_id IS NOT NULL
      AND team_id IS NULL
    )
    OR (
      worker_id IS NULL
      AND team_id IS NOT NULL
    )
  )
);
CREATE TABLE `mini_tasks_materials` (
  `id` VARCHAR(36) NOT NULL,
  `mini_task_id` VARCHAR(36) NOT NULL,
  `material_id` VARCHAR(36) NOT NULL,
  `planned_quantity` DECIMAL(10, 2) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_mt_material` (`mini_task_id`, `material_id`),
  CONSTRAINT fk_mtm_mt FOREIGN KEY (`mini_task_id`) REFERENCES mini_tasks(`id`),
  CONSTRAINT fk_mtm_material FOREIGN KEY (`material_id`) REFERENCES materials(`id`)
);
CREATE TABLE `attachments` (
  `id` VARCHAR(36) NOT NULL,
  `service_order_id` VARCHAR(36) NULL,
  `mini_task_id` VARCHAR(36) NULL,
  `file_path` VARCHAR(250) NOT NULL,
  `file_name` VARCHAR(250) NOT NULL,
  `mime_type` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_attachments_so FOREIGN KEY (`service_order_id`) REFERENCES service_orders(`id`),
  CONSTRAINT fk_attachments_mt FOREIGN KEY (`mini_task_id`) REFERENCES mini_tasks(`id`),
  CONSTRAINT check_attachment_entity CHECK (
    (
      service_order_id IS NOT NULL
      AND mini_task_id IS NULL
    )
    OR (
      service_order_id IS NULL
      AND mini_task_id IS NOT NULL
    )
  )
);
CREATE TABLE `equipment_revisions` (
  `id` VARCHAR(36) NOT NULL,
  `equipment_id` VARCHAR(36) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `approved_by` VARCHAR(36) NULL,
  `approved_at` DATETIME NULL,
  `revision_date` DATETIME NOT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT fk_er_equipment FOREIGN KEY (`equipment_id`) REFERENCES equipments(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_er_approver FOREIGN KEY (`approved_by`) REFERENCES users(`id`) ON DELETE SET NULL
);
CREATE TABLE `work_log_equipment` (
  `work_log_id` VARCHAR(36) NOT NULL,
  `equipment_id` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`work_log_id`, `equipment_id`),
  CONSTRAINT fk_wle_wl FOREIGN KEY (`work_log_id`) REFERENCES work_logs(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_wle_equipment FOREIGN KEY (`equipment_id`) REFERENCES equipments(`id`) ON DELETE CASCADE
);
-- ===== INDEXES COMPLETOS =====
-- Users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_users_status ON users(status);
-- Clients
CREATE INDEX idx_clients_user ON clients(user_id);
-- Locations & Geography
CREATE INDEX idx_locations_parish ON locations(parish_id);
CREATE INDEX idx_parishes_municipality ON parishes(municipality_id);
CREATE INDEX idx_municipalities_district ON municipalities(district_id);
-- Sectors & Teams
CREATE INDEX idx_sectors_head ON sectors(head_id);
CREATE INDEX idx_teams_sector ON teams(sector_id);
-- Service Orders
CREATE INDEX idx_service_orders_manager ON service_orders(manager_id);
CREATE INDEX idx_service_orders_client ON service_orders(client_id);
CREATE INDEX idx_service_orders_location ON service_orders(location_id);
CREATE INDEX idx_service_orders_service_type ON service_orders(service_type_id);
CREATE INDEX idx_service_orders_status ON service_orders(status);
CREATE INDEX idx_service_orders_priority ON service_orders(priority);
CREATE INDEX idx_service_orders_created ON service_orders(created_at);
CREATE INDEX idx_service_orders_status_created ON service_orders(status, created_at);
CREATE INDEX idx_service_orders_equipment ON service_orders(equipment_id);
-- Tasks
CREATE INDEX idx_tasks_service_order ON tasks(service_order_id);
CREATE INDEX idx_tasks_manager ON tasks(manager_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_so_status ON tasks(service_order_id, status);
-- Tasks-Sectors (Junction)
CREATE INDEX idx_tasks_sectors_task ON tasks_sectors(task_id);
CREATE INDEX idx_tasks_sectors_sector ON tasks_sectors(sector_id);
-- Mini-tasks
CREATE INDEX idx_mini_tasks_task ON mini_tasks(task_id);
CREATE INDEX idx_mini_tasks_supervisor ON mini_tasks(supervisor_id);
CREATE INDEX idx_mini_tasks_status ON mini_tasks(status);
CREATE INDEX idx_mini_tasks_task_status ON mini_tasks(task_id, status);
-- Work Logs
CREATE INDEX idx_work_logs_mini_task ON work_logs(mini_task_id);
CREATE INDEX idx_work_logs_created ON work_logs(created_at);
-- Work Logs Materials
CREATE INDEX idx_work_logs_materials_wl ON work_logs_materials(work_log_id);
CREATE INDEX idx_work_logs_materials_material ON work_logs_materials(material_id);
-- Work Logs Workers (Junction)
CREATE INDEX idx_work_logs_workers_wl ON work_logs_workers(work_log_id);
CREATE INDEX idx_work_logs_workers_worker ON work_logs_workers(worker_id);
-- Materials
CREATE INDEX idx_materials_unit ON materials(unit_id);
-- Workers
CREATE INDEX idx_workers_user ON workers(user_id);
CREATE INDEX idx_workers_team ON workers(team_id);
-- Mini-tasks Materials
CREATE INDEX idx_mini_tasks_materials_mt ON mini_tasks_materials(mini_task_id);
CREATE INDEX idx_mini_tasks_materials_material ON mini_tasks_materials(material_id);
-- Mini-tasks Workers Teams (Junction)
CREATE INDEX idx_mini_tasks_workers_teams_mt ON mini_tasks_workers_teams(mini_task_id);
CREATE INDEX idx_mini_tasks_workers_teams_worker ON mini_tasks_workers_teams(worker_id);
CREATE INDEX idx_mini_tasks_workers_teams_team ON mini_tasks_workers_teams(team_id);
-- Attachments
CREATE INDEX idx_attachments_so ON attachments(service_order_id);
CREATE INDEX idx_attachments_mt ON attachments(mini_task_id);
-- User Roles (Junction)
CREATE INDEX idx_user_roles_user ON user_roles(user_id);
CREATE INDEX idx_user_roles_role ON user_roles(role_id);
-- Role Permissions
CREATE INDEX idx_role_permissions_role ON role_permissions(role_id);
-- User Preferences
CREATE INDEX idx_user_preferences_user ON user_preferences(user_id);
-- Materials Unit
CREATE INDEX idx_materials_unit_fk ON materials(unit_id);
-- ===== EQUIPMENT INDEXES =====
CREATE INDEX idx_equipments_manager ON equipments(manager_id);
CREATE INDEX idx_equipments_status ON equipments(status);
CREATE INDEX idx_equipments_next_revision ON equipments(next_revision_date);
-- ===== EQUIPMENT_REVISIONS INDEXES =====
CREATE INDEX idx_equipment_revisions_equipment ON equipment_revisions(equipment_id);
CREATE INDEX idx_equipment_revisions_status ON equipment_revisions(status);
CREATE INDEX idx_equipment_revisions_approved_at ON equipment_revisions(approved_at);
-- ===== WORK_LOG_EQUIPMENT INDEXES =====
CREATE INDEX idx_work_log_equipment_wl ON work_log_equipment(work_log_id);
CREATE INDEX idx_work_log_equipment_equipment ON work_log_equipment(equipment_id);