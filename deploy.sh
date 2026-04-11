# Navigate to project directory
cd /var/www/adilishastemlabs.com/html

# Put application in maintenance mode
echo "Enabling maintenance mode..."
sudo -u www-data php artisan down

# Discard any local changes and pull latest from MAIN branch
echo "Pulling latest code from GitHub (main branch)..."
sudo -u www-data git fetch origin
sudo -u www-data git reset --hard origin/main

# Install/update Composer dependencies
echo "Installing Composer dependencies..."
sudo -u www-data composer install --optimize-autoloader --no-dev

# Run database migrations
echo "Running database migrations..."
sudo -u www-data php artisan migrate --force

# Clear and cache configurations
echo "Clearing and caching configurations..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Signal queue workers to exit after current job so systemd restarts them with new code/config
echo "Restarting queue workers..."
sudo -u www-data php artisan queue:restart

# Set proper permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/adilishastemlabs.com/html
sudo chmod -R 755 /var/www/adilishastemlabs.com/html
sudo chmod -R 775 /var/www/adilishastemlabs.com/html/storage
sudo chmod -R 775 /var/www/adilishastemlabs.com/html/bootstrap/cache

# Disable maintenance mode
echo "Disabling maintenance mode..."
sudo -u www-data php artisan up

echo "======================================="
echo "MAIN Deployment Complete!"
echo "======================================="
