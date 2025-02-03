# DOCUMENTAÇÃO API BANCO SICREDI

## Introdução

Siga a docmentação para poder utilizar a API do SICREDI

## testar conexão com a api

Testar a conexão com a biblioteca que acessar os endpoints da api pix e boleto sicredi.<br>

PIX

```php
    require_once '../../../vendor/autoload.php';
    use Divulgueregional\ApiSicredi\SicrediPix;

    $config  = [
        "producao" => 0, // 0 Homo | 1 Prod
        "CLIENT_ID" => "",
        "CLIENT_SECRET" => "",
        "CERTIFICADO_CER" => __DIR__ . "/cert.cer",
        "CERTIFICADO_KEY" => __DIR__ . "/cert.key",
        "pass" => ""
    ];
    $sicrediPix = new SicrediPix($config);

    $response = $sicrediPix->teste();
    echo "<pre>";
    print_r($response);
```

BOLETO

```php
    require_once '../../../vendor/autoload.php';
    use Divulgueregional\ApiSicredi\SicrediBoletos;

    $config = [
        'sandbox' => true, // false = produção, true = sandbox
        'x-api-key' => '',
        'username' => '123456789',
        'password' => 'teste123',
        'cooperativa' => '6789',
        'posto' => '03',
        'codigoBeneficiario' => '12345',
        'client_id' => '',
    ];
    $sicredi = new SicrediBoletos($config);

    $response = $sicredi->teste();
    echo "<pre>";
    print_r($response);
```
