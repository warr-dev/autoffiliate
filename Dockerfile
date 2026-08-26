FROM fafawar/laravel-devcontainer:php8.3-node22

WORKDIR /app

RUN mkdir -p /app/storage /app/bootstrap/cache /app/public/media \
    && chmod -R 777 /app/storage /app/bootstrap/cache /app/public/media

EXPOSE 8000 8001 5001 5173

CMD ["sh", "-c", "php artisan storage:link || true && php artisan migrate --force && npx concurrently 'php artisan serve --host=0.0.0.0 --port=8000' 'npm run dev -- --host 0.0.0.0 --port 5001'"]
