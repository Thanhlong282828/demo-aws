#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")/../.."
python3 -m json.tool docs/postman/Mini-Shop-API.postman_collection.json > /dev/null
printf "Postman JSON hợp lệ\n"
