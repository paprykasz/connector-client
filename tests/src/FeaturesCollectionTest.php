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
use Jtl\Connector\Client\Features\FeaturesCollection;
use PHPUnit\Framework\TestCase;

class FeaturesCollectionTest extends TestCase
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
    public function it_should_has_collection_of_entities(): void
    {
        $featuresCollection = new FeaturesCollection();
    
        /**
         * @todo move to factory
         */
        $entity = new FeatureEntity(
            $this->faker->word,$this->faker->boolean,$this->faker->boolean,$this->faker->boolean
        );
        
        $featuresCollection->setEntity($entity);
        
        $this->assertCount(1,$featuresCollection->getEntities());
    }
    
}