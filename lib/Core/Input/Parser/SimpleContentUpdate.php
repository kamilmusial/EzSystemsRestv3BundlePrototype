<?php

/**
 * File containing the ContentCreate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\Restv3\Core\Input\Parser;

use eZ\Publish\API\Repository\LocationService;
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
class SimpleContentUpdate extends BaseParser
{
    /**
     * Content service.
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Content service.
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * FieldType parser.
     *
     * @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        FieldTypeParser $fieldTypeParser
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->fieldTypeParser = $fieldTypeParser;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();

        // Missing initial language code

        if (array_key_exists('initialLanguageCode', $data)) {
            $contentUpdateStruct->initialLanguageCode = $data['initialLanguageCode'];
        }

        if (!array_key_exists('fields', $data) || !is_array($data['fields'])) {
            throw new Exceptions\Parser("Missing or invalid 'fields' element for SimpleContentCreate.");
        }

        foreach ($data['fields'] as $fieldDefinitionIdentifier => $value) {
            $contentId = $this->requestParser->parseHref($data['__url'], 'contentId');
            $content = $this->contentService->loadContent($contentId);

            $fieldValue = $this->fieldTypeParser->parseFieldValue(
                $content->id,
                $fieldDefinitionIdentifier,
                $value
            );

            $languageCode =  !array_key_exists('languageCode', $data) ? 'eng-GB' : $data['languageCode'];

            $contentUpdateStruct->setField($fieldDefinitionIdentifier, $fieldValue, $languageCode);
        }

        return $contentUpdateStruct;
    }
}
