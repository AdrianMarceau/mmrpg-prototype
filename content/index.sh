#!/usr/bin/env bash

declare -a REPO_KINDS=(
    "sql"
    "types"
    "battles"
    "players"
    "robots"
    "abilities"
    "items"
    "skills"
    "fields"
    "stars"
    "challenges"
    "pages"
    )

declare -A REPO_PATHS
REPO_PATHS[sql]=".sql"
REPO_PATHS[types]="types"
REPO_PATHS[battles]="battles"
REPO_PATHS[players]="players"
REPO_PATHS[robots]="robots"
REPO_PATHS[abilities]="abilities"
REPO_PATHS[skills]="skills"
REPO_PATHS[items]="items"
REPO_PATHS[fields]="fields"
REPO_PATHS[stars]="stars"
REPO_PATHS[challenges]="challenges"
REPO_PATHS[pages]="pages"
