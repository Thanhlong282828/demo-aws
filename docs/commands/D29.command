#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")/../.."
php artisan route:list --path=api/products
