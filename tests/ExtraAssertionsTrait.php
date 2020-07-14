<?php
namespace TwitchClient\Tests;

trait ExtraAssertionsTrait
{
    /**
     * Asserts that an array has a set of keys.
     * 
     * @param array $keys The keys to check in the array
     * @param array $array The array to test.
     * @return void 
     */
    public function assertArrayHasKeys(array $keys, array $array)
    {
        foreach($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    /**
     * Asserts that an object has a set of attributes.
     * 
     * @param array $keys The keys to check in the object
     * @param object $object The object to test.
     * @return void 
     */
    public function assertObjectHasAttributes(array $keys, object $object)
    {
        foreach($keys as $key) {
            $this->assertObjectHasAttribute($key, $object);
        }
    }
}