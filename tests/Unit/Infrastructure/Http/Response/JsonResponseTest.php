<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Http\Response;

use App\Infrastructure\Http\Response\JsonResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonResponseTest extends TestCase
{
    public function testWithCode(): void
    {
        $response = new JsonResponse(0, 201);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals('0', $response->getBody()->getContents());
        self::assertEquals(201, $response->getStatusCode());
    }

    #[DataProvider('getCases')]
    public function testResponse(mixed $source, mixed $expect): void
    {
        $response = new JsonResponse($source);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals($expect, $response->getBody()->getContents());
        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return array[]
     */
    public static function getCases(): array
    {
        $object = new stdClass();
        $object->str = 'value';
        $object->int = 1;
        $object->none = null;

        $array = [
            'str' => 'value',
            'int' => 1,
            'none' => null
        ];

        return [
            'null' => [null, 'null'],
            'empty' => ['', '""'],
            'number' => [12, '12'],
            'string' => ['12', '"12"'],
            'object' => [$object, '{"str":"value","int":1,"none":null}'],
            'array' => [$array, '{"str":"value","int":1,"none":null}'],
        ];
    }
}
