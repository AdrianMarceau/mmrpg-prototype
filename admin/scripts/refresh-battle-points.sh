#!/usr/bin/env bash

source "$(dirname "$0")/x-common.sh"

php "${ADMIN_PATH}scripts/refresh-battle-points.php" "limit=$1" "user_id=$2"

echo ""