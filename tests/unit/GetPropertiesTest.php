<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Jovel\Easybroker\GetProperties;
use Mockery as Mockery;
use PHPUnit\Framework\TestCase;

final class GetPropertiesTest extends TestCase
{
    private const DATATEST = [
        'Error' => [
            [
                'content' => [],
            ],
        ],
        'Correct' => [
            [
                'pagination' => [
                    'next_page' => 'https=>//api.stagingeb.com/v1/properties?limit=50&page=2',
                ],
                'content' => [[
                    'title' => 'oficinas en renta Santa Maria la Ribera',
                ], [
                    'title' => 'Departamento en venta Tecamachalco 4 recamaras',
                ]],
            ],
            [
                'pagination' => [
                    'next_page' => 'https=>//api.stagingeb.com/v1/properties?limit=50&page=3',
                ],
                'content' => [[
                    'title' => 'Casa - Zona Valle Oriente Sur',
                ], [
                    'title' => 'Beautiful property in Condesa.',
                ]],
            ],
            [
                'pagination' => [
                    'next_page' => null,
                ],
                'content' => [[
                    'title' => 'Prueba de carga 1',
                ], [
                    'title' => 'Departamento en Renta Torre Arcangeles en San Pedro Garza Garcia',
                ]],
            ],
        ],
    ];

    protected $mockStream;

    protected $mockResponse;

    protected $mockClient;

    public static function tearDownAfterClass(): void
    {
        Mockery::close();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockStream = Mockery::mock(Stream::class);
        $this->mockResponse = Mockery::mock(Response::class);
        $this->mockClient = Mockery::mock(Client::class);
    }

    public function testCheckPropertiesAndReceiveCorrectData(): void
    {
        $this->createMockWithResponse('Correct');

        $getProperties = new GetProperties(
            $this->mockClient,
            'https://api.stagingeb.com/v1/properties?page=1&limit=50'
        );
        $getProperties->loadProperties();

        $this->assertSame(self::DATATEST['Correct'][1]['pagination']['next_page'], $getProperties->getUrl());
        ob_clean();
    }

    public function testCheckPropertiesAndReceiveErrorData(): void
    {
        $this->createMockWithResponse('Error');
        $url = 'https://api.stagingeb.com/v1/properties?page=100&limit=50';

        $getProperties = new GetProperties($this->mockClient, $url);
        $getProperties->loadProperties();

        $this->assertSame($url, $getProperties->getUrl());
        ob_clean();
    }

    private function createMockWithResponse(string $type): void
    {
        $this->mockStream->shouldReceive('getContents')
            ->andReturnValues(array_map(fn ($item) => json_encode($item), self::DATATEST[$type]));
        $this->mockResponse->shouldReceive('getBody')
            ->andReturn($this->mockStream);
        $this->mockClient->shouldReceive('request')
            ->andReturn($this->mockResponse);
    }
}
