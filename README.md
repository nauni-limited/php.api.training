## PHP API Training

### Requirements
```
docker >= v20
docker-compose >= v1.27 
```

### Basic usage

#### Clone the new project
```sh
git clone git@github.com:nauni-limited/php.api.training.git
```

#### Use Xdebug and change exposed ports
- Rename docker-compose.override.yml and change it as you like
- Using pcov is faster, only use xdebug when you are debugging code

#### Run the project for the first time
```sh
docker-compose pull
docker-compose build --no-cache
docker-compose up
docker exec -ti php-api-training-fpm composer install
```

#### Run the migrations
```sh
# Run for dev env
docker exec -ti php-api-training-fpm console doctrine:migrations:migrate
# Run for tests
docker exec -ti php-api-training-fpm console doctrine:migrations:migrate --env=test --no-interaction
```
You will need to repeat this if you rebuild the project or if you kill / remove your containers.


#### Run individual tests for the whole project
```sh
#### PHP Unit
# all phpunit tests
docker exec -ti php-api-training-fpm phpunit tests
# feature tests only
docker exec -ti php-api-training-fpm phpunit tests/Feature
# generate html code coverage 
docker exec -ti php-api-training-fpm phpunit tests --coverage-html var/coverage

#### PHP CS
# run checks
docker exec -ti php-api-training-fpm vendor/bin/phpcs src tests && echo "PASS"
#auto fix code style where possible
docker exec -ti php-api-training-fpm vendor/bin/phpcbf src tests

#### PHP STAN
docker exec -ti php-api-training-fpm vendor/bin/phpstan analyse \
              --level=max \
              --configuration=phpstan-src.neon \
              src

docker exec -ti php-api-training-fpm vendor/bin/phpstan analyse \
              --level=max \
              --configuration=phpstan-tests.neon \
              tests
```

#### Run all tests for a suite
- You need to copy phpunit.xml.dist to phpunit.xml

```sh
docker exec -ti php-api-training-fpm console test:all [suite]
```

#### Rebuild the project images
```sh
docker-compose kill \
  && docker-compose rm  -f \
  && docker-compose build --no-cache \
  && docker-compose up --remove-orphans 
```