# TOKEN-SICREDI

## Orientações Iniciais

O token não precisa ser informado pois gera na hora da requisição dos endpoints, mas se quiser guardar pode setar o token.

## Gerar o token

Gerar o token.

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
    $tokenPix = $sicrediPix->gerarTokenPix();
    echo "<pre>";
    print_r($tokenPix);
```

Set token pix

```php
    $token = '';
    $sicrediPix->setToken($token);
```

Set token boleto

```php
    $token = '';
    $sicredi->setToken($token);
```
