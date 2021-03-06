<?php

namespace Lighthouse\CoreBundle\Tests\Controller;

use Guzzle\Plugin\Mock\MockPlugin;
use Lighthouse\CoreBundle\Document\File\FileUploader;
use Lighthouse\CoreBundle\Test\Assert;
use Lighthouse\CoreBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderControllerTest extends WebTestCase
{
    public function testPostOrderAction()
    {
        $store = $this->factory()->store()->getStore();
        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));
        $supplier = $this->factory()->supplier()->getSupplier();

        $orderProducts = array(
            array(
                'product' => $products['1']->id,
                'quantity' => 3,
            ),
            array(
                'product' => $products['2']->id,
                'quantity' => 2,
            ),
            array(
                'product' => $products['3']->id,
                'quantity' => 5,
            ),
            array(
                'product' => $products['1']->id,
                'quantity' => 1,
            ),
        );

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => $orderProducts,
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            "/api/1/stores/{$store->id}/orders",
            $orderData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonPathEquals($supplier->id, 'supplier.id', $response);
        Assert::assertJsonPathEquals(10001, 'number', $response);
        foreach ($orderProducts as $orderProduct) {
            Assert::assertJsonPathEquals($orderProduct['quantity'], 'products.*.quantity', $response);
            Assert::assertJsonPathEquals($orderProduct['product'], 'products.*.product.product.id', $response);
        }

        $this->assertOrder($accessToken, $store->id, $response['id'], $supplier->id, $orderProducts);
    }

    public function testPostOrderEmptyProductsValidation()
    {
        $storeId = $this->factory()->store()->getStoreId();
        $this->factory()->catalog()->getProduct();
        $supplier = $this->factory()->supplier()->getSupplier();
        $this->factory()->flush();

        $postData = array(
            'supplier' => $supplier->id,
            'products' => array(),
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($storeId);
        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores/' . $storeId . '/orders',
            $postData
        );

        $this->assertResponseCode(400);
        Assert::assertJsonPathEquals('Нужно добавить минимум один товар', 'errors.errors.0', $response);
    }

    /**
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     *
     * @dataProvider validationProvider
     */
    public function testPostOrderValidation($expectedCode, array $data, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();
        $supplier = $this->factory()->supplier()->getSupplier();
        $this->factory()->flush();

        $postData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'product' => $product->id,
                    'quantity' => 1.11,
                )
            ),
        );

        if (array_key_exists('products', $data)) {
            $postData['products'][0] = $data['products'][0] + $postData['products'][0];
            unset($data['products']);
        }

        $postData = $data + $postData;

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            "/api/1/stores/{$store->id}/orders",
            $postData
        );

        $this->assertResponseCode($expectedCode);
        $this->performJsonAssertions($response, $assertions, true);
    }

    /**
     * @return array
     */
    public function validationProvider()
    {
        return array(
            /***********************************************************************************************
             * 'quantity'
             ***********************************************************************************************/
            'valid quantity 7' => array(
                201,
                array('products' => array(array('quantity' => 7))),
            ),
            'empty quantity' => array(
                400,
                array('products' => array(array('quantity' => ''))),
                array('errors.children.products.children.0.children.quantity.errors.0' => 'Заполните это поле')
            ),
            'negative quantity -10' => array(
                400,
                array('products' => array(array('quantity' => -10))),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть больше 0'
                )
            ),
            'negative quantity -1' => array(
                400,
                array('products' => array(array('quantity' => -1))),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть больше 0'
                )
            ),
            'zero quantity' => array(
                400,
                array('products' => array(array('quantity' => 0))),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть больше 0'
                )
            ),
            'float quantity' => array(
                201,
                array('products' => array(array('quantity' => 2.5))),
            ),
            'float quantity with coma' => array(
                201,
                array('products' => array(array('quantity' => '2,5'))),
            ),
            'float quantity very float' => array(
                400,
                array('products' => array(array('quantity' => 2.5555))),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой'
                )
            ),
            'float quantity very float with coma' => array(
                400,
                array('products' => array(array('quantity' => '2,5555'))),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой',
                )
            ),
            'float quantity very float only one message' => array(
                400,
                array('products' => array(array('quantity' => '2,5555'))),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой',
                    'errors.children.products.children.0.children.quantity.errors.1'
                    =>
                    null
                )
            ),
            'not numeric quantity' => array(
                400,
                array('products' => array(array('quantity' => 'abc'))),
                array('errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть числом')
            ),
            /***********************************************************************************************
             * 'product'
             ***********************************************************************************************/
            'not valid product' => array(
                400,
                array('products' => array(array('product' => 'not_valid_product_id'))),
                array('errors.children.products.children.0.children.product.errors.0' => 'Такого товара не существует'),
            ),
            'empty product' => array(
                400,
                array('products' => array(array('product' => ''))),
                array('errors.children.products.children.0.children.product.errors.0' => 'Заполните это поле'),
            ),
            /***********************************************************************************************
             * 'supplier'
             ***********************************************************************************************/
            'not valid supplier' => array(
                400,
                array('supplier' => 'notExists'),
                array('errors.children.supplier.errors.0' => 'Такого поставщика не существует'),
            ),
            'empty supplier' => array(
                400,
                array('supplier' => ''),
                array('errors.children.supplier.errors.0' => 'Выберите поставщика'),
            ),
        );
    }

    public function testGerOrdersAction()
    {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');

        $product = $this->factory()->catalog()->getProduct();

        $supplier1 = $this->factory()->supplier()->getSupplier('Перевоз1');
        $supplier2 = $this->factory()->supplier()->getSupplier('Перевоз2');
        $supplier3 = $this->factory()->supplier()->getSupplier('Перевоз3');

        $this->factory()
            ->order()
                ->createOrder($store1, $supplier1, '2014-02-14 04:05:06')
                ->createOrderProduct($product->id)
            ->persist()
                ->createOrder($store1, $supplier2, '2014-02-13 04:05:06')
                ->createOrderProduct($product->id)
            ->persist()
                ->createOrder($store1, $supplier3, '2014-02-13 14:05:06')
                ->createOrderProduct($product->id)
            ->persist()
                ->createOrder($store2, $supplier1)
                ->createOrderProduct($product->id)
            ->persist()
                ->createOrder($store2, $supplier3, date('r', time() + 120))
                ->createOrderProduct($product->id)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store1->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store1->id}/orders"
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathCount(3, '*.id', $response);
        Assert::assertJsonPathEquals($supplier1->name, '0.supplier.name', $response, 1);
        Assert::assertJsonPathEquals($supplier2->name, '2.supplier.name', $response, 1);
        Assert::assertJsonPathEquals($supplier3->name, '1.supplier.name', $response, 1);

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store2->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            '/api/1/stores/' . $store2->id . '/orders'
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathCount(2, '*.id', $response);
        Assert::assertJsonPathEquals($supplier1->name, '1.supplier.name', $response, 1);
        Assert::assertJsonPathEquals($supplier3->name, '0.supplier.name', $response, 1);

        Assert::assertJsonPathEquals($supplier2->name, '*.supplier.name', $response, 0);
    }

    /**
     * @dataProvider gerOrdersFilterProvider
     * @param array $filter
     * @param array $expectedOrders
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function testGerOrdersFilter(array $filter, array $expectedOrders)
    {
        $store = $this->factory()->store()->getStore();
        $productId = $this->factory()->catalog()->getProductByName()->id;
        $supplier = $this->factory()->supplier()->getSupplier();

        $order1 = $this->factory()
            ->order()
                ->createOrder($store, $supplier, '2014-02-13 04:05:06')
                ->createOrderProduct($productId, 1)
            ->flush();

        $this->factory()
            ->order()
                ->createOrder($store, $supplier, '2014-02-13 04:05:06')
                ->createOrderProduct($productId, 2)
            ->flush();

        $order3 = $this->factory()
            ->order()
                ->createOrder($store, $supplier, '2014-02-13 14:05:06')
                ->createOrderProduct($productId, 3)
            ->flush();

        $this->factory()
            ->invoice()
                ->createInvoice(array(), $store->id, $supplier->id, $order1->id)
                ->createInvoiceProduct($productId, 1, 5.99)
            ->persist()
                ->createInvoice(array(), $store->id, $supplier->id, $order3->id)
                ->createInvoiceProduct($productId, 2, 6.99)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/orders",
            null,
            $filter
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathCount(count($expectedOrders), '*.id', $response);
        foreach ($expectedOrders as $index => $expectedOrderNumber) {
            Assert::assertJsonPathEquals($expectedOrderNumber, $index . '.number', $response);
        }
    }

    /**
     * @return array
     */
    public function gerOrdersFilterProvider()
    {
        return array(
            'incomplete' => array(
                array('incomplete' => '1'),
                array('10002'),
            ),
            'all' => array(
                array(),
                array('10003', '10002', '10001'),
            ),
        );
    }

    /**
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     *
     * @dataProvider orderProductValidationProvider
     */
    public function testPostOrderProductValidation($expectedCode, array $data, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();

        $postData = $data + array(
            'product' => $product->id,
            'quantity' => 1.11,
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            "/api/1/stores/{$store->id}/orders/products?validate=true",
            $postData
        );

        $this->assertResponseCode($expectedCode);
        $this->performJsonAssertions($response, $assertions, true);
    }

    /**
     * @return array
     */
    public function orderProductValidationProvider()
    {
        return array(
            /***********************************************************************************************
             * 'quantity'
             ***********************************************************************************************/
            'valid quantity 7' => array(
                200,
                array('quantity' => 7),
            ),
            'empty quantity' => array(
                400,
                array('quantity' => ''),
                array('errors.children.quantity.errors.0' => 'Заполните это поле')
            ),
            'negative quantity -10' => array(
                400,
                array('quantity' => -10),
                array('errors.children.quantity.errors.0' => 'Значение должно быть больше 0')
            ),
            'negative quantity -1' => array(
                400,
                array('quantity' => -1),
                array('errors.children.quantity.errors.0' => 'Значение должно быть больше 0')
            ),
            'zero quantity' => array(
                400,
                array('quantity' => 0),
                array('errors.children.quantity.errors.0' => 'Значение должно быть больше 0')
            ),
            'float quantity' => array(
                200,
                array('quantity' => 2.5),
            ),
            'float quantity with coma' => array(
                200,
                array('quantity' => '2,5'),
            ),
            'float quantity very float' => array(
                400,
                array('quantity' => 2.5555),
                array(
                    'errors.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой'
                )
            ),
            'float quantity very float with coma' => array(
                400,
                array('quantity' => '2,5555'),
                array(
                    'errors.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой',
                )
            ),
            'float quantity very float only one message' => array(
                400,
                array('quantity' => '2,5555'),
                array(
                    'errors.children.quantity.errors.0' => 'Значение не должно содержать больше 3 цифр после запятой',
                    'errors.children.quantity.errors.1' => null
                )
            ),
            'not numeric quantity' => array(
                400,
                array('quantity' => 'abc'),
                array('errors.children.quantity.errors.0' => 'Значение должно быть числом')
            ),
            /***********************************************************************************************
             * 'product'
             ***********************************************************************************************/
            'not valid product' => array(
                400,
                array('product' => 'not_valid_product_id'),
                array('errors.children.product.errors.0' => 'Такого товара не существует'),
            ),
            'empty product' => array(
                400,
                array('product' => ''),
                array('errors.children.product.errors.0' => 'Заполните это поле'),
            ),
        );
    }

    public function testOrderProductVersion()
    {
        $product = $this->factory()->catalog()->getProduct('original');
        $supplier = $this->factory()->supplier()->getSupplier();
        $this->factory()->flush();

        $postData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'product' => $product->id,
                    'quantity' => 1.11,
                )
            ),
        );
        $store = $this->factory()->store()->getStore();
        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            "/api/1/stores/{$store->id}/orders",
            $postData
        );

        $this->assertResponseCode(201);
        Assert::assertJsonPathEquals('original', 'products.0.product.product.name', $postResponse);
        Assert::assertJsonHasPath('id', $postResponse);

        $orderId = $postResponse['id'];

        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/orders/{$orderId}"
        );

        $this->assertResponseCode(200);
        Assert::assertJsonPathEquals('original', 'products.0.product.product.name', $getResponse);

        $this->updateProduct($product->id, array('name' => 'modified'));

        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/orders/{$orderId}"
        );

        $this->assertResponseCode(200);
        Assert::assertJsonPathEquals('original', 'products.0.product.product.name', $getResponse);
    }

    public function testOrderNumberCreation()
    {
        $store = $this->factory()->store()->getStore();
        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));
        $supplier = $this->factory()->supplier()->getSupplier();

        $orderProducts = array(
            array(
                'product' => $products['1']->id,
                'quantity' => 3,
            ),
            array(
                'product' => $products['2']->id,
                'quantity' => 2,
            ),
            array(
                'product' => $products['3']->id,
                'quantity' => 5,
            ),
            array(
                'product' => $products['1']->id,
                'quantity' => 1,
            ),
        );

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => $orderProducts,
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            "/api/1/stores/{$store->id}/orders",
            $orderData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonPathEquals(10001, 'number', $response);

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            "/api/1/stores/{$store->id}/orders",
            $orderData
        );

        $this->assertResponseCode(201);
        Assert::assertJsonPathEquals(10002, 'number', $response);
    }

    public function testPutOrderAction()
    {
        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));

        $store = $this->factory()->store()->getStore();
        $supplier = $this->factory()->supplier()->getSupplier();

        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($products['1']->id, 10)
                ->createOrderProduct($products['2']->id, 20)
                ->createOrderProduct($products['3']->id, 30)
            ->flush();

        $this->assertEquals(10001, $order->number);

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'id' => $order->products[0]->id,
                    'product' => $products['1']->id,
                    'quantity' => 20,
                ),
                array(
                    'product' => $products['2']->id,
                    'quantity' => 35,
                ),
            )
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/stores/{$store->id}/orders/{$order->id}",
            $orderData
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals('10001', 'number', $putResponse);
        Assert::assertJsonPathCount(2, 'products.*.id', $putResponse);
        Assert::assertJsonPathEquals($order->products[0]->id, 'products.0.id', $putResponse);
        Assert::assertJsonPathEquals($order->products[1]->id, 'products.1.id', $putResponse);
        Assert::assertNotJsonPathEquals($order->products[2]->id, 'products.*.id', $putResponse, 0);

        Assert::assertJsonPathEquals($products['1']->id, 'products.0.product.product.id', $putResponse);
        Assert::assertJsonPathEquals(20, 'products.0.quantity', $putResponse);

        Assert::assertJsonPathEquals($products['2']->id, 'products.1.product.product.id', $putResponse);
        Assert::assertJsonPathEquals(35, 'products.1.quantity', $putResponse);


        $this->assertOrder($accessToken, $store->id, $order->id, $supplier->id, $orderData['products']);
    }

    public function testPutOrderActionInvalidStore()
    {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');

        $product = $this->factory()->catalog()->getProduct('1');
        $supplier = $this->factory()->supplier()->getSupplier();

        $order = $this->factory()
            ->order()
                ->createOrder($store1, $supplier)
                ->createOrderProduct($product->id, 10)
            ->flush();

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'id' => $order->products[0]->id,
                    'product' => $product->id,
                    'quantity' => 20,
                ),
            )
        );

        $departmentManager = $this->factory()->store()->getDepartmentManager($store1->id);
        $this->factory()->store()->linkDepartmentManagers($departmentManager->id, $store2->id);

        $accessToken = $this->factory()->oauth()->auth($departmentManager);

        $this->client->setCatchException();
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/stores/{$store2->id}/orders/{$order->id}",
            $orderData
        );

        $this->assertResponseCode(404);

        Assert::assertJsonPathContains('Order object not found', 'message', $putResponse);
    }

    public function testGetOrderActionInvalidStore()
    {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');
        $product = $this->factory()->catalog()->getProduct('1');
        $supplier = $this->factory()->supplier()->getSupplier();
        $order = $this->factory()
            ->order()
                ->createOrder($store1, $supplier)
                ->createOrderProduct($product->id, 10)
            ->flush();

        $departmentManager = $this->factory()->store()->getDepartmentManager($store1->id);
        $this->factory()->store()->linkDepartmentManagers($departmentManager->id, $store2->id);

        $accessToken = $this->factory()->oauth()->auth($departmentManager);

        $this->client->setCatchException();
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store2->id}/orders/{$order->id}"
        );

        $this->assertResponseCode(404);

        Assert::assertJsonPathContains('Order object not found', 'message', $putResponse);
    }

    public function testDeleteOrderActionInvalidStore()
    {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');

        $product = $this->factory()->catalog()->getProduct('1');
        $supplier = $this->factory()->supplier()->getSupplier();

        $order = $this->factory()
            ->order()
                ->createOrder($store1, $supplier)
                ->createOrderProduct($product->id, 10)
            ->flush();

        $departmentManager = $this->factory()->store()->getDepartmentManager($store1->id);
        $this->factory()->store()->linkDepartmentManagers($departmentManager->id, $store2->id);

        $accessToken = $this->factory()->oauth()->auth($departmentManager);

        $this->client->setCatchException();
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'DELETE',
            "/api/1/stores/{$store2->id}/orders/{$order->id}"
        );

        $this->assertResponseCode(404);

        Assert::assertJsonPathContains('Order object not found', 'message', $putResponse);
    }

    public function testDeleteAction()
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();
        $supplier = $this->factory()->supplier()->getSupplier();
        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($product->id, 10)
            ->flush();

        $this->authenticateProject();
        $orderProductRepository = $this->getContainer()->get('lighthouse.core.document.repository.order_product');
        $this->assertCount(1, $orderProductRepository->findAll());

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $deleteResponse = $this->clientJsonRequest(
            $accessToken,
            'DELETE',
            "/api/1/stores/{$store->id}/orders/{$order->id}"
        );

        $this->assertResponseCode(204);

        $this->assertEmpty($deleteResponse);

        $this->assertCount(0, $orderProductRepository->findAll());
    }

    public function testDeleteWithInvoice()
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();
        $supplier = $this->factory()->supplier()->getSupplier();
        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($product->id, 10)
            ->flush();

        $this->factory()
            ->invoice()
                ->createInvoice(array(), $store->id, $supplier->id, $order->id)
                ->createInvoiceProduct($product->id, 10, 5.99)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);

        $this->client->setCatchException();
        $deleteResponse = $this->clientJsonRequest(
            $accessToken,
            'DELETE',
            "/api/1/stores/{$store->id}/orders/{$order->id}"
        );

        $this->assertResponseCode(409);

        Assert::assertJsonHasPath('message', $deleteResponse);
    }

    public function testDownloadOrderAction()
    {
        $store = $this->factory()->store()->getStore();
        $supplier = $this->factory()->supplier()->getSupplier();
        $product1 = $this->factory()->catalog()->getProduct('Кефир1Назв');
        $product2 = $this->factory()->catalog()->getProduct('Кефир2Назв');
        $product3 = $this->factory()->catalog()->getProduct('Кефир3Назв');

        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($product1->id, 3.11)
                ->createOrderProduct($product2->id, 2)
                ->createOrderProduct($product3->id, 7.77)
            ->flush();

        $mockPlugin = new MockPlugin();
        $mockPlugin->addResponse($this->getFixtureFilePath('OpenStack/auth.response.ok'));
        $mockPlugin->addResponse($this->getFixtureFilePath('OpenStack/container.response.ok'));
        $mockPlugin->addResponse($this->getFixtureFilePath('OpenStack/upload.response.ok'));
        $mockPlugin->addResponse($this->getFixtureFilePath('OpenStack/head.response.ok'));

        $mockGuzzle = function (ContainerInterface $container) use ($mockPlugin) {
            $client = $container->get('openstack.selectel');
            $client->addSubscriber($mockPlugin);
        };

        $mockFile = $this->getFixtureFilePath('OpenStack/auth.response.ok');
        $requestGetContentMock = function (ContainerInterface $container) use ($mockFile) {
            /* @var FileUploader $uploader */
            $uploader = $container->get('lighthouse.core.document.repository.file.uploader');
            $uploader->setFileResource(fopen($mockFile, 'rb'));
        };

        $this->client->addTweaker($mockGuzzle);
        $this->client->addTweaker($requestGetContentMock);

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/orders/{$order->id}/download"
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals("order{$order->number}.xlsx", 'name', $response);
        Assert::assertJsonHasPath('url', $response);
    }

    public function testPutOrderActionChangeProduct()
    {
        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));
        $store = $this->factory()->store()->getStore();
        $supplier = $this->factory()->supplier()->getSupplier();
        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($products['1']->id, 10)
                ->createOrderProduct($products['2']->id, 20)
            ->flush();

        $this->assertEquals(10001, $order->number);

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'product' => $products['1']->id,
                    'quantity' => 20,
                ),
                array(
                    'product' => $products['3']->id,
                    'quantity' => 35,
                ),
            )
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/stores/{$store->id}/orders/{$order->id}",
            $orderData
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals('10001', 'number', $putResponse);
        Assert::assertJsonPathCount(2, 'products.*.id', $putResponse);
        Assert::assertJsonPathEquals($order->products[0]->id, 'products.0.id', $putResponse);
        Assert::assertJsonPathEquals($order->products[1]->id, 'products.1.id', $putResponse);
        Assert::assertNotJsonPathEquals($products['2']->id, 'products.*.product.product.id', $putResponse);

        Assert::assertJsonPathEquals($products['1']->id, 'products.0.product.product.id', $putResponse);
        Assert::assertJsonPathEquals(20, 'products.0.quantity', $putResponse);

        Assert::assertJsonPathEquals($products['3']->id, 'products.1.product.product.id', $putResponse);
        Assert::assertJsonPathEquals(35, 'products.1.quantity', $putResponse);


        $this->assertOrder($accessToken, $store->id, $order->id, $supplier->id, $orderData['products']);
    }

    public function testPutOrderRemoveProduct()
    {
        $product1 = $this->factory()->catalog()->getProduct('1');
        $product2 = $this->factory()->catalog()->getProduct('2');

        $store = $this->factory()->store()->getStore();
        $supplier = $this->factory()->supplier()->getSupplier();
        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($product1->id, 10)
                ->createOrderProduct($product2->id, 20)
            ->flush();

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'product' => $product2->id,
                    'quantity' => 20,
                ),
            )
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/stores/{$store->id}/orders/{$order->id}",
            $orderData
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals('10001', 'number', $putResponse);
        Assert::assertJsonPathCount(1, 'products.*.id', $putResponse);
        Assert::assertJsonPathEquals($order->products[0]->id, 'products.*.id', $putResponse);
        Assert::assertNotJsonPathEquals($product1->id, 'products.*.product.product.id', $putResponse);

        $this->assertOrder($accessToken, $store->id, $order->id, $supplier->id, $orderData['products']);
    }

    public function testPutOrderAddProduct()
    {
        $product1 = $this->factory()->catalog()->getProduct('1');
        $product2 = $this->factory()->catalog()->getProduct('2');

        $store = $this->factory()->store()->getStore();
        $supplier = $this->factory()->supplier()->getSupplier();
        $order = $this->factory()
            ->order()
                ->createOrder($store, $supplier)
                ->createOrderProduct($product1->id, 10)
            ->flush();

        $orderData = array(
            'supplier' => $supplier->id,
            'products' => array(
                array(
                    'product' => $product1->id,
                    'quantity' => 10,
                ),
                array(
                    'product' => $product2->id,
                    'quantity' => 15,
                ),
            )
        );

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/stores/{$store->id}/orders/{$order->id}",
            $orderData
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals('10001', 'number', $putResponse);
        Assert::assertJsonPathCount(2, 'products.*.id', $putResponse);
        Assert::assertJsonPathEquals($product1->id, 'products.*.product.product.id', $putResponse);
        Assert::assertJsonPathEquals(10, 'products.*.quantity', $putResponse);
        Assert::assertJsonPathEquals($product2->id, 'products.*.product.product.id', $putResponse);
        Assert::assertJsonPathEquals(15, 'products.*.quantity', $putResponse);

        $this->assertOrder($accessToken, $store->id, $order->id, $supplier->id, $orderData['products']);
    }
}
