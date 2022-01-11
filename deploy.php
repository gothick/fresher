<?php
namespace Deployer;

require 'recipe/symfony.php';

require 'contrib/cachetool.php';
require 'contrib/webpack_encore.php';

// Cachetool needs to be a lower version than default,
// as the latest only works with php 8.
// https://github.com/deployphp/deployer/issues/2344
// https://gordalina.github.io/cachetool/
// https://github.com/deployphp/deployer/blob/master/contrib/cachetool.php#L55
// https://github.com/gordalina/cachetool/releases/download/7.0.0/cachetool.phar
set('bin/cachetool', function () {
    if (!test('[ -f {{release_or_current_path}}/cachetool.phar ]')) {
        run("cd {{release_or_current_path}} && curl -sLO https://github.com/gordalina/cachetool/releases/download/7.0.0/cachetool.phar");
    }
    return '{{release_or_current_path}}/cachetool.phar';
});


// Config
set('repository', 'git@github.com:gothick/fresher.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
host('fresher.gothick.org.uk')
    ->set('remote_user', 'fresher')
    ->set('deploy_path', '/var/www/sites/fresher.gothick.org.uk')
    ->set('webpack_encore/env', 'production')
    ->set('webpack_encore/package_manager', 'yarn')
    ->set('cachetool_args', '--fcgi=/run/php/chef-managed-fpm-fresher.sock --tmp-dir=/tmp')
    ;

// Tasks
task('build', function () {
    cd('{{release_path}}');
    run('npm run build');
});

after('deploy:failed', 'deploy:unlock');

// Migrate just before we symlink the new release
before('deploy:symlink', 'database:migrate');

// Clear opcache on successful deployment
after('deploy:symlink', 'cachetool:clear:opcache');

// Yarn and Webpack Encore.
after('deploy:vendors', 'yarn:install');
after('yarn:install', 'webpack_encore:build');
