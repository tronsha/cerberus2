Cerberus
========

Cerberus is an IRC bot written for PHP

## Install

### Use Composer

If you don't have Composer yet, download it following the instructions on [getcomposer.org][5] or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new project:

    php composer.phar create-project tronsha/cerberus --stability=dev
    
The Composer installation is without Webchat Frontend

### Clone from Github

Clone the projects `Cerberus` form Github:

    git clone https://github.com/tronsha/cerberus.git

Change to the cerberus directory:

    cd cerberus

Download the Composer:

    curl -s http://getcomposer.org/installer | php

Then, use the `install` command:

    php composer.phar install
    
## Require

* PHP 7.0 or greater.
* A Database: MySQL
 
## Info

* [RFC 1459][13]
* [RFC 2810][14]

## Libraries

* [Doctrine Database Abstraction Layer 2.5][6]
* [Symfony Console Component 3.4][7]
* [Symfony Translation Component 3.4][8]
* [Symfony Filesystem Component 3.4][9]

## Creator

**Stefan Hüsges**

:computer: [Homepage][1]

:octocat: [GitHub][2]

## Thanks

**Daniel Basten**

:octocat: [GitHub][3]

## License
[![GNU General Public License](http://www.gnu.org/graphics/gplv3-127x51.png)][4]

## Links

* [Comparison of Internet Relay Chat bots][15]

[1]: http://www.mpcx.net
[2]: https://github.com/tronsha
[3]: https://github.com/axhm3a
[4]: http://www.gnu.org/licenses/gpl-3.0
[5]: http://getcomposer.org/
[6]: http://www.doctrine-project.org/projects/dbal.html
[7]: http://symfony.com/components/Console
[8]: http://symfony.com/components/Translation
[9]: http://symfony.com/components/Translation
[10]: https://phpunit.de/
[11]: http://cs.sensiolabs.org/
[12]: https://github.com/symfony/symfony/pull/13607
[13]: https://tools.ietf.org/html/rfc1459
[14]: https://tools.ietf.org/html/rfc2810
[15]: https://en.wikipedia.org/wiki/Comparison_of_Internet_Relay_Chat_bots
