<?php
namespace Deployer;

set('user', 'deployer');
set('identityFile', file_exists('~/.ssh/id_rsa.pub') ? '~/.ssh/id_rsa.pub' : '~/.ssh/id_ed25519.pub');

before('deploy', function ()
{
    writeln("<fg=red;options=bold>" . get('deploy_path') . "</>");
});

// LastInFirstOut - LiFo
before('deploy:symlink', 'yarn');
before('deploy:symlink', 'artisan:db:seed');
before('deploy:symlink', 'artisan:migrate');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// damit q neuen commands started statt auf dem alten symlink
after('deploy:success', 'artisan:queue:restart');

task('yarn', function ()
{
    run('cd {{release_path}} && yarn install');
    run('cd {{release_path}} && npm run prod');
});

task('wsdl:generate', function ()
{
    run('cd {{release_path}} && php artisan wsdl:generate');
});

task('setup:laravel-shopify', function ()
{
    run('mkdir {{release_path}}/laravel-shopify');
    run('cd {{release_path}}/laravel-shopify && git clone --branch master https://github.com/StefanNeuser/laravel-shopify.git .');

    run('mkdir {{release_path}}/basic-shopify-api');
    run('cd {{release_path}}/basic-shopify-api && git clone --branch master https://github.com/StefanNeuser/basic-shopify-api.git .');
});
