Nucleus
=======

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton) [ ![Codeship Status for CodyErekson/Nucleus](https://app.codeship.com/projects/e0dd7b00-e11c-0135-9caa-3a15b47d4b16/status?branch=master)](https://app.codeship.com/projects/266583) ![Build](https://img.shields.io/badge/Build-beta-blue.svg) ![VERSION](https://img.shields.io/badge/Version-v1.0--beta-blue.svg) ![LICENSE](https://img.shields.io/github/license/CodyErekson/Nucleus.svg)


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


### Requirements

- PHP >= 7.1
- Composer
- MariaDB 10.1.30 or equivalent


### Installation

`php composer create-project chillem/nucleus [your-directory-name]`

That command will create a new directory and clone Nucleus into it. Next you need to point a virtual host to the `public/` directory and ensure that `logs/` is writeable by your web server user.

Copy `config/env.dist` to `config/.env` and edit that file with all of your required configuration values.

Next run the initial database migration to create the schema and populate some initial data:

`php vendor/bin/phinx migrate -e development`

If you want to insert the data that I initially use (some users and role associations) run the following:

`php vendor/bin/phinx seed:run -s UserSeeder
php vendor/bin/phinx seed:run -s UserRoleSeeder`
 
Finally, update `composer.json` to reflect your application's details.


### Contact

Email: cody.erekson@gmail.com