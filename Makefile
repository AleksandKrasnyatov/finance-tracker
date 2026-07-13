init: copy-env docker-down-clear docker-build docker-up composer-install migrations
up: docker-up
down: docker-down
restart: down up
check: validate-schema lint cs-check phpstan test

copy-env:
	[ -f .env ] || cp .env.example .env

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker compose down -v --remove-orphans

docker-build:
	docker compose build

composer-install:
	docker compose run --rm php-cli composer install

migrations:
	docker compose run --rm php-cli composer app migrations:migrate -- --no-interaction

validate-schema:
	docker compose run --rm php-cli composer app orm:validate-schema -- -v

test:
	docker compose run --rm php-cli composer test

test-unit:
	docker compose run --rm php-cli composer test Unit

test-functional:
	docker compose run --rm php-cli composer test Functional

test-acceptance:
	docker compose run --rm php-cli composer test Acceptance

test-coverage:
	docker compose run --rm php-cli composer test-coverage

cs-check:
	docker compose run --rm php-cli composer cs-check

cs-fix:
	docker compose run --rm php-cli composer cs-fix

phpstan:
	docker compose run --rm php-cli composer phpstan

lint:
	docker compose run --rm php-cli composer lint

telegram-polling:
	docker compose run --rm php-cli composer app telegram:run

telegram-add-webhook:
	docker compose exec app php bin/app.php telegram:webhook

telegram-delete-webhook:
	docker compose exec app php bin/app.php telegram:webhook --delete
