# Unit tests

Requirements: PHP >= 8.2, Composer 2, and the PHP extensions required by
Composer/PHPUnit (including curl, dom, mbstring, pcntl, xml and xmlwriter).

```sh
composer install
composer test
```

Run one group of tests:

```sh
vendor/bin/phpunit --filter RequestTest
```

The suite covers request conversion, response metadata and content delivery,
request state cleaners, container reflection, task dispatch and validation,
Apollo configuration pulls and saves, timer lock ownership, and the HTTP
client's cURL path. Swoole request/response objects are PHPUnit mocks based
on `swoole/ide-helper`; the HTTP tests mock cURL functions with `php-mock`.
The tests do not start Swoole, access external HTTP services or require Redis
or a Laravel application. Native Swoole server/coroutine behavior requires
separate integration tests.

The suite leaves `src` unchanged. Known defects remain recorded as skipped
regression cases: header values containing colons are truncated, and non-POST
form parsing assigns `ParameterBag` to a typed `InputBag` property on newer
Symfony versions. The latter cases still run on versions with an untyped
request property. Skipped cases are reported explicitly by PHPUnit.

For coverage, enable Xdebug or PCOV and run:

```sh
composer test:coverage
```

GitHub Actions runs the suite on PHP 8.2, 8.3 and 8.4.
