<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\CLI\Util;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

final class ShlinkTable
{
    private const DEFAULT_STYLE_NAME = 'default';
    private const TABLE_TITLE_STYLE = '<options=bold> %s </>';

    private ?Table $baseTable;

    public function __construct(Table $baseTable)
    {
        $this->baseTable = $baseTable;
    }

    public static function fromOutput(OutputInterface $output): self
    {
        return new self(new Table($output));
    }

    public function render(array $headers, array $rows, ?string $footerTitle = null, ?string $headerTitle = null): void
    {
        $style = Table::getStyleDefinition(self::DEFAULT_STYLE_NAME);
        $style->setFooterTitleFormat(self::TABLE_TITLE_STYLE)
              ->setHeaderTitleFormat(self::TABLE_TITLE_STYLE);

        $table = clone $this->baseTable;
        $table->setStyle($style)
              ->setHeaders($headers)
              ->setRows($rows)
              ->setFooterTitle($footerTitle)
              ->setHeaderTitle($headerTitle)
              ->render();
    }
}
