<?php
namespace ApacheSolrForTypo3\Solr\Tests\Unit\ContentObject;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2015 Ingo Renner <ingo@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use ApacheSolrForTypo3\Solr\Tests\Unit\UnitTest;

/**
 * Tests for the SOLR_MULTIVALUE cObj.
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @package TYPO3
 * @subpackage solr
 */
class MultivalueTest extends UnitTest
{
    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @test
     */
    public function convertsCommaSeparatedListFromRecordToSerializedArrayOfTrimmedValues()
    {
        $GLOBALS['TSFE']->cObjectDepthCounter = 2;

        $list = 'abc, def, ghi, jkl, mno, pqr, stu, vwx, yz';
        $expected = 'a:9:{i:0;s:3:"abc";i:1;s:3:"def";i:2;s:3:"ghi";i:3;s:3:"jkl";i:4;s:3:"mno";i:5;s:3:"pqr";i:6;s:3:"stu";i:7;s:3:"vwx";i:8;s:2:"yz";}';

        $this->contentObject->start(array('list' => $list));

        $actual = $this->contentObject->cObjGetSingle(
            \ApacheSolrForTypo3\Solr\ContentObject\Multivalue::CONTENT_OBJECT_NAME,
            array(
                'field' => 'list',
                'separator' => ','
            )
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function convertsCommaSeparatedListFromValueToSerializedArrayOfTrimmedValues()
    {
        $list = 'abc, def, ghi, jkl, mno, pqr, stu, vwx, yz';
        $expected = 'a:9:{i:0;s:3:"abc";i:1;s:3:"def";i:2;s:3:"ghi";i:3;s:3:"jkl";i:4;s:3:"mno";i:5;s:3:"pqr";i:6;s:3:"stu";i:7;s:3:"vwx";i:8;s:2:"yz";}';

        $this->contentObject->start(array());

        $actual = $this->contentObject->cObjGetSingle(
            \Tx_Solr_contentobject_Multivalue::CONTENT_OBJECT_NAME,
            array(
                'value' => $list,
                'separator' => ','
            )
        );

        $this->assertEquals($expected, $actual);
    }

    protected function setUp()
    {
        $this->skipInVersionBelow('7.6');
        // fake a registered hook
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][\ApacheSolrForTypo3\Solr\ContentObject\Multivalue::CONTENT_OBJECT_NAME] = array(
            \ApacheSolrForTypo3\Solr\ContentObject\Multivalue::CONTENT_OBJECT_NAME,
            'ApacheSolrForTypo3\\Solr\\ContentObject\\Multivalue'
        );

        $GLOBALS['TSFE'] = $this->getDumbMock('\\TYPO3\CMS\\Frontend\\Controller\\TypoScriptFrontendController');

        $this->contentObject = $this->getMock(
            '\\TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer',
            array('getResourceFactory', 'getEnvironmentVariable'),
            array($GLOBALS['TSFE'])
        );
    }

    protected function tearDown()
    {
        unset($GLOBALS['TSFE']);
    }
}
