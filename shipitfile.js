module.exports = function (shipit) {
  require('shipit-db')(shipit);
  require('shipit-assets')(shipit);

  const util = require('util');
  const dotenv = require('dotenv');

  const envProd = dotenv.config({ path: './.env.db.production' });
  const envStaging = dotenv.config({ path: './.env.db.staging' });
  const envLocal = dotenv.config({ path: './.env' });

  const shipitDbDefaults = {
    shell: {
      unzip: function (file) {
        return util.format('bzip2 -d -f %s', file);
      }
    },
    local: {
      host: envLocal.parsed.DB_SERVER,
      adapter: envLocal.parsed.DB_ADAPTER,
      username: envLocal.parsed.DB_USER,
      password: envLocal.parsed.DB_PASSWORD,
      database: envLocal.parsed.DB_DATABASE,
      port: envLocal.parsed.DB_PORT,
    }
  };

  shipit.initConfig({
    default: {
      workspace: '/tmp/tmp-shipit'
    },
    staging: {
      servers: envLocal.parsed.SHIPIT_SSH_USER + '@' + envLocal.parsed.SHIPIT_SSH_HOST,
      deployTo: envLocal.parsed.SHIPIT_DEPLOYTO_STAGING,
      db: Object.assign({}, shipitDbDefaults, {
        remote: {
          host: envStaging.parsed.DB_SERVER,
          adapter: envStaging.parsed.DB_ADAPTER,
          username: envStaging.parsed.DB_USER,
          password: envStaging.parsed.DB_PASSWORD,
          database: envStaging.parsed.DB_DATABASE,
        }
      }),
      assets: {
        remoteBasePath: envLocal.parsed.SHIPIT_DEPLOYTO_STAGING + '/shared',
        paths: [
          'www/uploads'
        ],
        options: {
          ignores: [],
          rsync: '--progress -h',
        },
      }
    },
    production: {
      servers: envLocal.parsed.SHIPIT_SSH_USER + '@' + envLocal.parsed.SHIPIT_SSH_HOST,
      deployTo: envLocal.parsed.SHIPIT_DEPLOYTO_PRODUCTION,
      db: Object.assign({}, shipitDbDefaults, {
        remote: {
          host: envProd.parsed.DB_SERVER,
          adapter: envProd.parsed.DB_ADAPTER,
          username: envProd.parsed.DB_USER,
          password: envProd.parsed.DB_PASSWORD,
          database: envProd.parsed.DB_DATABASE,
        }
      }),
      assets: {
        remoteBasePath: envLocal.parsed.SHIPIT_DEPLOYTO_PRODUCTION + '/shared',
        paths: [
          'www/uploads'
        ],
        options: {
          ignores: ['cache', '.DS_Store'],
          rsync: '--progress -h',
        },
      }
    }
  });
};
