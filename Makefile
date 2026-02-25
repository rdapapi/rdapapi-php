.PHONY: test test-coverage lint format analyse clean

test:
	./vendor/bin/pest

test-coverage:
	./vendor/bin/pest --coverage

lint:
	./vendor/bin/pint --test

format:
	./vendor/bin/pint

analyse:
	./vendor/bin/phpstan analyse

clean:
	rm -rf vendor .phpstan-cache coverage
