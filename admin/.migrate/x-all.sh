#!/usr/bin/env bash

source "$(dirname "$0")/x-common.sh"

echo ""

for REPO_KIND in "${REPO_KINDS[@]}"
do

    echo "##########################################"
    "${ADMIN_PATH}.migrate/x-migrate-subrepo.sh" "${REPO_KIND}"
    "${ADMIN_PATH}.migrate/x-reset-subrepo.sh" "${REPO_KIND}"

done

echo "##########################################"
echo ""