<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Testsuite\Utils\Factory;
use Testsuite\Utils\TestUtils;
use Filehosting\Helper\LinkHelper;

class LinkHelperTest extends TestCase
{
    public function linkGenerationDataProvider()
    {
        $linkHelper = new LinkHelper('en_US');
        return [
            'linkToFile' => [$linkHelper->getLinkToFile(Factory::fileFactory()), $linkHelper->getLinkToFile(Factory::fileFactory())],
            'fileDownloadLink' => [$linkHelper->getFileDownloadLink(Factory::fileFactory()), $linkHelper->getFileDownloadLink(Factory::fileFactory())],
            'fileDeletionLink' => [$linkHelper->getFileDeletionLink(Factory::fileFactory()), $linkHelper->getFileDeletionLink(Factory::fileFactory())],
            'fileStreamLink' => [$linkHelper->getFileStreamLink(Factory::fileFactory()), $linkHelper->getFileStreamLink(Factory::fileFactory())],
            'fileThumbnailLink' => [$linkHelper->getFileThumbnailLink(Factory::fileFactory()), $linkHelper->getFileThumbnailLink(Factory::fileFactory())],
            'commentFormReplyLink' => [$linkHelper->getCommentFormReplyLink(15), $linkHelper->getCommentFormReplyLink(20)]
        ];
    }

    /**
     * @dataProvider linkGenerationDataProvider
     */
    public function testLinkGenerationFunction($firstLink, $secondLink)
    {
        // Тестировать что это строка и она не пустая
        $this->assertFalse(TestUtils::isStringEmpty($firstLink));
        $this->assertFalse(TestUtils::isStringEmpty($secondLink));
        // Тестировать что в ней нет запрещенных символов (пробелов)
        $this->assertTrue($this->isLinkValid($firstLink));
        $this->assertTrue($this->isLinkValid($secondLink));
        // Тестировать что для разных сущностей ссылки разные
        $this->assertNotEquals($firstLink, $secondLink);
    }

    protected function isLinkValid($link)
    {
        return !boolval(preg_match('/\s/', $link));
    }
}