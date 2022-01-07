<?php

namespace App\Controller;

use App\Entity\User;
use PhpParser\Node\Expr\Instanceof_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected function isUserLoggedIn(): bool
    {
        return $this->getUser() instanceof User;
    }
}
