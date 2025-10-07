<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'git@github.com:codante-io/codante-io-api.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);
set('keep_releases', 3);

// Hosts

// host('159.89.44.90')
//     ->set('remote_user', 'robertotcestari')
//     ->set('deploy_path', '/var/www/codante-io-api');

host('216.238.103.47')
    ->set('remote_user', 'robertotcestari')
    ->set('deploy_path', '/var/www/codante-io-api');

task('test', function () {
    runLocally('php artisan test');
});

task('deploy:reload-process', function () {
    run('sudo supervisorctl restart all');
});

// Hooks
// before("deploy", "test");
after('deploy:cleanup', 'deploy:reload-process');
after('deploy:failed', 'deploy:unlock');
