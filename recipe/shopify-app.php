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

task('vendor:publish', function ()
{
    run('rm -rf {{release_path}}/public/vendor/crm');
    run('cd {{release_path}} && php artisan vendor:publish --provider="By247APPS\ShopifyAppCrm\ShopifyAppCrmProvider" --tag=shopify-app-crm-public-assets');
});

task('wsdl:generate', function ()
{
    run('cd {{release_path}} && php artisan wsdl:generate');
});

task('setup:crm', function ()
{
    run('mkdir {{release_path}}/crm');
    run('cd {{release_path}}/crm && git clone https://github.com/247apps-de/shopify-app-crm.git -b dhl .');
});

task('setup:laravel-shopify', function ()
{
    run('mkdir {{release_path}}/laravel-shopify');
    run('cd {{release_path}}/laravel-shopify && git clone --branch master https://github.com/StefanNeuser/laravel-shopify.git .');

    run('mkdir {{release_path}}/basic-shopify-api');
    run('cd {{release_path}}/basic-shopify-api && git clone --branch master https://github.com/StefanNeuser/basic-shopify-api.git .');
});
