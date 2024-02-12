<?php
namespace FRMS\Helpers;
use FRMS\Core\Game;

class UserException extends \BgaUserException
{
    public function __construct($str)
    {
        parent::__construct(Game::get()::translate($str));
    }
}
