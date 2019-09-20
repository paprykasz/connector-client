<?php
/**
 * Created by PhpStorm.
 * User: Jan Weskamp <jan.weskamp@jtl-software.com>
 * Date: 20.09.2019
 * Time: 14:48
 */

namespace jtl\Connector\Client;

use Faker\Factory;
use Jtl\Connector\Client\Features\FeatureEntity;
use PHPUnit\Framework\TestCase;

class FeatureEntityTest extends TestCase
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
    public function it_should_has_name_pull_push_and_delete_properties(): void
    {
        $name = $this->faker->word;
        $pull = $this->faker->boolean;
        $push = $this->faker->boolean;
        $delete = $this->faker->boolean;
        
        $featureEntity = new FeatureEntity($name,$pull,$push,$delete);
        
        $this->assertEquals($featureEntity->canDelete(),$delete);
        $this->assertEquals($featureEntity->canPull(),$pull);
        $this->assertEquals($featureEntity->canPush(),$push);
        $this->assertEquals($name,$featureEntity->getName());
    }
}