# PIX-SICREDI

## Orientações Iniciais

Para iniciar o processo de integração da API Pix, o associado Sicredi deve contratar o produto com seu Gerente de Conta e optar pela opção Outros Provedores (Provedores Parceiros são atendidos pelo Internet Banking do Sicredi).

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
