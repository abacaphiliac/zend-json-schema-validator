[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/abacaphiliac/zend-json-schema-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/abacaphiliac/zend-json-schema-validator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/abacaphiliac/zend-json-schema-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/abacaphiliac/zend-json-schema-validator/?branch=master)
[![Build Status](https://travis-ci.org/abacaphiliac/zend-json-schema-validator.svg?branch=master)](https://travis-ci.org/abacaphiliac/zend-json-schema-validator)

# abacaphiliac/zend-json-schema-validator
A ZF2 validator for justinrainbow/json-schema.

# Installation
```bash
composer require abacaphiliac/zend-json-schema-validator
```

# Usage
Use it inline:
```
$validator = new \Abacaphiliac\Zend\Validator\JsonSchema(array(
  'file' => '/path/to/your/schema.json',
));
if (!$validator->isValid('{"Foo":"Bar"}')) {
    $validationMessages = $validator->getMessages();
}
```

Hook it up to an Apigility input-filter-spec:
```
return array(
    'input_filter_specs' => array(
        'YourApi\\V1\\Rest\\YourService\\Validator' => array(
            array(
                'name' => 'YourJsonParam',
                'validators' => array(
                    array(
                        'name' => 'IntegrationConfiguration\\Validator\\JsonSchema',
                        'options' => array(
                            'file' => dirname(dirname(dirname(__DIR__))) . '/config/json-schema/IntegrationConfiguration/V1/Rest/OutboundDocumentation/configurations-config.json',
                        ),
                    ),
                )
            ),
        ),
    ),
);
```

# Dependencies
See [composer.json](composer.json).

## Contributing
```
composer update && vendor/bin/phing
```

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
