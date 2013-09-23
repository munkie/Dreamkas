<?php

namespace Lighthouse\CoreBundle\Test;

use Lighthouse\CoreBundle\Document\Auth\Client as AuthClient;
use Lighthouse\CoreBundle\Document\Store\Store;
use Lighthouse\CoreBundle\Document\User\User;
use Lighthouse\CoreBundle\Test\Client\JsonRequest;
use Lighthouse\CoreBundle\Test\Client\Client;

/**
 * @codeCoverageIgnore
 */
class WebTestCase extends ContainerAwareTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var User
     */
    protected $departmentManager;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var Factory
     */
    protected $factory;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->clearMongoDb();
        $this->factory = new Factory($this->getContainer());
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->factory = null;
        $this->client = null;
    }

    /**
     * @param \stdClass|string $token
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $parameters
     * @param array $server
     * @param bool $changeHistory
     * @return array
     * @throws \Exception
     */
    protected function clientJsonRequest(
        $token,
        $method,
        $uri,
        $data = null,
        array $parameters = array(),
        array $server = array(),
        $changeHistory = false
    ) {
        $request = new JsonRequest($uri, $method);

        $request->parameters = $parameters;
        $request->server = $server;
        $request->changeHistory = $changeHistory;

        if ($token) {
            $request->setAccessToken($token);
        }

        $request->setJsonData($data);
        $request->setJsonHeaders();

        return $this->jsonRequest($request);
    }

    /**
     * @param JsonRequest $jsonRequest
     * @param \stdClass|string $accessToken
     * @return array
     */
    protected function jsonRequest(JsonRequest $jsonRequest, $accessToken = null)
    {
        return $this->client->jsonRequest($jsonRequest, $accessToken);
    }

    /**
     * @param array $modifiedData
     * @param string $storeId
     * @param User $departmentManager
     * @return mixed
     */
    protected function createInvoice(array $modifiedData = array(), $storeId = null, User $departmentManager = null)
    {
        $storeId = ($storeId) ?: $this->createStore('42', '42', '42', true);
        $departmentManager = ($departmentManager) ?: $this->getRoleUser(User::ROLE_DEPARTMENT_MANAGER);

        $accessToken = $this->auth($departmentManager);

        $invoiceData = $modifiedData + array(
            'sku' => 'sku232',
            'supplier' => 'ООО "Поставщик"',
            'acceptanceDate' => '2013-03-18 12:56',
            'accepter' => 'Приемных Н.П.',
            'legalEntity' => 'ООО "Магазин"',
            'supplierInvoiceSku' => '1248373',
            'supplierInvoiceDate' => '17.03.2013',
        );

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores/' . $storeId . '/invoices',
            $invoiceData
        );

        $this->assertResponseCode(201);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $storeId
     * @param string $invoiceId
     * @param string $productId
     * @param int $quantity
     * @param float $price
     * @return string
     */
    public function createInvoiceProduct($invoiceId, $productId, $quantity, $price, $storeId = null, $manager = null)
    {
        $manager = ($manager) ?: $this->departmentManager;
        $storeId = ($storeId) ?: $this->storeId;

        $accessToken = $this->auth($manager);

        $invoiceProductData = array(
            'product' => $productId,
            'quantity' => $quantity,
            'price' => $price
        );

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores/' . $storeId . '/invoices/' . $invoiceId . '/products',
            $invoiceProductData
        );

        $this->assertResponseCode(201);

        return $postResponse['id'];
    }

    public function createPurchaseWithProduct($productId, $sellingPrice, $quantity, $date = 'now')
    {
        $purchaseProductData = array(
            'product' => $productId,
            'sellingPrice' => $sellingPrice,
            'quantity' => $quantity,
        );

        $accessToken = $this->authAsRole('ROLE_DEPARTMENT_MANAGER');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/purchases',
            array(
                'createdDate' => date('c', strtotime($date)),
                'products' => array($purchaseProductData),
            )
        );

        $this->assertResponseCode(201);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string|array $extra
     * @param null|string $subCategoryId
     * @param bool|string $putProductId string id of product to be updated
     * @return mixed
     */
    protected function createProduct($extra = '', $subCategoryId = null, $putProductId = false)
    {
        if ($subCategoryId == null) {
            $subCategoryId = $this->createSubCategory();
        }

        $productData = array(
            'name' => 'Кефир "Веселый Молочник" 1% 950гр',
            'units' => 'gr',
            'barcode' => '4607025392408',
            'purchasePrice' => 3048,
            'sku' => 'КЕФИР "ВЕСЕЛЫЙ МОЛОЧНИК" 1% КАРТОН УПК. 950ГР',
            'vat' => 10,
            'vendor' => 'Вимм-Билль-Данн',
            'vendorCountry' => 'Россия',
            'info' => 'Классный кефирчик, употребляю давно, всем рекомендую для поднятия тонуса',
            'subCategory' => $subCategoryId,
        );

        if (is_array($extra)) {
            $productData = $extra + $productData;
        } else {
            $productData['name'].= $extra;
            $productData['sku'].= $extra;
        }

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');
        $method = ($putProductId) ? 'PUT' : 'POST';
        $url = '/api/1/products' . (($putProductId) ? '/' . $putProductId : '');
        $request = new JsonRequest($url, $method, $productData);
        $postResponse = $this->jsonRequest($request, $accessToken);

        $responseCode = ($putProductId) ? 200 : 201;
        $this->assertResponseCode($responseCode);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $productId
     * @param array $data
     */
    protected function updateProduct($productId, array $data)
    {
        $this->createProduct($data, null, $productId);
    }

    /**
     * @param $productId
     * @param $invoiceId
     * @return array
     */
    protected function createInvoiceProducts($productId, $invoiceId)
    {
        $productsData = array(
            array(
                'product' => $productId,
                'quantity' => 10,
                'price' => 11.12,
                'productAmount' => 10,
            ),
            array(
                'product' => $productId,
                'quantity' => 5,
                'price' => 12.76,
                'productAmount' => 15,
            ),
            array(
                'product' => $productId,
                'quantity' => 1,
                'price' => 5.99,
                'productAmount' => 16,
            ),
        );

        $accessToken = $this->auth($this->departmentManager);

        foreach ($productsData as $i => $row) {

            $invoiceProductData = array(
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'product' => $row['product'],
            );

            $response = $this->clientJsonRequest(
                $accessToken,
                'POST',
                '/api/1/stores/' . $this->storeId . '/invoices/' . $invoiceId . '/products.json',
                $invoiceProductData
            );

            $this->assertResponseCode(201);
            $productsData[$i]['id'] = $response['id'];
        }

        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            '/api/1/stores/' . $this->storeId . '/invoices/' . $invoiceId . '/products'
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathCount(3, "*.id", $getResponse);

        foreach ($productsData as $productData) {
            Assert::assertJsonPathEquals($productData['id'], '*.id', $getResponse);
        }

        return $productsData;
    }

    /**
     * @param string $number
     * @param int $date timestamp
     * @return mixed
     */
    protected function createWriteOff(
        $number = '431-6782',
        $date = null,
        $storeId = null,
        User $departmentManager = null
    ) {
        $storeId = ($storeId) ?: $this->createStore('42', '42', '42', true);
        $departmentManager = ($departmentManager) ?: $this->getRoleUser(User::ROLE_DEPARTMENT_MANAGER);

        $accessToken = $this->auth($departmentManager);

        $date = $date ? : date('c', strtotime('-1 day'));

        $postData = array(
            'number' => $number,
            'date' => $date,
        );

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores/' . $storeId . '/writeoffs',
            $postData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $writeOffId
     * @param string $productId
     * @param float $price
     * @param int $quantity
     * @param string $cause
     * @return string
     */
    protected function createWriteOffProduct(
        $writeOffId,
        $productId,
        $price = 5.99,
        $quantity = 10,
        $cause = 'Порча',
        $storeId = null,
        $manager = null
    ) {
        $manager = ($manager) ?: $this->departmentManager;
        $storeId = ($storeId) ?: $this->storeId;
        $price = ($price) ?: 5.99;
        $quantity = ($quantity) ?: 10;
        $cause = ($cause) ?: 'Порча';

        $accessToken = $this->auth($manager);

        $postData = array(
            'product' => $productId,
            'price' => $price,
            'quantity' => $quantity,
            'cause' => $cause,
        );

        $request = new JsonRequest(
            '/api/1/stores/' . $storeId . '/writeoffs/' . $writeOffId . '/products',
            'POST',
            $postData
        );
        $postResponse = $this->jsonRequest($request, $accessToken);

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $name
     * @param bool $ifNotExists
     * @param mixed $retailMarkupMin
     * @param mixed $retailMarkupMax
     * @param string $rounding
     * @return string
     */
    protected function createGroup(
        $name = 'Продовольственные товары',
        $ifNotExists = true,
        $retailMarkupMin = null,
        $retailMarkupMax = null,
        $rounding = 'nearest1'
    ) {
        $postData = array(
            'name' => $name,
            'retailMarkupMin' => $retailMarkupMin,
            'retailMarkupMax' => $retailMarkupMax,
            'rounding' => $rounding,
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        if ($ifNotExists) {
            $postResponse = $this->clientJsonRequest(
                $accessToken,
                'GET',
                '/api/1/groups'
            );

            if (count($postResponse)) {
                foreach ($postResponse as $value) {
                    if ($value['name'] == $name) {
                        return $value['id'];
                    }
                }
            }
        }

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/groups',
            $postData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param mixed $json
     * @param array $assertions
     * @param bool  $contains
     */
    protected function performJsonAssertions($json, array $assertions, $contains = false)
    {
        foreach ($assertions as $path => $expected) {
            if (null === $expected) {
                Assert::assertNotJsonHasPath($path, $json);
            } elseif ($contains) {
                Assert::assertJsonPathContains($expected, $path, $json);
            } else {
                Assert::assertJsonPathEquals($expected, $path, $json);
            }
        }
    }

    /**
     * @param string $productId
     * @param array $assertions
     */
    protected function assertProduct($productId, array $assertions)
    {
        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $request = new JsonRequest('/api/1/products/' . $productId);
        $request->setAccessToken($accessToken);

        $productJson = $this->jsonRequest($request);

        $this->assertResponseCode(200);

        $this->performJsonAssertions($productJson, $assertions);
    }


    /**
     * @param string $productId
     * @param int $amount
     * @param float $lastPurchasePrice
     */
    protected function assertProductTotals($productId, $amount, $lastPurchasePrice)
    {
        $assertions = array(
            'amount' => $amount,
            'lastPurchasePrice' => $lastPurchasePrice,
        );

        $this->assertProduct($productId, $assertions);
    }

    /**
     * @param string $groupId
     * @param string $name
     * @param bool $ifNotExists
     * @param string $rounding
     * @return string
     */
    protected function createCategory(
        $groupId = null,
        $name = 'Винно-водочные изделия',
        $ifNotExists = true,
        $rounding = 'nearest1'
    ) {
        if ($groupId == null) {
            $groupId = $this->createGroup();
        }
        $categoryData = array(
            'name' => $name,
            'group' => $groupId,
            'rounding' => $rounding,
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        if ($ifNotExists) {
            $postResponse = $this->clientJsonRequest(
                $accessToken,
                'GET',
                '/api/1/groups/'. $groupId .'/categories'
            );

            if (count($postResponse)) {
                foreach ($postResponse as $value) {
                    if ($value['name'] == $name) {
                        return $value['id'];
                    }
                }
            }
        }

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/categories',
            $categoryData
        );

        $this->assertResponseCode(201);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }


    /**
     * @param string $categoryId
     * @param string $name
     * @param bool $ifNotExists
     * @return string
     */
    protected function createSubCategory($categoryId = null, $name = 'Водка', $ifNotExists = true)
    {
        if ($categoryId == null) {
            $categoryId = $this->createCategory();
        }
        $subCategoryData = array(
            'name' => $name,
            'category' => $categoryId,
            'rounding' => 'nearest1',
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        if ($ifNotExists) {
            $postResponse = $this->clientJsonRequest(
                $accessToken,
                'GET',
                '/api/1/categories/'. $categoryId .'/subcategories'
            );

            $this->assertResponseCode(200);

            if (count($postResponse)) {
                foreach ($postResponse as $value) {
                    if ($value['name'] == $name) {
                        return $value['id'];
                    }
                }
            }
        }

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/subcategories',
            $subCategoryData
        );

        $this->assertResponseCode(201);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $number
     * @param string $address
     * @param string $contacts
     * @param bool $ifNotExists
     * @return string
     */
    public function createStore(
        $number = 'номер_42',
        $address = 'адрес 42',
        $contacts = 'телефон 42',
        $ifNotExists = false
    ) {
        $storeData = array(
            'number' => $number,
            'address' => $address,
            'contacts' => $contacts,
        );

        $accessToken = $this->authAsRole(User::ROLE_COMMERCIAL_MANAGER);

        if ($ifNotExists) {
            $postResponse = $this->clientJsonRequest(
                $accessToken,
                'GET',
                '/api/1/stores'
            );

            if (count($postResponse)) {
                foreach ($postResponse as $value) {
                    if (is_array($value) && array_key_exists('number', $value) && $value['number'] == $number) {
                        return $value['id'];
                    }
                }
            }
        }

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores',
            $storeData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $response);
        foreach ($storeData as $name => $value) {
            Assert::assertJsonPathEquals($value, $name, $response);
        }

        return $response['id'];
    }

    public function createDepartment(
        $storeId = null,
        $number = 'отдел_42',
        $name = 'название отдела 42',
        $ifNotExists = true
    ) {
        if ($storeId == null) {
            $storeId = $this->createStore();
        }

        $storeData = array(
            'number' => $number,
            'name' => $name,
            'store' => $storeId,
        );

        $accessToken = $this->authAsRole("ROLE_COMMERCIAL_MANAGER");

        if ($ifNotExists) {
            $postResponse = $this->clientJsonRequest(
                $accessToken,
                'GET',
                '/api/1/stores/' . $storeId . '/departments'
            );

            if (count($postResponse)) {
                foreach ($postResponse as $value) {
                    if (is_array($value) && array_key_exists('number', $value) && $value['number'] == $number) {
                        return $value['id'];
                    }
                }
            }
        }

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/departments',
            $storeData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonHasPath('id', $response);
        Assert::assertJsonPathEquals($storeData['number'], 'number', $response);
        Assert::assertJsonPathEquals($storeData['name'], 'name', $response);

        return $response['id'];
    }

    /**
     * @param string $storeId
     * @param string|array $userIds
     * @param string $rel
     */
    public function linkStoreManagers($storeId, $userIds)
    {
        $this->factory->linkStoreManagers($storeId, $userIds);
    }

    /**
     * @param string $storeId
     * @param string|string[] $userIds
     */
    public function linkDepartmentManagers($storeId, $userIds)
    {
        $this->factory->linkDepartmentManagers($storeId, $userIds);
    }

    /**
     * @param string $storeId
     * @param string $productId
     * @param array $productData
     * @param User $storeManager
     * @param string $password
     */
    public function updateStoreProduct(
        $storeId,
        $productId,
        array $productData = array(),
        User $storeManager = null,
        $password = 'password'
    ) {
        if (null === $storeManager) {
            $storeManager = $this->getStoreManager($storeId);
        }

        $accessToken = $this->auth($storeManager, $password);

        $this->clientJsonRequest(
            $accessToken,
            'PUT',
            '/api/1/stores/' . $storeId . '/products/' . $productId,
            $productData
        );

        $this->assertResponseCode(200);
    }

    /**
     * @return AuthClient
     */
    protected function createAuthClient()
    {
        return $this->factory->getAuthClient();
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $role
     * @param string $name
     * @param string $position
     * @return User
     */
    protected function createUser(
        $username = 'admin',
        $password = Factory::USER_DEFAULT_PASSWORD,
        $role = User::ROLE_ADMINISTRATOR,
        $name = 'Админ Админыч',
        $position = 'Администратор'
    ) {
        return $this->factory->getUser($username, $password, $role, $name, $position);
    }

    /**
     * @param string $role
     * @return \stdClass accessToken
     */
    protected function authAsRole($role)
    {
        return $this->factory->authAsRole($role);
    }

    /**
     * @param string $role
     * @return User
     */
    protected function getRoleUser($role)
    {
        return $this->factory->getRoleUser($role);
    }

    /**
     * @param string $storeId
     * @return User
     */
    protected function getStoreManager($storeId)
    {
        return $this->factory->getStoreManager($storeId);
    }

    /**
     * @param User $user
     * @param string $password
     * @param AuthClient $oauthClient
     * @return \stdClass access token
     */
    protected function auth(
        User $user,
        $password = Factory::USER_DEFAULT_PASSWORD,
        AuthClient $oauthClient = null
    ) {
        return $this->factory->auth($user, $password, $oauthClient);
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getNowDate($format = 'Y-m-d\\TH:')
    {
        return date($format);
    }

    /**
     * @param integer $expectedCode
     */
    public function assertResponseCode($expectedCode)
    {
        Assert::assertResponseCode($expectedCode, $this->client);
    }

    /**
     * @param string $name
     * @param string $value
     * @return mixed
     */
    public function createConfig($name = 'test-config', $value = 'test-config-value')
    {
        $configData = array(
            'name' => $name,
            'value' => $value,
        );

        $accessToken = $this->authAsRole('ROLE_ADMINISTRATOR');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            "POST",
            "/api/1/configs",
            $configData
        );

        $this->assertResponseCode(201);

        Assert::assertJsonPathEquals($name, 'name', $postResponse);
        Assert::assertJsonPathEquals($value, 'value', $postResponse);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $configId
     * @param string $name
     * @param string $value
     * @return string
     */
    public function updateConfig($configId, $name = 'test-config', $value = 'test-config-value')
    {
        $configData = array(
            'name' => $name,
            'value' => $value,
        );

        $accessToken = $this->authAsRole('ROLE_ADMINISTRATOR');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            "PUT",
            "/api/1/configs/" . $configId,
            $configData
        );

        $this->assertResponseCode(200);

        Assert::assertJsonPathEquals($name, 'name', $postResponse);
        if ($value !== '') {
            Assert::assertJsonPathEquals($value, 'value', $postResponse);
        }
        Assert::assertJsonPathEquals($configId, 'id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $userId
     * @return string
     */
    protected function getUserResourceUri($userId)
    {
        return $this->factory->getUserResourceUri($userId);
    }

    /**
     *
     */
    protected function initStoreDepartmentManager()
    {
        $this->departmentManager = $this->createUser('Краузе В.П.', 'password', User::ROLE_DEPARTMENT_MANAGER);
        $this->storeId = $this->createStore();

        $this->linkDepartmentManagers($this->storeId, $this->departmentManager->id);
    }
}
