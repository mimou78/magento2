<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Model\BlockRepository;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Test caching works for CMS blocks
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class BlockCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
    }

    /**
     * Test that the correct cache tags get added to request for cmsBlocks
     *
     * @magentoDataFixture Magento/Cms/_files/block.php
     */
    public function testCmsBlocksRequestHasCorrectTags(): void
    {
        $blockIdentifier = 'fixture_block';
        $blockRepository = $this->objectManager->get(BlockRepository::class);
        $block = $blockRepository->getById($blockIdentifier);

        $query
            = <<<QUERY
 {
    cmsBlocks(identifiers: ["$blockIdentifier"]) {
        items {
            title
    	    identifier
            content
        }
    }
}
QUERY;
        $request = $this->prepareRequest($query);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block->getId(), 'cms_b_' . $block->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
