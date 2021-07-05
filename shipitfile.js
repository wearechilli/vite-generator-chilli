module.exports = function (shipit) {
  const dotenv = require('dotenv');
  const envLocal = dotenv.config({ path: './.env' })
  const envVite = dotenv.config({ path: './.env.db.vite' })

  shipit.initConfig({
    default: {
      servers: envLocal.parsed.SHIPIT_SSH_USER + '@' + envLocal.parsed.SHIPIT_SSH_HOST
    },
    vite: {
      deployTo: envVite.parsed.SHIPIT_DEPLOYTO + '/vite'
    }
  });

  shipit.task('db:pull', async () => {
    const release = `checkout/${shipit.environment}/current`;
    await shipit.remote(`cd ${release} && ./craft db/backup`);
    await shipit.remote(`cd ${release}/storage/backups && ls -Art | tail -n 1 | tr -d '\n'`)
      .then(([{stdout}]) => {
        const remotePath = `${release}/storage/backups/${stdout}`;
        shipit.copyFromRemote(remotePath, './storage/backups').then(() => {
          shipit.local(`nitro db import storage/backups/${stdout} --name ${envLocal.parsed.DB_DATABASE}`);
        });
      });
    return;
  })
}
