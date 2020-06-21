#!/bin/bash
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=types && content/.migrate/reset-repo.sh types
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=battles && content/.migrate/reset-repo.sh battles
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=players && content/.migrate/reset-repo.sh players
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=robots && content/.migrate/reset-repo.sh robots
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=abilities && content/.migrate/reset-repo.sh abilities
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=items && content/.migrate/reset-repo.sh items
echo ""
echo "##########################################"
echo ""
php content/.migrate/migrate-objects.php kind=fields && content/.migrate/reset-repo.sh fields
echo "##########################################"
echo ""