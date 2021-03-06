<?php

/**
 * File containing the LocationCreate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\Restv3\Core\Input\Parser;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\LocationService;

/**
 * Parser for LocationCreate.
 */
class LocationCreate extends BaseParser
{
    /**
     * Location service.
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * URL Alias service.
     *
     * @var \eZ\Publish\API\Repository\UrlAliasService
     */
    protected $URLAliasService;
    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;



    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\URLAliasService $URLAliasService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(LocationService $locationService, URLAliasService $URLAliasService, ParserTools $parserTools)
    {
        $this->locationService = $locationService;
        $this->URLAliasService = $URLAliasService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $locationCreate = $data['LocationCreate'];

        if (array_key_exists('_parentId', $locationCreate)) {
            $parentLocation = $this->locationService->loadLocation($locationCreate['_parentId']);
        } elseif (array_key_exists('_parentUrlAliasPath', $locationCreate)) {
            $urlAlias = $this->URLAliasService->lookup($locationCreate['_parentUrlAliasPath']);
            $parentLocationId = $urlAlias->destination;
            $parentLocation = $this->locationService->loadLocation($parentLocationId);
        } else {
            throw new Exceptions\Parser("Missing 'parentId' or parentUrlAliasPath element for LocationCreate.");
        }

        $locationCreateStruct = $this->locationService->newLocationCreateStruct(
            $parentLocation->id
        );

        if (array_key_exists('priority', $locationCreate)) {
            $locationCreateStruct->priority = (int)$locationCreate['priority'];
        }

        if (array_key_exists('hidden', $locationCreate)) {
            $locationCreateStruct->hidden = $this->parserTools->parseBooleanValue($locationCreate['hidden']);
        }

        if (array_key_exists('remoteId', $locationCreate)) {
            $locationCreateStruct->remoteId = $locationCreate['remoteId'];
        }

        if (array_key_exists('sortField', $locationCreate)) {
            $locationCreateStruct->sortField = $this->parserTools->parseDefaultSortField($locationCreate['sortField']);
        }

        if (array_key_exists('sortOrder', $locationCreate)) {
            $locationCreateStruct->sortOrder = $this->parserTools->parseDefaultSortOrder($locationCreate['sortOrder']);
        }


        return $locationCreateStruct;
    }
}
