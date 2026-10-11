# Contributing

Fork the repository and create a branch for your change. Include a regression test
for behavior changes and update the affected documentation.

## PHP checks

From the repository root:

```bash
composer install
composer cs-check
composer test
```

Use `composer cs-fix` to apply coding standard fixes. To run the static analysis
used by CI:

```bash
composer stan-setup
composer stan
```

`composer test-coverage` writes reports under `tmp/coverage` and needs a PHP
coverage driver. GitHub Actions checks the supported PHP and database combinations;
see the repository's CI workflow for the current matrix.

## Documentation

From the `docs` directory:

```bash
npm ci
npm run docs:dev
```

Open the local URL printed by VitePress. Before submitting documentation changes,
check the production build:

```bash
npm run docs:build
npm run docs:preview
```

CI builds the site for pull requests. After changes reach `master`, the deployment
workflow publishes it to GitHub Pages. Repository settings must use GitHub Actions
as the Pages source.

See the [CakePHP coding conventions](https://book.cakephp.org/5/en/contributing/cakephp-coding-conventions.html)
and [PSR-2-R coding standards](https://github.com/php-fig-rectified/fig-rectified-standards/)
for the PHP style used in this repository.
