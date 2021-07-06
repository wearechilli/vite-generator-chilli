source "./scripts/defaults.sh"

# Perform remote db backup
OUTPUT=$(ssh $SSH_REMOTE_USER@$SSH_REMOTE_HOST "checkout/$1/current/craft db/backup")

# Cleanup output
BACKUP_FILE=$(echo $OUTPUT | sed -e 's/.*\/storage\/backups\/\(.*\) (.*/\1/')

# Make sure the backups folder exists
mkdir -p ./storage/backups

# Copy remote backup
scp $SSH_REMOTE_USER@$SSH_REMOTE_HOST:checkout/$1/current/storage/backups/$BACKUP_FILE ./storage/backups

# Import backup
nitro craft db/restore ./storage/backups/$BACKUP_FILE
