## Based to some degree on https://www.strangebuzz.com/en/snippets/the-perfect-makefile-for-symfony
DOCKER_COMP   = docker-compose

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
upp: ## Start the docker compose up for Postgres, which is the default
	$(DOCKER_COMP) up --detach

upm: ## Same, but with MySQL config
	$(DOCKER_COMP) -f docker-compose.mysql.yml -f docker-compose.mysql.override.yml up --detach

down:
	$(DOCKER_COMP) down
