.PHONY: help install serve test lint fix analyze clean setup logs

help: ## Display help
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install Composer dependencies
	composer install

setup: ## Prepare environment (logs, .env)
	@mkdir -p logs
	@chmod 755 logs
	@if [ ! -f .env ]; then cp .env.example .env; echo "Created .env file"; fi
	@echo "Environment ready!"

serve: ## Start development server
	@echo "Starting server at http://localhost:8000"
	php -S localhost:8000

serve-prod: ## Start production server on port 80 (requires sudo)
	@echo "Starting production server at http://localhost"
	sudo php -S localhost:80

test: ## Run PHP syntax tests
	@echo "Checking PHP syntax..."
	@find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; | grep -v "No syntax errors"
	@echo "All PHP files are valid!"

lint: ## Check code style (PHP-CS-Fixer dry-run)
	@echo "Checking code style..."
	@if [ -f vendor/bin/php-cs-fixer ]; then \
		vendor/bin/php-cs-fixer fix --dry-run --diff; \
	else \
		echo "Warning: PHP-CS-Fixer not installed. Run: make install"; \
	fi

fix: ## Fix code style automatically
	@echo "Fixing code style..."
	@if [ -f vendor/bin/php-cs-fixer ]; then \
		vendor/bin/php-cs-fixer fix; \
		echo "Code fixed!"; \
	else \
		echo "Warning: PHP-CS-Fixer not installed. Run: make install"; \
	fi

analyze: ## Run static analysis (PHPStan)
	@echo "Running static analysis..."
	@if [ -f vendor/bin/phpstan ]; then \
		vendor/bin/phpstan analyse --memory-limit=1G; \
	else \
		echo "Warning: PHPStan not installed. Run: make install"; \
	fi

check: lint analyze ## Full code verification (lint + analyze)
	@echo "Verification complete!"

db-import: ## Import database
	@echo "Importing database..."
	@read -p "MySQL username [root]: " user; \
	user=$${user:-root}; \
	mysql -u $$user -p < database.sql
	@echo "Database imported!"

db-export: ## Export database
	@echo "Exporting database..."
	@read -p "MySQL username [root]: " user; \
	user=$${user:-root}; \
	mysqldump -u $$user -p helpdesk_db > database_backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "Backup created!"

logs: ## Display recent logs
	@echo "Last 50 log entries:"
	@echo "\n=== SECURITY LOG ==="
	@tail -n 20 logs/security.log 2>/dev/null || echo "No security logs"
	@echo "\n=== ERROR LOG ==="
	@tail -n 20 logs/error.log 2>/dev/null || echo "No error logs"
	@echo "\n=== APP LOG ==="
	@tail -n 10 logs/app.log 2>/dev/null || echo "No application logs"

logs-clear: ## Clear all logs
	@echo "Clearing logs..."
	@rm -f logs/*.log
	@touch logs/security.log logs/error.log logs/app.log logs/rate_limit.log
	@echo "Logs cleared!"

clean: ## Clean temporary files
	@echo "Cleaning..."
	@rm -rf vendor/
	@rm -f composer.lock
	@rm -f .php-cs-fixer.cache
	@rm -rf .phpunit.cache
	@echo "Temporary files removed!"

info: ## Display system information
	@echo "Environment information:"
	@echo "PHP Version: $$(php -v | head -n 1)"
	@echo "Project: PHP Helpdesk v1.5.0"
	@echo "Status: Production Ready"
	@echo ""
	@echo "Project statistics:"
	@find . -name "*.php" -not -path "./vendor/*" | wc -l | xargs echo "PHP files:"
	@find . -name "*.php" -not -path "./vendor/*" -exec cat {} \; | wc -l | xargs echo "Lines of code:"
	@echo ""
	@echo "Security:"
	@grep -r "declare(strict_types=1)" --include="*.php" . | wc -l | xargs echo "Files with strict types:"

git-status: ## Git repository status
	@echo "Repository status:"
	@git branch --show-current | xargs echo "Branch:"
	@git log -1 --pretty=format:"Last commit: %h - %s (%cr)" 
	@echo ""
	@git status --short

deploy-check: check test ## Pre-deployment check
	@echo "Project ready for deployment!"
	@echo ""
	@echo "Next steps:"
	@echo "1. make db-export (backup database)"
	@echo "2. git push origin main"
	@echo "3. Copy files to production server"
	@echo "4. Set correct permissions (chmod 755)"
	@echo "5. Configure .env on server"

dev: setup serve ## Quick start for developers

test-syntax: ## Run PHP syntax tests
	@php tests/syntax_test.php

test-strict: ## Run strict types coverage test
	@php tests/strict_types_test.php

test-security: ## Run security features test
	@php tests/security_test.php

test-all: test-syntax test-strict test-security ## Run all tests
	@echo "\nAll tests passed successfully!"
