FROM php:8.5.2-apache

# 必要な拡張機能をインストール
RUN docker-php-ext-install pdo pdo_mysql

# Apacheの設定
RUN a2enmod rewrite

# 作業ディレクトリを設定
WORKDIR /var/www/html

# 権限を設定
RUN chown -R www-data:www-data /var/www/html

# ポート80を公開
EXPOSE 80