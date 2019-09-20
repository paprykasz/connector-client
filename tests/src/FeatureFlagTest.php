<?php
/**
 * Created by PhpStorm.
 * User: Jan Weskamp <jan.weskamp@jtl-software.com>
 * Date: 20.09.2019
 * Time: 14:48
 */

namespace jtl\Connector\Client;

use Faker\Factory;
use Jtl\Connector\Client\Features\FeatureFlag;
use PHPUnit\Framework\TestCase;

class FeatureFlagTest extends TestCase
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
     */
    public function it_should_has_name_and_active_properties(): void
    {
        $name = $this->faker->word;
        $active = $this->faker->boolean;
        
        $featureEntity = new FeatureFlag($name,$active);
        
        $this->assertEquals($featureEntity->isActive(),$active);
        $this->assertEquals($name,$featureEntity->getName());
    }
}