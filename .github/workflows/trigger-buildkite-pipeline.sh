#!/bin/bash

PAYLOAD='{"commit": "HEAD", "branch": "master", "message": "Triggered From omise-magento Plugin"}'
curl -H "Authorization: Bearer $1" -X POST "https://api.buildkite.com/v2/organizations/omise/pipelines/magento-docker/builds" -d "$PAYLOAD"
