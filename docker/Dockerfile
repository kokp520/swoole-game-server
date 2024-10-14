# 基於官方 PHP 8.3-fpm 映像
FROM php:8.3-fpm

# 更新包管理器並安裝依賴項
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libpcre3-dev \
    zlib1g-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libxml2-dev \
    git \
    zip \
    unzip \
    nginx \
    brotli \
    libbrotli-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# 安裝 Swoole
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# 清理多餘的安裝包
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 設置工作目錄
WORKDIR /var/www/html

# 複製當前目錄的所有文件到工作目錄
COPY . /var/www/html
