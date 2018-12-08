SERVER_NAME=Moddroid.Com

REMOTE_NAME="remote"
TIMESTAMP=$(date +"%F")
BACKUP_DIR="/home/backup/$TIMESTAMP"
MYSQL=/usr/bin/mysql
MYSQLDUMP=/usr/bin/mysqldump
MYSQL_USER="thien"
MYSQL_PASSWORD="thiendeptrai@"
SECONDS=0

# Begin run

mkdir -p "$BACKUP_DIR/mysql"

echo "Starting Backup Database";
databases=`$MYSQL -u $MYSQL_USER -p$MYSQL_PASSWORD -e "SHOW DATABASES;" | grep -Ev "(Database|information_schema|performance_schema|mysql)"`

for db in $databases; do
 $MYSQLDUMP -u $MYSQL_USER -p$MYSQL_PASSWORD --force --opt $db | gzip > "$BACKUP_DIR/mysql/$db.gz"
done
echo "Finished";
echo '';

echo "Starting Backup Website's files";
# Loop through /html directory
#for Web in /var/www/html/*; do
 #if [ -d "${Web}" ]; then #If a directory
  #folder_name=${Web##*/} # get web folder name
  #echo "- "$folder_name;
  zip -r $BACKUP_DIR/backup.zip /var/www/html/ -q -x /var/www/html/wp-content/cache/**\* #Exclude cache
 #fi
#done
echo "Finished";
echo '';

size=$(du -sh $BACKUP_DIR | awk '{ print $1}')

echo "Starting Uploading Backup";
/usr/sbin/rclone move $BACKUP_DIR "$REMOTE_NAME:$SERVER_NAME/$TIMESTAMP" >> /var/log/rclone.log 2>&1

# Clean up in google drive
rm -rf $BACKUP_DIR
/usr/sbin/rclone -q --min-age 10d delete "$REMOTE_NAME:$SERVER_NAME" #Remove all backups older than 4 days
/usr/sbin/rclone -q --min-age 10d rmdirs "$REMOTE_NAME:$SERVER_NAME" #Remove all empty folders older than 4 days
/usr/sbin/rclone cleanup "$REMOTE_NAME:" #Cleanup Trash
echo "Finished";
echo '';

duration=$SECONDS
echo "Done backup with: Total $size, In $(($duration / 60)) minutes and $(($duration % 60)) seconds elapsed."