#!/bin/bash

# Iniciar MySQL
mysqld &

# Esperar a que MySQL arranque completamente
sleep 5

# Crear base de datos y usuario, ejecutar scripts de inicialización
mysql -e "CREATE DATABASE IF NOT EXISTS facturas_db;"
mysql -e "CREATE USER 'usuario'@'%' IDENTIFIED BY 'password';"
mysql -e "GRANT ALL PRIVILEGES ON facturas_db.* TO 'usuario'@'%';"
mysql -e "FLUSH PRIVILEGES;"

# Ejecutar scripts SQL si existen
for file in /docker-entrypoint-initdb.d/*.sql; do
    mysql facturas_db < "$file"
done

# Iniciar Apache en primer plano
apache2-foreground
