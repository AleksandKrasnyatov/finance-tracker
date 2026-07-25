#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="${COMPOSE_FILE:-compose-prod.yml}"
APP_PORT="${APP_PORT:-8080}"

compose() {
  docker compose -f "${COMPOSE_FILE}" "$@"
}

cmd="${1:-deploy}"

case "${cmd}" in
  pull)
    compose pull
    ;;
  up)
    compose up -d
    ;;
  down)
    compose down --remove-orphans
    ;;
  migrate)
    compose --profile tools run --rm php-cli \
      php bin/app.php migrations:migrate --no-interaction
    ;;
  webhook)
    # php-cli is under Compose profile "tools" so it does not stay up with `up -d`
    compose --profile tools run --rm php-cli \
      php bin/app.php telegram:webhook
    ;;
  health)
    curl -sf "http://127.0.0.1:${APP_PORT}/health" | grep -q alive
    echo "health: alive"
    ;;
  deploy)
    compose pull
    compose up -d
    compose --profile tools run --rm php-cli \
      php bin/app.php migrations:migrate --no-interaction
    for _ in $(seq 1 30); do
      if curl -sf "http://127.0.0.1:${APP_PORT}/health" | grep -q alive; then
        echo "health: alive"
        # Idempotent: Telegram keeps the same webhook URL; also refreshes command menu
        compose --profile tools run --rm php-cli \
          php bin/app.php telegram:webhook
        exit 0
      fi
      sleep 1
    done
    echo "health check failed" >&2
    exit 1
    ;;
  *)
    echo "Usage: $0 {pull|up|down|migrate|webhook|health|deploy}" >&2
    exit 1
    ;;
esac
