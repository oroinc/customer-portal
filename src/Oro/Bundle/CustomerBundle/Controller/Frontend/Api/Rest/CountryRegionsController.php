<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\AddressBundle\Controller\Api\Rest\CountryRegionsController as BaseController;
use Oro\Bundle\AddressBundle\Entity\Country;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("country/regions")
 * @NamePrefix("oro_api_frontend_country_")
 */
class CountryRegionsController extends BaseController
{
    /**
     * REST GET regions by country
     *
     * @param Country $country
     *
     * @ApiDoc(
     *      description="Get regions by country id",
     *      resource=true
     * )
     * @return Response
     */
    public function getAction(Country $country = null)
    {
        return parent::getAction($country);
    }
}
