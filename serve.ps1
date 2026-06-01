# Start Laravel with upload limits for large chapter ZIP files (up to ~500 MB).
php -d post_max_size=512M -d upload_max_filesize=512M -d max_execution_time=0 -d memory_limit=512M artisan serve --port=8283
