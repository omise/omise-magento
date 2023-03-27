#!/bin/bash

attempt_counter=0
max_attempts=30
healh_check_url="https://magento.staging-omise.co/aut/"

until $(curl --output /dev/null --silent --fail ${healh_check_url}); do
if [ ${attempt_counter} -eq ${max_attempts} ];then
    echo "Max attempts reached\n"
    exit 1
fi
attempt_counter=$(($attempt_counter+1))
printf "(${attempt_counter}) waiting for ${healh_check_url} to finish starting . . .\n"
sleep 30
done
echo "Website is ready..."
