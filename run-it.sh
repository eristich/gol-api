./wait-for-it.sh gol-db:5432
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load --no-interaction
frankenphp run --config /etc/caddy/Caddyfile