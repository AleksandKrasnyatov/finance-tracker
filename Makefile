init: copy-env docker-down-clear docker-build docker-up wait-db composer-install test-build migrations
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

wait-db:
	docker compose up -d postgres redis
	docker compose run --rm php-cli wait-for-it postgres:5432 -t 30

composer-install:
	docker compose run --rm php-cli composer install

migrations:
	docker compose run --rm php-cli composer app migrations:migrate -- --no-interaction

validate-schema:
	docker compose run --rm php-cli composer app orm:validate-schema -- -v

test-build:
	docker compose run --rm php-cli composer test-build

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

reminders-send:
	docker compose run --rm php-cli composer app reminders:send

telegram-add-webhook:
	docker compose exec app php bin/app.php telegram:webhook

telegram-delete-webhook:
	docker compose exec app php bin/app.php telegram:webhook --delete

build:
	docker build --pull --file=docker/production/nginx/Dockerfile --tag=${REGISTRY}/finance-tracker-nginx:${IMAGE_TAG} .
	docker build --pull --file=docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/finance-tracker-php-fpm:${IMAGE_TAG} .
	docker build --pull --file=docker/production/php-cli/Dockerfile --tag=${REGISTRY}/finance-tracker-php-cli:${IMAGE_TAG} .

push:
	docker push ${REGISTRY}/finance-tracker-nginx:${IMAGE_TAG}
	docker push ${REGISTRY}/finance-tracker-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY}/finance-tracker-php-cli:${IMAGE_TAG}

try-build:
	REGISTRY=localhost IMAGE_TAG=testing $(MAKE) build

testing-pull:
	docker compose -f compose-testing.yml pull

testing-up: copy-env
	docker compose -f compose-testing.yml up -d

testing-down:
	docker compose -f compose-testing.yml down --remove-orphans

testing-down-clear:
	docker compose -f compose-testing.yml down -v --remove-orphans
