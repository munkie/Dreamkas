<?php

namespace Lighthouse\ReportsBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Lighthouse\CoreBundle\Document\Classifier\SubCategory\SubCategory;
use Lighthouse\ReportsBundle\Reports\GrossMarginSales\CatalogGroups\GrossMarginSalesByCatalogGroupsCollection;
use Lighthouse\ReportsBundle\Reports\GrossMarginSales\Products\GrossMarginSalesByProductsCollection;
use Lighthouse\ReportsBundle\Reports\GrossMarginSales\GrossMarginSalesReportManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use DateTime;

class GrossMarginSalesController extends FOSRestController
{
    /**
     * @DI\Inject("lighthouse.reports.gross_margin_sales.manager")
     * @var GrossMarginSalesReportManager
     */
    protected $grossMarginSalesReportManager;

    /**
     * @param SubCategory $group
     * @param Request $request
     * @return GrossMarginSalesByProductsCollection
     *
     * @Secure(roles="ROLE_COMMERCIAL_MANAGER")
     * @Rest\Route("catalog/groups/{group}/reports/grossMarginSalesByProduct")
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc()
     */
    public function getCatalogGroupReportsGrossMarginSalesByProductAction(SubCategory $group, Request $request)
    {
        $storeId = $request->get('store');
        $startDate = new DateTime($request->get('startDate', '-1 week 00:00:00'));
        $endDate = new DateTime($request->get('endDate', 'now'));

        if (null !== $storeId) {
            return $this
                ->grossMarginSalesReportManager
                ->getGrossSalesByProductForStoreReports($group, $storeId, $startDate, $endDate);
        } else {
            return $this
                ->grossMarginSalesReportManager
                ->getGrossSalesByProductForSubCategoryReports($group, $startDate, $endDate);
        }
    }

    /**
     * @param Request $request
     * @return GrossMarginSalesByCatalogGroupsCollection
     *
     * @Secure(roles="ROLE_COMMERCIAL_MANAGER")
     * @Rest\Route("catalog/groups/reports/grossMarginSalesByCatalogGroup")
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc()
     */
    public function getCatalogGroupReportsGrossMarginSalesByCatalogGroupAction(Request $request)
    {
        $storeId = $request->get('store');
        $dateFrom = new DateTime($request->get('dateFrom', '-1 week 00:00:00'));
        $dateTo = new DateTime($request->get('dateTo', 'now'));

        return $this->grossMarginSalesReportManager->getCatalogGroupsReports($dateFrom, $dateTo, $storeId);
    }
}
