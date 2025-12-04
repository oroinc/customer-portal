@ticket-BB-26154
@fixture-OroSaleBundle:GuestLinkQuotesFixtures.yml

Feature: Stateless CSRF form protection
  As a visitor
  I want to browse all pages with forms on it which are available for customer visitor without creating a session cookie

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Enable guest features and disable Create Customer Visitors Immediately
    Given I proceed as the Admin
    And I login as administrator
    And go to System/ Configuration
    When I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Create Customer Visitors Immediately" field
    And I uncheck "Create Customer Visitors Immediately"
    And I save form
    Then I should see "Configuration saved" flash message
    When I follow "Commerce/Sales/Shopping List" on configuration sidebar
    And uncheck "Use default" for "Enable Guest Shopping List" field
    And I check "Enable Guest Shopping List"
    And I save form
    Then I should see "Configuration saved" flash message
    When I follow "Commerce/Sales/Request For Quote" on configuration sidebar
    And uncheck "Use default" for "Enable Guest RFQ" field
    And I check "Enable Guest RFQ"
    And I save form
    Then I should see "Configuration saved" flash message
    When I follow "Commerce/Sales/Quick Order Form" on configuration sidebar
    And uncheck "Use default" for "Enable Guest Quick Order Form" field
    And I check "Enable Guest Quick Order Form"
    And I save form
    Then I should see "Configuration saved" flash message
    When I follow "Commerce/Sales/Quotes" on configuration sidebar
    And uncheck "Use default" for "Enable Quote (Store Front)" field
    And check "Enable Quote (Store Front)"
    And uncheck "Use default" for "Enable Guest Quote" field
    And check "Enable Guest Quote"
    And I save form
    Then I should see "Configuration saved" flash message

  Scenario: Visit add to shopping list forms as a visitor
    Given I proceed as the User
    When I am on the homepage
    Then Customer visitor cookie should not exist
    When I type "PSKU1" in "search"
    And I click "Search Button"
    Then I should see "Product1"
    Then Customer visitor cookie should not exist
    When I click "Product1"
    Then Customer visitor cookie should not exist

  Scenario: Visit quick order form and request for quote form as a visitor
    When I click "Quick Order Form"
    Then Customer visitor cookie should not exist
    When I fill "Quick Add Copy Paste Form" with:
      | Paste your order | PSKU1 1 |
    And I click "Verify Order"
    Then Customer visitor cookie should not exist
    When I click "Get Quote"
    Then I should see "Request A Quote"
    And I should see "Product1"
    And Customer visitor cookie should not exist

  Scenario: Send Quote to customer
    Given I proceed as the Admin
    When I go to Sales/Quotes
    And I click view Quote_1 in grid
    And I should see Quote with:
      | Internal Status | Draft   |
      | Customer Status | N/A     |
      | Website         | Default |
    And I should not see "Unique Guest link"
    When I click "Send to Customer"
    Then "Send to Customer Form" must contains values:
      | Apply template | quote_email_link_guest |
    When I fill "Send to Customer Form" with:
      | To | charlie@sheen.com |
    And click "Send"
    And I should see "Quote_1 successfully sent to customer" flash message
    And I should see "Unique Guest link"

  Scenario: Visit quote form as a visitor
    Given I proceed as the User
    When I visit guest quote link for quote Quote_1
    Then I should see "QUOTE #QUOTE_1"
    And Customer visitor cookie should not exist
