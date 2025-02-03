# CONSULTAR CALLBACKS-INTER

## Consultar Callbacks

Retorna o link do webhook.<br>

```php
    require '../../../vendor/autoload.php';

    use Divulgueregional\ApiInterV2\InterBanking;

    $config  = [
        "producao" => 1, // 0 Homo | 1 prod
        "CLIENT_ID" => "",
        "CLIENT_SECRET" => "",
        "CERTIFICADO_CER" => __DIR__ . "/cert.cer",
        "CERTIFICADO_KEY" => __DIR__ . "/cert.key",
        "pass" => ""
    ];
    $sicrediPix = new SicrediPix($config);

    $token = '';//seu token
    try {
        echo "<pre>";
        $chave_pix = '';
        $reponse = $sicrediPix->getWebhook($chave_pix);

        print_r($reponse);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
```
