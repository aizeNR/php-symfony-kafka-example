COMPOSE_PROJECT_NAME := "php-symfony-kafka"

TIMEZONE := ""
CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

COMPOSE=docker-compose -f docker/docker-compose.yml
build:
	 $(COMPOSE) build php-core

sh:
	$(COMPOSE) run -u $(CURRENT_UID) --entrypoint bash php-core
