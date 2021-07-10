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
$html2text = new Html2Text();
$plainText = $html2text->convert('<p>Lorem ipsum<p>');
```

History
-------

This library started life on the blog of Jon Abernathy http://www.chuggnutt.com/html2text

A number of projects picked up the library and started using it - among those was RoundCube mail. They made a number of updates to it over time to suit their webmail client.

This is a fork of [Html2Text](https://github.com/mtibben/html2text).
