Sanitize
========
Sanitizing library

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Add

```
"ops-ril/sanitize": "dev-master"
```

to the require section and

```
{
    "type": "vcs",
    "url": "https://github.com/ops-ril/sanitize.git"
}
```

to the repositories section of your `composer.json` file.


Usage
-----
**Html2Text**

```php
use OpsRil\Sanitize\Html2Text;
$html2text = new Html2Text('<p>Lorem ipsum<p>');
$plainText = $html2text->getText();
```

History
-------

This library started life on the blog of Jon Abernathy http://www.chuggnutt.com/html2text

A number of projects picked up the library and started using it - among those was RoundCube mail. They made a number of updates to it over time to suit their webmail client.

This is a fork of [Html2Text](https://github.com/mtibben/html2text).

We created our own version since https://github.com/mtibben/html2text
could not be configured to work like we needed it. We also tried https://github.com/voku/html2text
which can be configured, but it stripped all uppercase tags.
After that we decided to derive a html2text implementation
that serves exactly our needs.
