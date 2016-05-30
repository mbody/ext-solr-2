<?php
namespace ApacheSolrForTypo3\Solr\Backend\SolrModule;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2015 Ingo Renner <ingo@typo3.org>
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

use ApacheSolrForTypo3\Solr\SolrService;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Index Maintenance Module
 *
 * @author Ingo Renner <ingo@typo3.org>
 */
class IndexMaintenanceModuleController extends AbstractModuleController
{

    /**
     * Module name, used to identify a module f.e. in URL parameters.
     *
     * @var string
     */
    protected $moduleName = 'IndexMaintenance';

    /**
     * Module title, shows up in the module menu.
     *
     * @var string
     */
    protected $moduleTitle = 'Index Maintenance';


    /**
     * Index action, shows an overview of available index maintenance operations.
     *
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * Empties the site's indexes.
     *
     * @return void
     */
    public function emptyIndexAction()
    {
        $siteHash = $this->site->getSiteHash();
        $message = 'Index emptied.';
        $severity = FlashMessage::OK;

        try {
            $solrServers = $this->connectionManager->getConnectionsBySite($this->site);
            foreach ($solrServers as $solrServer) {
                /* @var $solrServer SolrService */
                // make sure maybe not-yet committed documents are committed
                $solrServer->commit();
                $solrServer->deleteByQuery('siteHash:' . $siteHash);
                $solrServer->commit(false, false, false);
            }
        } catch (\Exception $e) {
            $message = '<p>An error occurred while trying to delete documents from the index:</p>'
                . '<p>' . $e->__toString() . '</p>';
            $severity = FlashMessage::ERROR;
        }

        $this->addFlashMessage(
            $message,
            '',
            $severity
        );

        $this->forward('index');
    }

    /**
     * Reloads the site's Solr cores.
     *
     * @return void
     */
    public function reloadIndexConfigurationAction()
    {
        $coresReloaded = true;
        $solrServers = $this->connectionManager->getConnectionsBySite($this->site);

        foreach ($solrServers as $solrServer) {
            /* @var $solrServer SolrService */

            $coreName = array_pop(explode('/',
                trim($solrServer->getPath(), '/')));
            $coreReloaded = $this->reloadCore($solrServer, $coreName);

            if (!$coreReloaded) {
                $coresReloaded = false;

                $this->addFlashMessage(
                    'Failed to reload index configuration for core "' . $coreName . '"',
                    '',
                    FlashMessage::ERROR
                );
                break;
            }
        }

        if ($coresReloaded) {
            $this->addFlashMessage(
                'Core configuration reloaded.',
                '',
                FlashMessage::OK
            );
        }

        $this->forward('index');
    }

    /**
     * Reloads a single Solr core.
     *
     * @param SolrService $solrServer A Solr server connection
     * @param string $coreName Name of the core to reload
     * @return bool TRUE if reloading the core was successful, FALSE otherwise
     */
    protected function reloadCore(SolrService $solrServer, $coreName)
    {
        $coreReloaded = false;

        $path = $solrServer->getPath();
        $pathElements = explode('/', trim($path, '/'));

        $coreAdminReloadUrl =
            $solrServer->getScheme() . '://' .
            $solrServer->getHost() . ':' .
            $solrServer->getPort() . '/' .
            $pathElements[0] . '/' .
            'admin/cores?action=reload&core=' .
            $coreName;

        $httpTransport = $solrServer->getHttpTransport();
        $httpResponse = $httpTransport->performGetRequest($coreAdminReloadUrl);
        $solrResponse = new \Apache_Solr_Response($httpResponse,
            $solrServer->getCreateDocuments(),
            $solrServer->getCollapseSingleValueArrays());

        if ($solrResponse->getHttpStatus() == 200) {
            $coreReloaded = true;
        }

        return $coreReloaded;
    }
}
