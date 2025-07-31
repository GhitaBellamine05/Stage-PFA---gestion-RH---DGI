<?php
namespace Deployer;

require 'recipe/common.php';

// Config

set('repository', '');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('ghita')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/DRI stage GHITA BELLAMINE');

// Hooks

after('deploy:failed', 'deploy:unlock');
