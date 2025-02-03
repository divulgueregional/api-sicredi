# PIX-SICREDI

## Orientações Iniciais

Para iniciar o processo de integração da API Pix, o associado Sicredi deve contratar o produto com seu Gerente de Conta e optar pela opção Outros Provedores (Provedores Parceiros são atendidos pelo Internet Banking do Sicredi).

## gerar certificado

- Gerar certificado: https://developer.sicredi.com.br/api-portal/pt-br/certificates

## Gerar o Pix

Gerar o pix.

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

    $sicrediPix = new SicrediPix($config); // true ativa sendbox
    $cobranca  = [
        "calendario" => [
            "expiracao" => 3600,
            // "dataDeVencimento" => "2040-04-01",
            // "validadeAposVencimento" => 1
        ],
        "valor" => [
            "original" => "10.00",
            "modalidadeAlteracao" => 1,
        ],
        "chave" => "",// chave pix
        "solicitacaoPagador" => "Serviço realizado.",
    ];

    $reponse = $sicrediPix->criarCobranca($cobranca);
    echo "<pre>";
    print_r($reponse);
```

CONSULTAR PIX

```php
    $txid = '5bb4ff33fe5c48eea80df3a0bcefb298';

    $reponse = $sicrediPix->dadosDeCobranca($txid);
    echo "<pre>";
    print_r($response);
```
