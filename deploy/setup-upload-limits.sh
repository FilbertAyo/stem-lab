#!/usr/bin/env bash
#
# One-time (or idempotent) server setup for file uploads on Ubuntu + nginx + PHP-FPM.
# Run from the project root on the server:
#   sudo bash deploy/setup-upload-limits.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
NGINX_SNIPPET="/etc/nginx/conf.d/stem-lab-uploads.conf"
PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
PHP_INI="/etc/php/${PHP_VER}/fpm/conf.d/99-stem-lab-upload-limits.ini"
PHP_FPM_SERVICE="php${PHP_VER}-fpm"

echo "Configuring upload limits for Stem Lab..."

if ! command -v nginx >/dev/null 2>&1; then
    echo "ERROR: nginx is not installed."
    exit 1
fi

if ! command -v php >/dev/null 2>&1; then
    echo "ERROR: php is not installed."
    exit 1
fi

echo "→ Installing nginx upload snippet at ${NGINX_SNIPPET}"
cp "${SCRIPT_DIR}/nginx/uploads.conf" "${NGINX_SNIPPET}"

# Site configs can override http-level limits; patch the app vhost when present.
for SITE_CONFIG in \
    "/etc/nginx/sites-available/adilishastemlabs.com" \
    "/etc/nginx/sites-available/default" \
    "/etc/nginx/sites-enabled/adilishastemlabs.com"
do
    if [ -f "${SITE_CONFIG}" ]; then
        if grep -q 'client_max_body_size' "${SITE_CONFIG}"; then
            sed -i 's/^[[:space:]]*client_max_body_size[[:space:]].*;/    client_max_body_size 100M;/' "${SITE_CONFIG}"
            echo "→ Updated client_max_body_size in ${SITE_CONFIG}"
        else
            sed -i '/server_name/a \    client_max_body_size 100M;' "${SITE_CONFIG}"
            echo "→ Added client_max_body_size to ${SITE_CONFIG}"
        fi
    fi
done

echo "→ Testing nginx configuration"
nginx -t

echo "→ Reloading nginx"
systemctl reload nginx

if [ -d "/etc/php/${PHP_VER}/fpm/conf.d" ]; then
    echo "→ Installing PHP-FPM upload limits at ${PHP_INI}"
    cp "${SCRIPT_DIR}/php/99-upload-limits.ini" "${PHP_INI}"

    if systemctl is-active --quiet "${PHP_FPM_SERVICE}"; then
        echo "→ Restarting ${PHP_FPM_SERVICE}"
        systemctl restart "${PHP_FPM_SERVICE}"
    else
        echo "WARNING: ${PHP_FPM_SERVICE} is not active; PHP limits were written but FPM was not restarted."
    fi
else
    echo "WARNING: PHP-FPM conf.d directory not found for PHP ${PHP_VER}."
    echo "         Copy deploy/php/99-upload-limits.ini into your PHP ini manually."
fi

echo ""
echo "Upload limits configured:"
echo "  nginx client_max_body_size: 100M"
echo "  PHP upload_max_filesize:    100M"
echo "  PHP post_max_size:          100M"
echo ""
echo "Done. You can now upload files up to 100 MB."
