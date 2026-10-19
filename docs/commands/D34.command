#!/bin/bash
set -euo pipefail
curl --fail --silent --show-error -o /dev/null -w 'HTTPS API: %{http_code}; TLS: %{ssl_verify_result}\n' https://54.252.208.88/api/products
