#!/bin/bash
# Print org id/name so we don't reset the wrong client.
cd /var/www/connectpulse
php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Models\Organization::query()->orderBy("id")->get(["id","company_name","email"])->each(function ($o) {
    echo "#{$o->id}\t{$o->company_name}\t{$o->email}\n";
});
'
