#!/bin/bash

PAYLOAD='{"commit": "HEAD","branch": "master","message": "Trigger magento e2e tests from omise-magento plugin"}'
curl -H "Authorization: Bearer $1" -X POST "https://api.buildkite.com/v2/organizations/omise/pipelines/omise-magento-test/builds" -d "$PAYLOAD"

