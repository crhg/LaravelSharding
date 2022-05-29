#!/usr/bin/env bash

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS testing;
    GRANT ALL PRIVILEGES ON testing.* TO '$MYSQL_USER'@'%';
    CREATE DATABASE IF NOT EXISTS testing_a1;
    GRANT ALL PRIVILEGES ON testing_a1.* TO '$MYSQL_USER'@'%';
    CREATE DATABASE IF NOT EXISTS testing_a2;
    GRANT ALL PRIVILEGES ON testing_a2.* TO '$MYSQL_USER'@'%';
EOSQL
