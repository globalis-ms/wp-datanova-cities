# wp-datanova-cities

Datanova laposte_hexasmal wrapper for WordPress

## Available functions

- getCities($postcode)

Examples:

```php
<?php

use \Globalis\WP\Datanova\Cities;

Cities::instance()->getCities('01090'));

// Array
// (
//     [0] => Array
//         (
//             [ville] => FERNEY VOLTAIRE
//             [code_postal] => 01210
//             [code_insee] => 01160
//         )
//
//     [1] => Array
//         (
//             [ville] => ORNEX
//             [code_postal] => 01210
//             [code_insee] => 01281
//         )
//
//     [2] => Array
//         (
//             [ville] => VERSONNEX
//             [code_postal] => 01210
//             [code_insee] => 01435
//         )
//
// )

Cities::instance()->getCities('ABCDE'));

// Array
// (
// )
```

- getCitiesNames($postcode)

Examples:

```php
<?php

use \Globalis\WP\Datanova\Cities;

Cities::instance()->getCitiesNames('01090'));

// Array
// (
//     [0] => FERNEY VOLTAIRE
//     [1] => ORNEX
//     [2] => VERSONNEX
// )

Cities::instance()->getCitiesNames('ABCDE'));

// Array
// (
// )
```

- isCityValid($name, $postcode)

Examples:

```php
<?php

use \Globalis\WP\Datanova\Cities;

Cities::instance()->isCityValid('PARIS 15', '75014')

// (boolean) false

Cities::instance()->isCityValid('PARIS 15', '75015')

// (boolean) true

Cities::instance()->isCityValid('FOO', 'BAR')

// (boolean) false

```

## AJAX

Same functions are available by calling WordPress admin-ajax following actions:

- `datanova_get_cities` (POST params: postcode)
- `datanova_get_cities_names` (POST params: postcode)
- `datanova_is_city_valid` (POST params: name, postcode)
