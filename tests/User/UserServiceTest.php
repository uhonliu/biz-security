<?php

namespace Tests;



use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\User\Service\UserService;

class UserServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $currentUser = array(
            'id' => 1
        );
        $this->biz['user'] = $currentUser;
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testInvalidParameterExceptionWhenCreateUser()
    {
        $user = $this->mockUser();
        unset($user['login_name']);

        $this->getUserService()->register($user);
    }

    public function testCreateUserWhenUsernameMode()
    {
        $this->createUserByRegisterMode('username');
    }

    public function testCreateUserWhenEmailMode()
    {
        $this->createUserByRegisterMode('email');
    }

    public function testCreateUserWhenMobileMode()
    {
        $this->createUserByRegisterMode('mobile');
    }

    protected function createUserByRegisterMode($mode)
    {
        $this->biz['user.options.register_mode'] = $mode;
        unset($this->biz['user']);
        $user = $this->mockUser();
        $savedUser = $this->getUserService()->register($user);
        $this->expectedUser($user, $savedUser);
    }

    public function testCreateUserWithBindType()
    {
        $user = $this->mockUser();
        $userBind = $this->mockUserBind();
        $bind = array_merge($user, $userBind);
        $savedUser = $this->getUserService()->bindUser($bind);
        $this->expectedUser($user, $savedUser);
        $this->expectedUserBind($userBind, $savedUser['bind']);
    }

    public function testUnbindUser()
    {
        $user = $this->mockUser();
        $userBind = $this->mockUserBind();
        $bind = array_merge($user, $userBind);
        $savedUser = $this->getUserService()->bindUser($bind);

        $this->getUserService()->unbindUser($userBind['type'], $userBind['bind_id']);

        $bind = $this->getUserBindDao()->get($savedUser['bind']['id']);
        $this->assertEmpty($bind);
    }

    protected function expectedUserBind($expectedBind, $actualBind, $unAssertKeys = array())
    {
        foreach (array('type', 'type_alias', 'bind_id') as $key) {
            $this->assertArrayHasKey($key, $actualBind);
        }

        $this->assertArrayHasKey('user_id', $actualBind);

        foreach (array_keys($expectedBind) as $key) {
            if (!empty($unAssertKeys) && !in_array($key, $unAssertKeys)) {
                $this->assertEquals($expectedBind, $actualBind);
            }
        }
    }

    protected function expectedUser($expectedUser, $actualUser, $unAssertKeys = array())
    {
        foreach (array('username', 'nickname', 'email', 'mobile', 'created_user_id', 'created_ip', 'created_source') as $key) {
            $this->assertArrayHasKey($key, $actualUser);
        }

        $this->assertArrayNotHasKey('password', $actualUser);
        $this->assertArrayNotHasKey('salt', $actualUser);

        foreach (array_keys($expectedUser) as $key) {
            if ($key != 'password' && (!empty($unAssertKeys) && !in_array($key, $unAssertKeys))) {
                $this->assertEquals($expectedUser[$key], $actualUser[$key]);
            }
        }
    }

    protected function mockUserBind()
    {
        return array(
            'type' => 'wechat_app',
            'type_alias' => 'wechat',
            'bind_id' => '12345',
            'bind_ext' => '23456333',
        );
    }

    protected function mockUser()
    {
        return array(
            'login_name' => 'test',
            'password' => '123456',
            'created_source' => 'web',
            'created_ip' => '127.0.0.1'
        );
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getUserBindDao()
    {
        return $this->biz->dao('User:UserBindDao');
    }
}