<?php

namespace EzSystems\Restv3\Core\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * SimpleRestContent value object visitor.
 */
class SimpleRestContent extends ValueObjectVisitor
{
    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    /**
     * @var \eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry
     */
    protected $fieldTypeProcessorRegistry;

    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * @param \eZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     * @param \eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry $fieldTypeProcessorRegistry
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     */
    public function __construct(
        FieldTypeService $fieldTypeService,
        FieldTypeProcessorRegistry $fieldTypeProcessorRegistry,
        URLAliasService $urlAliasService
    ) {
        $this->fieldTypeService = $fieldTypeService;
        $this->fieldTypeProcessorRegistry = $fieldTypeProcessorRegistry;
        $this->urlAliasService = $urlAliasService;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestContent $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $restContent = $data;
        $contentType = $restContent->contentType;
        $mainLocation = $restContent->mainLocation;
        $currentVersion = $restContent->currentVersion;
        $lang = $data->contentInfo->mainLanguageCode;

        $mediaType = ($restContent->currentVersion === null ? 'ContentInfo' : 'Content');

        $generator->startObjectElement('Content', $mediaType);

        $visitor->setHeader('Content-Type', $generator->getMediaType($mediaType));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('ContentUpdate'));

        $generator->startValueElement('ContentType', $contentType->identifier);
        $generator->endValueElement('ContentType');

        $generator->startValueElement('ContentTypeName', $contentType->getName($lang));
        $generator->endValueElement('ContentTypeName');

        $generator->startHashElement('fields');

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            $fieldType = $this->fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier);
            $hash = '';
            if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldDefinition->fieldTypeIdentifier)) {
                $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldDefinition->fieldTypeIdentifier);
                $hash = $processor->postProcessValueHash($fieldType->toHash($currentVersion->getFieldValue($fieldDefinition->identifier, $lang)));
            }

            $generator->startValueElement($fieldDefinition->identifier, $hash);
            $generator->endValueElement($fieldDefinition->identifier);
        }

        $generator->endHashElement('fields');

        if ($data->mainLocation !== null) {
            $generator->startValueElement('Location', $this->urlAliasService->reverseLookup($mainLocation)->path);
            $generator->endValueElement('Location');
        }
        $generator->endObjectElement('Content');
    }
}
