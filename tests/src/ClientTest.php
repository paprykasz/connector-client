<?php
/**
 * @author Immanuel Klinkenberg <immanuel.klinkenberg@jtl-software.com>
 * @copyright 2010-2019 JTL-Software GmbH
 * Created at 19.08.2019 09:45
 */

namespace jtl\Connector\Client;

use Faker\Factory;
use GuzzleHttp\Psr7\Response;
use Jtl\Connector\Client\Client;
use Jtl\Connector\Client\ResponseException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $faker;
    
    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
    }
    
    /**
     * @test
     * @throws \ReflectionException
     */
    public function should_has_defined_required_constants(): void
    {
        $clientReflection = new \ReflectionClass(Client::class);
        
        $this->assertSame([
            'JTL_RPC_VERSION'    => '2.0',
            'DEFAULT_PULL_LIMIT' => 100,
            'METHOD_ACK'         => 'core.connector.ack',
            'METHOD_AUTH'        => 'core.connector.auth',
            'METHOD_FEATURES'    => 'core.connector.features',
            'METHOD_IDENTIFY'    => 'connector.identify',
            'METHOD_FINISH'      => 'connector.finish',
            'METHOD_CLEAR'       => 'core.linker.clear',
            'DATA_FORMAT_JSON'   => 'json',
            'DATA_FORMAT_ARRAY'  => 'array',
            'DATA_FORMAT_OBJECT' => 'object',
        ], $clientReflection->getConstants());
    }
    
    /**
     * @test
     */
    public function should_has_basic_call_methods(): void
    {
        $defaultMethodsExpected = [
            'features',
            'statistic',
            'ack',
            'delete',
            'pull',
        ];
        
        $token = 'some_random_token_string';
        $endpointUrl = 'http://endpointurl';
        
        $client = new Client($token, $endpointUrl);
        $clientReflection = new \ReflectionClass($client);
        $clientMethods = $clientReflection->getMethods();
        
        $clientMethodsNames = array_map(function ($method) {
            return $method->name;
        }, $clientMethods);
        
        foreach ($defaultMethodsExpected as $expectedMethod) {
            $this->assertContains($expectedMethod,$clientMethodsNames);
        }
    }
    
    /**
     * @test
     * @throws \ReflectionException
     */
    public function should_default_support_array_json_and_object_response_formats(): void
    {
        $token = 'some_random_token_string';
        $endpointUrl = 'http://endpointurl';
        
        $client = new Client($token, $endpointUrl);
        
        $clientReflection = new \ReflectionClass($client);
        $responseFormats = $clientReflection->getProperty('responseFormats');
        $responseFormats->setAccessible(true);
        
        $this->assertSame([
            Client::DATA_FORMAT_ARRAY,
            Client::DATA_FORMAT_JSON,
            Client::DATA_FORMAT_OBJECT,
        ], $responseFormats->getValue(new Client($token, $endpointUrl)));
    }
    
    /**
     * @test
     * @throws \ReflectionException
     */
    public function should_has_token_and_endpoint_properties(): void
    {
        $token = 'some_random_token_string';
        $endpointUrl = 'http://endpointurl';
        
        $client = new Client($token, $endpointUrl);
        
        $this->assertClassHasAttribute('token',Client::class);
        $this->assertClassHasAttribute('endpointUrl',Client::class);
        
        $clientReflection = new \ReflectionClass($client);
        $tokenProperty = $clientReflection->getProperty('token');
        $tokenProperty->setAccessible(true);
        $this->assertEquals($token, $tokenProperty->getValue(new Client($token, $endpointUrl)));
        
        $tokenProperty = $clientReflection->getProperty('endpointUrl');
        $tokenProperty->setAccessible(true);
        $this->assertEquals($endpointUrl, $tokenProperty->getValue(new Client($token, $endpointUrl)));
    }
    
    /**
     * @test
     * @throws \ReflectionException
     */
    public function authentication_call_sets_session_id(): void
    {
        $someSessionId = $this->faker->randomNumber();
        
        $mockedHttpClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedHttpClient->shouldReceive('post')->andReturn(new Response(
            200,
            [],
            json_encode([
                'result' => [
                    'sessionId' => $someSessionId,
                ],
            ])
        ));
        
        $token = 'some_random_token_string';
        $endpointUrl = 'http://endpointurl';
        $client = new Client($token, $endpointUrl, $mockedHttpClient);
        $client->authenticate();
        
        $clientReflection = new \ReflectionClass($client);
        $tokenProperty = $clientReflection->getProperty('sessionId');
        $tokenProperty->setAccessible(true);
        $this->assertEquals($someSessionId, $tokenProperty->getValue($client));
    }
    
    /**
     * @test
     * @throws \ReflectionException
     */
    public function should_convert_underscore_to_camelcase(): void
    {
        $token = 'some_random_token_string';
        $endpointUrl = 'http://endpointurl';
        $client = new Client($token, $endpointUrl);
        
        $clientReflection = new \ReflectionClass($client);
        $underscoreToCamelCaseMethod = $clientReflection->getMethod('underscoreToCamelCase');
        
        $underscoreToCamelCaseMethod->setAccessible(true);
        
        $assertions = [
            'some_example' => 'SomeExample',
            'foo_bar_bin'  => 'FooBarBin',
        ];
        
        foreach ($assertions as $try => $expected) {
            $result = $underscoreToCamelCaseMethod->invoke(new Client($token, $endpointUrl), $try);
            $this->assertEquals($expected, $result);
        }
    }
    
    /**
     * @test
     * @throws \ReflectionException
     */
    public function should_created_request_params(): void
    {
        $token = 'some_random_token_string';
        $endpointUrl = 'http://endpointurl';
        $client = new Client($token, $endpointUrl);
        
        $clientReflection = new \ReflectionClass($client);
        $createRequestParamsMethod = $clientReflection->getMethod('createRequestParams');
        
        $createRequestParamsMethod->setAccessible(true);
        
        $requestId = $this->faker->randomNumber();
        $method = Client::METHOD_FEATURES;
        
        $params = $createRequestParamsMethod->invoke(new Client($token, $endpointUrl), $requestId, $method);
        
        $this->assertSame([
            'jtlrpc' => json_encode([
                'method' => $method,
                'jtlrpc' => Client::JTL_RPC_VERSION,
                'id'     => (string)$requestId,
            ]),
        ], $params);
    }
    
    /**
     * @test
     */
    public function it_should_throw_unknown_error_when_no_result_is_present_in_response(): void
    {
        $mockedHttpClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedHttpClient->shouldReceive('post')->andReturn(new Response(
            200,
            [],
            ''
        ));
        
        $mockedClient = \Mockery::mock(Client::class);
        $mockedClient->shouldReceive('authenticate')->andThrow(ResponseException::unknownError());
        
        $this->expectException(ResponseException::class);
        
        $mockedClient->authenticate();
    }
    
    /**
     * @test
     */
    public function it_should_throw_response_exception_error_with_message_defined_in_response_when_http_error_occurs(
    ): void
    {
        $errorCode = 3000;
        $errorMessage = 'detailed_error_description_message';
        
        $mockedHttpClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedHttpClient->shouldReceive('post')->andReturn(new Response(
            200,
            [],
            json_encode([
                'error' => [
                    'message' => $errorMessage,
                    'code'    => $errorCode,
                ],
            ])
        ));
        
        $mockedClient = \Mockery::mock(Client::class);
        $mockedClient->shouldReceive('authenticate')
            ->andThrow(ResponseException::responseError($errorMessage, $errorCode));
        
        $this->expectExceptionCode($errorCode);
        $this->expectExceptionMessage($errorMessage);
        
        $mockedClient->authenticate();
    }
    
    
}
