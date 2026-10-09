$table->publicId(); 
#maria db
DB_CONNECTION=mariadb \
DB_URL= \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=your_database \
DB_USERNAME=your_user \
DB_PASSWORD='your_password' \
php artisan optimize:clear && \
DB_CONNECTION=mariadb \
DB_URL= \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=your_database \
DB_USERNAME=your_user \
DB_PASSWORD='your_password' \
php artisan migrate:fresh --seed --database=mariadb --force

#mysql
DB_CONNECTION=mysql \
DB_URL= \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=your_database \
DB_USERNAME=your_user \
DB_PASSWORD='your_password' \
php artisan optimize:clear && \
DB_CONNECTION=mysql \
DB_URL= \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=your_database \
DB_USERNAME=your_user \
DB_PASSWORD='your_password' \
php artisan migrate:fresh --seed --database=mysql --force
