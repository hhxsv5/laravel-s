# Unit tests

The test suite requires PHP 7.4 or later and Composer 2, including the PHP
extensions required by PHPUnit (curl, dom, mbstring, pcntl, xml, xmlwriter).
The library's PHP runtime requirement is unchanged; the test dependencies
require PHP 7.4.

```sh
composer install
composer test
```

Run a single group with `vendor/bin/phpunit --filter RequestTest`. With Xdebug
or PCOV enabled, run `composer test:coverage` for HTML and Clover reports.

The tests cover HTTP request and response conversion, cleanup between requests,
container reflection, task dispatch, Apollo configuration, timer locks, and
the HTTP client's cURL path. Without Swoole, test-only HTTP classes provide mock
targets. The suite does not require Redis or an external HTTP service. Real
Swoole server and coroutine behavior require separate integration tests.

The header-value regression test is skipped when the existing parser truncates
values at a colon. On newer Symfony releases the non-POST form tests are also
skipped when the request property is typed as InputBag. These known issues are
reported by PHPUnit; the source remains unchanged.
