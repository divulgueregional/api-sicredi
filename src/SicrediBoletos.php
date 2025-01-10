<?php

namespace Divulgueregional\apisicredi;

// use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SicrediBoletos
{
    private $config;
    private $baseUrl;
    private $sandbox;
    private $token;
    private $client;
    private $optionsRequest;
    private $tokenExpiry;

    function __construct($config)
    {
        $this->config = $config;

        $this->client = new Client([
            'base_uri' => 'https://api-parceiro.sicredi.com.br',
        ]);

        $this->optionsRequest = [
            'headers' => [
                'x-api-key' => $config['x-api-key'],
                'Accept' => 'application/json',
            ],
        ];

        //token
        // $this->initializeToken();
        if ($config['token'] == '') {
            $token =  $this->gerarToken();
            if (isset($token['access_token'])) {
                $this->token =  $token['access_token'];
            } else {
                return ['error' => 'Falha ao gerar o token: ' . $token['details']];
            }
        } else {
            $this->token =  $config['token'];
        }
    }

    private function initializeToken(): void
    {
        if (empty($this->config['token']) || empty($this->config['token_expiry']) || $this->config['token_expiry'] <= time()) {
            $this->generateToken();
        } else {
            $this->token = $this->config['token'];
            $this->tokenExpiry = $this->config['token_expiry'];
        }
    }

    private function generateToken(): void
    {
        $options = $this->optionsRequest;

        $options['form_params'] = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'grant_type' => 'password',
            'cooperativa' => $this->config['cooperativa'],
            'scope' => 'cobranca',
            'posto' => $this->config['posto'],
            'codigoBeneficiario' => $this->config['codigoBeneficiario'],
        ];

        try {
            $response = $this->client->post('/auth/openapi/token', $options);
            $data = json_decode($response->getBody(), true);

            if (!isset($data['access_token'], $data['expires_in'])) {
                throw new \Exception('Token inválido ou resposta malformada.');
            }

            $this->token = $data['access_token'];
            $this->tokenExpiry = time() + $data['expires_in'];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar o token: ' . $e->getMessage());
        }
    }

    public function request(string $method, string $endpoint, array $additionalHeaders = [], array $body = []): array
    {
        // Renova o token se estiver expirado
        if ($this->tokenExpiry <= time()) {
            $this->generateToken();
        }

        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers'] = array_merge($options['headers'], $additionalHeaders);

        if (!empty($body)) {
            $options['json'] = $body;
        }

        try {
            $response = $this->client->request($method, $endpoint, $options);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception('Erro ao fazer a requisição: ' . $e->getMessage());
        }
    }

    #####################################################################
    ######## TOKEN ######################################################
    #####################################################################

    public function gerarToken()
    {
        $options = $this->optionsRequest;
        $options['form_params'] = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'grant_type' => 'password',
            'cooperativa' => $this->config['cooperativa'],
            'scope' => 'cobranca',
            'posto' => $this->config['posto'],
            'codigoBeneficiario' => $this->config['codigoBeneficiario'],
        ];

        $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $options['headers']['context'] = 'COBRANCA';

        $endpoint = '/auth/openapi/token';
        if ($this->config['sandbox']) {
            $endpoint = '/sb/auth/openapi/token';
        }
        try {
            $response = $this->client->request(
                'POST',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function getToken()
    {
        $response = $this->gerarToken();
        $this->token = $response['access_token'];
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function refreshToken($token)
    {
        $options = $this->optionsRequest;
        $options['form_params'] = [
            'username' => '123456789',
            'password' => 'teste123',
            'scope' => 'cobranca',
            'grant_type' => 'password',
            'refresh_token' => $token
        ];

        $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $options['headers']['context'] = 'COBRANCA';

        $endpoint = '/auth/openapi/token';
        if ($this->config['sandbox']) {
            $endpoint = '/sb/auth/openapi/token';
        }
        try {
            $response = $this->client->request(
                'POST',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    #####################################################################
    ######## FIM - TOKEN ################################################
    #####################################################################

    #####################################################################
    ######## BOLETO #####################################################
    #####################################################################
    public function registrarBoleto($dadosBoleto)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['headers']['Content-Type'] = 'application/json';
        $options['body'] = json_encode($dadosBoleto);

        $endpoint = 'cobranca/boleto/v1/boletos';
        if ($this->config['sandbox']) {
            $endpoint = '/sb/cobranca/boleto/v1/boletos';
        }
        try {
            $response = $this->client->request(
                'POST',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function pdfBoleto($linhaDigitavel)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';

        $endpoint = "/cobranca/boleto/v1/boletos/pdf?linhaDigitavel={$linhaDigitavel}";
        if ($this->config['sandbox']) {
            $endpoint = "sb/cobranca/boleto/v1/boletos/pdf?linhaDigitavel={$linhaDigitavel}";
        }
        try {
            $response = $this->client->request(
                'GET',
                $endpoint,
                $options
            );
            $pdfContent = $response->getBody()->getContents();

            return $pdfContent;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function baixarBoleto($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/baixa";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/baixa";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function alterarVencimentoBoleto($nossoNumero, $vencimento)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = json_encode($vencimento);

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/data-vencimento";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/data-vencimento";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function alterarDescontoBoleto($nossoNumero, $desconto)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = json_encode($desconto);

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/desconto";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/desconto";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function alterarDataDescontoBoleto($nossoNumero, $desconto)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = json_encode($desconto);

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/data-desconto";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/data-desconto";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function alterarJurosBoleto($nossoNumero, $juros)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = json_encode($juros);

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/juros";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/juros";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function alterarSeuNumeroBoleto($nossoNumero, $seuNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = json_encode($seuNumero);

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/seu-numero";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/seu-numero";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function concederAbatimentoBoleto($nossoNumero, $valorAbatimento)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = json_encode($valorAbatimento);

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/conceder-abatimento";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/conceder-abatimento";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function cancelarAbatimentoConcedidoBoleto($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/cancelar-abatimento";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/cancelar-abatimento";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function pedidoProtestoBoleto($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/protesto";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/protesto";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function sustarProtestoBaixaTitulo($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-protesto-baixar-titulo";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-protesto-baixar-titulo";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function sustarProtestoManterTitulo($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-protesto-manter-titulo";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-protesto-manter-titulo";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function incluirNegativacaoBoleto($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/negativacao";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/negativacao";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function sustarNegativacaoBaixaTitulo($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-negativacao-baixar-titulo/";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-negativacao-baixar-titulo/";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function sustarNegativacaoManterTitulo($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-negativacao-manter-titulo";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/sustar-negativacao-manter-titulo";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function cancelarProtestoAutomatico($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['codigoBeneficiario'] = $this->config['codigoBeneficiario'];
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];
        $options['body'] = '{}';

        $endpoint = "cobranca/boleto/v1/boletos/{$nossoNumero}/cancelar-protesto-automatico";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/{$nossoNumero}/cancelar-protesto-automatico";
        }
        try {
            $response = $this->client->request(
                'PATCH',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function consultarNossoNumeroBoleto($nossoNumero)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];

        $endpoint = "cobranca/boleto/v1/boletos?codigoBeneficiario=12345&nossoNumero={$nossoNumero}";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos?codigoBeneficiario=12345&nossoNumero={$nossoNumero}";
        }
        try {
            $response = $this->client->request(
                'GET',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function consultaBoletosLiquidadoDia($dia)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];

        $endpoint = "cobranca/boleto/v1/boletos/liquidados/dia?codigoBeneficiario=12345&dia={$dia}";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/liquidados/dia?codigoBeneficiario=12345&dia={$dia}";
        }
        try {
            $response = $this->client->request(
                'GET',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    public function movimentacaoFinanceira($dados)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";

        $endpoint = "cobranca/v1/cobranca-financeiro/movimentacoes?cooperativa=6789&codigoBeneficiario=12345&posto=03&dataLancamento={$dados['dataLancamento']}&pagina={$dados['pagina']}&tipoMovimento={$dados['tipoMovimentacao']}";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/v1/cobranca-financeiro/movimentacoes?cooperativa=6789&codigoBeneficiario=12345&posto=03&dataLancamento={$dados['dataLancamento']}&pagina={$dados['pagina']}&tipoMovimento={$dados['tipoMovimentacao']}";
        }
        try {
            $response = $this->client->request(
                'GET',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    // Falta revisar
    public function consultarSeuNumeroIdEmpresaBoleto()
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Content-Type'] = 'application/json';
        $options['headers']['cooperativa'] = $this->config['cooperativa'];
        $options['headers']['posto'] = $this->config['posto'];

        $endpoint = "cobranca/boleto/v1/boletos/cadastrados?idTituloEmpresa=XYZ123456789ABC&codigoBeneficiario=12345";
        if ($this->config['sandbox']) {
            $endpoint = "/sb/cobranca/boleto/v1/boletos/cadastrados?idTituloEmpresa=445488181811848&codigoBeneficiario=12345";
        }
        try {
            $response = $this->client->request(
                'GET',
                $endpoint,
                $options
            );
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Falha ao decodificar JSON da resposta'];
            }
            return $responseBody;
        } catch (ClientException $e) {
            return [
                'error' => 'Erro: ' . $e->getMessage(),
                'details' => $e->getResponse()->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    #####################################################################
    ######## FIM - BOLETO ###############################################
    #####################################################################

    public function teste()
    {
        return 'teste realiado com sucesso';
    }
}
