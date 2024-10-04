# Explanation
## Thinking ahead
The submitted task looks like a plugin.
I must first initialize a development environment for a WordPress plugin. The [@wordpress/env](https://www.npmjs.com/package/@wordpress/env) node library is quite a handy tool for such a task. **Docker required!**

`npm install --save-dev @wordpress/env`

Next, I create a `.wp-env.json` file to specify `wp-env` I'm working on a plugin:

```
{
    ...
    "plugins": [ "." ],
    ...
}
```

`wp-env` conveniently comes with the PHP package manager `composer`. Another great tool for OOP development with its autoloader and the ease of installation of PHPUnit and PHPCS as requested by this technical assessment.

For convenience, I add some scripts in the `package.json` file to execute `wp-env` commands.

For example, I am now able to use composer through the `wp-env` docker container with just :

`npm run wp-env composer`

Take a look at the `package.json` for additional scripts.

Next, I install composer dependencies for PHPUnit and the WordPress polyfills for integration tests, PHPCS with Wordpress Coding Standards and the autoloader compatible with those standards. Take a look at the `composer.json` file for details beyond the scope of this explanation.

Now I can run tests and check coding standards. The cherry on the cake would be to run those on a push to the `main` or `develop` branch. You can check the `.github/workflows/ci.yml` I made to achieve that, inspired by the Gutenberg repository.

In the `tests/test-coolkidsplugin.php` test file, I check that the plugins activates properly with no errors.

* [x] Development environment
* [x] Plugin Skeleton
* [x] Check Coding Standards
* [x] Run Tests
* [x] Github Actions

Now we can talk!

## User Story 1: Register as a Cool Kid
An anonymous user must be able to register to the website provided only an email address, then being attributed the role of Cool Kid and a set of user metadata: Firstname, Lastname and Country.

### Add new roles
Before managing the registration, I need the new user roles: Cool Kid, Cooler Kid, Coolest Kid.

The new roles are defined in the `src/class-coolroles.php:CoolRoles` class. The class contains two static methods `activate()` and `deactivate()` being called respectively on plugin activation hook and deactivation hook. This way, the roles are added only on plugin activation, then remove on plugin deactivation when the site isn't cool anymore.

The `tests/test-coolroles.php` test case checks that the roles are properly created on activation and remove on deactivation.

Now that the new roles are created, I have to manage new cool kids registration. The `src/class-registration.php:Registration` class handles it. It registers a new shortcode `[cool_kids_registration_form]` that will display a registration form with an email field and nonce field.

The `is_valid_form_submission()` method checks if a form is properly submitted to handle new cool kid registration.

The user creation is handled by the `process_registration()` method that calls the `get_random_user_data()` to generate user meta data from randomuser.me API.

The `get_random_user_data()` handles various API failures then fall back to the `get_fallback_user_data()` that generates data in a cooler way.

The `tests/test-registration` test case checks all steps in isolation. I had to use the `ReflectionClass` to access private methods independently and simulate successes and failures.

## User Story 2: Login and see self data
I can register as a cool kid. Now I must log in to see how cool I am.

Here is the case : I go to a profile page to see my data. If I'm not logged in, I get a login form to do so. Otherwise, I get my data.

The `src/class-coolkiddata.php:CoolKidData` covers this feature with a shortcode that manages user data and login form depending the situation.

The login form is not tested here as it is a bit out of the scope of this technical assessment, but it would be as the registration form tests.