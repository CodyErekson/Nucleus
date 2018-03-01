Nucleus
=======

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton) [ ![Codeship Status for CodyErekson/Nucleus](https://app.codeship.com/projects/e0dd7b00-e11c-0135-9caa-3a15b47d4b16/status?branch=master)](https://app.codeship.com/projects/266583) ![VERSION](https://img.shields.io/badge/Version-1.1.0-blue.svg) ![LICENSE](https://img.shields.io/github/license/CodyErekson/Nucleus.svg)


A Slim based PHP application scaffolding.

This is more than a framework, thus the word "scaffolding." Nucleus is a fully functional out-of-the-box web application. All you need to do is build your environment, define values in config/.env, run the database migrations and seeds, then your app is ready to go!

The major components and relevant documentation are below:
- Slim (framework) https://www.slimframework.com/
- Twig (templates) https://twig.symfony.com/
- Phinx (database migrations) https://phinx.org/
- Eloquent (ORM) https://laravel.com/docs/5.5/eloquent
- Monolog (logging) https://github.com/Seldaek/monolog
- Respect Validation (validation) https://github.com/Respect/Validation
- Whoops (error reporting) https://github.com/filp/whoops
- League\Event (event emitter) http://event.thephpleague.com/2.0/
- League\CLImate (CLI output formatting) http://climate.thephpleague.com/
- Swiftmailer (email) https://swiftmailer.symfony.com/


### Requirements

- PHP >= 7.1
- Composer
- NPM
- MariaDB 10.1.30 or equivalent


### Installation

`php composer create-project chillem/nucleus [your-directory-name]`

That command will create a new directory and clone Nucleus into it. Next you need to point a virtual host to the `public/` directory and ensure that `logs/` is writeable by your web server user.

Copy `config/env.dist` to `config/.env` and edit that file with all of your required configuration values.

Next run the initial database migration to create the schema and populate some initial data:

`php vendor/bin/phinx migrate -e development`

If you want to insert the data that I initially use (some users and role associations) run the following:

`php vendor/bin/phinx seed:run -s UserSeeder`

`php vendor/bin/phinx seed:run -s UserRoleSeeder`

`php vendor/bin/phinx seed:run -s GlobalSettings`
 
Finally, update `composer.json` to reflect your application's details.


### CLI Utility

v1.0.1 introduces a new semi-experimental feature -- a CLI command runner.
The first functionality provider is that of a wrapper around those CLI scripts provided by other packages.

To run Phinx commands:

`bin/nucleate db migrate -e development`

To run PHPUnit: 

`bin/nucleate test`

To run PHPCS:

`bin/nucleate cs -n --standard=PSR1,PSR2 src/`

`bin/nucleate csfix -n --standard=PSR1,PSR2 src/`

Anything else is passed directly through to the command runner. Commands are defined in `src/Helpers/Commands` and 
extend the BaseCommand class.

More information about commands can be found here: https://github.com/adrianfalleiro/slim-cli-runner 
(Note: I have chosen to use the nomenclature "command" rather than "task" for the sake of consistency.)

### Contact

Email: cody.erekson@gmail.com