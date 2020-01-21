fix:
	vendor/bin/phpcbf

testPhpCs:
	@printf "\e[103;30m******************************             PhpCs             ******************************\e[0m\n"
	vendor/bin/phpcs -p

testPhpStan:
	@printf "\e[103;30m******************************            PhpStan            ******************************\e[0m\n"
	@printf "Running stan...\n"
	vendor/bin/phpstan analyse

testPhp:
	@printf "\e[103;30m******************************            PhpTest            ******************************\e[0m\n"
	vendor/bin/tester tests --coverage coverage.xml -C --coverage-src src
	@rm -rf ./tmp

test: testPhpCs testPhpStan testPhp
