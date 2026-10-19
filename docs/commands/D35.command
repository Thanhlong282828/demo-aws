#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")/../.."
php artisan test --compact
