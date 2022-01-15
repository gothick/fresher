## Based to some degree on https://www.strangebuzz.com/en/snippets/the-perfect-makefile-for-symfony
DOCKER_COMP   = docker-compose
SYMFONY_BIN   = symfony

CONSOLE       = $(SYMFONY_BIN) console

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands
	@$(CONSOLE)

cc: ## Clear the cache. DID YOU CLEAR YOUR CACHE????
	@$(CONSOLE) cache:clear

warmup: ## Warmup the cache
	@$(CONSOLE) cache:warmup

## —— Symfony server 💻 ————————————————————————————————————————————————————————
cert-install: ## Install the local HTTPS certificates
	@$(SYMFONY_BIN) server:ca:install

proxy:
	@$(SYMFONY_BIN) proxy:start

proxy-domain:
	@$(SYMFONY_BIN) proxy:domain:attach fresher

serveup:
	@$(SYMFONY_BIN) server:start -d

servedown: ## Stop the webserver
	@$(SYMFONY_BIN) server:stop


## —— Docker 🐳 ————————————————————————————————————————————————————————————————
upp: ## Start the docker compose up for Postgres, which is the default
	$(DOCKER_COMP) up --detach

upm: ## Same, but with MySQL config
	$(DOCKER_COMP) -f docker-compose.mysql.yml -f docker-compose.mysql.override.yml up --detach

down:
	$(DOCKER_COMP) down
