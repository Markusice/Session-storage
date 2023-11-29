<?php

declare (strict_types=1);

require_once './Storage.php';

class UserStorage extends Storage
{
    public function __construct()
    {
        parent::__construct(new JsonIO('users.json'));
    }
}
