FROM php:8.2-apache

# Copie les fichiers dans le dossier du serveur Apache
COPY . /var/www/html/

# Active mod_rewrite (utile pour Laravel ou autres frameworks)
RUN a2enmod rewrite

# Droits pour Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80