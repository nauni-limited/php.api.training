#!/bin/bash

set -e

# Check unit test database
while ! mysql --protocol TCP -uphp_api_training_user -puser_secret_test_password -hphp-api-training-db-test -e "show databases;" > /dev/null 2>&1; do
       echo "Waiting for php-api-training-db-test connection. "
       sleep 5
done
