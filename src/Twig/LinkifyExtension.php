<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Misd\Linkify\Linkify;

class LinkifyExtension extends AbstractExtension
{
    /** @var Linkify */
    private $linkify;

    public function __construct()
    {
        $this->linkify = new Linkify();
    }

    public function getFilters()
    {
        return [
            new TwigFilter('linkify', [$this, 'linkifyFilter'], [
                'pre_escape' => 'html',
                'is_safe' => ['html']
            ]),
        ];
    }
    public function linkifyFilter(string $text): string
    {
        return $this->linkify->process($text);
    }
}
