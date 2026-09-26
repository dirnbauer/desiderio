# Cloud Agent base image for the Desiderio TYPO3 14 extension.
# Provides the stable system toolchain (PHP 8.4 + extensions, Composer, Node.js)
# that the default Cursor image lacks. Repository dependencies (Composer/npm
# packages, the disposable QA app) are installed by the `install` script in
# .cursor/environment.json so they always match the checked-out sources.
FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive \
    LANG=C.UTF-8 \
    LC_ALL=C.UTF-8

# Base tooling + PHP 8.4 (matches the CI matrix and composer platform pin) with
# every extension the extension and its functional/QA suites need:
#   intl, gd  -> rendering; sqlite3/pdo_sqlite -> disposable QA app + functional
#   tests; mysql(i) -> MariaDB functional runs; mbstring/xml/curl/zip/bcmath/gmp
#   -> TYPO3 core + Content Blocks.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl git gnupg lsb-release software-properties-common \
        sudo unzip \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php8.4-cli \
        php8.4-intl \
        php8.4-gd \
        php8.4-sqlite3 \
        php8.4-mysql \
        php8.4-mbstring \
        php8.4-xml \
        php8.4-curl \
        php8.4-zip \
        php8.4-bcmath \
        php8.4-gmp \
    && update-alternatives --install /usr/bin/php php /usr/bin/php8.4 84 \
    && php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');" \
    && php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer \
    && rm -f /tmp/composer-setup.php \
    && curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# The `ubuntu` user (uid 1000) already ships in the base image; give it
# passwordless sudo to mirror the default Cloud Agent image.
RUN echo 'ubuntu ALL=(ALL) NOPASSWD:ALL' > /etc/sudoers.d/ubuntu \
    && chmod 0440 /etc/sudoers.d/ubuntu

USER ubuntu
