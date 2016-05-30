<?php
namespace ApacheSolrForTypo3\Solr\Plugin\Results;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2015 Ingo Renner <ingo@typo3.org>
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

use ApacheSolrForTypo3\Solr\Plugin\FormModifier;
use ApacheSolrForTypo3\Solr\Template;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Spell check form modifier, suggests spell checked queries
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @package TYPO3
 * @subpackage solr
 */
class SpellCheckFormModifier implements FormModifier
{

    /**
     * Modifies the search form by providing an additional marker linking to a
     * new query with the suggestions provided by Solr as the search terms.
     *
     * @param array $markers An array of existing form markers
     * @param Template $template An instance of the template engine
     * @return array Array with additional markers for suggestions
     */
    public function modifyForm(array $markers, Template $template)
    {
        $spellChecker = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\SpellChecker');
        $suggestionsLink = $spellChecker->getSpellCheckingSuggestions();

        if (!empty($suggestionsLink)) {
            $markers['suggestion'] = $suggestionsLink;
        }

        return $markers;
    }
}
