.PHONY: up down restart test fresh logs shell lint build setup

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose down && docker-compose up -d

test:
	docker-compose exec app php artisan test

fresh:
	docker-compose exec app php artisan migrate:fresh --seed

logs:
	docker-compose logs -f

shell:
	docker-compose exec app bash

lint:
	docker-compose exec app ./vendor/bin/pint --test

build:
	docker-compose build

setup:
	docker-compose up -d --build
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate:fresh --seed
	cd frontend && npm install && npm run build
