# bpost-addresses
A php wrapper for Bpost addresses validation API

*bpost* ([Belgian Post Group](https://www.bpost.be/)) provides an API to validate belgian address : <https://www.bpost.be/site/en/webservice-address>.

The goal of this tool is to help querying the *bpost* API easily with PHP.

## Install

The tool only requires **PHP 7.0+** and **guzzlehttp/guzzle**.

Use it via composer + packagist: https://packagist.org/packages/ici-brussels/bpost-addresses

## Usage

```php
$bpost = new Bpost_Address_Validation();

// Find relevant address according to input
$bpost->getAddress_Structurated("Rue de la loix", "15", null, 1000, "Bruxelles");

// get array with validated address
$result = $bpost->getStructuredAddress();
print_r($result);
/*
Array
(
    [StreetName] => Rue De La Loi
    [StreetNumber] => 15
    [BoxNumber] => 
    [PostalCode] => 1040
    [MunicipalityName] => Bruxelles
    [CountryName] => Belgique
)
*/

// get array with latitude/longitude
$result = $bpost->getGeographicalLocation();
print_r($result);
/*
Array
(
    [lat] => 50.845465
    [lon] => 4.369107
)
*/
```

## Credits ##
- Created by Bruno Veyckemans ([ici Bruxelles](https://ici.brussels/)). All comments and suggestions welcome !
- Inspired by ([geo6/bpost-batch-validation](https://github.com/geo6/bpost-batch-validation))
- Thanks to Xavier Querriau @ bpost for the API
