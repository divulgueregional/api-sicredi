# BOLETO-SICREDI

## Orientações Iniciais

Para iniciar o processo de integração da API Boleto, o associado Sicredi deve contratar o produto com seu Gerente de Conta e optar pela opção Outros Provedores (Provedores Parceiros são atendidos pelo Internet Banking do Sicredi).

## Processo

- Cadastre-se no Portal do Desenvolvedor do Sicredi: https://developer.sicredi.com.br/api-portal/pt-br
- Login: https://developer.sicredi.com.br/api-portal/pt-br/user/login
- Criar App: Meus Apps. Após clique em cadastrar novo app. Selecione: <br>
  OAuth 2.0 <br>
  Open API - Cobranca - Parceiros 1.0.0 Serviços de cobranca (Boletos, webhook de contrato e relatórios)<br>
  após no botão Registrar.
- Aba um chamado. <br>
  Selecione: Api Cobrança Boletos.<br>
  Motivo: Selecionar Access Token. <br>
  Assunto: Access token produção. <br>
  Ambiente: produção<br>
  Clique em Enviar.<br>
- Username: utilizando o Beneficiário + Cooperativa<br>
- password: que foi gerada no Internet Banking

## Gerar o Boleto

Gerar o boleto.

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

    $sicredi = new SicrediBoletos($config); // true ativa sendbox

    $dadosBoleto = [
        "beneficiarioFinal" => [
            "cep" => 91250000,
            "cidade" => "PORTO ALEGRE",
            "documento" => "65613259585",
            "logradouro" => "RUA DOUTOR VARGAS NETO 180",
            "nome" => "TESTE FAKE",
            "numeroEndereco" => 119,
            "tipoPessoa" => "PESSOA_FISICA",
            "uf" => "RS"
        ],
        "codigoBeneficiario" => "12345",
        "idTituloEmpresa" => '', // Id de controle do beneficiário. Semelhante ao “seuNumero” que permite mais caracteres.
        "dataVencimento" => "2024-12-15",
        "diasProtestoAuto" => '', // quantidade de dias, após o vencimento, em que será realizado o protesto automático do boleto
        "diasNegativacaoAuto" => '', // Quantidade de dias, após o vencimento, em que o boleto será negativado automaticamente
        "validadeAposVencimento" => '', // Quantidade de dias que o QRCode continuará válido após o vencimento, caso seja um boleto híbrido.
        "tipoDesconto" => 'PERCENTUAL', // Tipo de desconto podendo ser: A - VALOR  B - PERCENTUAL
        "valorDesconto1" => '', // Valor de desconto 1
        "dataDesconto1" => '', // YYYY-MM-DD - Data limite para concessão de desconto1
        // "valorDesconto2" => 7.00,
        // "dataDesconto2" => "2022-07-20",
        // "valorDesconto3" => 3.00,
        // "dataDesconto3" => "2022-07-30",
        "descontoAntecipado" => '', // Valor de Desconto Antecipado
        // "tipoJuros" => 'VALOR', //Tipo de Juros, podendo ser: A - VALOR  B - PERCENTUAL
        // "juros" => '5.00', // Valor de juros a cobrar por dia
        "multa" => '3.00', // Percentual de multa a cobrar
        "especieDocumento" => "DUPLICATA_MERCANTIL_INDICACAO",
        "pagador" => [
            "cep" => "91250000",
            "cidade" => "PORTO ALEGRE",
            "documento" => "35169335695",
            "nome" => "TESTE FAKE",
            "tipoPessoa" => "PESSOA_FISICA",
            "endereco" => "RUA DOUTOR VARGAS NETO 180",
            "uf" => "RS"
        ],
        "tipoCobranca" => "HIBRIDO",
        "seuNumero" => "TESTE",
        "valor" => 55.00,
        "informativos" => [
            "info 1",
            "info 2",
            "info 3",
            "info 4",
            "info 5"
        ],
        "informativos" => [
            "mens 1",
            "mens 2",
            "mens 3",
            "mens 4"
        ],
    ];

    $reponse = $sicredi->registrarBoleto($dadosBoleto);
    echo "<pre>";
    print_r($reponse);
```

PDF BOLETO

```php
    $reponse = $sicredi->pdfBoleto($linhaDigitavel);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="boleto.pdf"'); // Exibir no navegador
    echo $reponse;
```

PDF BOLETO

```php
    $reponse = $sicredi->pdfBoleto($linhaDigitavel);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="boleto.pdf"'); // Exibir no navegador
    echo $reponse;
```

BAIXAR BOLETO

```php
    $nossoNumero = '211001292';
    $reponse = $sicredi->baixarBoleto($nossoNumero);
    echo "<pre>";
    print_r($reponse);
```

ALTERAR DATA VENCIMENTO BOLETO

```php
    $nossoNumero = '211001292';
    $vencimento = [
        "dataVencimento" => '2024-12-17'
    ];

    $reponse = $sicredi->alterarVencimentoBoleto($nossoNumero, $vencimento);
    echo "<pre>";
    print_r($response);
```

ALTERAR DESCONTO BOLETO

```php
    $nossoNumero = '211001292';
    $desconto = [
        "valorDesconto1" => 0.50,
        "valorDesconto2" => 0.30,
        "valorDesconto3" => 0.20
    ];

    $reponse = $sicredi->alterarDescontoBoleto($nossoNumero, $desconto);
    echo "<pre>";
    print_r($response);
```

ALTERAR DATA DESCONTO BOLETO

```php
    $nossoNumero = '211001292';
    $desconto = [
        "data1" => "2024-12-20",
        "data2" => "2024-12-22",
        "data3" => "2024-12-24"
    ];

    $reponse = $sicredi->alterarDataDescontoBoleto($nossoNumero, $desconto);
    echo "<pre>";
    print_r($response);
```

ALTERAR JUROS BOLETO

```php
    $nossoNumero = '211001292';
    $juros = [
        "valorOuPercentual" => 2.00
    ];

    $reponse = $sicredi->alterarJurosBoleto($nossoNumero, $juros);
    echo "<pre>";
    print_r($response);
```

ALTERAR SEU NUMERO BOLETO

```php
    $nossoNumero = '211001292';
    $seuNumero = [
        "seuNumero" => "0123456789"
    ];

    $reponse = $sicredi->alterarSeuNumeroBoleto($nossoNumero, $seuNumero);
    echo "<pre>";
    print_r($response);
```

CONCEDER ABATIMENTO BOLETO

```php
    $nossoNumero = '211001292';
    $valorAbatimento = [
        "valorAbatimento" => "10.00"
    ];

    $reponse = $sicredi->concederAbatimentoBoleto($nossoNumero, $valorAbatimento);
    echo "<pre>";
    print_r($response);
```

CANCELAR ABATIMENTO CONCEDIDO BOLETO

```php
    $nossoNumero = '211001292';

    $reponse = $sicredi->cancelarAbatimentoConcedidoBoleto($nossoNumero);
    echo "<pre>";
    print_r($response);
```

PROTESTAR BOLETO

```php
    $nossoNumero = '211001292';

    $reponse = $sicredi->pedidoProtestoBoleto($nossoNumero);
    echo "<pre>";
    print_r($response);
```

SUSTAR PROTESTAR E BAIXA TITULO BOLETO

```php
    $nossoNumero = '211001292';

    $reponse = $sicredi->sustarProtestoBaixaTitulo($nossoNumero);
    echo "<pre>";
    print_r($response);
```

SUSTAR PROTESTAR E MANTER TITULO BOLETO

```php
    $nossoNumero = '211001292';
    $reponse = $sicredi->sustarProtestoManterTitulo($nossoNumero);
    echo "<pre>";
    print_r($response);
```

INCLUIR NEGATIVAÇÃO BOLETO

```php
    $nossoNumero = '211001292';
    $response = $sicredi->incluirNegativacaoBoleto($nossoNumero);
    echo "<pre>";
    print_r($response);
```

SUSTAR NEGATIVAÇÃO BAIXA TITULO

```php
    $nossoNumero = '211001292';
    $response = $sicredi->sustarNegativacaoBaixaTitulo($nossoNumero);
    echo "<pre>";
    print_r($response);
```

SUSTAR NEGATIVAÇÃO MANTER TITULO

```php
    $nossoNumero = '211001292';
    $response = $sicredi->sustarNegativacaoManterTitulo($nossoNumero);
    echo "<pre>";
    print_r($response);
```

CANCELAR PROTESTO AUTOMATICO

```php
    $nossoNumero = '211001292';
    $response = $sicredi->cancelarProtestoAutomatico($nossoNumero);
    echo "<pre>";
    print_r($response);
```

CANCELAR PROTESTO AUTOMATICO

```php
    $nossoNumero = '211001292';
    $response = $sicredi->consultarNossoNumeroBoleto($nossoNumero);
    echo "<pre>";
    print_r($response);
```

CONSULTAR BOLETO LIQUIDADO DIA

```php
    $dia = '25/01/2024';

    $reponse = $sicredi->consultaBoletosLiquidadoDia($dia);
    echo "<pre>";
    print_r($response);
```

CONSULTAR MOVIMENTAÇÃO

```php
    $dadosConsulta = [
        "tipoMovimentacao" => 'DEBITO', // CREDITO, DEBITO, AMBOS
        "pagina" => 0,
        "dataLancamento" => '17-01-2026',
    ];

    $reponse = $sicredi->movimentacaoFinanceira($dadosConsulta);
    echo "<pre>";
    print_r($response);
```
