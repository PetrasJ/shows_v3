https://shows.botai.eu

sample user: test pass: test 

## Episode Calendar

Difference with other calendars:

* comment on episode
* offset to shows independently
* add show multiple times

Shows & Episodes source: http://www.tvmaze.com/

Deployment:

* composer install
* configure .env, .env.dev
* php bin/console doctrine:database:create
* php bin/console make:migration
* php bin/console doctrine:migrations:migrate
* npm run-script build

Commands:

* php bin/console import-shows
* php bin/console update-shows
* php bin/console update-all-shows
