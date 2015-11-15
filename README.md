# turbo
Turbo - Speeds up your development process.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zepi/turbo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zepi/turbo/?branch=master)
[![Build Status](https://travis-ci.org/zepi/turbo.svg?branch=master)](https://travis-ci.org/zepi/turbo)
[![codecov.io](https://codecov.io/github/zepi/turbo/coverage.svg?branch=master)](https://codecov.io/github/zepi/turbo?branch=master)

# Installation
1. Clone or download the git repository [zepi/turbo-boilerplate](https://github.com/zepi/turbo-boilerplate)
2. Execute composer `composer update`
3. Execute turbo `./turbo install`

# Features
1. Modules: Everything is in a module. You can add your own modules or use modules from someone else.
2. Events: Turbo knowns no controllers or pages, there are only events. You can use events to filter content, to respond to web requests or to execute cronjobs.
3. Routes: Define routes in the module configuration file in php. The route redirects the request to the target event.
4. General: The module name is based on a namespace, like {Vendor}\{Project}. There will be no naming conflict because only you are using the vendor part.
5. Turbo Base offers a good base to develop your own application. There is already a fully functional access control system with users, groups, access levels any many more. Clone it from Github ([zepi/turbo-base](https://github.com/zepi/turbo-base)) or use it with composer `composer require zepi/turbo-base`
