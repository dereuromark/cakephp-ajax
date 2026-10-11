---
layout: home
hero:
  name: CakePHP Ajax
  text: JSON responses and HTML fragments
  tagline: Handle Ajax requests, redirects, and flash messages in CakePHP controllers.
  image:
    src: /logo.svg
    alt: CakePHP Ajax request and response arrows
  actions:
    - theme: brand
      text: Installation
      link: /guide/installation
    - theme: alt
      text: Getting started
      link: /guide/getting-started
    - theme: alt
      text: GitHub
      link: https://github.com/dereuromark/cakephp-ajax
features:
  - title: JSON and HTML responses
    details: Render HTML fragments or serialize view variables from your controller actions.
  - title: Redirects and flash messages
    details: Return controller redirect details and flash messages in the Ajax response.
  - title: Browser fetch support
    details: Configure JSON request detection and follow an example using the browser fetch API.
---

## Start with one action

[Install the plugin](guide/installation.md), then follow the
[Getting started example](guide/getting-started.md) to call a controller action
with the browser's `fetch()` API.

## Choose the response

[Ajax Component](reference/component.md) selects Ajax handling for configured
actions and request detectors. It can include flash messages and turn controller
redirects into JSON responses.

[Ajax View](reference/view.md) renders HTML fragments or serializes view variables.
Read the [overview](guide/index.md) to decide when to use the plugin or CakePHP's
core JsonView.
