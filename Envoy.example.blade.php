@servers(['namebranch' => env('DEPLOY_SERVER')])

@task('deploy', ['on' => 'namebranch'])
    cd ~/folder/folder-project

    # Backup database (contoh)
    # mysqldump -u username -p database_name > backup_$(date +%F).sql

    git pull origin master
    if [ $? -ne 0 ]; then exit 1; fi

    composer install --no-interaction --prefer-dist
    if [ $? -ne 0 ]; then exit 1; fi

    php artisan migrate --force
    if [ $? -ne 0 ]; then
        echo "Migration failed, rolling back..."
        php artisan migrate:rollback --force
        exit 1
    fi

    # Caching and optimizing
    php artisan cache:clear
    php artisan view:cache
    # php artisan optimize:clear
    # php artisan route:cache
    # php artisan route:clear
    # php artisan view:clear

    # Health check with improved check
    http_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 20 {{ env('APP_URL') }})
    if [ "$http_status" -eq 200 ]; then
        echo "Health check passed."
    else
        echo "Health check failed with status: $http_status"
        exit 1
    fi

    echo "Deployment successful!"
@endtask
