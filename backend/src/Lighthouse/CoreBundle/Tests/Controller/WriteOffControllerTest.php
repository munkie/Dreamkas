<?php

namespace Lighthouse\CoreBundle\Tests\Controller;

use Lighthouse\CoreBundle\Document\StockMovement\StockMovementProductRepository;
use Lighthouse\CoreBundle\Document\StockMovement\WriteOff\WriteOffRepository;
use Lighthouse\CoreBundle\Document\User\User;
use Lighthouse\CoreBundle\Test\Assert;
use Lighthouse\CoreBundle\Test\Client\Request\WriteOffBuilder;
use Lighthouse\CoreBundle\Test\WebTestCase;

class WriteOffControllerTest extends WebTestCase
{
    public function testPostAction()
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();
        $date = strtotime('-1 day');

        $writeOffData = WriteOffBuilder::create(date('c', $date), $store->id)
            ->addProduct($product->id)
            ->toArray();

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $writeOffData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $postResponse);
        Assert::assertJsonPathCount(1, 'products.*.product', $postResponse);
        Assert::assertJsonPathEquals('10001', 'number', $postResponse);
        Assert::assertJsonPathContains(date('Y-m-d\TH:i', $date), 'date', $postResponse);
        Assert::assertJsonPathEquals($store->id, 'store.id', $postResponse);
    }

    /**
     * @dataProvider validationWriteOffProvider
     *
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     */
    public function testPostWriteOffValidation($expectedCode, array $data, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();
        $writeOffData = WriteOffBuilder::create('2012-07-11', $store->id)
            ->addProduct($product->id)
            ->toArray($data);

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $writeOffData
        );

        $this->assertResponseCode($expectedCode);

        foreach ($assertions as $path => $expected) {
            Assert::assertJsonPathContains($expected, $path, $postResponse);
        }
    }

    /**
     * @dataProvider validationWriteOffProvider
     *
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     */
    public function testPutWriteOffValidation($expectedCode, array $data, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();
        $postData = WriteOffBuilder::create('11.07.2012', $store->id)
            ->addProduct($product->id)
            ->toArray();

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $postData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $postResponse);

        $writeOffId = $postResponse['id'];

        $putData = $data + $postData;

        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/writeOffs/{$writeOffId}",
            $putData
        );

        $expectedCode = ($expectedCode == 201) ? 200 : $expectedCode;

        $this->assertResponseCode($expectedCode);

        foreach ($assertions as $path => $expected) {
            Assert::assertJsonPathContains($expected, $path, $putResponse);
        }
    }

    /**
     * @return array
     */
    public function validationWriteOffProvider()
    {
        return array(
            'not valid empty date' => array(
                400,
                array('date' => ''),
                array(
                    'errors.children.date.errors.0' => 'Заполните это поле'
                )
            ),
            'valid date' => array(
                201,
                array('date' => '2013-12-31')
            ),
            'not valid date' => array(
                400,
                array('date' => '2013-2sd-31'),
                array(
                    'errors.children.date.errors.0'
                    =>
                    'Вы ввели неверную дату 2013-2sd-31, формат должен быть следующий дд.мм.гггг'
                )
            ),
            'not valid number given' => array(
                400,
                array('number' => '1111'),
                array(
                    'errors.errors.0' => 'Эта форма не должна содержать дополнительных полей: "number"'
                )
            ),
            'not valid empty products' => array(
                400,
                array('products' => array()),
                array(
                    'errors.errors.0' => 'Нужно добавить минимум один товар'
                )
            ),
        );
    }

    public function testGetAction()
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();

        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store, '2012-05-23T15:12:05+0400')
                ->createWriteOffProduct($product->id)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals($writeOff->id, 'id', $getResponse);
        Assert::assertJsonPathEquals('10001', 'number', $getResponse);
        Assert::assertJsonPathEquals('2012-05-23T15:12:05+0400', 'date', $getResponse);
    }

    public function testGetActionNotFound()
    {
        $product = $this->factory()->catalog()->getProduct();
        $this->factory()
            ->writeOff()
                ->createWriteOff()
                ->createWriteOffProduct($product->id)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $this->client->setCatchException();
        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            '/api/1/writeOffs/invalidId'
        );

        $this->assertResponseCode(404);

        // There is not message in debug=false mode
        Assert::assertJsonPathContains('not found', 'message', $getResponse);
        Assert::assertNotJsonHasPath('id', $getResponse);
        Assert::assertNotJsonHasPath('number', $getResponse);
        Assert::assertNotJsonHasPath('date', $getResponse);
    }

    public function testWriteOffTotals()
    {
        $store = $this->factory()->store()->getStore();
        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));

        // Create writeoff with product#1
        $writeOffData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($products['1']->id, 12, 5.99);

        $postResponse = $this->postWriteOff($writeOffData->toArray());
        $writeOffId = $postResponse['id'];

        $this->assertWriteOff($store->id, $writeOffId, array('itemsCount' => 1, 'sumTotal' => 71.88));

        // Add product#2
        $writeOffData->addProduct($products['2']->id, 3, 6.49);

        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertWriteOff($store->id, $writeOffId, array('itemsCount' => 2, 'sumTotal' => 91.35));

        // Add product#3
        $writeOffData->addProduct($products['3']->id, 1, 11.12);

        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertWriteOff($store->id, $writeOffId, array('itemsCount' => 3, 'sumTotal' => 102.47));

        // update 1st write off product quantity and price

        $writeOffData->setProduct(0, $products['1']->id, 10, 6.99, 'because');

        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertWriteOff($store->id, $writeOffId, array('itemsCount' => 3, 'sumTotal' => 100.49));

        // update 2nd write off product product id

        $writeOffData->setProduct(1, $products['3']->id, 3, 6.49, 'because');

        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertWriteOff($store->id, $writeOffId, array('itemsCount' => 3, 'sumTotal' => 100.49));

        // remove 3rd write off product

        $writeOffData->removeProduct(2);

        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertWriteOff($store->id, $writeOffId, array('itemsCount' => 2, 'sumTotal' => 89.37));
    }

    /**
     * @param string $storeId
     * @param string $writeOffId
     * @param array $assertions
     * @throws \PHPUnit_Framework_ExpectationFailedException
     * @throws \PHPUnit_Framework_Exception
     */
    protected function assertWriteOff($storeId, $writeOffId, array $assertions = array())
    {
        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($storeId);

        $writeOffJson = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$storeId}/writeOffs/{$writeOffId}"
        );

        $this->assertResponseCode(200);

        $this->performJsonAssertions($writeOffJson, $assertions);
    }

    public function testGetWriteOffsAction()
    {
        $store = $this->factory()->store()->getStore();

        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));

        $writeOff1 = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($products['1']->id, 12, 5.99, 'Порча')
                ->createWriteOffProduct($products['2']->id, 3, 6.49, 'Порча')
                ->createWriteOffProduct($products['3']->id, 1, 11.12, 'Порча')
            ->flush();

        $writeOff2 = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($products['1']->id, 1, 6.92, 'Порча')
                ->createWriteOffProduct($products['2']->id, 2, 3.49, 'Порча')
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);

        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/writeOffs"
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathCount(2, '*.id', $response);
        Assert::assertJsonPathEquals($writeOff1->id, '*.id', $response, 1);
        Assert::assertJsonPathEquals($writeOff2->id, '*.id', $response, 1);
    }

    public function testDepartmentManagerCantGetWriteOffsFromAnotherStore()
    {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');

        $product = $this->factory()->catalog()->getProduct();

        $writeOff1 = $this->factory()
            ->writeOff()
                ->createWriteOff($store1)
                ->createWriteOffProduct($product->id)
            ->flush();

        $writeOff2 = $this->factory()
            ->writeOff()
                ->createWriteOff($store2)
                ->createWriteOffProduct($product->id)
            ->flush();

        $accessToken1 = $this->factory()->oauth()->authAsDepartmentManager($store1->id);
        $accessToken2 = $this->factory()->oauth()->authAsDepartmentManager($store2->id);

        $this->client->setCatchException();
        $this->clientJsonRequest(
            $accessToken2,
            'GET',
            "/api/1/stores/{$store1->id}/writeOffs/{$writeOff1->id}"
        );

        $this->assertResponseCode(403);

        $this->client->setCatchException();
        $this->clientJsonRequest(
            $accessToken1,
            'GET',
            "/api/1/stores/{$store2->id}/writeOffs/{$writeOff2->id}"
        );

        $this->assertResponseCode(403);

        $this->clientJsonRequest(
            $accessToken1,
            'GET',
            "/api/1/stores/{$store1->id}/writeOffs/{$writeOff1->id}"
        );

        $this->assertResponseCode(200);

        $this->clientJsonRequest(
            $accessToken2,
            'GET',
            "/api/1/stores/{$store2->id}/writeOffs/{$writeOff2->id}"
        );

        $this->assertResponseCode(200);
    }

    public function testGetWriteOffNotFoundInAnotherStore()
    {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');
        $product = $this->factory()->catalog()->getProduct();
        $departmentManager = $this->factory()->store()->getDepartmentManager($store1->id);
        $this->factory()->store()->linkDepartmentManagers($departmentManager->id, $store2->id);

        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store1)
                ->createWriteOffProduct($product->id)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store1->id);

        $this->client->setCatchException();
        $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store2->id}/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(404);

        $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store1->id}/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(200);
    }

    /**
     * @param string $query
     * @param int $count
     * @param array $assertions
     *
     * @dataProvider writeOffFilterProvider
     */
    public function testWriteOffsFilter($query, $count, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();

        $products = $this->factory()->catalog()->getProductByNames(array('111', '222', '333'));

        $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($products['111']->id, 10, 6.98, 'Бой')
            ->flush();

        $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($products['222']->id, 5, 10.12, 'Бой')
            ->flush();

        $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($products['333']->id, 7, 67.32, 'Бой')
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/writeOffs",
            null,
            array('number' => $query)
        );

        $this->assertResponseCode(200);
        Assert::assertJsonPathCount($count, '*.id', $response);
        $this->performJsonAssertions($response, $assertions);
    }

    /**
     * @return array
     */
    public function writeOffFilterProvider()
    {
        return array(
            'one by number' => array(
                '10002',
                1,
                array(
                    '0.number' => '10002',
                    '0._meta.highlights.number' => true,
                )
            ),
            'none found: not existing number' => array(
                '1234',
                0,
            ),
            'none found: empty number' => array(
                '',
                0,
            ),
        );
    }

    /**
     * @dataProvider validationWriteOffProductProvider
     *
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     */
    public function testPostWriteOffProductValidation($expectedCode, array $data, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();

        $product = $this->factory()->catalog()->getProduct();

        $writeOffData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($product->id, 7.99, 2, 'Сгнил товар')
            ->toArray();

        $writeOffData['products'][0] = $data + $writeOffData['products'][0];


        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $writeOffData
        );

        $this->assertResponseCode($expectedCode);

        $this->performJsonAssertions($postResponse, $assertions);
    }

    /**
     * @dataProvider validationWriteOffProductProvider
     *
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     */
    public function testPostWriteOffProductValidationGroups($expectedCode, array $data, array $assertions = array())
    {
        $store = $this->factory()->store()->getStore();

        $product = $this->factory()->catalog()->getProduct();

        $writeOffData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($product->id, 7.99, 2, 'Сгнил товар')
            ->toArray();

        $writeOffData['products'][0] = $data + $writeOffData['products'][0];


        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs?validate=true&validationGroups=products',
            $writeOffData
        );

        $this->assertResponseCode($expectedCode);

        $this->performJsonAssertions($postResponse, $assertions);

        if (400 != $expectedCode) {
            Assert::assertNotJsonHasPath('id', $postResponse);
        }
    }

    /**
     * @return array
     */
    public function validationWriteOffProductProvider()
    {
        return array(
            /***********************************************************************************************
             * 'quantity'
             ***********************************************************************************************/
            'valid quantity 7' => array(
                201,
                array('quantity' => 7),
                array('products.0.quantity' => 7)
            ),
            'empty quantity' => array(
                400,
                array('quantity' => ''),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Заполните это поле'
                )
            ),
            'negative quantity -10' => array(
                400,
                array('quantity' => -10),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть больше 0'
                )
            ),
            'negative quantity -1' => array(
                400,
                array('quantity' => -1),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть больше 0'
                )
            ),
            'zero quantity' => array(
                400,
                array('quantity' => 0),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0' => 'Значение должно быть больше 0'
                )
            ),
            'float quantity' => array(
                201,
                array('quantity' => 2.5),
            ),
            'float quantity very float' => array(
                400,
                array('quantity' => 2.5555),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой'
                )
            ),
            'float quantity with coma' => array(
                201,
                array('quantity' => '2,5'),
            ),
            'float quantity very float with coma' => array(
                400,
                array('quantity' => '2,5555'),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0'
                    =>
                    'Значение не должно содержать больше 3 цифр после запятой'
                )
            ),
            'float quantity very float only one message' => array(
                400,
                array('quantity' => '2,5555'),
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
                array('quantity' => 'abc'),
                array(
                    'errors.children.products.children.0.children.quantity.errors.0'
                    =>
                    'Значение должно быть числом'
                )
            ),
            /***********************************************************************************************
             * 'price'
             ***********************************************************************************************/
            'valid price dot' => array(
                201,
                array('price' => 10.89),
            ),
            'valid price dot 79.99' => array(
                201,
                array('price' => 79.99),
            ),
            'valid price coma' => array(
                201,
                array('price' => '10,89'),
            ),
            'empty price' => array(
                400,
                array('price' => ''),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Заполните это поле'
                )
            ),
            'not valid price very float' => array(
                400,
                array('price' => '10,898'),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Цена не должна содержать больше 2 цифр после запятой'
                ),
            ),
            'not valid price very float dot' => array(
                400,
                array('price' => '10.898'),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Цена не должна содержать больше 2 цифр после запятой'
                ),
            ),
            'valid price very float with dot' => array(
                201,
                array('price' => '10.12')
            ),
            'not valid price not a number' => array(
                400,
                array('price' => 'not a number'),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Значение должно быть числом',
                ),
            ),
            'not valid price zero' => array(
                400,
                array('price' => 0),
            ),
            'not valid price negative' => array(
                400,
                array('price' => -10),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Цена не должна быть меньше или равна нулю'
                )
            ),
            'not valid price too big 2 000 000 001' => array(
                400,
                array('price' => 2000000001),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Цена не должна быть больше 10000000'
                ),
            ),
            'not valid price too big 100 000 000' => array(
                400,
                array('price' => '100000000'),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Цена не должна быть больше 10000000'
                ),
            ),
            'valid price too big 10 000 000' => array(
                201,
                array('price' => '10000000'),
            ),
            'not valid price too big 10 000 001' => array(
                400,
                array('price' => '10000001'),
                array(
                    'errors.children.products.children.0.children.price.errors.0'
                    =>
                    'Цена не должна быть больше 10000000'
                ),
            ),
            /***********************************************************************************************
             * 'product'
             ***********************************************************************************************/
            'not valid product' => array(
                400,
                array('product' => 'not_valid_product_id'),
                array(
                    'errors.children.products.children.0.children.product.errors.0' => 'Такого товара не существует'
                ),
            ),
            'empty product' => array(
                400,
                array('product' => ''),
                array(
                    'errors.children.products.children.0.children.product.errors.0' => 'Заполните это поле'
                ),
            ),
            /***********************************************************************************************
             * 'cause'
             ***********************************************************************************************/
            'not valid empty cause' => array(
                400,
                array('cause' => ''),
                array(
                    'errors.children.products.children.0.children.cause.errors.0' => 'Заполните это поле'
                ),
            ),
            'not valid cause long 1001' => array(
                400,
                array('cause' => str_repeat('z', 1001)),
                array(
                    'errors.children.products.children.0.children.cause.errors.0' => 'Не более 1000 символов'
                ),
            ),
            'valid cause long 1000' => array(
                201,
                array('cause' => str_repeat("z", 1000)),
            ),
            'valid cause special symbols' => array(
                201,
                array('cause' => '!@#$%^&^&*QWERTY}{}":<></.,][;.,`~\=0=-\\'),
            ),
            /***********************************************************************************************
             * 'totals'
             ***********************************************************************************************/
            'valid invoice totals recalc' => array(
                201,
                array('quantity' => 9, 'price' => 5.99),
                array('sumTotal' => 53.91, 'itemsCount' => 1)
            ),
        );
    }

    public function testProductAmountChangeOnWriteOf()
    {
        $store = $this->factory()->store()->getStore();

        $product1 = $this->factory()->catalog()->getProduct('1');
        $product2 = $this->factory()->catalog()->getProduct('2');

        $this->assertStoreProductTotals($store->id, $product1->id, 0);

        $this->factory()
            ->invoice()
                ->createInvoice(array(), $store->id)
                ->createInvoiceProduct($product1->id, 10, 4.99)
                ->createInvoiceProduct($product2->id, 20, 6.99)
            ->flush();

        $this->assertStoreProductTotals($store->id, $product1->id, 10, 4.99);
        $this->assertStoreProductTotals($store->id, $product2->id, 20, 6.99);

        // create product 1 write off
        $writeOffData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($product1->id, 5, 3.49, 'Порча');

        $postResponse = $this->postWriteOff($writeOffData->toArray());
        $writeOffId = $postResponse['id'];

        $this->assertStoreProductTotals($store->id, $product1->id, 5, 4.99);
        $this->assertStoreProductTotals($store->id, $product2->id, 20, 6.99);

        // change 1st product write off quantity
        $writeOffData->setProduct(0, $product1->id, 7, 4.49, 'Порча');
        $putResponse1 = $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertStoreProductTotals($store->id, $product1->id, 3, 4.99);

        Assert::assertNotJsonPathEquals($postResponse['products'][0]['id'], 'products.0.id', $putResponse1);

        // add 2nd write off product
        $writeOffData->addProduct($product2->id, 4, 20.99, 'Бой посуды');
        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertStoreProductTotals($store->id, $product2->id, 16, 6.99);

        // change 2nd product id
        $writeOffData->setProduct(1, $product1->id, 4, 20.99, 'Бой посуды');
        $putResponse3 = $this->putWriteOff($writeOffId, $writeOffData->toArray());

        Assert::assertJsonPathEquals($product1->id, 'products.1.product.id', $putResponse3);

        $this->assertStoreProductTotals($store->id, $product1->id, -1, 4.99);
        $this->assertStoreProductTotals($store->id, $product2->id, 20, 6.99);

        // remove 2nd write off product
        $writeOffData->removeProduct(1);
        $this->putWriteOff($writeOffId, $writeOffData->toArray());

        $this->assertStoreProductTotals($store->id, $product1->id, 3, 4.99);
        $this->assertStoreProductTotals($store->id, $product2->id, 20, 6.99);

        // remove write off
        $this->deleteWriteOff($writeOffId);

        $this->assertStoreProductTotals($store->id, $product1->id, 10, 4.99);
        $this->assertStoreProductTotals($store->id, $product2->id, 20, 6.99);
    }

    public function testProductDataDoesNotChangeInWriteOffAfterProductUpdate()
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct('Кефир 1%');
        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($product->id, 10, 5.99, 'Бой')
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);

        $writeoffResponse1 = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals('Кефир 1%', 'products.*.product.name', $writeoffResponse1, 1);

        $this->updateProduct($product->id, array('name' => 'Кефир 5%'));

        $writeoffResponse2 = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals('Кефир 1%', 'products.*.product.name', $writeoffResponse2, 1);

        $this->assertProduct($product->id, array('name' => 'Кефир 5%'));
    }

    /**
     * @dataProvider departmentManagerCanNotAccessWriteOffFromAnotherStoreProvider
     * @param string $method
     * @param string $url
     * @param int $expectedCode
     * @param bool $sendData
     */
    public function testDepartmentManagerCanNotAccessWriteOffFromAnotherStore(
        $method,
        $url,
        $expectedCode,
        $sendData = false
    ) {
        $store1 = $this->factory()->store()->getStore('1');
        $store2 = $this->factory()->store()->getStore('2');

        $product = $this->factory()->catalog()->getProduct();

        $writeOff1 = $this->factory()
            ->writeOff()
                ->createWriteOff($store1)
                ->createWriteOffProduct($product->id, 2, 20, 'Бой')
            ->flush();
        $writeOff2 = $this->factory()
            ->writeOff()
                ->createWriteOff($store2)
                ->createWriteOffProduct($product->id, 1, 10, 'Порча')
            ->flush();

        $accessToken1 = $this->factory()->oauth()->authAsDepartmentManager($store1->id);
        $accessToken2 = $this->factory()->oauth()->authAsDepartmentManager($store2->id);

        if ($sendData) {
            $data = WriteOffBuilder::create()
                ->addProduct($product->id)
                ->toArray();
        } else {
            $data = null;
        }

        $url1 = strtr(
            $url,
            array(
                '{store}' => $store1->id,
                '{writeOff}' => $writeOff1->id,
            )
        );

        $this->client->setCatchException();
        $this->clientJsonRequest($accessToken2, $method, $url1, $data);
        $this->assertResponseCode(403);

        $this->clientJsonRequest($accessToken1, $method, $url1, $data);
        $this->assertResponseCode($expectedCode);

        $url2 = strtr(
            $url,
            array(
                '{store}' => $store2->id,
                '{writeOff}' => $writeOff2->id,
            )
        );

        $this->client->setCatchException();
        $this->clientJsonRequest($accessToken1, $method, $url2, $data);
        $this->assertResponseCode(403);

        $this->clientJsonRequest($accessToken2, $method, $url2, $data);
        $this->assertResponseCode($expectedCode);
    }

    /**
     * @return array
     */
    public function departmentManagerCanNotAccessWriteOffFromAnotherStoreProvider()
    {
        return array(
            'GET' => array(
                'GET',
                '/api/1/stores/{store}/writeOffs/{writeOff}',
                200,
                false
            ),
            'POST' => array(
                'POST',
                '/api/1/stores/{store}/writeOffs',
                201,
                true
            ),
            'PUT' => array(
                'PUT',
                '/api/1/stores/{store}/writeOffs/{writeOff}',
                200,
                true
            ),
        );
    }

    public function testPutWithEmptyQuantity()
    {
        $store = $this->factory()->store()->getStore();
        $product = $this->factory()->catalog()->getProduct();

        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($product->id, 1, 9.99, 'Порча')
            ->flush();

        $putData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($product->id, '', 9.99, 'Порча')
            ->toArray();

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $response = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/writeOffs/{$writeOff->id}",
            $putData
        );

        $this->assertResponseCode(400);
        Assert::assertJsonPathEquals(
            'Заполните это поле',
            'errors.children.products.children.0.children.quantity.errors.0',
            $response
        );
    }

    public function testProductCategoryIsNotExposed()
    {
        $store = $this->factory()->store()->getStore();
        $products = $this->factory()->catalog()->getProductByNames(array('1', '2', '3'));

        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($products['1']->id, 2, 5.99, 'Порча')
                ->createWriteOffProduct($products['2']->id, 1, 6.99, 'Порча')
                ->createWriteOffProduct($products['3']->id, 3, 2.59, 'Порча')
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsDepartmentManager($store->id);
        $storeGetResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/stores/{$store->id}/writeOffs/{$writeOff->id}"
        );
        $this->assertResponseCode(200);
        Assert::assertJsonHasPath('products.*.product.subCategory', $storeGetResponse);
        Assert::assertNotJsonHasPath('products.*.writeOff', $storeGetResponse);
        Assert::assertNotJsonHasPath('products.*.product.subCategory.category.group', $storeGetResponse);
        Assert::assertNotJsonHasPath('products.*.product.subCategory.category', $storeGetResponse);

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);
        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/writeOffs/{$writeOff->id}"
        );

        $this->assertSame($storeGetResponse, $getResponse);
    }

    public function testDeleteWriteOff()
    {
        $store = $this->factory()->store()->getStore();

        $product = $this->factory()->catalog()->getProduct('Продукт');

        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($product->id)
            ->flush();

        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        $deleteResponse = $this->clientJsonRequest(
            $accessToken,
            'DELETE',
            "/api/1/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(204);

        $this->assertNull($deleteResponse);

        $this->client->setCatchException();
        $this->clientJsonRequest(
            $accessToken,
            'GET',
            "/api/1/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(404);

        $this->assertWriteOffDelete($writeOff->id);
        $this->assertWriteOffProductDelete($writeOff->products[0]->id);
    }

    public function testPostWithDeletedStore()
    {
        $store = $this->factory()->store()->createStore();

        $product = $this->factory()->catalog()->getProductByName();

        $this->factory()->store()->deleteStore($store);

        $writeOffData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($product->id, 10, 5.99)
            ->toArray();

        $accessToken = $this->factory()->oauth()->authAsProjectUser();

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $writeOffData
        );

        $this->assertResponseCode(400);
        Assert::assertJsonPathEquals(
            'Операции с участием удаленного магазина запрещены',
            'errors.children.store.errors.0',
            $postResponse
        );
    }

    public function testPutWithDeletedStore()
    {
        $store = $this->factory()->store()->createStore();
        $product = $this->factory()->catalog()->getProductByName();

        $this->factory()
            ->invoice()
                ->createInvoice(array(), $store->id)
                ->createInvoiceProduct($product->id, 10, 6.00)
            ->flush();

        $writeOffData = WriteOffBuilder::create(null, $store->id)
            ->addProduct($product->id, 10, 5.99)
            ->toArray();

        $accessToken = $this->factory()->oauth()->authAsProjectUser();

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $writeOffData
        );

        $this->assertResponseCode(201);

        $this->factory()->clear();
        $this->factory()->store()->deleteStore($store);

        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/writeOffs/{$postResponse['id']}",
            $writeOffData
        );

        $this->assertResponseCode(400);

        Assert::assertJsonPathEquals(
            'Операции с участием удаленного магазина запрещены',
            'errors.children.store.errors.0',
            $putResponse
        );
    }

    public function testPutWithOriginalStoreDeleted()
    {
        $store1 = $this->factory()->store()->createStore('Store 1');
        $store2 = $this->factory()->store()->createStore('Store 2');
        $product = $this->factory()->catalog()->getProduct();

        $this->factory()
            ->invoice()
                ->createInvoice(array(), $store1->id)
                ->createInvoiceProduct($product->id, 10, 6.00)
            ->flush();

        $writeOffData = WriteOffBuilder::create(null, $store1->id)
            ->addProduct($product->id, 10, 5.99);

        $accessToken = $this->factory()->oauth()->authAsProjectUser();

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $writeOffData->toArray()
        );

        $this->assertResponseCode(201);

        $this->factory()->clear();
        $this->factory()->store()->deleteStore($store1);

        $writeOffData->setStore($store2->id);

        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/writeOffs/{$postResponse['id']}",
            $writeOffData->toArray()
        );

        $this->assertResponseCode(400);

        Assert::assertJsonPathEquals(
            'Операции с участием удаленного магазина запрещены',
            'errors.children.store.errors.0',
            $putResponse
        );
    }

    public function testDeleteWithDeletedStore()
    {
        $store = $this->factory()->store()->getStore();

        $product = $this->factory()->catalog()->getProductByName();

        $this->factory()
            ->stockIn()
                ->createStockIn($store)
                ->createStockInProduct($product->id, 10, 5.12)
            ->flush();

        $writeOff = $this->factory()
            ->writeOff()
                ->createWriteOff($store)
                ->createWriteOffProduct($product->id, 10, 7.49)
            ->flush();

        $this->factory()->clear();
        $this->factory()->store()->deleteStore($store);

        $accessToken = $this->factory()->oauth()->authAsProjectUser();

        $this->client->setCatchException();
        $deleteResponse = $this->clientJsonRequest(
            $accessToken,
            'DELETE',
            "/api/1/writeOffs/{$writeOff->id}"
        );

        $this->assertResponseCode(409);
        Assert::assertJsonPathEquals(
            'Удаление операции с участием удаленного магазина запрещено',
            'message',
            $deleteResponse
        );
    }


    /**
     * @param string $writeOffId
     */
    protected function assertWriteOffDelete($writeOffId)
    {
        $invoice = $this->getWriteOffRepository()->find($writeOffId);
        $this->assertNull($invoice);
    }

    /**
     * @param string $writeOffProductId
     */
    protected function assertWriteOffProductDelete($writeOffProductId)
    {
        $writeOffProduct = $this->getWriteOffProductRepository()->find($writeOffProductId);
        $this->assertNull($writeOffProduct);
    }

    /**
     * @return WriteOffRepository
     */
    protected function getWriteOffRepository()
    {
        return $this->getContainer()->get('lighthouse.core.document.repository.stock_movement.writeoff');
    }

    /**
     * @return StockMovementProductRepository
     */
    protected function getWriteOffProductRepository()
    {
        return $this->getContainer()->get('lighthouse.core.document.repository.stock_movement.writeoff_product');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function postWriteOff(array $data)
    {
        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);
        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeOffs',
            $data
        );

        $this->assertResponseCode(201);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse;
    }

    /**
     * @param string $writeOffId
     * @param array $data
     * @return array
     */
    protected function putWriteOff($writeOffId, array $data)
    {
        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);
        $putResponse = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            "/api/1/writeOffs/{$writeOffId}",
            $data
        );

        $this->assertResponseCode(200);

        return $putResponse;
    }

    /**
     * @param string $writeOffId
     */
    protected function deleteWriteOff($writeOffId)
    {
        $accessToken = $this->factory()->oauth()->authAsRole(User::ROLE_COMMERCIAL_MANAGER);
        $deleteResponse = $this->clientJsonRequest(
            $accessToken,
            'DELETE',
            "/api/1/writeOffs/{$writeOffId}"
        );

        $this->assertResponseCode(204);
        $this->assertNull($deleteResponse);
    }
}
