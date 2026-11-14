#!/bin/bash
set -e

# O Render injeta a variável $PORT (ex: 10000)
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/000-default.conf
fi

# Cria o link de storage se ainda não existir
php artisan storage:link || true

# Executa migrações automaticamente se RUN_MIGRATIONS estiver ativado
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Executando migrações do banco de dados..."
    php artisan migrate --force
fi

# Executa seeds se RUN_SEED estiver ativado
if [ "$RUN_SEED" = "true" ]; then
    echo "Executando seeds do banco de dados..."
    php artisan db:seed --force
fi

# Otimizações de cache em produção
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
