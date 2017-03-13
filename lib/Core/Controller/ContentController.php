<?php

namespace EzSystems\Restv3\Core\Controller;

use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Controller\Content;
use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentValidationException;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use eZ\Publish\Core\REST\Server\Exceptions\ContentFieldValidationException as RESTContentFieldValidationException;
use eZ\Publish\Core\REST\Server\Values;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends Content
{
    public function updateContent($locationId, Request $request)
    {
        // waiting for spec clarification
    }

    public function updateFields($locationId, Request $request)
    {
        $contentService = $this->repository->getContentService();

        $location = $this->repository->getLocationService()->loadLocation($locationId);
        $content = $contentService->loadContent($location->contentId);
        $contentInfo = $content->contentInfo;
        $draft = $contentService->createContentDraft($contentInfo);

        $contentUpdateStruct = $this->inputDispatcher->parse(
            new Message([
                    'Content-Type' => $request->headers->get('Content-Type'),
                    'Url' => $this->router->generate(
                        'ezpublish_rest_loadContent',
                        array(
                            'contentId' => $content->id,
                        )
                    ),
                ],
                $request->getContent()
            )
        );

        $draft = $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);

        try {
            $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new RESTContentFieldValidationException($e);
        }

        $content = $contentService->publishVersion($draft->versionInfo);

        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\Version(
            $content,
            $contentType,
            $contentService->loadRelations($content->getVersionInfo()),
            $request->getPathInfo()
        );
    }
}
