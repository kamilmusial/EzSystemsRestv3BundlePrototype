<?php

/**
 * File containing the ContentCreate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\Restv3\Core\Input\Parser;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\REST\Server\Values\RestContentCreateStruct;
use DateTime;

/**
 * Parser for ContentCreate.
 */
class SimpleContentCreate extends BaseParser
{
    /**
     * Content service.
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * ContentType service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * User service.
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * FieldType parser.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * LocationCreate parser.
     *
     * @var \EzSystems\Restv3\Core\Input\Parser\SimpleLocationCreate
     */
    protected $locationCreateParser;

    /**
     * Parser tools.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     * @param \EzSystems\Restv3\Core\Input\Parser\SimpleLocationCreate $locationCreateParser
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        UserService $userService,
        FieldTypeParser $fieldTypeParser,
        SimpleLocationCreate $locationCreateParser,
        ParserTools $parserTools
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
        $this->fieldTypeParser = $fieldTypeParser;
        $this->locationCreateParser = $locationCreateParser;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('ContentLocation', $data) || (!is_numeric($data['ContentLocation']) && !is_string($data['ContentLocation']))) {
            throw new Exceptions\Parser("Missing or invalid 'ContentLocation' element for SimpleContentCreate.");
        }

        $locationCreateStruct = $this->locationCreateParser->parse(['ContentLocation' => $data['ContentLocation']], $parsingDispatcher);

        if (!array_key_exists('ContentType', $data) || (!is_array($data['ContentType']) && !is_string($data['ContentType']))) {
            throw new Exceptions\Parser("Missing or invalid 'ContentType' element for SimpleContentCreate.");
        }

        // @todo: better fallback mechanism
        $mainLanguageCode =  !array_key_exists('mainLanguageCode', $data) ? 'eng-GB' : $data['mainLanguageCode'];

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($data['ContentType']);

        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $mainLanguageCode);

        if (array_key_exists('ContentSection', $data) && (is_array($data['ContentSection']) || is_string($data['ContentSection']))) {
            $contentCreateStruct->sectionId = $data['ContentSection'];
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $contentCreateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('remoteId', $data)) {
            $contentCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('modificationDate', $data)) {
            $contentCreateStruct->modificationDate = new DateTime($data['modificationDate']);
        }

        if (array_key_exists('User', $data)) {
            $userId = $data['User'];
            if (is_string($data['User'])) {
                $userId = $this->userService->loadUserByLogin($data['User'])->getUserId();
            }

            $contentCreateStruct->ownerId = $userId;
        }

        if (!array_key_exists('fields', $data) || !is_array($data['fields'])) {
            throw new Exceptions\Parser("Missing or invalid 'fields' element for SimpleContentCreate.");
        }

        foreach ($data['fields'] as $fieldDefinitionIdentifier => $value) {
            $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);
            if (!$fieldDefinition) {
                throw new Exceptions\Parser(
                    "'{$fieldDefinitionIdentifier}' is invalid field definition identifier for '{$contentType->identifier}' content type in SimpleContentCreate."
                );
            }

            $fieldValue = $this->fieldTypeParser->parseValue($fieldDefinition->fieldTypeIdentifier, $value);

            $contentCreateStruct->setField($fieldDefinitionIdentifier, $fieldValue, $mainLanguageCode);
        }

        return new RestContentCreateStruct($contentCreateStruct, $locationCreateStruct);
    }
}
