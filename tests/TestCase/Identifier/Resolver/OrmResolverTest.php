<?php
namespace Phauthentic\Authentication\Test\TestCase\Identifier\Resolver;

use Cake\Datasource\EntityInterface;
use Phauthentic\Authentication\Identifier\Resolver\OrmResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase;

class OrmResolverTest extends AuthenticationTestCase
{
    public function testFindDefault()
    {
        $resolver = new OrmResolver();

        $user = $resolver->find([
            'username' => 'mariano'
        ]);

        $this->assertInstanceOf(EntityInterface::class, $user);
        $this->assertEquals('mariano', $user['username']);
    }

    public function testFindConfig()
    {
        $resolver = new OrmResolver([
            'userModel' => 'AuthUsers',
            'finder' => [
                'all',
                'auth' => ['return_created' => true]
            ]
        ]);

        $user = $resolver->find([
            'username' => 'mariano'
        ]);

        $this->assertNotEmpty($user->created);
    }

    public function testFindAnd()
    {
        $resolver = new OrmResolver();

        $user = $resolver->find([
            'id' => 1,
            'username' => 'mariano'
        ]);

        $this->assertEquals(1, $user['id']);
    }

    public function testFindMissing()
    {
        $resolver = new OrmResolver();

        $user = $resolver->find([
            'id' => 1,
            'username' => 'luigiano'
        ]);

        $this->assertNull($user);
    }

    public function testFindMultipleValues()
    {
        $resolver = new OrmResolver();

        $user = $resolver->find([
            'username' => [
                'luigiano',
                'mariano'
            ]
        ]);

        $this->assertEquals(1, $user['id']);
    }
}
