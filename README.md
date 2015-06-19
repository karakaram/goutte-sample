Goutte Sample
===========

A sample project to test web application with Goutte and PHPUnit

## Installation

Starts working on the project, the parameters.yml file must be created by using the parameters.yml.dist as a template.

```bash
$ cp app/config/parameters.yml.dist app/config/parameters.yml
```

Then edit parameters.yml

```yaml
database:
    driver: "pdo_mysql"
    host: "localhost"
    user: "root"
    password: "pa$$w0rd"
    dbname: "database-name"
    charset: "utf8"
facebook:
    email: "hoge@example.com"
    password: "pa$$w0rd"
```