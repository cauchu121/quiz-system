FROM php:8.2-apache

# Cài đặt extension mysqli để kết nối MySQL và cấu hình Apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Cấu hình Apache nhận diện UTF-8
RUN echo "AddDefaultCharset UTF-8" >> /etc/apache2/conf-available/charset.conf \
    && a2enconf charset

# Copy mã nguồn vào thư mục web server
COPY . /var/www/html/

# Phân quyền cho www-data
RUN chown -R www-data:www-data /var/www/html
