<?php

namespace Codeages\Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserBindDao extends GeneralDaoInterface
{
	public function getByTypeAndBindId($type, $bindId);

	public function deleteByTypeAndBindId($type, $bindId);
}