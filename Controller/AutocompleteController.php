<?php

namespace Pintushi\Bundle\FormBundle\Controller;

use Pintushi\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Pintushi\Bundle\FormBundle\Autocomplete\Security;
use Pintushi\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Pintushi\Bundle\FormBundle\Model\AutocompleteRequest;
use Pintushi\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AutocompleteController extends AbstractController
{
    private $security;

    private $searchRegistry;

    private $validator;

    public function __construct(
        Security $security,
        SearchRegistry $searchRegistry,
        ValidatorInterface $validator
    ) {
        $this->security = $security;
        $this->searchRegistry = $searchRegistry;
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws HttpException|AccessDeniedHttpException
     *
     * @Route(
     *     "autocomplete/search",
     *     name="pintushi_form_autocomplete_search"
     * )
     * AclAncestor("pintushi_search")
     */
    public function searchAction(Request $request)
    {
        $autocompleteRequest = new AutocompleteRequest($request);
        $isXmlHttpRequest    = $request->isXmlHttpRequest();
        $code                = 200;
        $result              = [
            'results' => [],
            'hasMore' => false,
            'errors'  => []
        ];

        if ($violations = $this->validator->validate($autocompleteRequest)) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $result['errors'][] = $violation->getMessage();
            }
        }

        if (!$this->security->isAutocompleteGranted($autocompleteRequest->getName())) {
            $result['errors'][] = 'Access denied.';
        }

        if (!empty($result['errors'])) {
            if ($isXmlHttpRequest) {
                return new JsonResponse($result, $code);
            }

            throw new HttpException($code, implode(', ', $result['errors']));
        }

        /** @var SearchHandlerInterface $searchHandler */
        $searchHandler = $this->searchRegistry
            ->getSearchHandler($autocompleteRequest->getName())
        ;

        return new JsonResponse(
            $searchHandler->search(
                $autocompleteRequest->getQuery(),
                $autocompleteRequest->getPage(),
                $autocompleteRequest->getPerPage(),
                $autocompleteRequest->isSearchById()
            )
        );
    }
}
