#!/usr/bin/env bash

source "$(dirname "$0")/x-common.sh"

if [ "${REPO_PATHS[$1]}" == "" ]; then echo "Invalid content type '${1}'!"; exit; fi

REPO_KIND="${1}"
REPO_DIR="${REPO_PATHS[$1]}"
REPO_PATH="${BASE_PATH}content/${REPO_DIR}/"

"${ADMIN_PATH}.migrate/x-migrate-subrepo.sh" "${REPO_KIND}"
"${ADMIN_PATH}.migrate/x-reset-subrepo.sh" "${REPO_KIND}"

echo ""