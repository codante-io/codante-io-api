<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'git@github.com:codante-io/codante-io-api.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

// host('159.89.44.90')
//     ->set('remote_user', 'robertotcestari')
//     ->set('deploy_path', '/var/www/codante-io-api');


host('216.238.108.237')
    ->set('remote_user', 'robertotcestari')
    ->set('deploy_path', '/var/www/codante-io-api');
// Hooks

after('deploy:failed', 'deploy:unlock');
