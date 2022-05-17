# simplesamlphp-module-metaproc
SimpleSAMLphp authproc filters for processing SAML metadata

## IdpTag2Attribute

This filter allows to copy any tags found in the IdP metadata to the
target attribute. If the attribute already exists, the values added
will be merged. If you instead want to replace the existing
attribute, you may add the '%replace' option.

#### Example configuration

```php
'authproc' => [
    ...
    '101' => [
        'class' => 'metaproc:IdpTag2Attribute',
        'targetAttribute' => 'schacHomeOrganisationType', // default
    ],
],
```

## Compatibility matrix

This table matches the module version with the supported SimpleSAMLphp version.

| Module |  SimpleSAMLphp |
|:------:|:--------------:|
| v1.0   | v1.17+          |

## License

Licensed under the Apache 2.0 license, for details see `LICENSE`.