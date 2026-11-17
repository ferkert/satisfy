# Satisfy [![Tests](https://github.com/ferkert/satisfy/actions/workflows/phpunit.yaml/badge.svg)](https://github.com/ferkert/satisfy/actions/workflows/phpunit.yaml)

[Satis Composer repository manager](http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md) with a simple web UI.

<p>
  <img src="http://project-satisfy.github.io/satisfy/images/list.png" width="280" height="235" alt="Satis: list composer repositories"/>
  <img src="http://project-satisfy.github.io/satisfy/images/create.png" width="280" height="235" alt="Satis: add a new composer repository"/>
  <img src="http://project-satisfy.github.io/satisfy/images/update.png" width="280" height="235" alt="Satis: update an existing composer repository"/>
</p>

## Introduction

Satisfy provides:

* a Web UI: A CRUD to manage your satis configuration file
* a simple security layer with a login form
* a webhook endpoints for the most popular version control systems
* Satis itself

Satisfy ease your satis configuration management. It provides a simple web UI over Satis to avoid a hand-editing of the satis.json configuration file. All repositories from a composer.lock file can also be imported via upload.


## How it works?

Basically, it just reads/writes the satis.json file and provides a web CRUD.

* On each HTTP request, the satis.json is loaded.
* If a write occurs in this file, a backup is made in var/satis/

## Installation

### Composer

* Clone project `git clone https://github.com/ferkert/satisfy.git`
* Download composer `wget http://getcomposer.org/composer.phar`
* Install `php composer.phar install -n`

### Satis configuration

* Either define your [default/existing satis configuration](http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md)
* or use interactice satis CLI tool `./bin/satis init`
* or submit form at /admin/configuration

By default, the `satis.json` file is expected at the project root, but You can set another path under the parameter `satis_filename`.

### Application configuration

* Customize `config/parameters.yml` according to your needs. It is created from `config/parameters.yml.dist` on `composer install` and overrides the defaults from `config/services.yaml`.

### Security

You can restrict the access setting `admin.auth` parameter (in `config/parameters.yml`) to `true`. Set authorized users in `admin.users` config array. Passwords are stored in plain text:

```yaml
parameters:
    admin.auth: true
    admin.users:
        admin:
            password: change-me
            roles: ROLE_ADMIN
```

## Run Satisfy

Create a web server pointing to the `public` directory. Browse to »/admin/« to manage the `satis.json`. To serve the `package.json` it is required to run Satis first.

### Satis packages definition generation

Since Satisfy does only manages the Satis configuration file, it is necessary to build the package definitions using Satis.

A bin shortcut to Satis is already included in Satisfy, so run the following command to generate the files in the web folder.

 ```
./bin/satis build
```

### Automatically build a single package using WebHooks

For example, you can trigger a package generation for your BitBucket project by setting up a BitBucket webhook to connect back to [your-satis-url]/webhook/bitbucket every time you do a code push. This is more efficient than doing a full build and avoids you having to run full builds on a frequent schedule or logging in to the admin interface just to force a build.

## Frontend

The admin interface uses [Bootstrap 6](https://getbootstrap.com/) (CSS only) and a small dependency-free script, `public/js/app.js`. All files are served from `public/`; the interface loads nothing from a CDN or any other external host. Icons are inline SVGs from [Bootstrap Icons](https://icons.getbootstrap.com/) in `templates/_icons.html.twig`.

Bootstrap is a pinned npm dev dependency. The copied files in `public/css/` are committed, so running Satisfy does not need Node.js. To update Bootstrap, change the version in `package.json`, then copy the new files and commit them:

```
npm install
npm run assets
```

Bootstrap 6 relies on recent CSS features (cascade layers, `light-dark()`, relative colours), so the interface needs a current browser. It follows the system light or dark mode.

## Authors

* Ludovic Fleury - <ludo.fleury@gmail.com> - <http://twitter.com/ludofleury>
* Julius Beckmann - <satisfy@h4cc.de> - <https://twitter.com/h4cc>
* Ramūnas Dronga - <satisfy@ramuno.lt>
* Vitalis Ferkert - <info@devvi.de>

## License

Satisfy is licensed under the MIT License - see the LICENSE file for details

