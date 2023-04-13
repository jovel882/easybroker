<?php

declare(strict_types=1);

namespace Jovel\Easybroker;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use stdClass;

class GetProperties
{
    public function __construct(
        private Client $client,
        private string $url
    ) {
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function loadProperties(): void
    {
        try {
            $properties = (object) json_decode(
                $this->executeRequest()
                    ->getBody()
                    ->getContents(),
                true
            );
            $this->validateResponse($properties);
            $this->showContent($properties->content);

            if (! empty($properties->pagination['next_page'])) {
                $this->setUrl($properties->pagination['next_page']);
                $this->loadProperties();
            }
        } catch (\Throwable $th) {
            echo '<pre>Fallo la conexión: ' . print_r($th, true) . '</pre>';
        }
    }

    private function executeRequest(): Response
    {
        return $this->client->request('GET', $this->url, [
            'headers' => [
                'X-Authorization' => $_ENV['API_KEY'],
                'accept' => 'application/json',
            ],
        ]);
    }

    private function validateResponse(stdClass $properties): void
    {
        if (empty($properties->content)) {
            throw new \Exception('Esta vacío el contenido', 500);
        }
    }

    private function showContent(array $properties): void
    {
        foreach ($properties as $propertie) {
            echo "{$propertie['title']}<br/>";
        }
    }
}
