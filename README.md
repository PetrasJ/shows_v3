https://shows.botai.eu

sample user: test@test.com pass: test 

## Episode Calendar

Difference with other calendars:

* comment on episode
* offset to shows independently
* add show multiple times

Shows & Episodes source: http://www.tvmaze.com/

Deployment:

* cp .env.production .env (configure database, secret)
* composer install
* php bin/console doctrine:database:create
* php bin/console doctrine:schema:update --force
* npm run-script build

Commands:

* php bin/console import-shows
    - initial, imports shows
* php bin/console update-shows
    - daily, updates user shows fully (episodes, images)
* php bin/console update-all-shows
    - weekly, updates all shows partially (-episodes, -images)
