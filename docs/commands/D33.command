#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")/../.."
php artisan test --compact --filter=test_production_error_is_sanitized_and_logged
