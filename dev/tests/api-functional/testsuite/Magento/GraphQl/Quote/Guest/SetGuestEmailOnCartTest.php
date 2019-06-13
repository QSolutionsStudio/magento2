<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setGuestEmailOnCart mutation
 */
class SetGuestEmailOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testSetGuestEmailOnCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('setGuestEmailOnCart', $response);
        $this->assertArrayHasKey('cart', $response['setGuestEmailOnCart']);
        $this->assertEquals($email, $response['setGuestEmailOnCart']['cart']['email']);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testSetGuestEmailOnCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     * @dataProvider incorrectEmailDataProvider
     * @param string $email
     * @param string $exceptionMessage
     */
    public function testSetGuestEmailOnCartWithIncorrectEmail(
        string $email,
        string $exceptionMessage
    ) {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->expectExceptionMessage($exceptionMessage);
        $this->graphQlMutation($query);
    }

    /**
     * @return array
     */
    public function incorrectEmailDataProvider(): array
    {
        return [
            'wrong_email' => ['some', 'Invalid email format'],
            'no_email' => ['', 'Required parameter "email" is missing'],
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testSetGuestEmailOnNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameter "cart_id" is missing
     */
    public function testSetGuestEmailWithEmptyCartId()
    {
        $maskedQuoteId = '';
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/disable_guest_checkout.php
     * @magentoConfigFixture default_store checkout/options/guest_checkout 0
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Guest checkout is not allowed. Register a customer account or login with existing one.
     */
    public function testSetGuestEmailOnCartWithGuestCheckoutDisabled()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query);
    }

    /**
     * Returns GraphQl mutation query for setting email address for a guest
     *
     * @param string $maskedQuoteId
     * @param string $email
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $email): string
    {
        return <<<QUERY
mutation {
  setGuestEmailOnCart(input: {
    cart_id: "$maskedQuoteId"
    email: "$email"
  }) {
    cart {
      email
    }
  }
}
QUERY;
    }
}
