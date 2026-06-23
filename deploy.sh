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

# Run database migrations (only applies new migrations; does not re-run old ones or seed data)
echo "Running database migrations..."
sudo -u www-data php artisan migrate --force

# NOTE: Do not add seeders here. Routine deploy must not overwrite admin content
# (VR experiments, thumbnails, active flags, etc.). To load PhET sims once, run manually:
#   sudo -u www-data php artisan db:seed --class=SubjectSeeder --force
#   sudo -u www-data php artisan db:seed --class=PhetVrExperimentSeeder --force
# To load curriculum-aligned quiz questions (skips experiments not in DB):
#   sudo -u www-data php artisan db:seed --class=PhetVrQuestionSeeder --force

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
