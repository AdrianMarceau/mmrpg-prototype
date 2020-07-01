#!/bin/bash

CURRENT_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )/"
CONTENT_PATH="$(dirname "${CURRENT_PATH}")/"
BASE_PATH="$(dirname "${CONTENT_PATH}")/"
ADMIN_PATH="${BASE_PATH}admin/"
GITHUB_BASE="https://github.com/AdrianMarceau/"

declare -a REPO_KINDS=(
    "sql"
    "types"
    "battles"
    "players"
    "robots"
    "abilities"
    "items"
    "fields"
    )

declare -A REPO_PATHS
REPO_PATHS[sql]=".sql"
REPO_PATHS[types]="types"
REPO_PATHS[battles]="battles"
REPO_PATHS[players]="players"
REPO_PATHS[robots]="robots"
REPO_PATHS[abilities]="abilities"
REPO_PATHS[items]="items"
REPO_PATHS[fields]="fields"