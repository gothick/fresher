<?php

namespace App\Twig;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

// Basically the standard KNP MarkdownTwigExtension duplicated, with the
// stupid paragraph tags horribly removed.
class InlineMarkdownExtension extends AbstractExtension
{
    /** @var MarkdownParserInterface */
    private $parser;

    public function __construct(MarkdownParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('markdown_inline', array($this, 'markdownInline'), array('is_safe' => array('html'))),
        );
    }

    public function markdownInline(string $text): string
    {
        // It's bizarre how hard it is to find a markdown parser that
        // doesn't wrap its entire output in <p> tags. This ain't nice,
        // but it'll do.
        // https://stackoverflow.com/a/4575830/300836
        $markdown = $this->parser->transformMarkdown($text);
        return preg_replace('!^<p>(.*?)</p>$!i', '$1', $markdown);
    }

    public function getName(): string
    {
        return 'markdown_inline';
    }
}
