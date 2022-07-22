#!/bin/sh

#--------------------------------------------------------------------------
# Prepare Laravel app to run
#
# Would be better to put this file to separate container, and to communicate
# with 'workspace' container from there.
# That way there'll be no need to use 'tail' hack to leave 'workspace' running,
# and user will be able to decide, if s/he needs to run these commands on a
# given container startup, or not.
#--------------------------------------------------------------------------

#Folder, where we have files of a site
newsitefolder="/var/www"     #x
#Files folder, specific for a site
customfilesfolder="storage"     #x

cd $newsitefolder/

# Install dependencies
composer install

#Permissions for files folder
find . -type d -name $customfilesfolder -exec chmod ug=rwx,o=rwx '{}' \;
find . -name $customfilesfolder -type d -exec find '{}' -type f \; | while read FILE; do chmod ug=rwx,o=rwx "$FILE"; done
find . -name $customfilesfolder -type d -exec find '{}' -type d \; | while read DIR; do chmod ug=rwx,o=rwx "$DIR"; done

# Create DB structure
# 'Workspace' container starts only after Postgres DB (not just it's container) is up
# So it's safe to invoke 'migrate' command
php artisan migrate
php artisan migrate --env testing
# Create symlink from 'public/storage' to 'storage/app/public'
php artisan storage:link

# Ugly hack to make container run after commands in script file were executed
tail -f /dev/null