# Usar una imagen base de PHP con Apache
FROM php:8.2.18-apache

# Instalar extensiones necesarias y MySQL Server
RUN apt-get update && apt-get install -y \
    mariadb-server \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Configurar el entorno de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación al contenedor
COPY ./bills /var/www/html

# Copiar script de inicialización de la base de datos
COPY ./scripts /docker-entrypoint-initdb.d

# Configurar permisos y propietarios
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copiar archivo de configuración personalizada de Apache
COPY custom-access.conf /etc/apache2/conf-available/custom-access.conf

# Habilitar la configuración personalizada de Apache
RUN a2enconf custom-access \
    && a2dissite 000-default.conf

# Definir un volumen para la persistencia de datos
VOLUME /var/lib/mysql

# Exponer el puerto 80 para el servidor web
EXPOSE 80

# Script de inicialización para configurar MySQL y Apache
COPY ./init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Comando por defecto para ejecutar el script de inicialización
CMD ["init.sh"]
