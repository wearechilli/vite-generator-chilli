source "./scripts/defaults.sh"

# Perform db backup
OUTPUT=$(nitro craft db/backup)

# Cleanup output
BACKUP_FILE=$(echo $OUTPUT | sed -e 's/.*\/storage\/backups\/\(.*\) (.*/\1/')

# Make sure the backups folder exists
ssh $SSH_REMOTE_USER@$SSH_REMOTE_HOST "mkdir -p checkout/$1/current/storage/backups" &> /dev/null

# Upload backup
scp ./storage/backups/$BACKUP_FILE $SSH_REMOTE_USER@$SSH_REMOTE_HOST:checkout/$1/current/storage/backups

# Import backup
ssh $SSH_REMOTE_USER@$SSH_REMOTE_HOST "checkout/$1/current/craft db/restore checkout/$1/current/storage/backups/$BACKUP_FILE"
