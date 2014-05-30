AllegroSitesBundle
=================

[![Build Status](https://secure.travis-ci.org/allegro-project/AllegroSitesBundle.png?branch=master)](http://travis-ci.org/allegro-project/AllegroSitesBundle)

A Symfony2 simple multilang multisite bundle.

## Prerequisites

- [Sonata Admin Bundle](http://sonata-project.org/bundles/admin/master/doc/reference/installation.html)
- [Sonata User Bundle](http://sonata-project.org/bundles/user/master/doc/reference/installation.html)
- [Gregwar Captcha Bundle](#captcha) (optional, for using contact page)
- [Doctrine Fixtures Bundle](#fixtures) (optional, for loading example data)

## Installation

### Download from git

    $ cd MySymfonyProject/src/
    $ git clone https://github.com/allegro-project/AllegroSitesBundle.git Allegro/SitesBundle


### Enable the bundle

Enable the bundle in the kernel (`app/AppKernel.php`):

```php
<?php
// app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Allegro\SitesBundle\AllegroSitesBundle(),
            // ...
        );
    }
```

### Add to assetic in app/config/config.yml
```yml
assetic:
    ...
    bundles:        [ ..., AllegroSitesBundle ]
    ...
```


### Add access route in app/config/routing.yml

```yml
allegro_sites:
    resource: "@AllegroSitesBundle/Resources/config/routing.yml"
    prefix: /pages
```


### Create the entities (in project root dir)
    php app/console doctrine:generate:entities Allegro/SitesBundle/Entity/Site
    php app/console doctrine:generate:entities Allegro/SitesBundle/Entity/Page
    php app/console doctrine:generate:entities Allegro/SitesBundle/Entity/SiteTranslation
    php app/console doctrine:generate:entities Allegro/SitesBundle/Entity/PageTranslation
    php app/console doctrine:schema:update --force


### Configure the bundle

Add to `app/config/config.yml`

```yml
...
allegro_sites:
    format:
        # absolute | relative
        urls: absolute
    emails:
        contact:
            # html | txt
            format: html
...
```


### Update resources

    $ php app/console assets:install web --symlink
    $ php app/console cache:clear


## Adding captcha for contact page<a id="captcha">#</a>

Install the GD module for PHP if not installed

Add the Grewgar Captcha bundle

    composer require gregwar/captcha-bundle:dev-master

Register it

```php
// app/AppKernel.php
public function registerBundles() {
    // ...
    new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
    // ...
}
```

Add the config entry in app/config/config.yml

```yml
gregwar_captcha: ~
```


And add the routing configuration to app/config/routing.yml

```yml
gregwar_captcha_routing:
    resource: "@GregwarCaptchaBundle/Resources/config/routing/routing.yml"
```


## Loading fixtures <a id="fixtures">#</a>

Install the Doctrine Fixtures Bundle if you haven't. Two steps installation:

Add the bundle

    $ composer require doctrine/doctrine-fixtures-bundle:dev-master

And register it

```php
// app/AppKernel.php
public function registerBundles() {
    // ...
    new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
    // ...
}
```

### Loading test data

    $ php app/console doctrine:fixtures:load --fixtures=src/Allegro/SitesBundle/DataFixtures/ (--append | --purge-with-truncate)

A test admin user will be created with username `SiteAdmin` and password `xxxx`


## Translate contact and sitemap page labels

Copy the files `SitesBundle/Resources/translations/messages.en.yml` and `SitesBundle/Resources/translations/validators.en.yml`
rename those files replacing `en` with the lang id you want to add (wich must be used as lang in a created site) and then edit
them, translating the messages. Finally, clear the cache (app/console cache:clear --env=prod --no-debug) to apply the changes.


## Custom templates per site

Any template can be overrided for a site replicating the location structure of the original, inside a custom directory.
If you check the base template directory `Resources/views` you`ll see a directory called `base`, which contains the base frontend templates.

To override a template for a site you need to create a directory inside `Resources/views` named as your site slug prefixed with `tpl_`, so if you have a site with slug `corporatex` you have to create a directory called `tpl_corporatex`.

Inside your site templates directory add / copy and modify a file with the same name and location than the one you want to override, that's all.

E.g.:

To override the main layout (base/layout.html.twig) in your corporatext site create the file tpl_corporatex/layout.html.twig
To override layout used for pages (base/Page/show.html.twig) in your corporatext site create the file tpl_corporatex/Page/show.html.twig

When editing / extending the templates you have to use the twig `allg_template()` extension function to get the templates dynamically, which returns the custom template or the default one as fall down.
the parameter for this extension function is a string with the name of the template with its first extension and preceded with its subdirectory if any.
Remember to must clear the cache to apply any change.

E.g.

```html
<!-- allg_template examples -->

{% extends allg_template('layout.html') %}

{%
    include allg_template('locale_links.html')
    with { 'routes': localeRoutes } only


    include allg_template('Site:map_page.html')
    with { 'pages': site.pages } only
%}
```

There's a controller method which does exactly the same: `getTemplate()`
```php
    return $this->render($this->getTemplate('menu.html'), array(...

    ->setBody($this->renderView(
        $this->getTemplate('Enquiry:contactEmail' . $emailFormat),
        ...
```

## Licence

[Bundle licence file](https://github.com/allegro-project/AllegroSitesBundle/blob/master/Resources/meta/LICENSE)
