#!/bin/bash

source "$(dirname "$0")/x-common.sh"

echo ""

for REPO_KIND in "${REPO_KINDS[@]}"
do

    echo "##########################################"
    echo ""
    "${CONTENT_PATH}.migrate/x-reset-repo.sh" "${REPO_KIND}"

done

echo "##########################################"
echo ""