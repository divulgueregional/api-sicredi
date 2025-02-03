# CRIAR WEBHOOK-PIX-SICREDI

## Como criar o webhook

Crie um arquivo para receber as notificações, após informe a url desse arquivo para criar o webhook

## criando o webhook

Você só pode ter 1 webhook

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
    $webhookUrl = 'https://seu_dominio/api/sicredi/webhook.php';
    try {
        $reponse = $sicrediPix->updateWebhook($webhookUrl, $chave_pix);

        echo "<pre>";
        print_r($reponse);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
```
