<?php

namespace Divulgueregional\ApiSicredi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SicrediPix
{
    private $config;
    private $url;
    protected $client;
    protected $token;

    function __construct($config = [])
    {
        $this->config = $config;
        $this->url = 'https://api-pix.sicredi.com.br'; // Definindo URL para o ambiente de produção

        if ($this->config['producao'] == 0) {
            $this->url = 'https://api-pix-h.sicredi.com.br'; //, define homologação
        }

        $this->client = new Client([
            'base_uri' => $this->url,
        ]);
    }

    #################################################
    ###### TOKEN ####################################
    #################################################
    public function gerarTokenPix()
    {
        try {
            $response = $this->client->request(
                'POST',
                'oauth/token?grant_type=client_credentials&scope=cob.write+cob.read+webhook.read+webhook.write',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($this->config['CLIENT_ID'] . ':' . $this->config['CLIENT_SECRET']) . ''
                    ],
                    'cert' => $this->config['CERTIFICADO_PEM'], // Caminho para o certificado
                    'ssl_key' => $this->config['CERTIFICADO_KEY'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Ou o caminho para o certificado da CA se necessário
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

    private function analisarToken()
    {
        if ($this->token == '') {
            $token = $this->gerarTokenPix();
            $this->token = $token['access_token'];
        }
    }

    #################################################
    ###### FIM TOKEN ################################
    #################################################

    #################################################
    ###### PIX ######################################
    #################################################

    // Gerar Pix
    public function criarCobranca($cobranca)
    {
        $this->analisarToken();
        try {
            $response = $this->client->request(
                'POST',
                '/api/v2/cob',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO_PEM'], // Caminho para o certificado
                    'ssl_key' => $this->config['CERTIFICADO_KEY'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                    'body' => json_encode($cobranca),
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            return new Exception("Falha ao gerar Pix: {$e->getMessage()}");
        }
    }

    public function dadosDeCobranca($txid)
    {
        $this->analisarToken();
        try {
            $response = $this->client->request(
                'GET',
                "/api/v2/cob/{$txid}",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO_PEM'], // Caminho para o certificado
                    'ssl_key' => $this->config['CERTIFICADO_KEY'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\Exception $e) {
            return new Exception("Falha ao buscar Pix: {$e->getMessage()}");
        }
    }

    #################################################
    ###### FIM - PIX ################################
    #################################################

    #################################################
    ###### WEBHOOK ##################################
    #################################################
    public function updateWebhook($webhookUrl, $chave_pix)
    {
        $this->analisarToken();
        try {
            $response = $this->client->request(
                'PUT',
                "/api/v2/webhook/{$chave_pix}",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->token}", // Utilizando Bearer Token
                    ],
                    'cert' => $this->config['CERTIFICADO_PEM'], // Caminho para o certificado
                    'ssl_key' => $this->config['CERTIFICADO_KEY'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                    'json' => [
                        'webhookUrl' => $webhookUrl,
                    ],
                ]
            );
            $responseBody = $response->getBody()->getContents();
            if (empty($responseBody)) {
                return "WebHook criado com sucesso.";
            } else {
                return "Corpo da resposta: " . $responseBody;
            }
            // $retorno = json_decode($response->getBody()->getContents());
            // return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura a resposta do erro.
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true); // Decodifica o JSON.

            // Verifica se o campo "title" existe e exibe.
            if (isset($data['title'])) {
                return $data['title'];
            } else {
                return "Erro desconhecido.";
            }
            // return new Exception("Falha ao buscar webhook: {$e->getMessage()}");
        }
    }

    public function getWebhook($chave_pix)
    {
        $this->analisarToken();
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
                    'cert' => $this->config['CERTIFICADO_PEM'], // Caminho para o certificado
                    'ssl_key' => $this->config['CERTIFICADO_KEY'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura a resposta do erro.
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true); // Decodifica o JSON.

            // Verifica se o campo "title" existe e exibe.
            if (isset($data['title'])) {
                return $data['title'];
            } else {
                return "Erro desconhecido.";
            }
            // return new Exception("Falha ao buscar webhook: {$e->getMessage()}");
        }
    }

    public function deleteWebhook($chave_pix)
    {
        $this->analisarToken();
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
                    'cert' => $this->config['CERTIFICADO_PEM'], // Caminho para o certificado
                    'ssl_key' => $this->config['CERTIFICADO_KEY'], // Caminho para a chave privada (sem senha)
                    'verify' => false, // Verificar se necessário
                ]
            );
            $retorno = json_decode($response->getBody()->getContents());
            if (empty($retorno)) {
                return "WebHook EXCLUIDO com sucesso.";
            } else {
                return "Resposta: " . $retorno;
            }
            return $retorno; // Aqui você retorna o resultado da cobrança
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura a resposta do erro.
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true); // Decodifica o JSON.

            // Verifica se o campo "title" existe e exibe.
            if (isset($data['title'])) {
                return $data['title'];
            } else {
                return "Erro desconhecido.";
            }
            // return new Exception("Falha ao buscar webhook: {$e->getMessage()}");
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
        return 'Teste acesso pix feito com sucesso';
    }
}
