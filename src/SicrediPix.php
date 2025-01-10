<?php

namespace Divulgueregional\apisicredi;

// use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SicrediPix
{
    private $config;
    private $tokens;
    private $token;
    private $retornoTtoken;
    private $client;
    private $optionsRequest;

    function __construct($config, $sandbox = false)
    {
        $this->config = $config;
        $url = 'https://api-parceiro.sicredi.com.br';
        if ($sandbox) {
            $url = 'https://api-parceiro.sicredi.com.br/sb';
        }
        $this->client = new Client([
            'base_uri' => $url,
        ]);

        // $this->optionsRequest = [
        //     'headers' => [
        //         'Accept' => 'application/json',
        //         'Content-Type' => 'application/x-www-form-urlencoded',
        //         'x-sicoob-clientid' => $config['client_id']
        //     ],
        // ];
    }

    #################################################
    ###### TOKEN ####################################
    #################################################
    public function gerarToken()
    {
        try {
            $response = $this->client->request(
                'POST',
                '/oauth/token',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($this->config['CLIENT_ID'] . ':' . $this->config['CLIENT_SECRET']) . ''
                    ],
                    'cert' => $this->config['CERTIFICADO'], // Caminho para o certificado
                    'ssl_key' => $this->config['CHAVE_PRIVADA'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Ou o caminho para o certificado da CA se necessário
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'scope' => 'cob.write+cob.read+webhook.read+webhook.write'
                    ]
                ]
            );

            return (array) json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            // return $this->parseResultClient($e);
            return $e;
        } catch (\Exception $e) {
            $response = $e->getMessage();
            return ['error' => $response];
        }
    }

    public function setToken(String $token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
    #################################################
    ###### FIM TOKEN ################################
    #################################################

    #################################################
    ###### PIX ######################################
    #################################################

    // Gerar Pix
    public function criarCobranca($dadosPix)
    {
        try {
            $response = $this->client->request(
                'POST',
                '/api/v2/cob',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO'], // Caminho para o certificado
                    'ssl_key' => $this->config['CHAVE_PRIVADA'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                    'json' => [
                        'calendario' => [
                            'dataDeVencimento' => $dadosPix['dataDeVencimento'], //"2040-04-01",
                            "validadeAposVencimento" => 1
                        ],
                        'valor' => [
                            'original' => $dadosPix['valor'],
                            "modalidadeAlteracao" => 1
                        ],
                        'chave' => $dadosPix['chave_pix'],
                        "solicitacaoPagador" => "Serviço realizado.",
                        'infoAdicionais' => [
                            'nome' =>  $dadosPix['fatura_id'],
                            'valor' =>  $dadosPix['parcela_valor']
                        ],
                        // [
                        //     "nome" => "fatura_id",
                        //     "valor" =>  123334
                        // ]
                    ],
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            // return new Exception("Falha ao gerar Pix: {$e->getMessage()}");
        }
    }

    public function dadosDeCobranca($id)
    {
        try {
            $response = $this->client->request(
                'GET',
                "/api/v2/cob/{$id}",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO'], // Caminho para o certificado
                    'ssl_key' => $this->config['CHAVE_PRIVADA'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            // return new Exception("Falha ao buscar Pix: {$e->getMessage()}");
        }
    }

    #################################################
    ###### FIM - PIX ################################
    #################################################

    #################################################
    ###### WEBHOOK ##################################
    #################################################
    public function updateWebhook($chave_pix, $webhookUrl)
    {
        try {
            $response = $this->client->request(
                'PUT',
                "/api/v2/webhook/{$chave_pix}",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO'], // Caminho para o certificado
                    'ssl_key' => $this->config['CHAVE_PRIVADA'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                    'json' => [
                        'webhookUrl' => $webhookUrl,
                    ],
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            // return new Exception("Falha ao criar webhook: {$e->getMessage()}");
        }
    }

    public function getWebhook($chave_pix)
    {
        try {
            $response = $this->client->request(
                'GET',
                "/api/v2/webhook/{$chave_pix}",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO'], // Caminho para o certificado
                    'ssl_key' => $this->config['CHAVE_PRIVADA'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            // return new Exception("Falha ao buscar webhook: {$e->getMessage()}");
        }
    }

    public function deleteWebhook($chave_pix)
    {
        try {
            $response = $this->client->request(
                'DELETE',
                "/api/v2/webhook/{$chave_pix}",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO'], // Caminho para o certificado
                    'ssl_key' => $this->config['CHAVE_PRIVADA'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            // return new Exception("Falha ao deletar webhook: {$e->getMessage()}");
        }
    }
    #################################################
    ###### FIM - WEBHOOK ############################
    #################################################

    #################################################
    ###### TESTE ####################################
    #################################################
    public function teste()
    {
        return 'Teste OK';
    }
}
