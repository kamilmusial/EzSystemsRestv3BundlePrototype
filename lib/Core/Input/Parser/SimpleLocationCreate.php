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
class SimpleLocationCreate extends BaseParser
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
        $parentLocationId = $data['ContentLocation'];
        if (is_string($data['ContentLocation'])) {
            $urlAlias = $this->URLAliasService->lookup($data['ContentLocation']);
            $parentLocationId = $urlAlias->destination;
        }
        $parentLocation = $this->locationService->loadLocation($parentLocationId);

        $locationCreateStruct = $this->locationService->newLocationCreateStruct(
            $parentLocation->id
        );

        // @todo: metadata

//        if (array_key_exists('priority', $data)) {
//            $locationCreateStruct->priority = (int)$data['priority'];
//        }
//
//        if (array_key_exists('hidden', $data)) {
//            $locationCreateStruct->hidden = $this->parserTools->parseBooleanValue($data['hidden']);
//        }
//
//        if (array_key_exists('remoteId', $data)) {
//            $locationCreateStruct->remoteId = $data['remoteId'];
//        }

//        if (!array_key_exists('sortField', $data)) {
//            throw new Exceptions\Parser("Missing 'sortField' element for LocationCreate.");
//        }
//
//        $locationCreateStruct->sortField = $this->parserTools->parseDefaultSortField($data['sortField']);
//
//        if (!array_key_exists('sortOrder', $data)) {
//            throw new Exceptions\Parser("Missing 'sortOrder' element for LocationCreate.");
//        }
//
//        $locationCreateStruct->sortOrder = $this->parserTools->parseDefaultSortOrder($data['sortOrder']);

        return $locationCreateStruct;
    }
}
