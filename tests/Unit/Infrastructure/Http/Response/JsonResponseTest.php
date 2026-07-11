<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Http\Response;

use App\Infrastructure\Http\Response\JsonResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class JsonResponseTest extends TestCase
{
    #[Test]
    public function givenPayloadAndStatusCodeWhenJsonResponseIsCreatedThenHeadersBodyAndStatusMatch(): void
    {
        $response = new JsonResponse(0, 201);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals('0', $response->getBody()->getContents());
        self::assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    #[DataProvider('getCases')]
    public function givenVariousPayloadsWhenJsonResponseIsCreatedThenBodyIsEncodedAsJson(mixed $source, mixed $expect): void
    {
        $response = new JsonResponse($source);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals($expect, $response->getBody()->getContents());
        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return array<string, list<array<string, int|string|null>|int|stdClass|string|null>>
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
