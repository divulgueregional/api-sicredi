# DOCUMENTAÇÃO API BANCO SICREDI

## Introdução

Siga a docmentação para poder utilizar a API do SICREDI

## testar conexão com a api

Testar a conexão com a biblioteca que acessar os endpoints da api pix e boleto sicredi.

```php
    require_once '../../../vendor/autoload.php';
    use Divulgueregional\ApiSicredi\SicrediPix;

    $response = $sicrediPix->teste();
    echo "<pre>";
    print_r($response);
```
