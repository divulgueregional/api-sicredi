# EXCLUIR WEBHOOK-INTER

## Excluindo o webhook cadastrado

```php
    require '../../../vendor/autoload.php';

    use Divulgueregional\ApiInterV2\InterBanking;

    $config  = [
        "producao" => 0, // 0 Homo | 1 prod
        "CLIENT_ID" => "",
        "CLIENT_SECRET" => "",
        "CERTIFICADO_CER" => __DIR__ . "/cert.cer",
        "CERTIFICADO_KEY" => __DIR__ . "/cert.key",
        "pass" => ""
    ];
    $sicrediPix = new SicrediPix($config);

    $chave_pix = '';
    try {
        $reponse = $reponse = $sicrediPix->deleteWebhook($chave_pix);

        echo "<pre>";
        print_r($reponse);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
```
