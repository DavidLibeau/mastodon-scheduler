/ [Mastodon tools](https://github.com/DavidLibeau/mastodon-tools) / Mastodon scheduler

# Mastodon Scheduler

:arrow_right: [More info](https://github.com/DavidLibeau/mastodon-tools/tree/master/scheduler)


## Install

1. Clone this repository
2. `composer install`
3. Fill the database information (or in /app/config/parameters.yml)
4. Setup your database server (Create an empty database)
5. `php bin/console doctrine:schema:update --force`
6. `php bin/console server:run` or `php bin/console server:start`
