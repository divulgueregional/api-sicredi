# CRIAR WEBHOOK-PIX-SICREDI

## Como criar o webhook

Crie um arquivo para receber as notificações, após informe a url desse arquivo para criar o webhook

## criando o webhook

Você só pode ter 1 webhook

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
        $webhookUrl = 'https://seu_dominio/api/sicredi/webhook.php';
        $chave_pix = '';
        $reponse = $sicrediPix->updateWebhook($webhookUrl, $chave_pix);

        print_r($reponse);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
```
