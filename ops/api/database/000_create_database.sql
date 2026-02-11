-- This file is mounted in docker-entrypoint-initdb.d to create ops_db on first run
CREATE DATABASE IF NOT EXISTS ops_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON ops_db.* TO 'snoezelen'@'%';
FLUSH PRIVILEGES;
